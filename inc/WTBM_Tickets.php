<?php
/**
 * Secure ticket access, customer changes and admission.
 *
 * @package Theaterly
 */

// phpcs:disable WordPress.Files.FileName -- Preserve the plugin's established class/file naming convention.
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
/**
 * Theaterly operational service.
 */
class WTBM_Tickets {
	/**
	 * Boot.
	 */
	public static function boot() {
		add_action( 'template_redirect', array( __CLASS__, 'private_headers' ) );
		add_shortcode( 'wtbm_my_tickets', array( __CLASS__, 'page' ) );
		add_action(
			'init',
			function () {
				add_rewrite_endpoint( 'theater-tickets', EP_ROOT | EP_PAGES );
			}
		);
		add_filter(
			'woocommerce_account_menu_items',
			function ( $items ) {
				$items['theater-tickets'] = __( 'My theater tickets', 'wptheaterly' );
				return $items;
			}
		);
		add_action(
			'woocommerce_account_theater-tickets_endpoint',
			function () {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- page() escapes each dynamic field and returns a complete form.
				echo WTBM_Tickets::page();
			}
		);
		add_action( 'wp_ajax_wtbm_ticket_qr', array( __CLASS__, 'qr' ) );
		add_action( 'wp_ajax_nopriv_wtbm_ticket_qr', array( __CLASS__, 'qr' ) );
		add_action( 'admin_post_wtbm_ticket_calendar', array( __CLASS__, 'calendar' ) );
		add_action( 'admin_post_nopriv_wtbm_ticket_calendar', array( __CLASS__, 'calendar' ) );
		add_action( 'wp_ajax_wtbm_ticket_action', array( __CLASS__, 'action' ) );
		add_action( 'wp_ajax_nopriv_wtbm_ticket_action', array( __CLASS__, 'action' ) );
		add_action( 'wp_ajax_wtbm_admit_ticket', array( __CLASS__, 'admit_ajax' ) );
		add_action( 'admin_post_wtbm_ticket_action', array( __CLASS__, 'action' ) );
		add_action( 'admin_post_nopriv_wtbm_ticket_action', array( __CLASS__, 'action' ) );
		add_filter(
			'woocommerce_get_query_vars',
			function ( $vars ) {
				$vars['theater-tickets'] = 'theater-tickets';
				return $vars;
			}
		);
	}
	/**
	 * Token.
	 *
	 * @param mixed $bid Bid.
	 * @param mixed $seat Seat.
	 */
	public static function token( $bid, $seat = '' ) {
		$version = max( 1, (int) get_post_meta( $bid, 'wtbm_ticket_version', true ) );
		$body    = absint( $bid ) . ':' . $version . ':' . $seat;
		return $body . ':' . hash_hmac( 'sha256', $body, wp_salt( 'auth' ) );
	}
	/**
	 * Parse.
	 *
	 * @param mixed $token Token.
	 */
	public static function parse( $token ) {
		$parts = explode( ':', trim( $token ) );
		if ( 4 !== count( $parts ) || ! ctype_digit( $parts[0] ) || 'wtbm_booking' !== get_post_type( (int) $parts[0] ) || ! hash_equals( self::token( (int) $parts[0], $parts[2] ), trim( $token ) ) ) {
			return new WP_Error( 'ticket', __( 'Invalid or revoked ticket.', 'wptheaterly' ) ); }
		return array(
			'booking' => (int) $parts[0],
			'seat'    => $parts[2],
		);
	}
	/**
	 * Access.
	 *
	 * @param mixed $order Order.
	 * @param mixed $key Key.
	 * @param mixed $email Email.
	 */
	public static function access( $order, $key = '', $email = '' ) {
		if ( ! $order ) {
			return false; }
		if ( WTBM_Booking_Service::can( 'manage' ) || WTBM_Booking_Service::can( 'sell' ) ) {
			return true; }
		if ( get_current_user_id() && (int) $order->get_customer_id() === get_current_user_id() ) {
			return true; }
		return $key && hash_equals( $order->get_order_key(), $key ) && ( ! $email || strtolower( $order->get_billing_email() ) === strtolower( $email ) );
	}
	/**
	 * Pdf url.
	 *
	 * @param mixed $order Order.
	 * @param mixed $key Key.
	 */
	public static function pdf_url( $order, $key = '' ) {
		return wp_nonce_url(
			add_query_arg(
				array(
					'action'    => 'wtbm_generate_pdf',
					'order_id'  => $order->get_id(),
					'order_key' => $key ? $key : $order->get_order_key(),
				),
				admin_url( 'admin-ajax.php' )
			),
			'wtbm_generate_pdf'
		);
	}
	/**
	 * Customer policy.
	 *
	 * @param mixed $bid Bid.
	 */
	public static function customer_policy( $bid ) {
		if ( ! WTBM_Booking_Service::setting( 'customer_changes', 1 ) ) {
			return new WP_Error( 'policy', __( 'Customer changes are disabled. Contact the box office.', 'wptheaterly' ) ); }
		$stamp = WTBM_Booking_Service::timestamp( get_post_meta( $bid, 'wtbm_order_date', true ), get_post_meta( $bid, 'wtbm_order_time', true ) );
		if ( $stamp <= time() + (int) WTBM_Booking_Service::setting( 'change_cutoff_minutes', 120 ) * 60 ) {
			return new WP_Error( 'cutoff', __( 'The cancellation/rescheduling deadline has passed.', 'wptheaterly' ) ); }
		return true;
	}
	/**
	 * Cancel.
	 *
	 * @param mixed $bid Bid.
	 * @param mixed $remove Remove.
	 * @param mixed $staff Staff.
	 * @param mixed $reason Reason.
	 * @param mixed $expected_version Optional request ticket version.
	 */
	public static function cancel( $bid, $remove = array(), $staff = false, $reason = '', $expected_version = null ) {
		if ( 'wtbm_booking' !== get_post_type( $bid ) || ( $staff && ! WTBM_Booking_Service::can( 'manage' ) ) ) {
			return new WP_Error( 'booking', __( 'Booking not found or permission denied.', 'wptheaterly' ) ); }
		if ( ! $staff ) {
			$policy = self::customer_policy( $bid );
			if ( is_wp_error( $policy ) ) {
				return $policy; }
		}
		return WTBM_Booking_Service::lock(
			'change|' . $bid,
			function () use ( $bid, $remove, $staff, $reason, $expected_version ) {
				if ( null !== $expected_version && (string) get_post_meta( $bid, 'wtbm_ticket_version', true ) !== (string) $expected_version ) {
					return new WP_Error( 'changed', __( 'This booking changed after the request was submitted.', 'wptheaterly' ) ); }
				global $wpdb;
				$order = wc_get_order( get_post_meta( $bid, 'wtbm_order_id', true ) );
				if ( ! $order ) {
					return new WP_Error( 'order', __( 'Order not found.', 'wptheaterly' ) ); }
				WTBM_Booking_Service::sync_order( $order );
				$item  = $order->get_item( absint( get_post_meta( $bid, 'wtbm_item_id', true ) ) );
				$ids   = (array) get_post_meta( $bid, 'wtbm_seat_ids', true );
				$names = (array) get_post_meta( $bid, 'wtbm_seats', true );
				if ( ! $item || ! $ids ) {
					return new WP_Error( 'empty', __( 'This booking has no active seats.', 'wptheaterly' ) ); }
				$remove = $remove ? $remove : $ids;
				if ( array_diff( $remove, $ids ) || count( $remove ) !== count( array_unique( $remove ) ) ) {
					return new WP_Error( 'seats', __( 'Invalid seats for cancellation.', 'wptheaterly' ) ); }
				foreach ( $remove as $id ) {
					if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wtbm_claims WHERE booking_id=%d AND seat_id=%s AND admitted_at IS NOT NULL", $bid, $id ) ) ) {
						return new WP_Error( 'admitted', __( 'Checked-in seats cannot be cancelled.', 'wptheaterly' ) ); }
				}
				$net      = max( 0, (float) $item->get_total() - (float) $order->get_total_refunded_for_item( $item->get_id() ) );
				$weights  = array();
				$snapshot = get_post_meta( $bid, 'wtbm_plan_snapshot', true );
				foreach ( $snapshot['seat_data'] ?? array() as $seat ) {
					$weights[ 'seat_' . $seat['id'] ] = max( 0, (float) ( $seat['price'] ?? 0 ) ); }
				$total_weight = array_sum( array_intersect_key( $weights, array_flip( $ids ) ) );
				$ratio        = $total_weight > 0 ? array_sum( array_intersect_key( $weights, array_flip( $remove ) ) ) / $total_weight : count( $remove ) / count( $ids );
				$amount       = round( $net * $ratio, wc_get_price_decimals() );
				$manual       = false;
				if ( $order->is_paid() && $amount > 0 ) {
					$gateways  = WC()->payment_gateways()->payment_gateways();
					$gateway   = $gateways[ $order->get_payment_method() ] ?? null;
					$automatic = $gateway && $gateway->supports( 'refunds' );
					$manual    = ! $automatic;
					$taxes     = array();
					foreach ( $item->get_taxes()['total'] ?? array() as $rate => $tax ) {
						$taxes[ $rate ] = round( max( 0, (float) $tax - (float) $order->get_tax_refunded_for_item( $item->get_id(), $rate ) ) * $ratio, wc_get_price_decimals() ); }
					$automatic = WTBM_Operations::automatic_refund( $order, $amount + array_sum( $taxes ) );
					$manual    = ! $automatic;
					$refund    = wc_create_refund(
						array(
							'order_id'       => $order->get_id(),
							'amount'         => $amount + array_sum( $taxes ),
							'reason'         => sanitize_text_field( $reason ) ? sanitize_text_field( $reason ) : __( 'Theater ticket cancellation', 'wptheaterly' ),
							'refund_payment' => (bool) $automatic,
							'restock_items'  => false,
							'line_items'     => array(
								$item->get_id() => array(
									'qty'          => 0,
									'refund_total' => $amount,
									'refund_tax'   => $taxes,
								),
							),
						)
					);
					if ( is_wp_error( $refund ) ) {
						return $refund; }
					if ( $manual ) {
						$order->add_order_note( __( 'Manual reimbursement required for the recorded theater refund.', 'wptheaterly' ) ); }
				}
				$remaining       = array_values( array_diff( $ids, $remove ) );
				$remaining_names = array();
				foreach ( $remaining as $id ) {
					$remaining_names[] = $names[ array_search( $id, $ids, true ) ]; }
				$item->update_meta_data( '_wtbm_selected_seat_ids', $remaining );
				$item->update_meta_data( '_wtbm_selected_seats', $remaining_names );
				$item->update_meta_data( '_wtbm_number_of_seats', count( $remaining ) );
				$item->save();
				foreach ( $remove as $id ) {
					$wpdb->delete(
						$wpdb->prefix . 'wtbm_claims',
						array(
							'booking_id' => $bid,
							'seat_id'    => $id,
						)
					); }
				update_post_meta( $bid, 'wtbm_seat_ids', $remaining );
				update_post_meta( $bid, 'wtbm_seats', $remaining_names );
				update_post_meta( $bid, 'wtbm_number_of_seats', count( $remaining ) );
				if ( ! $remaining ) {
					wp_update_post(
						array(
							'ID'          => $bid,
							'post_status' => 'draft',
						)
					);
					if ( 1 === count( $order->get_items() ) && ! $order->has_status( 'refunded' ) ) {
									$order->update_status( 'cancelled' ); }
				}
				WTBM_Booking_Service::revoke( $bid );
				WTBM_Booking_Service::log(
					'seats_cancelled',
					$bid,
					$order->get_id(),
					array(
						'seats'                => $remove,
						'refund'               => $amount,
						'manual_reimbursement' => $manual,
						'reason'               => sanitize_text_field( $reason ),
					)
				);
				do_action( 'wtbm_ticket_changed', $order->get_id() );
				return array(
					'message' => $manual ? __( 'Seats cancelled. Refund recorded; the box office must reimburse the payment manually.', 'wptheaterly' ) : __( 'Seats cancelled and refund processed where payment was collected.', 'wptheaterly' ),
					'refund'  => $amount,
				);
			}
		);
	}
	/**
	 * Admit ajax.
	 */
	public static function admit_ajax() {
		check_ajax_referer( 'wtbm_operations', 'nonce' );
		WTBM_Operations::response( self::admit( sanitize_text_field( wp_unslash( $_POST['token'] ?? '' ) ), absint( $_POST['theater'] ?? 0 ), sanitize_text_field( wp_unslash( $_POST['show_date'] ?? current_time( 'Y-m-d' ) ) ) ) );
	}
	/**
	 * Admit.
	 *
	 * @param mixed $token Token.
	 * @param mixed $theater Theater.
	 * @param mixed $date Date.
	 */
	public static function admit( $token, $theater = 0, $date = '' ) {
		if ( ! WTBM_Booking_Service::can( 'admit' ) ) {
			return new WP_Error( 'permission', __( 'Check-in permission required.', 'wptheaterly' ) ); }
		if ( preg_match( '/^BK(\d+)$/i', trim( $token ), $match ) ) {
			$bookings = WTBM_Booking_Service::bookings( absint( $match[1] ) );
			$matches  = array_values(
				array_filter(
					$bookings,
					static function ( $bid ) use ( $theater, $date ) {
						return ( ! $theater || (int) get_post_meta( $bid, 'wtbm_theater_id', true ) === $theater ) && ( ! $date || get_post_meta( $bid, 'wtbm_order_date', true ) === $date );
					}
				)
			);
			if ( ! $bookings ) {
				return new WP_Error( 'booking', __( 'Booking number not found. Check the number or scan the ticket QR.', 'wptheaterly' ) ); }
			if ( ! $matches ) {
				return new WP_Error( 'show', __( 'Booking found, but it belongs to another show date or screen. Select the date and screen shown on the ticket.', 'wptheaterly' ) ); }
			if ( 1 !== count( $matches ) ) {
				return new WP_Error( 'ambiguous', __( 'This order contains multiple bookings. Select a screen or scan the specific ticket QR.', 'wptheaterly' ) ); }
			$token = self::token( $matches[0] );
		}
		$parsed = self::parse( $token );
		if ( is_wp_error( $parsed ) ) {
			return $parsed; }
		$bid = $parsed['booking'];
		return WTBM_Booking_Service::lock(
			'change|' . $bid,
			function () use ( $bid, $parsed, $theater, $date, $token ) {
				global $wpdb;
				$fresh = self::parse( $token );
				if ( is_wp_error( $fresh ) ) {
					return $fresh; }
				$order = wc_get_order( get_post_meta( $bid, 'wtbm_order_id', true ) );
				if ( ! $order || ! $order->is_paid() || 'draft' === get_post_status( $bid ) || get_post_meta( $bid, 'wtbm_allocation_error', true ) ) {
					return new WP_Error( 'unpaid', __( 'Ticket is unpaid, cancelled or awaiting seat resolution.', 'wptheaterly' ) ); }
				if ( ( $theater && (int) get_post_meta( $bid, 'wtbm_theater_id', true ) !== $theater ) || ( $date && get_post_meta( $bid, 'wtbm_order_date', true ) !== $date ) ) {
					return new WP_Error( 'show', __( 'Ticket belongs to another screen or date.', 'wptheaterly' ) ); }
				$ids = (array) get_post_meta( $bid, 'wtbm_seat_ids', true );
				if ( $parsed['seat'] ) {
					if ( ! in_array( $parsed['seat'], $ids, true ) ) {
								return new WP_Error( 'seat', __( 'Seat was cancelled.', 'wptheaterly' ) );
					} $ids = array( $parsed['seat'] ); }
				if ( ! $ids ) {
					return new WP_Error( 'empty', __( 'Ticket has no active seats.', 'wptheaterly' ) ); }
				WTBM_Booking_Service::sync_order( $order );
				$rows    = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wtbm_claims WHERE booking_id=%d", $bid ), OBJECT_K );
				$by_seat = array();
				foreach ( $rows as $row ) {
					$by_seat[ $row->seat_id ] = $row; }
				foreach ( $ids as $id ) {
					if ( ! isset( $by_seat[ $id ] ) || $by_seat[ $id ]->admitted_at ) {
								return new WP_Error( 'duplicate', __( 'Ticket has already been used or is not allocated.', 'wptheaterly' ) ); }
				}
				foreach ( $ids as $id ) {
					$wpdb->update(
						$wpdb->prefix . 'wtbm_claims',
						array(
							'admitted_at' => current_time( 'mysql', true ),
							'admitted_by' => get_current_user_id(),
						),
						array(
							'booking_id' => $bid,
							'seat_id'    => $id,
						)
					); }
				WTBM_Booking_Service::log( 'admitted', $bid, $order->get_id(), array( 'seats' => $ids ) );
				$seat_names = self::seat_names( $bid, $ids );
				return array(
					'message'    => __( 'Check-in accepted.', 'wptheaterly' ),
					'booking'    => $bid,
					'movie'      => get_the_title( get_post_meta( $bid, 'wtbm_movie_id', true ) ),
					'seats'      => $ids,
					'seat_names' => $seat_names,
					'time'       => get_post_meta( $bid, 'wtbm_order_time', true ),
				);
			}
		);
	}
	/**
	 * Resolve stored booking seat labels for allocation IDs.
	 *
	 * @param int   $bid Booking ID.
	 * @param array $ids Seat IDs.
	 * @return array
	 */
	public static function seat_names( $bid, $ids ) {
		$booked_ids   = (array) get_post_meta( $bid, 'wtbm_seat_ids', true );
		$booked_names = (array) get_post_meta( $bid, 'wtbm_seats', true );
		$name_map     = array();
		$snapshot     = get_post_meta( $bid, 'wtbm_plan_snapshot', true );
		foreach ( $snapshot['seat_data'] ?? array() as $seat ) {
			$name_map[ 'seat_' . $seat['id'] ] = (string) $seat['seat_number']; }
		foreach ( $booked_ids as $index => $id ) {
			if ( isset( $booked_names[ $index ] ) && '' !== (string) $booked_names[ $index ] ) {
				$name_map[ $id ] = (string) $booked_names[ $index ]; }
		}
		return array_map(
			static function ( $id ) use ( $name_map ) {
				return $name_map[ $id ] ?? $id;
			},
			$ids
		);
	}

	/**
	 * Read check-in records for a displayed booking page in a single query.
	 *
	 * @param array $bids Booking IDs.
	 * @return array
	 */
	public static function checkin_data( $bids ) {
		$bids = array_values( array_unique( array_filter( array_map( 'absint', $bids ) ) ) );
		if ( ! $bids ) {
			return array(); }
		global $wpdb;
		$placeholders = implode( ',', array_fill( 0, count( $bids ), '%d' ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Only generated %d placeholders and the trusted table prefix are interpolated.
		$rows  = $wpdb->get_results( $wpdb->prepare( "SELECT booking_id,seat_id,admitted_at,admitted_by FROM {$wpdb->prefix}wtbm_claims WHERE booking_id IN ($placeholders) AND admitted_at IS NOT NULL ORDER BY admitted_at ASC,id ASC", $bids ) );
		$users = array();
		$uids  = array_values( array_unique( array_filter( array_map( 'absint', wp_list_pluck( $rows, 'admitted_by' ) ) ) ) );
		if ( $uids ) {
			foreach ( get_users( array( 'include' => $uids ) ) as $user ) {
				$users[ $user->ID ] = $user->display_name; }
		}
		$result = array();
		$labels = array();
		foreach ( $bids as $bid ) {
			$ids = (array) get_post_meta( $bid, 'wtbm_seat_ids', true );
			$labels[ $bid ] = $ids ? array_combine( $ids, self::seat_names( $bid, $ids ) ) : array();
			$result[ $bid ] = array(
				'total'  => count( $ids ),
				'count'  => 0,
				'groups' => array(),
			); }
		foreach ( $rows as $row ) {
			if ( ! isset( $labels[ $row->booking_id ][ $row->seat_id ] ) ) {
				continue; }
			$group = $row->admitted_at . '|' . $row->admitted_by;
			if ( ! isset( $result[ $row->booking_id ]['groups'][ $group ] ) ) {
				$result[ $row->booking_id ]['groups'][ $group ] = array(
					'seats' => array(),
					'time'  => wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $row->admitted_at . ' UTC' ), wp_timezone() ),
					'staff' => $users[ $row->admitted_by ] ?? __( 'Staff unavailable', 'wptheaterly' ),
				);
			}
			$result[ $row->booking_id ]['groups'][ $group ]['seats'][] = $labels[ $row->booking_id ][ $row->seat_id ];
			++$result[ $row->booking_id ]['count'];
		}
		return $result;
	}

	/**
	 * Action.
	 */
	public static function action() {
		check_admin_referer( 'wtbm_customer_ticket', 'nonce' );
		$bid   = absint( $_POST['booking'] ?? 0 );
		$order = wc_get_order( get_post_meta( $bid, 'wtbm_order_id', true ) );
		$key   = sanitize_text_field( wp_unslash( $_POST['order_key'] ?? '' ) );
		if ( ! self::access( $order, $key ) ) {
			wp_die( esc_html__( 'Ticket access denied.', 'wptheaterly' ), '', array( 'response' => 403 ) ); }
		$verb   = sanitize_key( $_POST['verb'] ?? '' );
		$policy = 'resend' === $verb ? true : self::customer_policy( $bid );
		if ( is_wp_error( $policy ) ) {
			if ( wp_doing_ajax() ) {
				WTBM_Operations::response( $policy ); }
			wp_die( esc_html( $policy->get_error_message() ) ); }
		if ( 'cancel' === $verb ) {
			$seats  = array_map( 'sanitize_text_field', (array) wp_unslash( $_POST['seats'] ?? array() ) );
			$result = $seats ? WTBM_Change_Requests::submit(
				$bid,
				'cancel',
				array(
					'ids'    => $seats,
					'reason' => sanitize_text_field( wp_unslash( $_POST['reason'] ?? '' ) ),
				),
				$key
			) : new WP_Error( 'seats', __( 'Select at least one seat to cancel.', 'wptheaterly' ) ); } elseif ( 'reschedule' === $verb ) {
			$result = WTBM_Change_Requests::submit(
				$bid,
				'reschedule',
				array(
					'movie'   => absint( $_POST['movie'] ?? 0 ),
					'theater' => absint( $_POST['theater'] ?? 0 ),
					'date'    => sanitize_text_field( wp_unslash( $_POST['date'] ?? '' ) ),
					'time'    => sanitize_text_field( wp_unslash( $_POST['time'] ?? '' ) ),
					'ids'     => array_filter( array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $_POST['seat_ids'] ?? '' ) ) ) ) ),
					'reason'  => sanitize_text_field( wp_unslash( $_POST['reason'] ?? '' ) ),
				),
				$key
			); } elseif ( 'resend' === $verb ) {
					$result = WTBM_Notifications::queue( $order->get_id(), true ); } else {
				$result = new WP_Error( 'action', __( 'Invalid ticket action.', 'wptheaterly' ) ); }
					if ( wp_doing_ajax() ) {
						WTBM_Operations::response( $result ); }
					if ( ! is_wp_error( $result ) && ! empty( $result['payment_url'] ) ) {
						wp_safe_redirect( $result['payment_url'] );
						exit; }
					wp_die( esc_html( is_wp_error( $result ) ? $result->get_error_message() : ( $result['message'] ?? __( 'Request received.', 'wptheaterly' ) ) ), esc_html__( 'Theaterly ticket update', 'wptheaterly' ), array( 'back_link' => true ) );
	}
	/**
	 * Private headers.
	 */
	public static function private_headers() {
		if ( is_page( (int) get_option( 'wtbm_tickets_page' ) ) || ( function_exists( 'is_account_page' ) && is_account_page() ) ) {
			nocache_headers();
			header( 'Referrer-Policy: no-referrer' );
			header( 'X-Robots-Tag: noindex, nofollow' );
		}
	}
	/**
	 * Page.
	 */
	/** Return a private QR image for an owned paid ticket. */
	public static function qr() {
		check_ajax_referer( 'wtbm_customer_ticket', 'nonce' );
		$bid   = absint( $_POST['booking'] ?? 0 );
		$order = wc_get_order( get_post_meta( $bid, 'wtbm_order_id', true ) );
		if ( ! self::access( $order, sanitize_text_field( wp_unslash( $_POST['order_key'] ?? '' ) ) ) || ! $order->is_paid() || ! get_post_meta( $bid, 'wtbm_seat_ids', true ) ) {
			WTBM_Operations::response( new WP_Error( 'ticket', __( 'A valid paid ticket is required.', 'wptheaterly' ) ) ); }
		try {
			$code = self::token( $bid );
			$png  = ( new \Mpdf\QrCode\Output\Png() )->output( new \Mpdf\QrCode\QrCode( $code ), 320 );
			WTBM_Operations::response(
				array(
					'image' => 'data:image/png;base64,' . base64_encode( $png ),
					'code'  => $code,
				)
			);
		} catch ( Throwable $error ) {
			WTBM_Operations::response( new WP_Error( 'qr', __( 'QR display is unavailable. Download the PDF ticket instead.', 'wptheaterly' ) ) ); }
	}
	/** Download an owned booking as an iCalendar event. */
	public static function calendar() {
		$bid   = absint( $_GET['booking'] ?? 0 );
		$order = wc_get_order( get_post_meta( $bid, 'wtbm_order_id', true ) );
		if ( ! self::access( $order, sanitize_text_field( wp_unslash( $_GET['order_key'] ?? '' ) ) ) || ! get_post_meta( $bid, 'wtbm_seat_ids', true ) || in_array( $order->get_status(), array( 'cancelled', 'refunded', 'failed' ), true ) ) {
			wp_die( esc_html__( 'Ticket access denied.', 'wptheaterly' ), '', array( 'response' => 403 ) ); }
		$content = self::calendar_content( $bid );
		nocache_headers();
		header( 'Content-Type: text/calendar; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="theaterly-show.ics"' );
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Fields are escaped for iCalendar, not HTML.
		echo $content;
		exit;
	}

	/**
	 * Build an iCalendar event from a booking using UTC event times.
	 *
	 * @param int $bid Booking ID.
	 * @return string
	 */
	public static function calendar_content( $bid ) {
		$movie    = get_post_meta( $bid, 'wtbm_movie_id', true );
		$start    = WTBM_Booking_Service::timestamp( get_post_meta( $bid, 'wtbm_order_date', true ), get_post_meta( $bid, 'wtbm_order_time', true ) );
		$duration = get_post_meta( $movie, 'wtbp_movie_duration', true );
		preg_match( '/(\d+)\s*h/i', $duration, $hours );
		preg_match( '/(\d+)\s*m/i', $duration, $minutes );
		$length = (int) ( $hours[1] ?? 0 ) * 60 + (int) ( $minutes[1] ?? 0 );
		$escape = static function ( $value ) {
			return str_replace( array( '\\', "\r", "\n", ',', ';' ), array( '\\\\', '', '\\n', '\\,', '\\;' ), wp_strip_all_tags( $value ) );
		};
		$lines  = array( 'BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//MagePeople//Theaterly//EN', 'BEGIN:VEVENT', 'UID:wtbm-' . $bid . '@' . wp_parse_url( home_url(), PHP_URL_HOST ), 'DTSTAMP:' . gmdate( 'Ymd\THis\Z' ), 'DTSTART:' . gmdate( 'Ymd\THis\Z', $start ), 'DTEND:' . gmdate( 'Ymd\THis\Z', $start + max( 1, $length ? $length : 120 ) * 60 ), 'SUMMARY:' . $escape( get_the_title( $movie ) ), 'LOCATION:' . $escape( get_the_title( get_post_meta( $bid, 'wtbm_theater_id', true ) ) ), 'DESCRIPTION:' . $escape( __( 'Seats: ', 'wptheaterly' ) . implode( ', ', (array) get_post_meta( $bid, 'wtbm_seats', true ) ) ), 'END:VEVENT', 'END:VCALENDAR' );
		return implode( "\r\n", $lines ) . "\r\n";
	}

	/**
	 * Render the private customer ticket dashboard.
	 *
	 * @return string
	 */
	public static function page() {
		if ( ! function_exists( 'wc_get_orders' ) ) {
			return esc_html__( 'Ticket lookup requires WooCommerce.', 'wptheaterly' ); }
		$orders = array();
		$key    = '';
		if ( ! empty( $_POST['wtbm_lookup'] ) ) {
			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'wtbm_lookup' ) ) {
				return esc_html__( 'Invalid lookup request.', 'wptheaterly' ); }
			$key   = sanitize_text_field( wp_unslash( $_POST['order_key'] ?? '' ) );
			$order = wc_get_order( absint( $_POST['order_id'] ?? 0 ) );
			if ( self::access( $order, $key, sanitize_email( wp_unslash( $_POST['email'] ?? '' ) ) ) ) {
				$orders = array( $order ); }
		} elseif ( ! empty( $_GET['order_id'] ) && ( ! empty( $_GET['order_key'] ) || WTBM_Booking_Service::can( 'manage' ) ) ) {
			$key   = sanitize_text_field( wp_unslash( $_GET['order_key'] ?? '' ) );
			$order = wc_get_order( absint( $_GET['order_id'] ) );
			if ( self::access( $order, $key ) ) {
				$orders = array( $order );
			}
		} elseif ( is_user_logged_in() ) {
			$orders = wc_get_orders(
				array(
					'customer_id' => get_current_user_id(),
					'limit'       => 100,
					'orderby'     => 'date',
					'order'       => 'DESC',
				)
			); }
		$cards    = array();
		$upcoming = 0;
		$seats    = 0;
		foreach ( $orders as $order ) {
			foreach ( WTBM_Booking_Service::bookings( $order->get_id() ) as $bid ) {
				$cards[] = array( $bid, $order );
				$stamp   = WTBM_Booking_Service::timestamp( get_post_meta( $bid, 'wtbm_order_date', true ), get_post_meta( $bid, 'wtbm_order_time', true ) );
				if ( $stamp > time() && get_post_meta( $bid, 'wtbm_seat_ids', true ) && ! in_array( $order->get_status(), array( 'cancelled', 'refunded', 'failed' ), true ) ) {
					++$upcoming;
					$seats += count( (array) get_post_meta( $bid, 'wtbm_seat_ids', true ) ); }
			}
		}
		ob_start(); ?>
		<div class="wtbm-operations wtbm-tickets-dashboard">
		<header class="wtbm-tickets-hero"><div><span class="wtbm-ticket-eyebrow"><?php esc_html_e( 'Your cinema companion', 'wptheaterly' ); ?></span><h2><?php esc_html_e( 'Your tickets, ready for the show', 'wptheaterly' ); ?></h2><p><?php esc_html_e( 'Find your seats, download your tickets and manage your next movie visit.', 'wptheaterly' ); ?></p></div><span class="wtbm-ticket-hero-icon" aria-hidden="true">✦</span></header>
		<div class="wtbm-ticket-stats"><article><span><?php esc_html_e( 'Upcoming visits', 'wptheaterly' ); ?></span><strong><?php echo esc_html( $upcoming ); ?></strong></article><article><span><?php esc_html_e( 'Reserved seats', 'wptheaterly' ); ?></span><strong><?php echo esc_html( $seats ); ?></strong></article><article><span><?php esc_html_e( 'Your bookings', 'wptheaterly' ); ?></span><strong><?php echo esc_html( count( $cards ) ); ?></strong></article></div>
		<details class="wtbm-guest-lookup" <?php echo ! $cards ? 'open' : ''; ?>><summary><?php esc_html_e( 'Find a guest booking', 'wptheaterly' ); ?><span><?php esc_html_e( 'Use the details from your confirmation', 'wptheaterly' ); ?></span></summary><form method="post"><?php wp_nonce_field( 'wtbm_lookup', 'nonce' ); ?><input type="hidden" name="wtbm_lookup" value="1"><label><?php esc_html_e( 'Order number', 'wptheaterly' ); ?><input name="order_id" type="number" min="1" placeholder="e.g. 1234" required></label><label><?php esc_html_e( 'Billing email', 'wptheaterly' ); ?><input name="email" type="email" placeholder="you@example.com" required></label><label><?php esc_html_e( 'Confirmation key', 'wptheaterly' ); ?><input name="order_key" placeholder="From your confirmation link" required autocomplete="off"></label><button type="submit"><?php esc_html_e( 'Find tickets', 'wptheaterly' ); ?></button></form>
		<?php
		if ( ! empty( $_POST['wtbm_lookup'] ) && ! $cards ) {
			?>
			<p class="wtbm-ticket-feedback wtbm-ops-error"><?php esc_html_e( 'No matching tickets. Check your order number, email and confirmation key.', 'wptheaterly' ); ?></p><?php } ?></details>
		<div class="wtbm-ticket-toolbar"><nav aria-label="<?php esc_attr_e( 'Filter tickets', 'wptheaterly' ); ?>"><button type="button" class="is-active" data-ticket-filter="all"><?php esc_html_e( 'All tickets', 'wptheaterly' ); ?></button><button type="button" data-ticket-filter="upcoming"><?php esc_html_e( 'Upcoming', 'wptheaterly' ); ?></button><button type="button" data-ticket-filter="past"><?php esc_html_e( 'Past visits', 'wptheaterly' ); ?></button><button type="button" data-ticket-filter="cancelled"><?php esc_html_e( 'Cancelled', 'wptheaterly' ); ?></button></nav><input type="search" class="wtbm-ticket-search" aria-label="<?php esc_attr_e( 'Search your tickets', 'wptheaterly' ); ?>" placeholder="<?php esc_attr_e( 'Search movie, screen or order…', 'wptheaterly' ); ?>"></div><p class="wtbm-ticket-global-feedback" role="status"></p>
		<div class="wtbm-ticket-list">
		<?php
		foreach ( $cards as $card ) {
			self::card( $card[0], $card[1], $key ); }
		?>
		</div><div class="wtbm-ticket-empty" <?php echo $cards ? 'hidden' : ''; ?>><span aria-hidden="true">◈</span><h3><?php esc_html_e( 'No tickets to show', 'wptheaterly' ); ?></h3><p><?php esc_html_e( 'Try another filter or look up your guest booking above.', 'wptheaterly' ); ?></p></div>
		</div>
		<?php

		return ob_get_clean();
	}
	/**
	 * Card.
	 *
	 * @param mixed $bid Bid.
	 * @param mixed $order Order.
	 * @param mixed $key Key.
	 */
	public static function card( $bid, $order, $key = '' ) {
		$ids            = (array) get_post_meta( $bid, 'wtbm_seat_ids', true );
		$names          = (array) get_post_meta( $bid, 'wtbm_seats', true );
		$movie          = get_post_meta( $bid, 'wtbm_movie_id', true );
		$screen         = get_post_meta( $bid, 'wtbm_theater_id', true );
		$stamp          = WTBM_Booking_Service::timestamp( get_post_meta( $bid, 'wtbm_order_date', true ), get_post_meta( $bid, 'wtbm_order_time', true ) );
		$state          = ! $ids || in_array( $order->get_status(), array( 'cancelled', 'refunded', 'failed' ), true ) ? 'cancelled' : ( $stamp > time() ? 'upcoming' : 'past' );
		$item           = $order->get_item( absint( get_post_meta( $bid, 'wtbm_item_id', true ) ) );
		$amount         = $item ? max( 0, (float) $item->get_total() - (float) $order->get_total_refunded_for_item( $item->get_id() ) ) : (float) get_post_meta( $bid, 'wtbm_tp', true );
		$change_allowed = ! is_wp_error( self::customer_policy( $bid ) );
		if ( 'cancelled' === $state ) {
			$ids   = array();
			$names = array(); }
		$calendar_url = add_query_arg(
			array(
				'action'    => 'wtbm_ticket_calendar',
				'booking'   => $bid,
				'order_key' => $key ? $key : $order->get_order_key(),
			),
			admin_url( 'admin-post.php' )
		);
		?>
		<article class="wtbm-ops-card wtbm-customer-ticket" data-ticket-state="<?php echo esc_attr( $state ); ?>" data-order="<?php echo esc_attr( $order->get_id() ); ?>">
			<div class="wtbm-ticket-main"><img class="wtbm-ticket-poster" src="<?php echo esc_url( WTBM_Function::get_image_url( $movie ) ); ?>" alt=""><div class="wtbm-ticket-description"><div class="wtbm-ticket-topline"><span class="wtbm-ticket-eyebrow">#<?php echo esc_html( $order->get_order_number() ); ?></span><span class="wtbm-status-badge wtbm-status-<?php echo esc_attr( $order->get_status() ); ?>"><?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?></span></div><h3><?php echo esc_html( get_the_title( $movie ) ); ?></h3><p class="wtbm-ticket-screen"><?php echo esc_html( get_the_title( $screen ) ); ?></p><div class="wtbm-ticket-show"><span><?php echo esc_html( wp_date( 'D, d M Y', $stamp, wp_timezone() ) ); ?></span><strong><?php echo esc_html( wp_date( 'g:i A', $stamp, wp_timezone() ) ); ?></strong></div><div class="wtbm-ticket-seat-chips">
			<?php
			foreach ( $names as $name ) {
				?>
				<span><?php echo esc_html( $name ); ?></span>
				<?php
			} if ( ! $names ) {
				?>
				<span><?php esc_html_e( 'No active seats', 'wptheaterly' ); ?></span><?php } ?></div></div><div class="wtbm-ticket-price"><span><?php esc_html_e( 'Ticket value', 'wptheaterly' ); ?></span><strong><?php echo wp_kses_post( wc_price( $amount ) ); ?></strong><small><?php echo esc_html( $order->is_paid() ? __( 'Payment received', 'wptheaterly' ) : wc_get_order_status_name( $order->get_status() ) ); ?></small></div></div>
			<div class="wtbm-ticket-downloads">
			<?php
			if ( $order->is_paid() && $ids ) {
				?>
				<a class="button wtbm-ticket-primary" href="<?php echo esc_url( self::pdf_url( $order, $key ) ); ?>"><?php esc_html_e( 'Download PDF', 'wptheaterly' ); ?></a><button type="button" class="wtbm-show-entry-qr" data-booking="<?php echo esc_attr( $bid ); ?>"><?php esc_html_e( 'Show entry QR', 'wptheaterly' ); ?></button>
				<?php
			} elseif ( $ids && $order->needs_payment() ) {
				?>
				<a class="button wtbm-ticket-primary" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>"><?php esc_html_e( 'Complete payment', 'wptheaterly' ); ?></a>
				<?php
			} if ( $ids ) {
				?>
									<a class="button" href="<?php echo esc_url( $calendar_url ); ?>"><?php esc_html_e( 'Add to calendar', 'wptheaterly' ); ?></a><?php } ?></div><figure class="wtbm-entry-qr" hidden></figure>
			<?php WTBM_Change_Requests::customer_status( $bid ); ?>
			<?php if ( $ids ) { ?>
			<form class="wtbm-ticket-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"><?php wp_nonce_field( 'wtbm_customer_ticket', 'nonce' ); ?><input type="hidden" name="action" value="wtbm_ticket_action"><input type="hidden" name="booking" value="<?php echo esc_attr( $bid ); ?>"><input type="hidden" name="order_key" value="<?php echo esc_attr( $key ); ?>">
				<p class="wtbm-ticket-feedback" role="status"></p><div class="wtbm-ticket-email"><span><?php echo esc_html( $order->get_billing_email() ? $order->get_billing_email() : __( 'Contact the box office to add a ticket email.', 'wptheaterly' ) ); ?></span><button name="verb" value="resend"><?php esc_html_e( 'Resend ticket email', 'wptheaterly' ); ?></button></div>
				<?php if ( $change_allowed ) { ?>
				<details class="wtbm-cancel-ticket"><summary><?php esc_html_e( 'Request cancellation / refund', 'wptheaterly' ); ?></summary><p><?php esc_html_e( 'Select seats for cancellation. An admin must approve your request before seats are cancelled or a refund is processed.', 'wptheaterly' ); ?></p>
					<?php
					foreach ( $ids as $i => $id ) {
						?>
					<label><input type="checkbox" name="seats[]" value="<?php echo esc_attr( $id ); ?>"><?php echo esc_html( $names[ $i ] ?? $id ); ?></label><?php } ?><label><?php esc_html_e( 'Reason', 'wptheaterly' ); ?><input name="reason"></label><button name="verb" value="cancel"><?php esc_html_e( 'Submit cancellation request', 'wptheaterly' ); ?></button></details>
				<details class="wtbm-reschedule"><summary><?php esc_html_e( 'Request rescheduling', 'wptheaterly' ); ?></summary><label><?php esc_html_e( 'Movie', 'wptheaterly' ); ?><select name="movie">
					<?php
					foreach ( get_posts(
						array(
							'post_type'   => 'wtbm_movie',
							'numberposts' => -1,
						)
					) as $movie ) {
						?>
						<option value="<?php echo esc_attr( $movie->ID ); ?>" <?php selected( $movie->ID, get_post_meta( $bid, 'wtbm_movie_id', true ) ); ?>><?php echo esc_html( $movie->post_title ); ?></option><?php } ?></select></label><label><?php esc_html_e( 'Screen', 'wptheaterly' ); ?><select name="theater">
						<?php
						foreach ( get_posts(
							array(
								'post_type'   => 'wtbm_theater',
								'numberposts' => -1,
							)
						) as $screen ) {
							?>
						<option value="<?php echo esc_attr( $screen->ID ); ?>" <?php selected( $screen->ID, get_post_meta( $bid, 'wtbm_theater_id', true ) ); ?>><?php echo esc_html( $screen->post_title ); ?></option><?php } ?></select></label><label><?php esc_html_e( 'Show date', 'wptheaterly' ); ?><input name="date" type="date" value="<?php echo esc_attr( get_post_meta( $bid, 'wtbm_order_date', true ) ); ?>"></label><label><?php esc_html_e( 'Show time', 'wptheaterly' ); ?><select name="time"><option><?php echo esc_html( get_post_meta( $bid, 'wtbm_order_time', true ) ); ?></option></select></label><input type="hidden" name="seat_ids" value="<?php echo esc_attr( implode( ',', $ids ) ); ?>"><div class="wtbm-seat-picker-label"><?php esc_html_e( 'Choose new seats', 'wptheaterly' ); ?></div><div class="wtbm-change-seats"></div><p class="wtbm-change-help" role="status"></p><button name="verb" value="reschedule"><?php esc_html_e( 'Submit rescheduling request', 'wptheaterly' ); ?></button><p><?php esc_html_e( 'An admin must approve the new show and seats. Availability is checked again at approval; higher-priced changes require payment before confirmation.', 'wptheaterly' ); ?></p></details>
					<?php
				} else {
					?>
					<p class="wtbm-ticket-policy-note"><?php esc_html_e( 'The change deadline has passed or customer changes are disabled. Contact the box office for help.', 'wptheaterly' ); ?></p><?php } ?>
			</form><?php } ?>
		</article>
		<?php
	}
}
WTBM_Tickets::boot();
