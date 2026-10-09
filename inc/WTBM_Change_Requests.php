<?php
/**
 * Customer change requests and manager approval.
 *
 * @package Theaterly
 */

// phpcs:disable WordPress.Files.FileName -- Follow the existing plugin naming convention.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Customer changes remain requests until a manager approves them. */
class WTBM_Change_Requests {
	/** Register private storage and the manager decision handler. */
	public static function boot() {
		add_action(
			'init',
			static function () {
				register_post_type(
					'wtbm_change_request',
					array(
						'public'       => false,
						'show_ui'      => false,
						'show_in_rest' => false,
						'rewrite'      => false,
						'supports'     => array( 'title' ),
					)
				);
			}
		);
		add_action( 'admin_post_wtbm_review_change', array( __CLASS__, 'review_action' ) );
	}

	/**
	 * Find recent requests for a booking, or all bookings for manager review.
	 *
	 * @param int $bid Booking ID.
	 * @return int[]
	 */
	public static function requests( $bid = 0 ) {
		$args = array(
			'post_type'   => 'wtbm_change_request',
			'post_status' => 'private',
			'numberposts' => $bid ? 20 : 100,
			'fields'      => 'ids',
			'orderby'     => 'ID',
			'order'       => 'DESC',
		);
		if ( $bid ) {
			$args['meta_key']   = '_wtbm_booking';
			$args['meta_value'] = absint( $bid );
		}
		if ( ! $bid ) {
			$pending_args               = $args;
			$pending_args['meta_key']   = '_wtbm_status';
			$pending_args['meta_value'] = 'pending';
			$pending_args['order']      = 'ASC';
			$args['numberposts']        = 20;
			$args['meta_query']         = array(
				array(
					'key'     => '_wtbm_status',
					'value'   => 'pending',
					'compare' => '!=',
				),
			);
			return array_merge( get_posts( $pending_args ), get_posts( $args ) );
		}
		return get_posts( $args );
	}

	/**
	 * Store a validated request without changing the booking, order or ticket.
	 *
	 * @param int    $bid Booking ID.
	 * @param string $kind Cancel or reschedule.
	 * @param array  $payload Requested seats/show and reason.
	 * @param string $key Guest order key.
	 * @return array|WP_Error
	 */
	public static function submit( $bid, $kind, $payload, $key = '' ) {
		$order = wc_get_order( get_post_meta( $bid, 'wtbm_order_id', true ) );
		if ( 'wtbm_booking' !== get_post_type( $bid ) || ! WTBM_Tickets::access( $order, $key ) ) {
			return new WP_Error( 'permission', __( 'Ticket access denied.', 'wptheaterly' ) );
		}
		$policy = WTBM_Tickets::customer_policy( $bid );
		if ( is_wp_error( $policy ) ) {
			return $policy; }
		if ( ! in_array( $kind, array( 'cancel', 'reschedule' ), true ) || in_array( $order->get_status(), array( 'cancelled', 'refunded', 'failed' ), true ) ) {
			return new WP_Error( 'request', __( 'This booking cannot be changed.', 'wptheaterly' ) );
		}
		return WTBM_Booking_Service::lock(
			'change|' . absint( $bid ),
			static function () use ( $bid, $kind, $payload, $order ) {
				foreach ( self::requests( $bid ) as $id ) {
					$state   = get_post_meta( $id, '_wtbm_status', true );
					$payment = wc_get_order( get_post_meta( $id, '_wtbm_payment_order', true ) );
					if ( 'pending' === $state || ( 'approved' === $state && $payment && $payment->needs_payment() ) ) {
						return array(
							'request_id' => $id,
							'message'    => __( 'A change request already exists for this booking. Check its status below.', 'wptheaterly' ),
						);
					}
				}
				$ids = (array) get_post_meta( $bid, 'wtbm_seat_ids', true );
				if ( ! $ids ) {
					return new WP_Error( 'seats', __( 'No active seats remain.', 'wptheaterly' ) ); }
				global $wpdb;
				if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wtbm_claims WHERE booking_id=%d AND admitted_at IS NOT NULL LIMIT 1", $bid ) ) ) {
					return new WP_Error( 'admitted', __( 'An admitted booking cannot be changed.', 'wptheaterly' ) );
				}
				$payload['reason'] = sanitize_text_field( $payload['reason'] ?? '' );
				if ( 'cancel' === $kind ) {
					$payload['ids'] = array_values( array_map( 'sanitize_text_field', (array) ( $payload['ids'] ?? array() ) ) );
					if ( ! $payload['ids'] || array_diff( $payload['ids'], $ids ) || count( $payload['ids'] ) !== count( array_unique( $payload['ids'] ) ) ) {
						return new WP_Error( 'seats', __( 'Choose valid seats to cancel.', 'wptheaterly' ) );
					}
				} else {
					$quote = WTBM_Booking_Service::quote( absint( $payload['movie'] ?? 0 ), absint( $payload['theater'] ?? 0 ), sanitize_text_field( $payload['date'] ?? '' ), sanitize_text_field( $payload['time'] ?? '' ), (array) ( $payload['ids'] ?? array() ) );
					if ( is_wp_error( $quote ) ) {
						return $quote; }
					$occupied = WTBM_Booking_Service::occupied( $quote['theater'], $quote['movie'], $quote['date'], $quote['time'], $bid );
					if ( array_intersect( $quote['ids'], $occupied ) ) {
						return new WP_Error( 'seats', __( 'Some requested seats are unavailable.', 'wptheaterly' ) ); }
					$payload = array_intersect_key( $quote, array_flip( array( 'movie', 'theater', 'date', 'time', 'ids', 'names', 'price' ) ) ) + array( 'reason' => $payload['reason'] );
				}
				$id = wp_insert_post(
					array(
						'post_type'   => 'wtbm_change_request',
						'post_status' => 'draft',
						'post_title'  => 'Booking change #' . $order->get_order_number(),
					),
					true
				);
				if ( is_wp_error( $id ) ) {
					return $id; }
				foreach ( array(
					'_wtbm_booking' => $bid,
					'_wtbm_order'   => $order->get_id(),
					'_wtbm_kind'    => $kind,
					'_wtbm_payload' => $payload,
					'_wtbm_version' => (string) get_post_meta( $bid, 'wtbm_ticket_version', true ),
					'_wtbm_status'  => 'pending',
				) as $name => $value ) {
					update_post_meta( $id, $name, $value ); }
				wp_update_post(
					array(
						'ID'          => $id,
						'post_status' => 'private',
					)
				);
				WTBM_Booking_Service::log(
					'change_requested',
					$bid,
					$order->get_id(),
					array(
						'request_id' => $id,
						'type'       => $kind,
						'seats'      => $payload['ids'],
					)
				);
				return array(
					'request_id' => $id,
					'message'    => __( 'Request submitted for admin approval. Your current booking and seats remain unchanged.', 'wptheaterly' ),
				);
			}
		);
	}

	/**
	 * Approve or reject a pending request once.
	 *
	 * @param int    $id Request ID.
	 * @param string $decision Approve or reject.
	 * @param string $note Manager reply.
	 * @return array|WP_Error
	 */
	public static function review( $id, $decision, $note = '' ) {
		if ( ! WTBM_Booking_Service::can( 'manage' ) ) {
			return new WP_Error( 'permission', __( 'Manager approval permission required.', 'wptheaterly' ) ); }
		if ( 'wtbm_change_request' !== get_post_type( $id ) || ! in_array( $decision, array( 'approve', 'reject' ), true ) ) {
			return new WP_Error( 'request', __( 'Invalid change request.', 'wptheaterly' ) ); }
		return WTBM_Booking_Service::lock(
			'review|' . absint( $id ),
			static function () use ( $id, $decision, $note ) {
				if ( 'pending' !== get_post_meta( $id, '_wtbm_status', true ) ) {
					return new WP_Error( 'reviewed', __( 'This request has already been reviewed.', 'wptheaterly' ) ); }
				$bid     = absint( get_post_meta( $id, '_wtbm_booking', true ) );
				$payload = (array) get_post_meta( $id, '_wtbm_payload', true );
				$result  = array( 'message' => __( 'Request rejected. The booking is unchanged.', 'wptheaterly' ) );
				if ( 'approve' === $decision ) {
					if ( (string) get_post_meta( $id, '_wtbm_version', true ) !== (string) get_post_meta( $bid, 'wtbm_ticket_version', true ) ) {
						return new WP_Error( 'changed', __( 'The ticket changed after this request was submitted. Reject this request and ask the customer to submit a new one.', 'wptheaterly' ) ); }
					$payload['_expected_version'] = (string) get_post_meta( $id, '_wtbm_version', true );
					$result                       = 'cancel' === get_post_meta( $id, '_wtbm_kind', true ) ? WTBM_Tickets::cancel( $bid, $payload['ids'], true, $payload['reason'], $payload['_expected_version'] ) : WTBM_Operations::edit( $bid, $payload, true );
					if ( is_wp_error( $result ) ) {
						return $result; }
					if ( ! empty( $result['payment_url'] ) ) {
						update_post_meta( $id, '_wtbm_payment_order', absint( get_post_meta( $bid, 'wtbm_change_payment', true ) ) ); }
				}
				update_post_meta( $id, '_wtbm_status', 'approve' === $decision ? 'approved' : 'rejected' );
				update_post_meta( $id, '_wtbm_reply', sanitize_text_field( $note ) );
				update_post_meta( $id, '_wtbm_reviewed_by', get_current_user_id() );
				update_post_meta( $id, '_wtbm_reviewed_at', current_time( 'mysql', true ) );
				WTBM_Booking_Service::log(
					'change_' . ( 'approve' === $decision ? 'approved' : 'rejected' ),
					$bid,
					absint( get_post_meta( $id, '_wtbm_order', true ) ),
					array(
						'request_id' => $id,
						'note'       => sanitize_text_field( $note ),
					)
				);
				return $result;
			}
		);
	}

	/** Handle a nonce-protected manager review and return to the Requests tab. */
	public static function review_action() {
		check_admin_referer( 'wtbm_review_change' );
		$result = self::review( absint( $_POST['request_id'] ?? 0 ), sanitize_key( $_POST['decision'] ?? '' ), sanitize_text_field( wp_unslash( $_POST['note'] ?? '' ) ) );
		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ), '', array( 'back_link' => true ) ); }
		wp_safe_redirect( admin_url( 'admin.php?page=mptrs_main_menu&tab_name=wtbm_requests' ) );
		exit;
	}

	/**
	 * Show request status on an already access-checked customer booking card.
	 *
	 * @param int $bid Booking ID.
	 */
	public static function customer_status( $bid ) {
		foreach ( self::requests( $bid ) as $id ) {
			$state   = get_post_meta( $id, '_wtbm_status', true );
			$kind    = get_post_meta( $id, '_wtbm_kind', true );
			$payment = wc_get_order( get_post_meta( $id, '_wtbm_payment_order', true ) );
			?>
			<div class="wtbm-change-request-status"><strong><?php echo esc_html( 'cancel' === $kind ? __( 'Cancellation request', 'wptheaterly' ) : __( 'Rescheduling request', 'wptheaterly' ) ); ?></strong><span class="wtbm-status-badge"><?php echo esc_html( 'pending' === $state ? __( 'Awaiting admin approval', 'wptheaterly' ) : ( 'approved' === $state ? __( 'Approved', 'wptheaterly' ) : __( 'Rejected', 'wptheaterly' ) ) ); ?></span><p><?php echo esc_html( get_post_meta( $id, '_wtbm_reply', true ) ); ?></p>
			<?php
			if ( $payment && $payment->needs_payment() ) {
				?>
				<p><?php esc_html_e( 'Approved. Pay the price difference to confirm the new booking; your current seats remain valid until then.', 'wptheaterly' ); ?></p><a class="button" href="<?php echo esc_url( $payment->get_checkout_payment_url() ); ?>"><?php esc_html_e( 'Pay approved price difference', 'wptheaterly' ); ?></a><?php } ?></div>
			<?php
		}
	}

	/** Render the manager review queue in the main Theaterly workspace. */
	public static function admin_page() {
		if ( ! WTBM_Booking_Service::can( 'manage' ) ) {
			return; }
		?>
		<div class="wtbm-operations"><section class="wtbm-ops-card wtbm-requests-card"><header class="wtbm-operation-heading"><div><span class="wtbm-report-kicker"><?php esc_html_e( 'Customer care', 'wptheaterly' ); ?></span><h2><?php esc_html_e( 'Change requests', 'wptheaterly' ); ?></h2><p><?php esc_html_e( 'Review customer cancellations and rescheduling. No booking changes happen before approval.', 'wptheaterly' ); ?></p></div><span class="wtbm-operation-badge"><?php esc_html_e( 'Manager approval required', 'wptheaterly' ); ?></span></header><div class="wtbm-ops-table"><table><thead><tr><th><?php esc_html_e( 'Booking', 'wptheaterly' ); ?></th><th><?php esc_html_e( 'Requested change', 'wptheaterly' ); ?></th><th><?php esc_html_e( 'Status', 'wptheaterly' ); ?></th><th><?php esc_html_e( 'Review', 'wptheaterly' ); ?></th></tr></thead><tbody>
		<?php
		$requests = self::requests();
		foreach ( $requests as $id ) {
			$bid     = absint( get_post_meta( $id, '_wtbm_booking', true ) );
			$payload = (array) get_post_meta( $id, '_wtbm_payload', true );
			$kind    = get_post_meta( $id, '_wtbm_kind', true );
			$state   = get_post_meta( $id, '_wtbm_status', true );
			$names   = (array) get_post_meta( $bid, 'wtbm_seats', true );
			$ids     = (array) get_post_meta( $bid, 'wtbm_seat_ids', true );
			$map     = count( $ids ) === count( $names ) ? array_combine( $ids, $names ) : array();
			?>
			<tr><td><strong><?php echo esc_html( 'BK' . str_pad( get_post_meta( $id, '_wtbm_order', true ), 6, '0', STR_PAD_LEFT ) ); ?></strong><p><?php echo esc_html( get_the_title( get_post_meta( $bid, 'wtbm_movie_id', true ) ) ); ?></p><small><?php echo esc_html( get_the_date( 'd M Y, H:i', $id ) ); ?></small></td><td><strong><?php echo esc_html( 'cancel' === $kind ? __( 'Cancel seats', 'wptheaterly' ) : __( 'Reschedule', 'wptheaterly' ) ); ?></strong><p>
			<?php
			echo esc_html(
				implode(
					', ',
					'cancel' === $kind ? array_map(
						static function ( $seat ) use ( $map ) {
							return $map[ $seat ] ?? $seat;
						},
						$payload['ids'] ?? array()
					) : ( $payload['names'] ?? array() )
				)
			);
			?>
							</p>
							<?php
							if ( 'reschedule' === $kind ) {
								?>
	<p><?php echo esc_html( get_the_title( $payload['movie'] ) . ' · ' . get_the_title( $payload['theater'] ) . ' · ' . $payload['date'] . ' ' . $payload['time'] ); ?></p><?php } ?><p><?php echo esc_html( $payload['reason'] ?? '' ); ?></p></td><td><span class="wtbm-status-badge"><?php echo esc_html( ucfirst( $state ) ); ?></span></td><td>
			<?php
			if ( 'pending' === $state ) {
				?>
	<form class="wtbm-review-request" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php wp_nonce_field( 'wtbm_review_change' ); ?><input type="hidden" name="action" value="wtbm_review_change"><input type="hidden" name="request_id" value="<?php echo esc_attr( $id ); ?>"><label><?php esc_html_e( 'Reply to customer', 'wptheaterly' ); ?><input name="note" maxlength="500"></label><div><button class="button button-primary" name="decision" value="approve"><?php esc_html_e( 'Approve', 'wptheaterly' ); ?></button><button class="button" name="decision" value="reject"><?php esc_html_e( 'Reject', 'wptheaterly' ); ?></button></div></form>
				<?php
			} else {
				?>
	<p><?php echo esc_html( get_post_meta( $id, '_wtbm_reply', true ) ); ?></p><?php } ?></td></tr>
			<?php
		}
		if ( ! $requests ) {
			?>
		<tr><td colspan="4" class="wtbm-report-empty"><?php esc_html_e( 'No customer requests yet.', 'wptheaterly' ); ?></td></tr>
			<?php
		}
		?>
		</tbody></table></div></section></div>
		<?php
	}
}
WTBM_Change_Requests::boot();
