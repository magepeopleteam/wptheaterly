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

            add_action( 'wp_ajax_wtbm_get_single_theater_show_time_data', [ $this, 'wtbm_get_single_theater_show_time_data' ] );
            add_action( 'wp_ajax_nopriv_wtbm_get_single_theater_show_time_data', [ $this, 'wtbm_get_single_theater_show_time_data' ] );

            add_action( 'wp_ajax_wtbm_get_theater_seat_map_data', [ $this, 'wtbm_get_theater_seat_map_data' ] );
            add_action( 'wp_ajax_nopriv_wtbm_get_theater_seat_map_data', [ $this, 'wtbm_get_theater_seat_map_data' ] );

            add_action( 'wp_ajax_wtbm_theater_ticket_booking', [ $this, 'wtbm_theater_ticket_booking' ] );
            add_action( 'wp_ajax_nopriv_wtbm_theater_ticket_booking', [ $this, 'wtbm_theater_ticket_booking' ] );

            add_action( 'wp_ajax_wtbm_apply_pricing_rules_total_price', [ $this, 'wtbm_apply_pricing_rules_total_price' ] );
            add_action( 'wp_ajax_nopriv_wtbm_apply_pricing_rules_total_price', [ $this, 'wtbm_apply_pricing_rules_total_price' ] );


            add_action( 'wp_ajax_wtbm_pricing_dates', array( $this, 'pricing_dates' ) );
            add_action( 'wp_ajax_nopriv_wtbm_pricing_dates', array( $this, 'pricing_dates' ) );
//            $lay_outs = new WTBM_Details_Layout();

        }

        public function pricing_dates() {
            check_ajax_referer( 'wtbm_nonce', 'nonce' );
            $dates = array_slice( (array) ( $_POST['dates'] ?? array() ), 0, 366 );
            $theater = absint( $_POST['theater'] ?? 0 );
            $time = sanitize_text_field( wp_unslash( $_POST['time'] ?? '' ) );
            $seats = min( 100, absint( $_POST['seats'] ?? 0 ) );
            $result = array();
            foreach ( $dates as $date ) {
                $date = sanitize_text_field( wp_unslash( $date ) );
                $parsed = DateTimeImmutable::createFromFormat( '!Y-m-d', $date, wp_timezone() );
                if ( ! $parsed || $parsed->format( 'Y-m-d' ) !== $date ) { continue; }
                $result[ $date ] = WTBM_Set_Pricing_Rules::date_indicator( $date, $theater, $time, $seats );
            }
            wp_send_json_success( $result );
        }

        public function wtbm_get_movies_data_by_date(){
            if ( isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wtbm_nonce') ) {
                if ( isset($_POST['date'] ) ) {
                    $movies = '';
                    $date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
                    // Fetch movies or showtimes based on date
                    if( $date ){
                        $movies = WTBM_Details_Layout::display_date_wise_movies_ajax( $date );
                    }

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
                    $date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
                    $movie_id = isset( $_POST['movie_id'] ) ? sanitize_text_field( wp_unslash( $_POST['movie_id'] ) ) : '';
                    // Fetch movies or showtimes based on date
                    $theater_show_times = WTBM_Details_Layout::display_theater_show_time( $movie_id, $date );
                    wp_send_json_success($theater_show_times);
                } else {
                    wp_send_json_error('No date provided');
                }
            }
            wp_die();
        }

        public function wtbm_get_single_theater_show_time_data(){

            if ( isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wtbm_nonce') ) {

                if (isset($_POST['date']) && isset($_POST['movie_id'])) {
                    $date = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
                    $movie_id = isset( $_POST['movie_id'] ) ? sanitize_text_field( wp_unslash( $_POST['movie_id'] ) ) : '';
                    // Fetch movies or showtimes based on date
                    $theater_show_times = WTBM_Details_Layout::display_theater_show_time_single_movie( $movie_id, $date );

                    $search_time = '';
                    $theater_id = '';
                    $show_times = WTBM_Details_Layout::get_wtbm_show_time_by_date_and_movie_id( $movie_id, $date );
                    if( is_array( $show_times ) && !empty( $show_times ) ){
                        foreach ($show_times as $theater_id => $times) {
                            $search_time = isset( $times[0] ) ? $times[0] : '';
                            break;
                        }
                    }

                    $seat_maps = '';
                    if( $theater_id && $search_time ){
                        $seat_maps = WTBM_Manage_Ajax::get_seat_map_by_date_time_movie_theater_id( $theater_id, $movie_id,  $date, $search_time );
                    }


                    $result = array(
                        'theater_show_times' => $theater_show_times,
                        'seat_maps' => $seat_maps,
                        'theater_id' => $theater_id,
                        'show_time' => $search_time,
                    );

                    wp_send_json_success( $result );
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
                $movie_id = isset( $_POST['activeMovieId']) ? sanitize_text_field( wp_unslash($_POST['activeMovieId'] ) ) : '';
                $search_time = isset( $_POST['movie_time_slot']) ? sanitize_text_field( wp_unslash($_POST['movie_time_slot'] ) ) : '';
                $get_date = isset( $_POST['movie_date']) ? sanitize_text_field( wp_unslash($_POST['movie_date'] ) ) : '';

                /*if( $theater_id && $movie_id &&  $get_date && $search_time ){
                    $not_available = self::getAvailableSeats( $theater_id, $movie_id, $get_date, $search_time );
                }else{
                    $not_available = [];
                }

                if( $theater_id ){
                    $seat_map = WTBM_Details_Layout::display_theater_seat_mapping( $theater_id, $not_available );
                }*/
                $seat_map = self::get_seat_map_by_date_time_movie_theater_id( $theater_id, $movie_id,  $get_date, $search_time );

            }

            wp_send_json_success([
                'message' => 'Categories Data getting successfully.!',
                'wtbm_seatMaps' => $seat_map,
            ]);

        }

        public static function get_seat_map_by_date_time_movie_theater_id( $theater_id, $movie_id,  $get_date, $search_time ){
            $seat_map = '';
            if( $theater_id && $movie_id &&  $get_date && $search_time ){
                $not_available = self::getAvailableSeats( $theater_id, $movie_id, $get_date, $search_time );
            }else{
                $not_available = [];
            }

            if( $theater_id ){
                $seat_map = WTBM_Details_Layout::display_theater_seat_mapping( $theater_id, $not_available );
            }

            return $seat_map;
        }

        function wtbm_apply_pricing_rules_total_price(){

            if ( !isset($_POST['nonce']) || !wp_verify_nonce( sanitize_text_field( wp_unslash($_POST['nonce']) ), 'wtbm_nonce') ) {
                wp_send_json_error('Security check failed!');
            }

            if ( empty($_POST['booking_date']) ) {
                wp_send_json_error('Booking data missing!');
            }

            $booking_date = isset( $_POST['booking_date'] ) ? sanitize_text_field( wp_unslash($_POST['booking_date'] ) ) : '';
            $theater_id = isset( $_POST['theater_id'] ) ? sanitize_text_field( wp_unslash($_POST['theater_id'] ) ) : '';
            $booking_time = isset( $_POST['booking_time'] ) ? sanitize_text_field( wp_unslash($_POST['booking_time'] ) ) : '';
            $total_amount = isset( $_POST['total_amount'] ) ? sanitize_text_field( wp_unslash($_POST['total_amount'] ) ) : 0 ;
            $seat_count = isset( $_POST['seat_count'] ) ? sanitize_text_field( wp_unslash($_POST['seat_count'] ) ) : '';
            $day_of_week = strtolower(date('l', strtotime($booking_date)));;
//            $total_price = $total_amount;

            $total_price = WTBM_Set_Pricing_Rules::calculate_price_by_rules(  $total_amount, $day_of_week, $booking_date, $theater_id, $booking_time, $seat_count  );

            wp_send_json_success([
                'rules_price' => $total_price,
                'message'     => 'Price calculated successfully'
            ]);
        }
        function wtbm_theater_ticket_booking(){
            check_ajax_referer( 'wtbm_nonce', 'nonce' );
            WTBM_Operations::response( WTBM_Operations::purchase( wp_unslash( $_POST ) ) );
        }

        public static function getAvailableSeats( $theater_id, $movie_id, $get_date, $search_time ) {
            return WTBM_Booking_Service::occupied( $theater_id, $movie_id, $get_date, $search_time, 0, WTBM_Booking_Service::owner() );
        }
    }
    new WTBM_Manage_Ajax();
}
