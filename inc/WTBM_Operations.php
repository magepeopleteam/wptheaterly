<?php
/**
 * Booking changes, box office and schedule policy.
 *
 * @package Theaterly
 */

// phpcs:disable WordPress.Files.FileName -- Preserve the plugin's established class/file naming convention.
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
/**
 * Theaterly operational service.
 */
class WTBM_Operations {
	/**
	 * Boot.
	 */
	public static function boot() {
		add_action( 'wp_ajax_wtbm_hold_seats', array( __CLASS__, 'hold' ) );
		add_action( 'wp_ajax_nopriv_wtbm_hold_seats', array( __CLASS__, 'hold' ) );
		add_action( 'wp_ajax_wtbm_release_hold', array( __CLASS__, 'release_hold' ) );
		add_action( 'wp_ajax_nopriv_wtbm_release_hold', array( __CLASS__, 'release_hold' ) );
		add_action( 'wp_ajax_wtbm_change_options', array( __CLASS__, 'change_options' ) );
		add_action( 'wp_ajax_nopriv_wtbm_change_options', array( __CLASS__, 'change_options' ) );
		add_action( 'woocommerce_checkout_create_order', array( __CLASS__, 'checkout_owner' ), 10, 2 );
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( __CLASS__, 'checkout_owner' ), 10, 2 );
		add_action( 'woocommerce_payment_complete', array( __CLASS__, 'complete_change_payment' ), 30 );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'complete_change_payment' ), 30 );
	}
	/**
	 * Response.
	 *
	 * @param mixed $value Value.
	 */
	public static function response( $value ) {
		if ( is_wp_error( $value ) ) {
			wp_send_json_error( $value->get_error_message(), 400 ); }
		wp_send_json_success( $value );
	}
	/**
	 * Request quote.
	 *
	 * @param mixed $request Request.
	 */
	public static function request_quote( $request ) {
		$ids = $request['booked_seat_ids'] ?? array();
		if ( is_string( $ids ) ) {
			$ids = json_decode( $ids, true ); }
		return WTBM_Booking_Service::quote( absint( $request['movie_id'] ?? 0 ), absint( $request['theater_id'] ?? 0 ), sanitize_text_field( $request['booking_date'] ?? '' ), sanitize_text_field( $request['booking_time'] ?? '' ), $ids );
	}
	/**
	 * Hold.
	 */
	public static function hold() {
		check_ajax_referer( 'wtbm_nonce', 'nonce' );
		$quote = self::request_quote( wp_unslash( $_POST ) );
		if ( is_wp_error( $quote ) ) {
			self::response( $quote ); }
		$expiry = WTBM_Booking_Service::claim( $quote, WTBM_Booking_Service::owner() );
		if ( is_wp_error( $expiry ) ) {
			self::response( $expiry ); }
		self::response(
			array(
				'expires_at'      => $expiry,
				'price'           => $quote['price'],
				'formatted_price' => wc_price( $quote['price'] ),
				'pricing' => $quote['pricing'],
				'formatted_base' => wc_price( $quote['base'] ),
				'formatted_adjustment' => wc_price( abs( $quote['price'] - $quote['base'] ) ),
			)
		);
	}
	/**
	 * Change options.
	 */
	public static function change_options() {
		check_ajax_referer( 'wtbm_nonce', 'nonce' );
		$movie   = absint( $_POST['movie'] ?? 0 );
		$theater = absint( $_POST['theater'] ?? 0 );
		$date    = sanitize_text_field( wp_unslash( $_POST['date'] ?? '' ) );
		$time    = sanitize_text_field( wp_unslash( $_POST['time'] ?? '' ) );
		$times   = WTBM_Details_Layout::get_wtbm_show_time_by_date_and_movie_id( $movie, $date )[ $theater ] ?? array();
		foreach ( $times as $slot ) {
			if ( WTBM_Booking_Service::show_time( $slot ) === WTBM_Booking_Service::show_time( $time ) ) {
				$time = $slot;
				break; }
		}
		if ( ! $time || ! in_array( $time, $times, true ) ) {
			$time = $times[0] ?? ''; }
		$bid      = absint( $_POST['booking'] ?? 0 );
		$order    = wc_get_order( get_post_meta( $bid, 'wtbm_order_id', true ) );
		$except   = WTBM_Tickets::access( $order, sanitize_text_field( wp_unslash( $_POST['order_key'] ?? '' ) ) ) ? $bid : 0;
		$occupied = WTBM_Booking_Service::occupied( $theater, $movie, $date, $time, $except );
		$plan     = get_post_meta( $theater, 'wtbp_theater_seat_map', true );
		$seats    = array();
		foreach ( $plan['seat_data'] ?? array() as $seat ) {
			$id = 'seat_' . $seat['id'];
			if ( ! in_array( $id, $occupied, true ) ) {
				$seats[] = array(
					'id'   => $id,
					'name' => (string) $seat['seat_number'],
				); }
		}
		self::response(
			array(
				'times'         => array_values( $times ),
				'selected_time' => $time,
				'seats'         => $seats,
				'message'       => __( 'Select the available seats for the new show.', 'wptheaterly' ),
			)
		);
	}
	/**
	 * Release hold.
	 */
	public static function release_hold() {
		check_ajax_referer( 'wtbm_nonce', 'nonce' );
		global $wpdb;
		$wpdb->delete(
			$wpdb->prefix . 'wtbm_claims',
			array(
				'owner'    => WTBM_Booking_Service::owner(),
				'order_id' => 0,
			)
		);
		wp_send_json_success();
	}
	/**
	 * Checkout owner.
	 *
	 * @param mixed $order Order.
	 */
	public static function checkout_owner( $order ) {
		$order->update_meta_data( '_wtbm_claim_owner', WTBM_Booking_Service::owner() );
	}
	/**
	 * Purchase.
	 *
	 * @param mixed $request Request.
	 * @param mixed $admin Admin.
	 */
	public static function purchase( $request, $admin = false ) {
		if ( $admin && ! WTBM_Booking_Service::can( 'sell' ) ) {
			return new WP_Error( 'permission', __( 'You cannot create box-office sales.', 'wptheaterly' ) ); }
		$quote = self::request_quote( $request );
		if ( is_wp_error( $quote ) ) {
			return $quote; }
		$product = wc_get_product( absint( get_post_meta( $quote['movie'], 'link_wc_product', true ) ) );
		if ( ! $product ) {
			return new WP_Error( 'product', __( 'Movie product is unavailable.', 'wptheaterly' ) ); }
		$name  = sanitize_text_field( $request['userName'] ?? '' );
		$phone = sanitize_text_field( $request['userPhoneNum'] ?? '' );
		if ( ! $name || ! $phone ) {
			return new WP_Error( 'contact', __( 'Name and phone are required.', 'wptheaterly' ) ); }
		$owner      = WTBM_Booking_Service::owner();
		$request_id = sanitize_text_field( $request['request_id'] ?? '' );
		if ( ! preg_match( '/^[a-zA-Z0-9-]{16,80}$/', $request_id ) ) {
			return new WP_Error( 'request', __( 'Missing purchase reference. Reload the page and retry.', 'wptheaterly' ) ); }
		return WTBM_Booking_Service::lock(
			'purchase|' . $owner . $request_id,
			function () use ( $request, $request_id, $quote, $product, $name, $phone, $owner, $admin ) {
				if ( $admin ) {
					$orders = wc_get_orders(
						array(
							'limit'      => 1,
							'meta_key'   => '_wtbm_request_id',
							'meta_value' => $owner . ':' . $request_id,
						)
					);
					if ( $orders ) {
						return self::sale_response( $orders[0], $quote ); }
				} elseif ( WC()->session->get( 'wtbm_purchase_reference' ) === $request_id && WC()->cart->get_cart() ) {
					return array( 'checkout_url' => wc_get_checkout_url() ); }
				$claim = WTBM_Booking_Service::claim( $quote, $owner );
				if ( is_wp_error( $claim ) ) {
					return $claim; }
				if ( ! $admin ) {
					$data = array(
						'wtbm_movie_id'       => $quote['movie'],
						'wtbm_product_id'     => $product->get_id(),
						'theater_id'          => $quote['theater'],
						'booking_date'        => $quote['date'],
						'booking_time'        => $quote['time'],
						'seat_count'          => $quote['count'],
						'seat_names'          => $quote['names'],
						'booked_seat_ids'     => $quote['ids'],
						'wtbm_price'          => $quote['price'],
						'wtbm_pricing_details' => $quote['pricing'],
						'wtbm_original_price' => $quote['base'],
						'user_name'           => $name,
						'user_phone_num'      => $phone,
					);
					WC()->cart->empty_cart();
					$key = WC()->cart->add_to_cart( $product->get_id(), 1, 0, array(), $data );
					if ( ! $key ) {
						return new WP_Error( 'cart', __( 'Unable to add tickets to cart.', 'wptheaterly' ) ); }
					WC()->session->set( 'wtbm_purchase_reference', $request_id );
					return array(
						'checkout_url' => wc_get_checkout_url(),
						'price'        => $quote['price'],
					);
				}
				$method = sanitize_key( $request['payment_method'] ?? 'wtbm_cash' );
				if ( ! in_array( $method, array( 'wtbm_cash', 'wtbm_card', 'wtbm_manual', 'wtbm_unpaid' ), true ) ) {
					return new WP_Error( 'payment', __( 'Invalid payment method.', 'wptheaterly' ) ); }
				$tender = (float) ( $request['tender'] ?? 0 );
				if ( 'wtbm_cash' === $method && $tender < $quote['price'] ) {
					return new WP_Error( 'tender', __( 'Cash tender must cover the ticket price.', 'wptheaterly' ) ); }
				$reference = sanitize_text_field( $request['payment_reference'] ?? '' );
				if ( in_array( $method, array( 'wtbm_card', 'wtbm_manual' ), true ) && ! $reference ) {
					return new WP_Error( 'reference', __( 'Enter the terminal or payment reference.', 'wptheaterly' ) ); }
				$order = wc_create_order();
				if ( is_wp_error( $order ) ) {
					return $order; }
				$order->set_billing_first_name( $name );
				$order->set_billing_phone( $phone );
				$order->set_billing_email( sanitize_email( $request['email'] ?? '' ) );
				$order->set_payment_method( $method );
				$order->set_payment_method_title( self::payment_label( $method ) );
				$order->set_created_via( 'wtbm_box_office' );
				$order->update_meta_data( '_wtbm_request_id', $owner . ':' . $request_id );
				$order->update_meta_data( '_wtbm_claim_owner', $owner );
				$order->update_meta_data( '_wtbm_cashier_id', get_current_user_id() );
				$order->update_meta_data( '_wtbm_tender', $tender );
				$order->update_meta_data( '_wtbm_payment_reference', $reference );
				$item = new WC_Order_Item_Product();
				$item->set_product( $product );
				$item->set_quantity( 1 );
				$item->set_subtotal( $quote['price'] );
				$item->set_total( $quote['price'] );
				foreach ( array(
					'_wtbm_id'                => $quote['movie'],
					'_movie_id'               => $quote['movie'],
					'_theater_id'             => $quote['theater'],
					'_wtbm_date'              => $quote['date'],
					'_wtbm_time'              => $quote['time'],
					'_wtbm_tp'                => $quote['price'],
					'_wtbm_pricing_details' => $quote['pricing'],
					'_wtbm_selected_seats'    => $quote['names'],
					'_wtbm_selected_seat_ids' => $quote['ids'],
					'_wtbm_number_of_seats'   => $quote['count'],
				) as $key => $value ) {
					$item->add_meta_data( $key, $value, true ); }
				WTBM_Set_Pricing_Rules::order_details( $item, $quote['pricing'] );
				$order->add_item( $item );
				$order->calculate_totals();
				if ( 'wtbm_cash' === $method && $tender + 0.001 < (float) $order->get_total() ) {
					$order->delete( true );
					return new WP_Error( 'tender', __( 'Cash tender must cover the total including tax.', 'wptheaterly' ) );
				}
				$order->save();
				$order->update_status( 'pending' );
				WTBM_Booking_Service::sync_order( $order );
				if ( 'wtbm_unpaid' !== $method ) {
					$order->payment_complete( $reference ); }
				WTBM_Booking_Service::sync_order( $order );
				WTBM_Booking_Service::log(
					'box_office_sale',
					0,
					$order->get_id(),
					array(
						'method'  => $method,
						'tender'  => $tender,
						'change'  => max( 0, $tender - $order->get_total() ),
						'cashier' => get_current_user_id(),
					)
				);
				return self::sale_response( $order, $quote );
			}
		);
	}
	/**
	 * Payment label.
	 *
	 * @param mixed $method Method.
	 */
	public static function payment_label( $method ) {
		$labels = array(
			'wtbm_cash'   => __( 'Cash', 'wptheaterly' ),
			'wtbm_card'   => __( 'Card terminal', 'wptheaterly' ),
			'wtbm_manual' => __( 'Manual payment', 'wptheaterly' ),
			'wtbm_unpaid' => __( 'Unpaid reservation', 'wptheaterly' ),
		);
		return $labels[ $method ] ?? $method;
	}
	/**
	 * Sale response.
	 *
	 * @param mixed $order Order.
	 * @param mixed $quote Quote.
	 */
	public static function sale_response( $order, $quote ) {
		return array(
			'order_id'         => $order->get_id(),
			'message'          => __( 'Sale recorded.', 'wptheaterly' ),
			'wtbm_total_price' => $order->get_total(),
			'seat_map'         => WTBM_Details_Layout::display_theater_seat_mapping( $quote['theater'], WTBM_Booking_Service::occupied( $quote['theater'], $quote['movie'], $quote['date'], $quote['time'] ) ),
			'pdf_url_btn'      => WTBM_Pro_Pdf::get_pdf_url_button( $order->get_id() ),
			'receipt_url'      => self::receipt_url( $order->get_id() ),
			'change'           => max( 0, (float) $order->get_meta( '_wtbm_tender' ) - $order->get_total() ),
		);
	}
	/**
	 * Validate schedule.
	 *
	 * @param mixed $request Request.
	 * @param mixed $exclude Exclude.
	 */
	public static function validate_schedule( $request, $exclude = 0 ) {
		$movie       = absint( $request['movieId'] ?? 0 );
		$theater     = absint( $request['theaterId'] ?? 0 );
		$start       = $request['start_date'] ?? '';
		$end         = $request['end_date'] ? $request['end_date'] : $start;
		$time        = $request['startTime'] ?? '';
		$finish      = $request['endTime'] ?? '';
		$start_stamp = WTBM_Booking_Service::timestamp( $start, '00:00' );
		$end_stamp   = WTBM_Booking_Service::timestamp( $end, '00:00' );
		if ( 'wtbm_movie' !== get_post_type( $movie ) || 'wtbm_theater' !== get_post_type( $theater ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end ) || ! $start_stamp || ! $end_stamp || $start_stamp > $end_stamp || $end_stamp - $start_stamp > 366 * DAY_IN_SECONDS || ! strtotime( $time ) || ! strtotime( $finish ) ) {
			return new WP_Error( 'schedule', __( 'Enter a valid movie, screen, date range (up to one year), start and end time.', 'wptheaterly' ) ); }
		$duration_text = get_post_meta( $movie, 'wtbp_movie_duration', true );
		$duration      = 0;
		if ( preg_match( '/(\d+)\s*h/i', $duration_text, $m ) ) {
			$duration += (int) $m[1] * 60; }
		if ( preg_match( '/(\d+)\s*m/i', $duration_text, $m ) ) {
			$duration += (int) $m[1]; }
		$off    = array_filter( explode( ',', sanitize_text_field( $request['showtime_off_days'] ?? '' ) ) );
		$shows  = get_posts(
			array(
				'post_type'   => 'wtbm_show_time',
				'post_status' => 'publish',
				'numberposts' => -1,
				'exclude'     => array( $exclude ),
				'meta_key'    => 'wtbp_show_time_theaterId',
				'meta_value'  => $theater,
			)
		);
		$buffer = (int) WTBM_Booking_Service::setting( 'cleanup_minutes', 15 ) * 60;
		for ( $day = new DateTimeImmutable( $start, wp_timezone() ); $day->format( 'Y-m-d' ) <= $end; $day = $day->modify( '+1 day' ) ) {
			if ( in_array( strtolower( $day->format( 'l' ) ), $off, true ) ) {
				continue; }
			$a = WTBM_Booking_Service::timestamp( $day->format( 'Y-m-d' ), $time );
			$b = WTBM_Booking_Service::timestamp( $day->format( 'Y-m-d' ), $finish );
			if ( $b <= $a ) {
				$b += DAY_IN_SECONDS; }
			if ( $b - $a < $duration * 60 ) {
				return new WP_Error( 'duration', __( 'Show end time is shorter than the movie duration.', 'wptheaterly' ) ); }
			foreach ( $shows as $show ) {
				foreach ( array( $day->modify( '-1 day' ), $day, $day->modify( '+1 day' ) ) as $other_day ) {
					$date = $other_day->format( 'Y-m-d' );
					if ( $date < get_post_meta( $show->ID, 'wtbp_showtime_start_date', true ) || $date > get_post_meta( $show->ID, 'wtbp_showtime_end_date', true ) || in_array( strtolower( $other_day->format( 'l' ) ), (array) get_post_meta( $show->ID, 'wtbp_showtime_off_days', true ), true ) ) {
						continue; }
					$c = WTBM_Booking_Service::timestamp( $date, get_post_meta( $show->ID, 'wtbp_show_starting_time', true ) );
					$d = WTBM_Booking_Service::timestamp( $date, get_post_meta( $show->ID, 'wtbp_show_ending_time', true ) );
					if ( $d <= $c ) {
						$d += DAY_IN_SECONDS; }
					if ( $a < $d + $buffer && $b + $buffer > $c ) {
						// translators: %s is the date of the conflicting show.
						return new WP_Error( 'overlap', sprintf( __( 'Screen already has an overlapping show on %s (including cleanup buffer).', 'wptheaterly' ), $day->format( 'Y-m-d' ) ) ); }
				}
			}
		}
		return true;
	}
	/**
	 * Edit.
	 *
	 * @param mixed $bid Bid.
	 * @param mixed $changes Changes.
	 * @param mixed $staff Staff.
	 * @param mixed $settled_difference Settled difference.
	 */
	public static function edit( $bid, $changes, $staff = true, $settled_difference = 0 ) {
		if ( 'wtbm_booking' !== get_post_type( $bid ) || ( $staff && ! WTBM_Booking_Service::can( 'manage' ) ) ) {
			return new WP_Error( 'permission', __( 'Booking not found or permission denied.', 'wptheaterly' ) ); }
		return WTBM_Booking_Service::lock(
			'change|' . $bid,
			function () use ( $bid, $changes, $staff, $settled_difference ) {
				if ( isset( $changes['_expected_version'] ) && (string) get_post_meta( $bid, 'wtbm_ticket_version', true ) !== (string) $changes['_expected_version'] ) {
					return new WP_Error( 'changed', __( 'This booking changed after the request was submitted.', 'wptheaterly' ) ); }
				$order = wc_get_order( get_post_meta( $bid, 'wtbm_order_id', true ) );
				if ( ! $order ) {
					return new WP_Error( 'order', __( 'Booking order is unavailable.', 'wptheaterly' ) ); }
				WTBM_Booking_Service::sync_order( $order );
				$item = $order->get_item( absint( get_post_meta( $bid, 'wtbm_item_id', true ) ) );
				if ( ! $item ) {
					return new WP_Error( 'item', __( 'Booking order item is unavailable.', 'wptheaterly' ) ); }
				$ids   = $changes['ids'] ?? (array) get_post_meta( $bid, 'wtbm_seat_ids', true );
				$quote = WTBM_Booking_Service::quote( absint( $changes['movie'] ?? get_post_meta( $bid, 'wtbm_movie_id', true ) ), absint( $changes['theater'] ?? get_post_meta( $bid, 'wtbm_theater_id', true ) ), sanitize_text_field( $changes['date'] ?? get_post_meta( $bid, 'wtbm_order_date', true ) ), sanitize_text_field( $changes['time'] ?? get_post_meta( $bid, 'wtbm_order_time', true ) ), $ids, $staff );
				if ( is_wp_error( $quote ) ) {
					return $quote; }
				global $wpdb;
				if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}wtbm_claims WHERE booking_id=%d AND admitted_at IS NOT NULL LIMIT 1", $bid ) ) ) {
					return new WP_Error( 'admitted', __( 'An admitted booking cannot be changed.', 'wptheaterly' ) ); }
				if ( $order->is_paid() && ! $order->get_meta( '_wtbm_initial_paid_total' ) ) {
					$order->update_meta_data( '_wtbm_initial_paid_total', $order->get_total() );
					$order->save(); }
				$net = (float) $item->get_total() - (float) $order->get_total_refunded_for_item( $item->get_id() );
				if ( isset( $changes['status'] ) && ! in_array( $changes['status'], array( 'pending', 'on-hold', 'processing', 'completed', 'cancelled' ), true ) ) {
					return new WP_Error( 'status', __( 'Invalid order status.', 'wptheaterly' ) ); }
				if ( $order->is_paid() && $quote['price'] > $net + $settled_difference + 0.01 ) {
					return self::change_payment( $bid, $changes, $quote['price'] - $net ); }
				$owner                 = $order->get_meta( '_wtbm_claim_owner' ) ? $order->get_meta( '_wtbm_claim_owner' ) : hash( 'sha256', 'order|' . $order->get_id() );
				$old_quote             = array(
					'movie'   => get_post_meta( $bid, 'wtbm_movie_id', true ),
					'theater' => get_post_meta( $bid, 'wtbm_theater_id', true ),
					'date'    => get_post_meta( $bid, 'wtbm_order_date', true ),
					'time'    => get_post_meta( $bid, 'wtbm_order_time', true ),
					'ids'     => (array) get_post_meta( $bid, 'wtbm_seat_ids', true ),
				);
				$old_quote['show_key'] = WTBM_Booking_Service::show_key( $old_quote['theater'], $old_quote['date'], $old_quote['time'] );
				$reservation           = $quote;
				if ( $old_quote['show_key'] === $quote['show_key'] ) {
					$reservation['ids'] = array_values( array_unique( array_merge( $quote['ids'], $old_quote['ids'] ) ) ); }
				$claim = WTBM_Booking_Service::claim( $reservation, $owner, $order->get_id(), $bid, $order->is_paid() );
				if ( is_wp_error( $claim ) ) {
					return $claim; }
				$old_key = WTBM_Booking_Service::show_key( get_post_meta( $bid, 'wtbm_theater_id', true ), get_post_meta( $bid, 'wtbm_order_date', true ), get_post_meta( $bid, 'wtbm_order_time', true ) );
				if ( $order->is_paid() && $quote['price'] < $net - 0.01 ) {
					$gateways = WC()->payment_gateways()->payment_gateways();
					$gateway  = $gateways[ $order->get_payment_method() ] ?? null;
					$refund   = wc_create_refund(
						array(
							'order_id'       => $order->get_id(),
							'amount'         => round( $net - $quote['price'], wc_get_price_decimals() ),
							'reason'         => __( 'Ticket change price difference', 'wptheaterly' ),
							'refund_payment' => self::automatic_refund( $order, round( $net - $quote['price'], wc_get_price_decimals() ) ),
							'restock_items'  => false,
							'line_items'     => array(
								$item->get_id() => array(
									'qty'          => 0,
									'refund_total' => round( $net - $quote['price'], wc_get_price_decimals() ),
									'refund_tax'   => array(),
								),
							),
						)
					);
					if ( is_wp_error( $refund ) ) {
						if ( $old_key !== $quote['show_key'] ) {
							$wpdb->delete(
								$wpdb->prefix . 'wtbm_claims',
								array(
									'booking_id' => $bid,
									'show_key'   => $quote['show_key'],
								)
							); }
						WTBM_Booking_Service::claim( $old_quote, $owner, $order->get_id(), $bid, $order->is_paid() );
						return $refund; }
					if ( ! $refund->get_refunded_payment() ) {
						$order->add_order_note( __( 'Manual reimbursement required for ticket change price difference.', 'wptheaterly' ) ); }
					$order = wc_get_order( $order->get_id() );
				}
				if ( $old_key !== $quote['show_key'] ) {
					$wpdb->delete(
						$wpdb->prefix . 'wtbm_claims',
						array(
							'booking_id' => $bid,
							'show_key'   => $old_key,
						)
					); }
				WTBM_Booking_Service::claim( $quote, $owner, $order->get_id(), $bid, $order->is_paid() );
				$refunded = (float) $order->get_total_refunded_for_item( $item->get_id() );
				$item->set_product( wc_get_product( get_post_meta( $quote['movie'], 'link_wc_product', true ) ) );
				$item->set_total( $quote['price'] + $refunded );
				$item->set_subtotal( $quote['price'] + $refunded );
				foreach ( array(
					'_wtbm_id'                => $quote['movie'],
					'_movie_id'               => $quote['movie'],
					'_theater_id'             => $quote['theater'],
					'_wtbm_date'              => $quote['date'],
					'_wtbm_time'              => $quote['time'],
					'_wtbm_tp'                => $quote['price'],
					'_wtbm_pricing_details' => $quote['pricing'],
					'_wtbm_selected_seats'    => $quote['names'],
					'_wtbm_selected_seat_ids' => $quote['ids'],
					'_wtbm_number_of_seats'   => $quote['count'],
				) as $key => $value ) {
					$item->update_meta_data( $key, $value ); }
				WTBM_Set_Pricing_Rules::order_details( $item, $quote['pricing'] );
				$item->save();
				$order = wc_get_order( $order->get_id() );
				if ( isset( $changes['name'] ) ) {
					$order->set_billing_first_name( sanitize_text_field( $changes['name'] ) );
					$order->set_billing_last_name( '' ); }
				if ( isset( $changes['phone'] ) ) {
					$order->set_billing_phone( sanitize_text_field( $changes['phone'] ) ); }
				if ( isset( $changes['email'] ) ) {
					$order->set_billing_email( sanitize_email( $changes['email'] ) ); }
				$order->calculate_totals();
				$order->save();
				if ( isset( $changes['status'] ) ) {
					if ( ! in_array( $changes['status'], array( 'pending', 'on-hold', 'processing', 'completed', 'cancelled' ), true ) ) {
						return new WP_Error( 'status', __( 'Invalid order status.', 'wptheaterly' ) ); }
					$order->update_status( $changes['status'] );
				}
				WTBM_Booking_Service::revoke( $bid );
				update_post_meta( $bid, 'wtbm_plan_snapshot', $quote['plan'] );
				WTBM_Booking_Service::sync_order( $order );
				WTBM_Booking_Service::log(
					'booking_changed',
					$bid,
					$order->get_id(),
					array(
						'old_price' => $net,
						'price'     => $quote['price'],
						'seats'     => $quote['names'],
						'show'      => $quote['date'] . ' ' . $quote['time'],
						'reason'    => sanitize_text_field( $changes['reason'] ?? '' ),
					)
				);
				do_action( 'wtbm_ticket_changed', $order->get_id() );
				return array(
					'message' => __( 'Booking updated and ticket reissued.', 'wptheaterly' ),
					'price'   => $quote['price'],
				);
			}
		);
	}
	/**
	 * Change payment.
	 *
	 * @param mixed $bid Bid.
	 * @param mixed $changes Changes.
	 * @param mixed $difference Difference.
	 */
	public static function change_payment( $bid, $changes, $difference ) {
		$existing = absint( get_post_meta( $bid, 'wtbm_change_payment', true ) );
		$old      = wc_get_order( $existing );
		if ( $old && $old->has_status( 'pending' ) && $old->get_meta( '_wtbm_change_request' ) === $changes ) {
			return array(
				'payment_url' => $old->get_checkout_payment_url(),
				'message'     => __( 'Pay the price difference to confirm this change.', 'wptheaterly' ),
			); }
		if ( $old && $old->has_status( 'pending' ) ) {
			$old->update_status( 'cancelled', 'Superseded ticket change request.' ); }
		$source = wc_get_order( get_post_meta( $bid, 'wtbm_order_id', true ) );
		if ( ! $source->get_meta( '_wtbm_initial_paid_total' ) ) {
			$source->update_meta_data( '_wtbm_initial_paid_total', $source->get_total() );
			$source->save(); }
		$order = wc_create_order( array( 'customer_id' => $source->get_customer_id() ) );
		if ( is_wp_error( $order ) ) {
			return $order; }
		$order->set_address( $source->get_address( 'billing' ), 'billing' );
		$fee = new WC_Order_Item_Fee();
		$fee->set_name( __( 'Ticket rescheduling price difference', 'wptheaterly' ) );
		$fee->set_amount( $difference );
		$fee->set_total( $difference );
		$order->add_item( $fee );
		$order->update_meta_data( '_wtbm_change_booking', $bid );
		$order->update_meta_data( '_wtbm_change_request', $changes );
		$order->update_meta_data( '_wtbm_change_version', get_post_meta( $bid, 'wtbm_ticket_version', true ) );
		$order->calculate_totals();
		$order->save();
		update_post_meta( $bid, 'wtbm_change_payment', $order->get_id() );
		WTBM_Booking_Service::log( 'change_payment_requested', $bid, $order->get_id(), array( 'amount' => $difference ) );
		return array(
			'payment_url' => $order->get_checkout_payment_url(),
			'message'     => __( 'Pay the price difference to confirm this change. Availability is checked again after payment.', 'wptheaterly' ),
		);
	}
	/**
	 * Complete change payment.
	 *
	 * @param mixed $order_id Order id.
	 */
	/**
	 * Check the gateway refund capability and the remaining original capture.
	 * Price-difference payments are separate orders; excess refunds need reconciliation.
	 *
	 * @param WC_Order $order Source order.
	 * @param float    $amount Refund amount including tax.
	 * @return bool
	 */
	public static function automatic_refund( $order, $amount ) {
		$gateways = WC()->payment_gateways()->payment_gateways();
		$gateway  = $gateways[ $order->get_payment_method() ] ?? null;
		$captured = $order->get_meta( '_wtbm_initial_paid_total' );
		$captured = '' !== $captured ? (float) $captured : (float) $order->get_total();
		return $gateway && $gateway->supports( 'refunds' ) && $amount <= max( 0, $captured - (float) $order->get_total_refunded() ) + 0.001;
	}

	/**
	 * Apply a paid price-difference order once.
	 *
	 * @param int $order_id Paid order ID.
	 */
	public static function complete_change_payment( $order_id ) {
		return WTBM_Booking_Service::lock(
			'topup|' . absint( $order_id ),
			static function () use ( $order_id ) {
				$order = wc_get_order( $order_id );
				if ( ! $order || ! $order->is_paid() || ! $order->get_meta( '_wtbm_change_booking' ) || $order->get_meta( '_wtbm_change_applied' ) ) {
					return; }
				$bid = absint( $order->get_meta( '_wtbm_change_booking' ) );
				if ( (string) $order->get_meta( '_wtbm_change_version' ) !== (string) get_post_meta( $bid, 'wtbm_ticket_version', true ) ) {
					$order->add_order_note( 'Ticket changed since payment was requested. Refund or staff review required.' );
					return; }
				$result = self::edit( absint( $order->get_meta( '_wtbm_change_booking' ) ), (array) $order->get_meta( '_wtbm_change_request' ), false, (float) $order->get_total() );
				if ( is_wp_error( $result ) || isset( $result['payment_url'] ) ) {
					$order->add_order_note( __( 'Reschedule requires staff review: seats or price changed.', 'wptheaterly' ) );
					return; }
				$order->update_meta_data( '_wtbm_change_applied', true );
				$order->save();
			}
		);
	}
	/**
	 * Build a transport URL without HTML entity encoding.
	 *
	 * @param int $order_id Order ID.
	 * @return string
	 */
	public static function receipt_url( $order_id ) {
		return add_query_arg(
			array(
				'action'   => 'wtbm_receipt',
				'order_id' => absint( $order_id ),
				'_wpnonce' => wp_create_nonce( 'wtbm_receipt' ),
			),
			admin_url( 'admin-post.php' )
		);
	}
}
WTBM_Operations::boot();
