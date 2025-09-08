<?php

/*
   * @Author 		rubelcuet10.com
   * Copyright: 	mage-people.com
   */
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.
if ( ! class_exists( 'WTBM_Manage_Ajax' ) ) {
    class WTBM_Manage_Ajax{
        public function __construct(){

            add_action( 'wp_ajax_wtbm_get_movies_data_by_date', [ $this, 'wtbm_get_movies_data_by_date' ] );
            add_action( 'wp_ajax_nopriv_wtbm_get_movies_data_by_date', [ $this, 'wtbm_get_movies_data_by_date' ] );

            add_action( 'wp_ajax_wtbm_get_theater_show_time_data', [ $this, 'wtbm_get_theater_show_time_data' ] );
            add_action( 'wp_ajax_nopriv_wtbm_get_theater_show_time_data', [ $this, 'wtbm_get_theater_show_time_data' ] );

            add_action( 'wp_ajax_wtbm_get_theater_seat_map_data', [ $this, 'wtbm_get_theater_seat_map_data' ] );
            add_action( 'wp_ajax_nopriv_wtbm_get_theater_seat_map_data', [ $this, 'wtbm_get_theater_seat_map_data' ] );

            add_action( 'wp_ajax_wtbm_theater_ticket_booking', [ $this, 'wtbm_theater_ticket_booking' ] );
            add_action( 'wp_ajax_nopriv_wtbm_theater_ticket_booking', [ $this, 'wtbm_theater_ticket_booking' ] );

//            $lay_outs = new WTBM_Details_Layout();

        }

        public function wtbm_get_movies_data_by_date(){
            if ( isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wtbm_nonce') ) {
                if (isset($_POST['date'])) {
                    $date = sanitize_text_field($_POST['date']);
                    // Fetch movies or showtimes based on date
                    $movies = WTBM_Details_Layout::display_date_wise_movies($date);
                    wp_send_json_success($movies);
                } else {
                    wp_send_json_error('No date provided');
                }
            }
            wp_die();
        }

        public function wtbm_get_theater_show_time_data(){

            if ( isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wtbm_nonce') ) {

                if (isset($_POST['date']) && isset($_POST['movie_id'])) {
                    $date = sanitize_text_field($_POST['date']);
                    $movie_id = sanitize_text_field($_POST['movie_id']);
                    // Fetch movies or showtimes based on date
                    $theater_show_times = WTBM_Details_Layout::display_theater_show_time($movie_id, $date);

                    wp_send_json_success($theater_show_times);
                } else {
                    wp_send_json_error('No date provided');
                }
            }
            wp_die();
        }


        function wtbm_get_theater_seat_map_data(){

            $seat_map = '';

            if ( isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wtbm_nonce') ) {
                $theater_id = isset( $_POST['theater_id']) ? sanitize_text_field( wp_unslash($_POST['theater_id'] ) ) : '';
                $movie_id = isset( $_POST['theater_id']) ? sanitize_text_field( wp_unslash($_POST['activeMovieId'] ) ) : '';
                $search_time = isset( $_POST['movie_time_slot']) ? sanitize_text_field( wp_unslash($_POST['movie_time_slot'] ) ) : '';
                $get_date = isset( $_POST['movie_date']) ? sanitize_text_field( wp_unslash($_POST['movie_date'] ) ) : '';

                if( $theater_id && $movie_id &&  $get_date && $search_time ){
                    $not_available = self::getAvailableSeats( $theater_id, $movie_id, $get_date, $search_time );
                }else{
                    $not_available = [];
                }

                if( $theater_id ){
                    $seat_map = WTBM_Details_Layout::display_theater_seat_mapping( $theater_id, $not_available );
                }
            }

            wp_send_json_success([
                'message' => 'Categories Data getting successfully.!',
                'wtbm_seatMaps' => $seat_map,
            ]);

        }
        function wtbm_theater_ticket_booking(){

            if ( isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wtbm_nonce') ) {

                $original_post_id = intval($_POST['movie_id']);
                $product_id = intval(get_post_meta($original_post_id, 'link_wc_product', true));
                $quantity = 1;

                $cart_item_data = [
                    'wtbm_movie_id' => $original_post_id,
                    'wtbm_product_id' => $product_id,
                    'theater_id' => intval($_POST['theater_id']),
                    'booking_date' => sanitize_text_field( $_POST['booking_date'] ),
                    'booking_time' => sanitize_text_field( $_POST['booking_time'] ),
                    'seat_count' => intval($_POST['seat_count']),
                    'seat_names' => $_POST['seat_names'],
                    'booked_seat_ids' => $_POST['booked_seat_ids'],
                    'wtbm_price' => floatval($_POST['total_amount']),
                    'user_name' => sanitize_text_field($_POST['userName']),
                    'user_phone_num' => sanitize_text_field($_POST['userPhoneNum']),
                ];

                if (!class_exists('WC_Cart')) {
                    wp_send_json_error('WooCommerce is not active.');
                }

                WC()->cart->empty_cart();

                $cart_item_key = WC()->cart->add_to_cart( $product_id, $quantity, 0, [], $cart_item_data );

                if ($cart_item_key) {
                    wp_send_json_success('Item added to cart.');
                } else {
                    wp_send_json_error('Failed to add to cart.');
                }
            }

            wp_send_json_success([
                'message' => 'Add Cart Failed.!',
                'wtbm_seatMaps' => '',
            ]);

        }

        public static function getAvailableSeats( $theater_id, $movie_id, $get_date, $search_time ) {
            global $wpdb;
            $not_available = [];
            $bookings = get_posts([
                'post_type'      => 'wtbm_booking',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'meta_query'     => [
                    'relation' => 'AND',
                    [
                        'key'   => 'wtbm_movie_id',
                        'value' => $movie_id,
                    ],
                    [
                        'key'   => 'wtbm_theater_id',
                        'value' => $theater_id,
                    ],
                    [
                        'key'   => 'wtbm_order_date',
                        'value' => $get_date,
                    ],
                    [
                        'key'   => 'wtbm_order_time',
                        'value' => $search_time,
                    ],
                ]
            ]);

            if ( !empty($bookings) ) {
                foreach ( $bookings as $booking ) {
                    $seat_ids = get_post_meta( $booking->ID, 'wtbm_seat_ids', true );
                    if ( !empty($seat_ids) && is_serialized($seat_ids) ) {
                        $seat_ids = maybe_unserialize($seat_ids);
                    }

                    if ( is_array($seat_ids) ) {
                        $not_available = array_merge( $not_available, $seat_ids );
                    }
                }
            }

            return array_unique( $not_available );
        }



    }

    new WTBM_Manage_Ajax();
}