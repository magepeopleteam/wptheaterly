<?php

if (!defined('ABSPATH')) {
    die;
}

if (!class_exists('WTBP_Admin_Manage_Ajax')) {
    class WTBP_Admin_Manage_Ajax
    {
        public function __construct(){

            add_action('wp_ajax_mptrs_insert_movie_post', [ $this, 'mptrs_insert_movie_post' ]);
            add_action('wp_ajax_nopriv_mptrs_insert_movie_post', [ $this, 'mptrs_insert_movie_post' ]);

            add_action('wp_ajax_wtbm_update_movie_post', [ $this, 'wtbm_update_movie_post' ]);
            add_action('wp_ajax_wtbm_update_movie_post', [ $this, 'wtbm_update_movie_post' ]);

            add_action('wp_ajax_wtbp_add_edit_movie_form', [ $this, 'wtbp_add_edit_movie_form' ]);
            add_action('wp_ajax_nopriv_wtbp_add_edit_movie_form', [ $this, 'wtbp_add_edit_movie_form' ]);

            add_action('wp_ajax_mptrs_insert_theater_post', [ $this, 'mptrs_insert_theater_post' ]);
            add_action('wp_ajax_nopriv_mptrs_insert_theater_post', [ $this, 'mptrs_insert_theater_post' ]);

            add_action('wp_ajax_wtbp_add_edit_theater_form', [ $this, 'wtbp_add_edit_theater_form' ]);
            add_action('wp_ajax_nopriv_wtbp_add_edit_theater_form', [ $this, 'wtbp_add_edit_theater_form' ]);

            add_action('wp_ajax_mptrs_update_theater_post', [ $this, 'mptrs_update_theater_post' ]);
            add_action('wp_ajax_nopriv_mptrs_update_theater_post', [ $this, 'mptrs_update_theater_post' ]);

        }

        function mptrs_insert_movie_post() {
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $cpt = MPTRS_Function::get_movie_cpt();
            $title       = sanitize_text_field( $_POST['title']);
            $genre       = sanitize_text_field( $_POST['genre']);
            $duration    = sanitize_text_field( $_POST['duration']);
            $rating      = floatval($_POST['rating']);
            $releaseDate = sanitize_text_field($_POST['release_date']);
            $poster      = esc_url_raw($_POST['poster']);
            $description = sanitize_textarea_field($_POST['description']);

            $post_id = wp_insert_post([
                'post_title'   => $title,
                'post_type'    => $cpt, // your custom post type
                'post_status'  => 'publish',
                'post_content' => $description,
            ]);

            if ( $post_id ) {
                // Save meta data
                update_post_meta($post_id, 'wtbp_movie_genre', $genre);
                update_post_meta($post_id, 'wtbp_movie_duration', $duration);
                update_post_meta($post_id, 'wtbp_movie_rating', $rating);
                update_post_meta($post_id, 'wtbp_movie_release_date', $releaseDate);
                update_post_meta($post_id, 'wtbp_movie_poster', $poster);

                wp_send_json_success(get_post($post_id));
            } else {
                wp_send_json_error("Failed to insert post");
            }
        }

        function wtbm_update_movie_post() {
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');
            $cpt = MPTRS_Function::get_movie_cpt();
            $title       = sanitize_text_field( $_POST['title']);
            $genre       = sanitize_text_field( $_POST['genre']);
            $duration    = sanitize_text_field( $_POST['duration']);
            $rating      = floatval($_POST['rating']);
            $releaseDate = sanitize_text_field($_POST['release_date']);
            $poster      = esc_url_raw($_POST['poster']);
            $description = sanitize_textarea_field($_POST['description']);
            $post_id     = isset($_POST['post_id']) ? intval($_POST['post_id']) : '';
            $post_data = [
                'post_title'   => $title,
                'post_type'    => $cpt,
                'post_status'  => 'publish',
                'post_content' => $description,
            ];

            if ( $post_id ) {
                $post_data['ID'] = $post_id;
                $updated_post_id = wp_update_post( $post_data );
                // Save meta data
                update_post_meta($post_id, 'wtbp_movie_genre', $genre);
                update_post_meta($post_id, 'wtbp_movie_duration', $duration);
                update_post_meta($post_id, 'wtbp_movie_rating', $rating);
                update_post_meta($post_id, 'wtbp_movie_release_date', $releaseDate);
                update_post_meta($post_id, 'wtbp_movie_poster', $poster);

                wp_send_json_success( get_post( $post_id ) );
            } else {
                wp_send_json_error("Failed to Edit post");
            }

        }

        function mptrs_insert_theater_post() {
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $cpt = MPTRS_Function::get_theater_cpt();

            $title       = sanitize_text_field( $_POST['name']);
            $type      = sanitize_text_field( $_POST['type']);
            $rows    = sanitize_text_field( $_POST['rows']);
            $seatsPerRow     = sanitize_text_field($_POST['seatsPerRow']);
            $soundSystem = sanitize_text_field($_POST['soundSystem']);
            $status      = sanitize_text_field($_POST['status']);
            $description = sanitize_textarea_field($_POST['description']);

            $post_id = wp_insert_post([
                'post_title'   => $title,
                'post_type'    => $cpt,
                'post_status'  => 'publish',
                'post_content' => $description,
            ]);

            if ( $post_id ) {
                // Save meta data
                update_post_meta( $post_id, 'wtbp_theater_type', $type );
                update_post_meta( $post_id, 'wtbp_theater_rows', $rows );
                update_post_meta( $post_id, 'wtbp_theater_seatsPerRow', $seatsPerRow );
                update_post_meta( $post_id, 'wtbp_theater_soundSystem', $soundSystem );
                update_post_meta( $post_id, 'wtbp_theater_status', $status );

                wp_send_json_success( get_post( $post_id ) );
            } else {
                wp_send_json_error("Failed to insert post" );
            }
        }

        function mptrs_update_theater_post() {
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $cpt = MPTRS_Function::get_theater_cpt();

            $title       = sanitize_text_field( $_POST['name']);
            $type      = sanitize_text_field( $_POST['type']);
            $rows    = sanitize_text_field( $_POST['rows']);
            $seatsPerRow     = sanitize_text_field($_POST['seatsPerRow']);
            $soundSystem = sanitize_text_field($_POST['soundSystem']);
            $status      = sanitize_text_field($_POST['status']);
            $description = sanitize_textarea_field($_POST['description']);

            $post_id     = isset($_POST['post_id']) ? intval($_POST['post_id']) : '';
            $post_data['ID'] = $post_id;
            $post_data = [
                'post_title'   => $title,
                'post_type'    => $cpt,
                'post_status'  => 'publish',
                'post_content' => $description,
            ];

            if ( $post_id ) {

                $updated_post_id = wp_update_post( $post_data );
                // Save meta data
                update_post_meta( $post_id, 'wtbp_theater_type', $type );
                update_post_meta( $post_id, 'wtbp_theater_rows', $rows );
                update_post_meta( $post_id, 'wtbp_theater_seatsPerRow', $seatsPerRow );
                update_post_meta( $post_id, 'wtbp_theater_soundSystem', $soundSystem );
                update_post_meta( $post_id, 'wtbp_theater_status', $status );

                wp_send_json_success( 'Successfully updated theater' );
            } else {
                wp_send_json_error("Failed to edit theater" );
            }
        }

        public function wtbp_add_edit_movie_form(){
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
            $movie_data = [];
            if( $post_id == '' ){
                $type = 'add';
            }else{
                $type = 'edit';
                $movie_data = WTBM_Layout_Functions::get_movies_data_by_id( $post_id );
            }
            $add_form = WTBM_Layout_Functions::add_edit_new_movie_html( $type, $movie_data );

            wp_send_json_success(  $add_form );

        }

        public function wtbp_add_edit_theater_form(){
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
            if( $post_id == '' ){
                $theater_data = null;
                $type = 'add';
            }else{
                $type = 'edit';
                $theater_data = WTBM_Layout_Functions::get_theater_data_by_id( $post_id );
            }
            $add_form = WTBM_Layout_Functions::add_edit_theater_html( $type, $theater_data );

            wp_send_json_success(  $add_form );

        }


    }

    new WTBP_Admin_Manage_Ajax();
}