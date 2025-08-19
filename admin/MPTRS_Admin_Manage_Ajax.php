<?php

if (!defined('ABSPATH')) {
    die;
}

if (!class_exists('MPTRS_Admin_Manage_Ajax')) {
    class MPTRS_Admin_Manage_Ajax
    {

        public function __construct(){
            add_action('wp_ajax_mptrs_insert_movie_post', [ $this, 'mptrs_insert_movie_post' ]);
            add_action('wp_ajax_nopriv_mptrs_insert_movie_post', [ $this, 'mptrs_insert_movie_post' ]);
        }

        function mptrs_insert_movie_post() {
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $cpt = MPTRS_Function::get_movie_cpt();
            $title       = sanitize_text_field($_POST['title']);
            $genre       = sanitize_text_field($_POST['genre']);
            $duration    = sanitize_text_field($_POST['duration']);
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
                update_post_meta($post_id, 'genre', $genre);
                update_post_meta($post_id, 'duration', $duration);
                update_post_meta($post_id, 'rating', $rating);
                update_post_meta($post_id, 'release_date', $releaseDate);
                update_post_meta($post_id, 'poster', $poster);

                wp_send_json_success(get_post($post_id));
            } else {
                wp_send_json_error("Failed to insert post");
            }
        }

    }

    new MPTRS_Admin_Manage_Ajax();
}