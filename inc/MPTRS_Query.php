<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPTRS_Query' ) ) {
		class MPTRS_Query {
			public function __construct() {}
			public static function query_all_sold( $post_id, $date) {
				$_seat_booked_status      = MPTRS_Function::get_settings('mptrs_global_settings', 'set_book_status', array('processing', 'completed'));
				$seat_booked_status       = ! empty( $_seat_booked_status ) ? $_seat_booked_status : [];

				$date_filter              = ! empty( $date ) ? array(
					'key'     => 'mptrs_date',
					'value'   => $date,
					'compare' => 'LIKE'
				) : '';
				$pending_status_filter    = in_array( 'pending', $seat_booked_status ) ? array(
					'key'     => 'mptrs_order_status',
					'value'   => 'pending',
					'compare' => '='
				) : '';
				$on_hold_status_filter    = in_array( 'on-hold', $seat_booked_status ) ? array(
					'key'     => 'mptrs_order_status',
					'value'   => 'on-hold',
					'compare' => '='
				) : '';
				$processing_status_filter = in_array( 'processing', $seat_booked_status ) ? array(
					'key'     => 'mptrs_order_status',
					'value'   => 'processing',
					'compare' => '='
				) : '';
				$completed_status_filter  = in_array( 'completed', $seat_booked_status ) ? array(
					'key'     => 'mptrs_order_status',
					'value'   => 'completed',
					'compare' => '='
				) : '';
				$args                     = array(
					'post_type'      => 'mptrs_booking',
					'posts_per_page' => - 1,
					'meta_query'     => array(
						'relation' => 'AND',
						array(
							'relation' => 'AND',
							array(
								'key'     => 'mptrs_id',
								'value'   => $post_id,
								'compare' => '='
							),
							$date_filter
						),
						array(
							'relation' => 'OR',
							$pending_status_filter,
							$on_hold_status_filter,
							$processing_status_filter,
							$completed_status_filter
						)
					)
				);
				return new WP_Query( $args );
			}
		}
		new MPTRS_Query();
	}