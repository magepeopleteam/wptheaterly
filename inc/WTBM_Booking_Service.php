<?php
/**
 * Shared booking, allocation and audit services.
 *
 * @package Theaterly
 */

// phpcs:disable WordPress.Files.FileName -- Preserve the plugin's established class/file naming convention.
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

spl_autoload_register(
	function ( $class_name ) {
		$prefix = 'Mpdf\\QrCode\\';
		if ( 0 === strpos( $class_name, $prefix ) ) {
				$file = WTBM_PLUGIN_DIR . '/assets/vendor/mpdf-qrcode/src/' . str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) ) . '.php';
			if ( is_file( $file ) ) {
				require_once $file; }
		}
	}
);
/**
 * Theaterly operational service.
 */
class WTBM_Booking_Service {
	const SCHEMA = '1';

	/**
	 * Boot.
	 */
	public static function boot() {
		add_action( 'init', array( __CLASS__, 'install' ), 5 );
		add_action( 'wtbm_expire_claims', array( __CLASS__, 'expire' ) );
		add_action( 'woocommerce_order_refunded', array( __CLASS__, 'order_refunded' ), 20, 2 );
	}

	/**
	 * Can.
	 *
	 * @param mixed $cap Cap.
	 */
	public static function can( $cap ) {
		return current_user_can( 'manage_options' ) || current_user_can( 'wtbm_' . $cap );
	}

	/**
	 * Setting.
	 *
	 * @param mixed $key Key.
	 * @param mixed $fallback Default.
	 */
	public static function setting( $key, $fallback = 0 ) {
		$settings = get_option( 'wtbm_operations_settings', array() );
		return $settings[ $key ] ?? $fallback;
	}
	/**
	 * Pdf.
	 *
	 * @param mixed $config Config.
	 */
	public static function pdf( $config = array() ) {
		$identity  = function_exists( 'posix_geteuid' ) ? posix_geteuid() : PHP_SAPI;
		$directory = trailingslashit( get_temp_dir() ) . 'wtbm-mpdf-' . substr( hash( 'sha256', ABSPATH . wp_salt( 'auth' ) . $identity ), 0, 20 );
		if ( ! is_dir( $directory ) ) {
			wp_mkdir_p( $directory );
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_chmod -- Private local PDF cache must not be world-readable.
			chmod( $directory, 0700 ); }
		return new \Mpdf\Mpdf( array_merge( $config, array( 'tempDir' => $directory ) ) );
	}

	/**
	 * Install.
	 */
	public static function install() {
		if ( get_option( 'wtbm_operations_schema' ) === self::SCHEMA ) {
			return; }
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$collate = $wpdb->get_charset_collate();
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wtbm_claims (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            show_key varchar(64) NOT NULL,
            seat_id varchar(100) NOT NULL,
            owner varchar(64) NOT NULL,
            order_id bigint unsigned NOT NULL DEFAULT 0,
            booking_id bigint unsigned NOT NULL DEFAULT 0,
            expires_at bigint unsigned NOT NULL DEFAULT 0,
            admitted_at datetime DEFAULT NULL,
            admitted_by bigint unsigned NOT NULL DEFAULT 0,
            PRIMARY KEY  (id),
            UNIQUE KEY allocation (show_key,seat_id),
            KEY expiry (expires_at),
            KEY booking (booking_id),
            KEY orders (order_id)
        ) $collate;"
		);
		dbDelta(
			"CREATE TABLE {$wpdb->prefix}wtbm_events (
            id bigint unsigned NOT NULL AUTO_INCREMENT,
            booking_id bigint unsigned NOT NULL DEFAULT 0,
            order_id bigint unsigned NOT NULL DEFAULT 0,
            user_id bigint unsigned NOT NULL DEFAULT 0,
            event varchar(64) NOT NULL,
            details longtext NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY booking (booking_id),
            KEY orders (order_id),
            KEY created (created_at)
        ) $collate;"
		);
		$caps = array( 'wtbm_manage', 'wtbm_sell', 'wtbm_admit', 'wtbm_reports' );
		foreach ( array( 'administrator', 'wtbm_manager', 'wtbm_cashier', 'wtbm_admission' ) as $name ) {
			if ( ! get_role( $name ) ) {
				add_role( $name, ucwords( str_replace( '_', ' ', $name ) ), array( 'read' => true ) ); }
			$role = get_role( $name );
			foreach ( $caps as $cap ) {
				if ( in_array( $name, array( 'administrator', 'wtbm_manager' ), true ) || ( 'wtbm_cashier' === $name && 'wtbm_sell' === $cap ) || ( 'wtbm_admission' === $name && 'wtbm_admit' === $cap ) ) {
					$role->add_cap( $cap ); }
			}
		}
		if ( ! wp_next_scheduled( 'wtbm_expire_claims' ) ) {
			wp_schedule_event( time() + 60, 'hourly', 'wtbm_expire_claims' ); }
		update_option( 'wtbm_operations_schema', self::SCHEMA, false );
	}

	/**
	 * Log.
	 *
	 * @param mixed $event Event.
	 * @param mixed $booking Booking.
	 * @param mixed $order Order.
	 * @param mixed $details Details.
	 */
	public static function log( $event, $booking = 0, $order = 0, $details = array() ) {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'wtbm_events',
			array(
				'booking_id' => absint( $booking ),
				'order_id'   => absint( $order ),
				'user_id'    => get_current_user_id(),
				'event'      => sanitize_key( $event ),
				'details'    => wp_json_encode( $details ),
				'created_at' => current_time( 'mysql', true ),
			)
		);
	}

	/**
	 * Lock.
	 *
	 * @param mixed $key Key.
	 * @param mixed $callback Callback.
	 */
	public static function lock( $key, $callback ) {
		global $wpdb;
		$name = 'wtbm_' . substr( hash( 'sha256', $wpdb->prefix . $key ), 0, 48 );
		if ( '1' !== (string) $wpdb->get_var( $wpdb->prepare( 'SELECT GET_LOCK(%s, 10)', $name ) ) ) {
			return new WP_Error( 'busy', __( 'This booking is being updated. Please try again.', 'wptheaterly' ) );
		}
		try {
			return call_user_func( $callback ); } finally {
			$wpdb->get_var( $wpdb->prepare( 'SELECT RELEASE_LOCK(%s)', $name ) ); }
	}

	/**
	 * Show time.
	 *
	 * @param mixed $time Time.
	 */
	public static function show_time( $time ) {
		try {
			$parsed = new DateTimeImmutable( '2000-01-01 ' . trim( (string) $time ), wp_timezone() );
			return $parsed->format( 'H:i' );
		} catch ( Throwable $error ) {
			return ''; }
	}
	/**
	 * Show key.
	 *
	 * @param mixed $theater Theater.
	 * @param mixed $date Date.
	 * @param mixed $time Time.
	 */
	public static function show_key( $theater, $date, $time ) {
		return hash( 'sha256', absint( $theater ) . '|' . $date . '|' . self::show_time( $time ) );
	}

	/**
	 * Timestamp.
	 *
	 * @param mixed $date Date.
	 * @param mixed $time Time.
	 */
	public static function timestamp( $date, $time ) {
		try {
			$value = date_create_immutable( $date . ' ' . $time, wp_timezone() ); } catch ( Throwable $exception ) {
			return 0; }
			return $value ? $value->getTimestamp() : 0;
	}

	/**
	 * Owner.
	 */
	public static function owner() {
		if ( function_exists( 'wc_load_cart' ) && ( ! WC()->session || ! WC()->cart ) ) {
			wc_load_cart(); }
		if ( WC()->session ) {
			WC()->session->set_customer_session_cookie( true ); }
		return hash( 'sha256', 'session|' . ( WC()->session ? WC()->session->get_customer_id() : get_current_user_id() ) );
	}

	/**
	 * Quote.
	 *
	 * @param mixed $movie Movie.
	 * @param mixed $theater Theater.
	 * @param mixed $date Date.
	 * @param mixed $time Time.
	 * @param mixed $ids Ids.
	 * @param mixed $allow_closed Allow closed.
	 */
	public static function quote( $movie, $theater, $date, $time, $ids, $allow_closed = false ) {
		$movie   = absint( $movie );
		$theater = absint( $theater );
		if ( 'wtbm_movie' !== get_post_type( $movie ) || 'publish' !== get_post_status( $movie ) || 'wtbm_theater' !== get_post_type( $theater ) || 'publish' !== get_post_status( $theater ) ) {
			return new WP_Error( 'show', __( 'Invalid movie or theater.', 'wptheaterly' ) ); }
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) || gmdate( 'Y-m-d', strtotime( $date ) ) !== $date ) {
			return new WP_Error( 'date', __( 'Invalid show date.', 'wptheaterly' ) ); }
		$slots = WTBM_Details_Layout::get_wtbm_show_time_by_date_and_movie_id( $movie, $date );
		if ( ! in_array( self::show_time( $time ), array_map( array( __CLASS__, 'show_time' ), $slots[ $theater ] ?? array() ), true ) ) {
			return new WP_Error( 'show', __( 'This show is no longer scheduled.', 'wptheaterly' ) ); }
		$time  = self::show_time( $time );
		$stamp = self::timestamp( $date, $time );
		if ( ! $allow_closed && ( $stamp <= time() + (int) self::setting( 'cutoff_minutes', 15 ) * 60 || $stamp > time() + (int) self::setting( 'advance_days', 30 ) * DAY_IN_SECONDS ) ) {
			return new WP_Error( 'closed', __( 'This show is outside the booking window.', 'wptheaterly' ) ); }
		if ( ! is_array( $ids ) || ! $ids || count( $ids ) > 100 || count( $ids ) !== count( array_unique( $ids ) ) ) {
			return new WP_Error( 'seats', __( 'Select between 1 and 100 unique seats.', 'wptheaterly' ) ); }
		$plan = get_post_meta( $theater, 'wtbp_theater_seat_map', true );
		$map  = array();
		foreach ( $plan['seat_data'] ?? array() as $seat ) {
			$map[ 'seat_' . $seat['id'] ] = $seat; }
		$names  = array();
		$prices = array();
		$base   = 0;
		foreach ( $ids as $id ) {
			if ( ! is_string( $id ) || ! isset( $map[ $id ] ) || ! is_numeric( $map[ $id ]['price'] ?? null ) || (float) $map[ $id ]['price'] < 0 ) {
				return new WP_Error( 'seat', __( 'One of the seats does not exist in this theater.', 'wptheaterly' ) ); }
			$names[]       = (string) $map[ $id ]['seat_number'];
			$prices[ $id ] = (float) $map[ $id ]['price'];
			$base         += $prices[ $id ];
		}
		$pricing = WTBM_Set_Pricing_Rules::price_details( $base, strtolower( wp_date( 'l', $stamp, wp_timezone() ) ), $date, $theater, $time, count( $ids ) );
		return array(
			'movie'    => $movie,
			'theater'  => $theater,
			'date'     => $date,
			'time'     => $time,
			'ids'      => array_values( $ids ),
			'names'    => $names,
			'count'    => count( $ids ),
			'base'     => $base,
			'price'    => $pricing['price'],
			'pricing'  => $pricing,
			'prices'   => $prices,
			'plan'     => $plan,
			'show_key' => self::show_key( $theater, $date, $time ),
		);
	}

	/**
	 * Occupied.
	 *
	 * @param mixed $theater Theater.
	 * @param mixed $movie Movie.
	 * @param mixed $date Date.
	 * @param mixed $time Time.
	 * @param mixed $except_booking Except booking.
	 * @param mixed $except_owner Except owner.
	 */
	public static function occupied( $theater, $movie, $date, $time, $except_booking = 0, $except_owner = '' ) {
		global $wpdb;
		$key      = self::show_key( $theater, $date, $time );
		$ids      = $wpdb->get_col( $wpdb->prepare( "SELECT seat_id FROM {$wpdb->prefix}wtbm_claims WHERE show_key=%s AND (expires_at=0 OR expires_at>%d) AND (booking_id=0 OR booking_id<>%d) AND owner<>%s", $key, time(), $except_booking, $except_owner ) );
		$bookings = get_posts(
			array(
				'post_type'   => 'wtbm_booking',
				'post_status' => array( 'publish', 'private' ),
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_query'  => array(
					array(
						'key'   => 'wtbm_theater_id',
						'value' => $theater,
					),
					array(
						'key'   => 'wtbm_order_date',
						'value' => $date,
					),

				),
			)
		);
		foreach ( $bookings as $bid ) {
			if ( (int) $bid === (int) $except_booking || self::show_time( get_post_meta( $bid, 'wtbm_order_time', true ) ) !== self::show_time( $time ) ) {
				continue; }
			$order = wc_get_order( get_post_meta( $bid, 'wtbm_order_id', true ) );
			if ( ! $order || in_array( $order->get_status(), array( 'failed', 'cancelled', 'refunded' ), true ) ) {
				continue; }
			if ( ! $order->is_paid() && $order->get_date_created() && $order->get_date_created()->getTimestamp() + (int) self::setting( 'hold_minutes', 15 ) * 60 < time() ) {
				continue; }
			$ids = array_merge( $ids, (array) get_post_meta( $bid, 'wtbm_seat_ids', true ) );
		}
		return array_unique( $ids );
	}

	/**
	 * Claim.
	 *
	 * @param mixed $quote Quote.
	 * @param mixed $owner Owner.
	 * @param mixed $order Order.
	 * @param mixed $booking Booking.
	 * @param mixed $permanent Permanent.
	 */
	public static function claim( $quote, $owner, $order = 0, $booking = 0, $permanent = false ) {
		return self::lock(
			'show|' . $quote['show_key'],
			function () use ( $quote, $owner, $order, $booking, $permanent ) {
				global $wpdb;
				$table = $wpdb->prefix . 'wtbm_claims';
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wtbm_claims WHERE expires_at>0 AND expires_at<=%d AND show_key=%s", time(), $quote['show_key'] ) );
				$occupied = self::occupied( $quote['theater'], $quote['movie'], $quote['date'], $quote['time'], $booking, $owner );
				if ( array_intersect( $quote['ids'], $occupied ) ) {
					return new WP_Error( 'occupied', __( 'A selected seat has just been taken. Choose another seat.', 'wptheaterly' ) ); }
				$expiry = $permanent ? 0 : time() + max( 5, (int) self::setting( 'hold_minutes', 15 ) ) * 60;
				$wpdb->query( 'START TRANSACTION' );
				$owned = $wpdb->get_results( $wpdb->prepare( "SELECT id,seat_id FROM {$wpdb->prefix}wtbm_claims WHERE show_key=%s AND owner=%s AND order_id=%d", $quote['show_key'], $owner, $order ) );
				foreach ( $owned as $row ) {
					if ( ! in_array( $row->seat_id, $quote['ids'], true ) ) {
								$wpdb->delete( $table, array( 'id' => $row->id ) ); }
				}
				foreach ( $quote['ids'] as $id ) {
					$existing = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wtbm_claims WHERE show_key=%s AND seat_id=%s", $quote['show_key'], $id ) );
					if ( $existing && ( $existing->owner !== $owner && ( ! $booking || (int) $existing->booking_id !== $booking ) ) ) {
						$wpdb->query( 'ROLLBACK' );
						return new WP_Error( 'occupied', __( 'Seat allocation conflict.', 'wptheaterly' ) ); }
					$data   = array(
						'show_key'   => $quote['show_key'],
						'seat_id'    => $id,
						'owner'      => $owner,
						'order_id'   => $order,
						'booking_id' => $booking,
						'expires_at' => $expiry,
					);
					$result = $existing ? $wpdb->update( $table, $data, array( 'id' => $existing->id ) ) : $wpdb->insert( $table, $data );
					if ( false === $result ) {
						$wpdb->query( 'ROLLBACK' );
						return new WP_Error( 'storage', __( 'Unable to reserve seats. Please retry.', 'wptheaterly' ) ); }
				}
				$wpdb->query( 'COMMIT' );
				return $expiry;
			}
		);
	}

	/**
	 * Bookings.
	 *
	 * @param mixed $order_id Order id.
	 */
	public static function bookings( $order_id ) {
		return get_posts(
			array(
				'post_type'   => 'wtbm_booking',
				'post_status' => array( 'publish', 'private', 'draft' ),
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_key'    => 'wtbm_order_id',
				'meta_value'  => absint( $order_id ),
			)
		);
	}

	/**
	 * Sync order.
	 *
	 * @param mixed $order_or_id Order or id.
	 */
	public static function sync_order( $order_or_id ) {
		$order = is_a( $order_or_id, 'WC_Order' ) ? $order_or_id : wc_get_order( $order_or_id );
		if ( ! $order ) {
			return; }
		return self::lock(
			'order|' . $order->get_id(),
			function () use ( $order ) {
				global $wpdb;
				$bookings = self::bookings( $order->get_id() );
				foreach ( $order->get_items() as $item_id => $item ) {
					$movie = absint( $item->get_meta( '_wtbm_id' ) );
					if ( 'wtbm_movie' !== get_post_type( $movie ) ) {
						continue; }
					$bid = 0;
					foreach ( $bookings as $existing ) {
						if ( (int) get_post_meta( $existing, 'wtbm_item_id', true ) === $item_id || ( ! get_post_meta( $existing, 'wtbm_item_id', true ) && (int) get_post_meta( $existing, 'wtbm_movie_id', true ) === $movie ) ) {
							$bid = $existing;
							break; }
					}
					$status = $order->get_status();
					if ( ! $bid ) {
						$bid = wp_insert_post(
							array(
								'post_type'   => 'wtbm_booking',
								'post_status' => 'draft',
								'post_title'  => $order->get_formatted_billing_full_name(),
							),
							true
						);
						if ( is_wp_error( $bid ) ) {
							return $bid; }
						update_post_meta( $bid, 'wtbm_ticket_version', 1 );
						update_post_meta( $bid, 'wtbm_created_at', current_time( 'mysql', true ) );
						self::log( 'booking_created', $bid, $order->get_id(), array( 'item_id' => $item_id ) );
					}
					wp_update_post(
						array(
							'ID'         => $bid,
							'post_title' => sanitize_text_field( $order->get_formatted_billing_full_name() ),
						)
					);
					$ids  = (array) $item->get_meta( '_wtbm_selected_seat_ids' );
					$data = array(
						'wtbm_item_id'         => $item_id,
						'wtbm_movie_id'        => $movie,
						'wtbm_theater_id'      => $item->get_meta( '_theater_id' ),
						'wtbm_order_date'      => $item->get_meta( '_wtbm_date' ),
						'wtbm_order_time'      => $item->get_meta( '_wtbm_time' ),
						'wtbm_order_id'        => $order->get_id(),
						'wtbm_order_status'    => $status,
						'wtbm_tp'              => max( 0, (float) $item->get_total() - (float) $order->get_total_refunded_for_item( $item_id ) ),
						'wtbm_number_of_seats' => count( $ids ),
						'wtbm_seats'           => (array) $item->get_meta( '_wtbm_selected_seats' ),
						'wtbm_seat_ids'        => $ids,
						'wtbm_payment_method'  => $order->get_payment_method(),
						'wtbm_user_id'         => $order->get_customer_id(),
						'wtbm_billing_name'    => $order->get_formatted_billing_full_name(),
						'wtbm_billing_phone'   => $order->get_billing_phone(),
						'wtbm_billing_email'   => $order->get_billing_email(),
						'wtbm_billing_address' => $order->get_billing_address_1(),
					);
					foreach ( $data as $key => $value ) {
						update_post_meta( $bid, $key, $value ); }
					if ( ! get_post_meta( $bid, 'wtbm_plan_snapshot', true ) ) {
						update_post_meta( $bid, 'wtbm_plan_snapshot', get_post_meta( $data['wtbm_theater_id'], 'wtbp_theater_seat_map', true ) ); }
					if ( ! get_post_meta( $bid, 'wtbm_pin', true ) ) {
						update_post_meta( $bid, 'wtbm_pin', wp_generate_password( 18, false ) ); }
					if ( in_array( $status, array( 'cancelled', 'failed', 'refunded' ), true ) ) {
						$wpdb->delete( $wpdb->prefix . 'wtbm_claims', array( 'booking_id' => $bid ) );
						wp_update_post(
							array(
								'ID'          => $bid,
								'post_status' => 'draft',
							)
						);
						self::revoke( $bid );
					} elseif ( $ids ) {
						wp_update_post(
							array(
								'ID'          => $bid,
								'post_status' => 'publish',
							)
						);
						$quote = array(
							'movie'    => $movie,
							'theater'  => $data['wtbm_theater_id'],
							'date'     => $data['wtbm_order_date'],
							'time'     => $data['wtbm_order_time'],
							'ids'      => $ids,
							'show_key' => self::show_key( $data['wtbm_theater_id'], $data['wtbm_order_date'], $data['wtbm_order_time'] ),
						);
						$owner = $order->get_meta( '_wtbm_claim_owner' ) ? $order->get_meta( '_wtbm_claim_owner' ) : hash( 'sha256', 'order|' . $order->get_id() );
						$claim = self::claim( $quote, $owner, $order->get_id(), $bid, $order->is_paid() );
						if ( is_wp_error( $claim ) ) {
							update_post_meta( $bid, 'wtbm_allocation_error', $claim->get_error_message() );
							$order->add_order_note( $claim->get_error_message() );
							if ( ! $order->has_status( 'on-hold' ) ) {
								$order->update_status( 'on-hold', __( 'Seat conflict: staff must resolve before admission.', 'wptheaterly' ) ); }
							return $claim;
						}
						delete_post_meta( $bid, 'wtbm_allocation_error' );
					}
				}
				return true;
			}
		);
	}

	/**
	 * Revoke.
	 *
	 * @param mixed $bid Bid.
	 */
	public static function revoke( $bid ) {
		update_post_meta( $bid, 'wtbm_ticket_version', max( 1, (int) get_post_meta( $bid, 'wtbm_ticket_version', true ) ) + 1 );
	}

	/**
	 * Expire.
	 */
	public static function expire() {
		global $wpdb;
		$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wtbm_claims WHERE expires_at>0 AND expires_at<=%d", time() ) );
	}

	/**
	 * Order refunded.
	 *
	 * @param mixed $order_id Order id.
	 * @param mixed $refund_id Refund id.
	 */
	public static function order_refunded( $order_id, $refund_id ) {
		self::sync_order( $order_id );
		self::log( 'order_refunded', 0, $order_id, array( 'refund_id' => $refund_id ) );
	}

	/**
	 * Protect plan.
	 *
	 * @param mixed $theater Theater.
	 * @param mixed $plan Plan.
	 */
	public static function protect_plan( $theater, $plan ) {
		$new = array();
		foreach ( $plan['seat_data'] ?? array() as $seat ) {
			$new[ 'seat_' . $seat['id'] ] = (string) $seat['seat_number']; }
		$ids = get_posts(
			array(
				'post_type'   => 'wtbm_booking',
				'post_status' => array( 'publish', 'private' ),
				'numberposts' => -1,
				'fields'      => 'ids',
				'meta_query'  => array(
					array(
						'key'   => 'wtbm_theater_id',
						'value' => $theater,
					),
					array(
						'key'     => 'wtbm_order_date',
						'value'   => current_time( 'Y-m-d' ),
						'compare' => '>=',
						'type'    => 'DATE',
					),
				),
			)
		);
		foreach ( $ids as $bid ) {
			$seats = (array) get_post_meta( $bid, 'wtbm_seat_ids', true );
			$names = (array) get_post_meta( $bid, 'wtbm_seats', true );
			foreach ( $seats as $index => $seat_id ) {
				if ( ! isset( $new[ $seat_id ] ) || (string) ( $names[ $index ] ?? '' ) !== $new[ $seat_id ] ) {
					return new WP_Error( 'sold', __( 'A future booking uses a seat you removed or renamed. Reschedule or cancel that booking first.', 'wptheaterly' ) ); }
			}
		}
		$history = (array) get_post_meta( $theater, 'wtbm_plan_versions', true );
		$old     = get_post_meta( $theater, 'wtbp_theater_seat_map', true );
		if ( $old && $old !== $plan ) {
			$history[] = array(
				'date' => current_time( 'mysql', true ),
				'user' => get_current_user_id(),
				'plan' => $old,
			);
			update_post_meta( $theater, 'wtbm_plan_versions', array_slice( $history, -20 ) ); }
		self::log(
			'plan_saved',
			0,
			0,
			array(
				'theater_id' => $theater,
				'seats'      => count( $new ),
			)
		);
		return true;
	}
}
WTBM_Booking_Service::boot();
