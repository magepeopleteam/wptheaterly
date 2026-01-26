<?php

if (!defined('ABSPATH')) {
    die;
}

if (!class_exists('WTBP_Manage_Movie')) {
    class WTBP_Manage_Movie
    {
        public function __construct(){

            add_action('wp_ajax_mptrs_insert_movie_post', [ $this, 'mptrs_insert_movie_post' ]);
            add_action('wp_ajax_nopriv_mptrs_insert_movie_post', [ $this, 'mptrs_insert_movie_post' ]);

            add_action('wp_ajax_wtbm_update_movie_post', [ $this, 'wtbm_update_movie_post' ]);
            add_action('wp_ajax_wtbm_update_movie_post', [ $this, 'wtbm_update_movie_post' ]);

            add_action('wp_ajax_wtbp_add_edit_movie_form', [ $this, 'wtbp_add_edit_movie_form' ]);
            add_action('wp_ajax_nopriv_wtbp_add_edit_movie_form', [ $this, 'wtbp_add_edit_movie_form' ]);

            add_action('wp_ajax_wtbt_delete_custom_post', [ $this, 'wtbt_delete_custom_post' ]);
            add_action('wp_ajax_nopriv_wtbt_delete_custom_post', [ $this, 'wtbt_delete_custom_post' ]);

        }

        public function wtbt_delete_custom_post() {
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : '';

            if ( $post_id ) {
                $post = get_post($post_id);
                if ($post) {
                    $result = wp_trash_post($post_id);
                    if ($result) {
                        wp_send_json_success([
                            'success' => true,
                            'message' => 'Post moved to trash successfully.',
                            'post_id' => $post_id
                        ]);
                    } else {
                        wp_send_json_error([
                            'success' => false,
                            'message' => 'Failed to move post to trash.'
                        ]);
                    }
                } else {
                    wp_send_json_error([
                        'success' => false,
                        'message' => 'Post not found.'
                    ]);
                }
            } else {
                wp_send_json_error([
                    'success' => false,
                    'message' => 'Invalid post ID.'
                ]);
            }
        }


        function mptrs_insert_movie_post() {
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $cpt = WTBM_Function::get_movie_cpt();
            $title       = sanitize_text_field( $_POST['title']);
            $genre       = sanitize_text_field( $_POST['genre']);
            $duration    = sanitize_text_field( $_POST['duration']);
            $rating      = floatval($_POST['rating']);
            $releaseDate = sanitize_text_field($_POST['release_date']);
            $poster      = isset( $_POST['poster'] ) ? esc_url_raw($_POST['poster']) : '';
            $poster_id      = sanitize_textarea_field($_POST['poster_id']);
            $description = sanitize_textarea_field($_POST['description']);
            $active = sanitize_textarea_field($_POST['active']);

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
                update_post_meta($post_id, 'wtbp_movie_active', $active);
                update_post_meta($post_id, 'wtbp_movie_poster_id', $poster_id);

                wp_send_json_success( get_post( $post_id ) );
            } else {
                wp_send_json_error("Failed to insert post");
            }
        }

        function wtbm_update_movie_post() {
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');
            $cpt = WTBM_Function::get_movie_cpt();
            $title          = sanitize_text_field( $_POST['title']);
            $genre          = sanitize_text_field( $_POST['genre']);
            $duration       = sanitize_text_field( $_POST['duration']);
            $rating         = floatval($_POST['rating']);
            $releaseDate    = sanitize_text_field($_POST['release_date']);
            $poster         = isset( $_POST['poster'] ) ? esc_url_raw( $_POST['poster'] ) : '';
            $poster_id      = sanitize_textarea_field($_POST['poster_id']);
            $description    = sanitize_textarea_field($_POST['description']);
            $post_id        = isset($_POST['post_id']) ? intval($_POST['post_id']) : '';
            $active         = sanitize_textarea_field($_POST['active']);
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
                update_post_meta( $post_id, 'wtbp_movie_genre', $genre );
                update_post_meta( $post_id, 'wtbp_movie_duration', $duration );
                update_post_meta( $post_id, 'wtbp_movie_rating', $rating );
                update_post_meta( $post_id, 'wtbp_movie_release_date', $releaseDate );
                update_post_meta( $post_id, 'wtbp_movie_poster', $poster );
                update_post_meta( $post_id, 'wtbp_movie_active', $active );
                update_post_meta( $post_id, 'wtbp_movie_poster_id', $poster_id );

                $status = 'inactive';
                if( $active === 'true' ){
                    $status = 'active';
                }
                $new_movie[0] = array(
                    'id'                => $post_id,
                    'title'             => $title,
                    'active'            => $active,
                    'genre'             => $genre,
                    'duration'          => $duration,
                    'rating'            => $rating,
                    'release_date'      => $releaseDate,
                    'poster_image_url'  => esc_url( wp_get_attachment_url( $poster_id ) ),
                    'description'       => $title,
                    'status'            => $status,
                );

                $updated_movie = WTBM_Layout_Functions::display_movies_data( $new_movie );

                $result = array(
                    'success' => true,
                    'updated_movie' => $updated_movie,
                    'movie_title' => $title,
                );

                wp_send_json_success( $result );
            } else {
                wp_send_json_error("Failed to Edit post");
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




    }

    new WTBP_Manage_Movie();
}