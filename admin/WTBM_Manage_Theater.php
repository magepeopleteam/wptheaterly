<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WTBM_Manage_Theater' ) ) {
    class WTBM_Manage_Theater{
        public function __construct(){
            add_action('wp_ajax_mptrs_insert_theater_post', [ $this, 'mptrs_insert_theater_post' ]);
            add_action('wp_ajax_nopriv_mptrs_insert_theater_post', [ $this, 'mptrs_insert_theater_post' ]);

            add_action('wp_ajax_wtbp_add_edit_theater_form', [ $this, 'wtbp_add_edit_theater_form' ]);
            add_action('wp_ajax_nopriv_wtbp_add_edit_theater_form', [ $this, 'wtbp_add_edit_theater_form' ]);

            add_action('wp_ajax_mptrs_update_theater_post', [ $this, 'mptrs_update_theater_post' ]);
            add_action('wp_ajax_nopriv_mptrs_update_theater_post', [ $this, 'mptrs_update_theater_post' ]);

            add_action('wp_ajax_wtbm_theater_seat_map_add', [ $this, 'wtbm_theater_seat_map_add' ]);
            add_action('wp_ajax_nopriv_wtbm_theater_seat_map_add', [ $this, 'wtbm_theater_seat_map_add' ]);
        }

        public function wtbm_theater_seat_map_add(){
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $post_id     = isset($_POST['post_id']) ? intval($_POST['post_id']) : '';
            if( $post_id ) {
                $seat_maps_meta_data = isset($_POST['seat_maps_meta_data']) ? json_decode(sanitize_text_field(wp_unslash($_POST['seat_maps_meta_data'])), true) : [];
                $seat_plan_texts = isset($_POST['seatPlanTexts']) ? json_decode(sanitize_text_field(wp_unslash($_POST['seatPlanTexts'])), true) : '';
                $dynamicShapes = isset($_POST['dynamicShapes']) ? json_decode(sanitize_text_field(wp_unslash($_POST['dynamicShapes'])), true) : '';

                $seat_plan_data = array(
                    'seat_data' => $seat_maps_meta_data,
                    'seat_text_data' => $seat_plan_texts,
                    'dynamic_shapes' => $dynamicShapes,
                );

                $result = update_post_meta( $post_id, 'wtbp_theater_seat_map', $seat_plan_data );

                wp_send_json_success(array(
                    'message' => 'Seat plan saved successfully',
                    'post_id' => $result,
                ));


            }else{
                wp_send_json_error( 'Invalid Post' );
            }

        }


        function mptrs_insert_theater_post() {
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $cpt = MPTRS_Function::get_theater_cpt();

            $title          = sanitize_text_field( $_POST['name']);
            $type           = sanitize_text_field( $_POST['type']);
            $rows           = sanitize_text_field( $_POST['rows']);
            $seatsPerRow    = sanitize_text_field($_POST['seatsPerRow']);
            $soundSystem    = sanitize_text_field($_POST['soundSystem']);
            $status         = sanitize_text_field($_POST['status']);
            $description    = sanitize_textarea_field($_POST['description']);

            $categories = isset( $_POST['wtbm_categories'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['wtbm_categories'] ) ), true ) : '';

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
//                update_post_meta( $post_id, 'wtbp_theater_seat_map', $seat_plan_data );
                update_post_meta( $post_id, 'wtbp_theater_category', $categories );


                $theater_data =array(
                    0=>WTBM_Layout_Functions::get_theater_data_by_id( $post_id ),
                ) ;

                $new_theater = WTBM_Layout_Functions::display_theater_date( $theater_data );
                $seat_map = WTBM_Theater_Seat_Mapping::render_seat_mapping_meta_box( $post_id, 'add', $rows, $seatsPerRow, );

                $result = array(
                    'new_theater' => $new_theater,
                    'seat_map' => $seat_map,
                );

                wp_send_json_success( $result );
            } else {
                wp_send_json_error("Failed to insert post" );
            }
        }

        function mptrs_update_theater_post() {
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $cpt            = MPTRS_Function::get_theater_cpt();
            $title          = sanitize_text_field( $_POST['name']);
            $type           = sanitize_text_field( $_POST['type']);
            $rows           = sanitize_text_field( $_POST['rows']);
            $seatsPerRow    = sanitize_text_field($_POST['seatsPerRow']);
            $soundSystem    = sanitize_text_field($_POST['soundSystem']);
            $status         = sanitize_text_field($_POST['status']);
            $description    = sanitize_textarea_field($_POST['description']);

            $post_id     = isset($_POST['post_id']) ? intval($_POST['post_id']) : '';
            $post_data = [
                'ID'           => $post_id,
                'post_title'   => $title,
                'post_type'    => $cpt,
                'post_status'  => 'publish',
                'post_content' => $description,
            ];

            $updated_post_id = wp_update_post( $post_data );

            if ( $updated_post_id ) {

                $seat_maps_meta_data = isset( $_POST['seat_maps_meta_data'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['seat_maps_meta_data'] ) ), true ) : [];
                $seat_plan_texts= isset( $_POST['seatPlanTexts'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['seatPlanTexts'] ) ), true ) : '' ;
                $dynamicShapes = isset( $_POST['dynamicShapes'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['dynamicShapes'] ) ), true ) : '';
                $seat_plan_data = array(
                    'seat_data' => $seat_maps_meta_data,
                    'seat_text_data' => $seat_plan_texts,
                    'dynamic_shapes' => $dynamicShapes,
                );
                $categories = isset( $_POST['wtbm_categories'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['wtbm_categories'] ) ), true ) : '';

                // Save meta data
                update_post_meta( $updated_post_id, 'wtbp_theater_type', $type );
                update_post_meta( $updated_post_id, 'wtbp_theater_rows', $rows );
                update_post_meta( $updated_post_id, 'wtbp_theater_seatsPerRow', $seatsPerRow );
                update_post_meta( $updated_post_id, 'wtbp_theater_soundSystem', $soundSystem );
                update_post_meta( $updated_post_id, 'wtbp_theater_status', $status );

                update_post_meta( $updated_post_id, 'wtbp_theater_seat_map', $seat_plan_data );
                update_post_meta( $updated_post_id, 'wtbp_theater_category', $categories );



                $theater_data =array(
                    0=>WTBM_Layout_Functions::get_theater_data_by_id( $updated_post_id ),
                ) ;
                $new_theater = WTBM_Layout_Functions::display_theater_date( $theater_data );

                $result = array(
                    'new_theater' => $new_theater,
                    'seat_map' => '',
                );
                wp_send_json_success( $result );

            } else {
                wp_send_json_error("Failed to edit theater" );
            }
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

    new WTBM_Manage_Theater();
}
