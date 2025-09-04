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

//            $lay_outs = new WTBM_Details_Layout();

        }

        public function wtbm_get_movies_data_by_date(){
            if ( isset( $_POST['date'] ) ) {
                $date = sanitize_text_field( $_POST['date'] );
                // Fetch movies or showtimes based on date
                $movies = WTBM_Details_Layout::display_date_wise_movies( $date );
                wp_send_json_success( $movies );
            } else {
                wp_send_json_error( 'No date provided' );
            }
            wp_die();
        }

        public function wtbm_get_theater_show_time_data(){
            if ( isset( $_POST['date'] ) && isset( $_POST['movie_id'] ) ) {
                $date = sanitize_text_field( $_POST['date'] );
                $movie_id = sanitize_text_field( $_POST['movie_id'] );
                // Fetch movies or showtimes based on date
                $theater_show_times = WTBM_Details_Layout::display_theater_show_time( $movie_id, $date  );
                error_log( print_r( [
                    '$movie_id' => $movie_id ,
                    '$date' => $date ,
                    '$theater_show_times' => $theater_show_times ,
                ], true ) );

                wp_send_json_success( $theater_show_times );
            } else {
                wp_send_json_error( 'No date provided' );
            }
            wp_die();
        }


    }

    new WTBM_Manage_Ajax();
}