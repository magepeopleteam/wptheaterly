<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WTBM_Manage_Theater' ) ) {
    class WTBM_Manage_Theater{
        public function __construct(){
            add_action('wp_ajax_mptrs_insert_theater_post', [ $this, 'mptrs_insert_theater_post' ]);


            add_action('wp_ajax_mptrs_refresh_theater_seat_mapp', [ $this, 'mptrs_refresh_theater_seat_mapp' ]);


            add_action('wp_ajax_wtbp_add_edit_theater_form', [ $this, 'wtbp_add_edit_theater_form' ]);


            add_action('wp_ajax_mptrs_update_theater_post', [ $this, 'mptrs_update_theater_post' ]);


            add_action('wp_ajax_wtbm_theater_seat_map_add', [ $this, 'wtbm_theater_seat_map_add' ]);

        }

        private function read_plan_field( $field ) {
            if ( ! is_string( $_POST[ $field ] ) ) {
                wp_send_json_error( __( 'Invalid seat plan data.', 'wptheaterly' ), 400 );
            }
            $data = json_decode( wp_unslash( $_POST[ $field ] ), true );
            if ( ! is_array( $data ) || count( $data ) > 10000 ) {
                wp_send_json_error( __( 'Invalid seat plan data.', 'wptheaterly' ), 400 );
            }
            foreach ( $data as $item ) {
                if ( ! is_array( $item ) || count( array_filter( $item, 'is_array' ) ) ) {
                    wp_send_json_error( __( 'Invalid seat plan item.', 'wptheaterly' ), 400 );
                }
            }
            $data = map_deep( $data, 'sanitize_text_field' );
            if ( 'seat_maps_meta_data' === $field ) {
                $ids = array();
                $numbers = array();
                foreach ( $data as $seat ) {
                    $id = (string) ( $seat['id'] ?? '' );
                    $number = strtoupper( trim( $seat['seat_number'] ?? '' ) );
                    if ( '' === $id || '' === $number || isset( $ids[ $id ] ) || isset( $numbers[ $number ] ) || ! is_numeric( $seat['price'] ?? null ) || (float) $seat['price'] < 0 ) {
                        wp_send_json_error( __( 'Seats require unique IDs, unique numbers and a valid price.', 'wptheaterly' ), 400 );
                    }
                    foreach ( array( 'row', 'col', 'left', 'top', 'width', 'height' ) as $coordinate ) {
                        $value = (string) ( $seat[ $coordinate ] ?? '' );
                        if ( ! preg_match( '/^\d+(?:\.\d+)?(?:px)?$/', $value ) || (float) $value > 20000 ) {
                            wp_send_json_error( __( 'Invalid seat coordinates.', 'wptheaterly' ), 400 );
                        }
                    }
                    $ids[ $id ] = true;
                    $numbers[ $number ] = true;
                }
            }
            return $data;
        }

        public function wtbm_theater_seat_map_add(){
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');
            if ( ! WTBM_Booking_Service::can( 'manage' ) ) {
                wp_send_json_error( __( 'Permission denied.', 'wptheaterly' ), 403 );
            }
            if ( ! empty( $_POST['post_id'] ) && get_post_type( absint( $_POST['post_id'] ) ) !== WTBM_Function::get_theater_cpt() ) {
                wp_send_json_error( __( 'Invalid theater.', 'wptheaterly' ), 400 );
            }

            $post_id     = isset($_POST['post_id']) ? intval( wp_unslash( $_POST['post_id'] ) ) : '';
            if( $post_id ) {
                $seat_maps_meta_data = isset($_POST['seat_maps_meta_data']) ? $this->read_plan_field( 'seat_maps_meta_data' ) : [];
                $seat_plan_texts = isset($_POST['seatPlanTexts']) ? $this->read_plan_field( 'seatPlanTexts' ) : '';
                $dynamicShapes = isset($_POST['dynamicShapes']) ? $this->read_plan_field( 'dynamicShapes' ) : '';

                $seat_plan_data = array(
                    'seat_data' => $seat_maps_meta_data,
                    'seat_text_data' => $seat_plan_texts,
                    'dynamic_shapes' => $dynamicShapes,
                );
                $protected = WTBM_Booking_Service::protect_plan( $post_id, $seat_plan_data );
                if ( is_wp_error( $protected ) ) { wp_send_json_error( $protected->get_error_message(), 400 ); }

                $result = update_post_meta( $post_id, 'wtbp_theater_seat_map', $seat_plan_data );

                wp_send_json_success(array(
                    'message' => 'Seat plan saved successfully',
                    'post_id' => $result,
                ));


            }else{
                wp_send_json_error( 'Invalid Post' );
            }

        }


        function mptrs_refresh_theater_seat_mapp() {
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');
            if ( ! WTBM_Booking_Service::can( 'manage' ) ) {
                wp_send_json_error( __( 'Permission denied.', 'wptheaterly' ), 403 );
            }
            if ( ! empty( $_POST['post_id'] ) && get_post_type( absint( $_POST['post_id'] ) ) !== WTBM_Function::get_theater_cpt() ) {
                wp_send_json_error( __( 'Invalid theater.', 'wptheaterly' ), 400 );
            }

            $post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
            $rows = isset( $_POST['rows'] ) ? sanitize_text_field( wp_unslash( $_POST['rows'] ) ) : '';
            $seatsPerRow = isset( $_POST['seatsPerRow'] ) ? sanitize_text_field( wp_unslash( $_POST['seatsPerRow'] ) ) : '';
            $seat_map = WTBM_Theater_Seat_Mapping::clear_render_seat_mapping_meta_box( $post_id, 'add', $rows, $seatsPerRow, );

            $result = array(
                'seat_map' => $seat_map,
            );

            wp_send_json_success( $result );
        }

        function mptrs_insert_theater_post() {
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');
            if ( ! WTBM_Booking_Service::can( 'manage' ) ) {
                wp_send_json_error( __( 'Permission denied.', 'wptheaterly' ), 403 );
            }
            if ( ! empty( $_POST['post_id'] ) && get_post_type( absint( $_POST['post_id'] ) ) !== WTBM_Function::get_theater_cpt() ) {
                wp_send_json_error( __( 'Invalid theater.', 'wptheaterly' ), 400 );
            }

            $cpt = WTBM_Function::get_theater_cpt();

            $title          = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
            $type           = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
            $rows           = isset( $_POST['rows'] ) ? sanitize_text_field( wp_unslash( $_POST['rows'] ) ) : '';
            $seatsPerRow    = isset( $_POST['seatsPerRow'] ) ? sanitize_text_field( wp_unslash( $_POST['seatsPerRow'] ) ) : '';
            $soundSystem    = isset( $_POST['soundSystem'] ) ? sanitize_text_field( wp_unslash( $_POST['soundSystem'] ) ) : '';
            $status         = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
            $description    = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';

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
            if ( ! WTBM_Booking_Service::can( 'manage' ) ) {
                wp_send_json_error( __( 'Permission denied.', 'wptheaterly' ), 403 );
            }
            if ( ! empty( $_POST['post_id'] ) && get_post_type( absint( $_POST['post_id'] ) ) !== WTBM_Function::get_theater_cpt() ) {
                wp_send_json_error( __( 'Invalid theater.', 'wptheaterly' ), 400 );
            }

            $cpt            = WTBM_Function::get_theater_cpt();
            $title          = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
            $type           = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
            $rows           = isset( $_POST['rows'] ) ? sanitize_text_field( wp_unslash( $_POST['rows'] ) ) : '';
            $seatsPerRow    = isset( $_POST['seatsPerRow'] ) ? sanitize_text_field( wp_unslash( $_POST['seatsPerRow'] ) ) : '';
            $soundSystem    = isset( $_POST['soundSystem'] ) ? sanitize_text_field( wp_unslash( $_POST['soundSystem'] ) ) : '';
            $status         = isset( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
            $description    = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';

            $post_id     = isset($_POST['post_id']) ? intval( wp_unslash( $_POST['post_id'] ) ) : '';
            $post_data = [
                'ID'           => $post_id,
                'post_title'   => $title,
                'post_type'    => $cpt,
                'post_status'  => 'publish',
                'post_content' => $description,
            ];

            $updated_post_id = wp_update_post( $post_data );

            if ( $updated_post_id ) {

                $seat_maps_meta_data = isset( $_POST['seat_maps_meta_data'] ) ? $this->read_plan_field( 'seat_maps_meta_data' ) : [];
                $seat_plan_texts= isset( $_POST['seatPlanTexts'] ) ? $this->read_plan_field( 'seatPlanTexts' ) : '' ;
                $dynamicShapes = isset( $_POST['dynamicShapes'] ) ? $this->read_plan_field( 'dynamicShapes' ) : '';
                $seat_plan_data = array(
                    'seat_data' => $seat_maps_meta_data,
                    'seat_text_data' => $seat_plan_texts,
                    'dynamic_shapes' => $dynamicShapes,
                );
                $protected = WTBM_Booking_Service::protect_plan( $post_id, $seat_plan_data );
                if ( is_wp_error( $protected ) ) { wp_send_json_error( $protected->get_error_message(), 400 ); }
                $categories = isset( $_POST['wtbm_categories'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['wtbm_categories'] ) ), true ) : '';

                // Save meta data
                update_post_meta( $updated_post_id, 'wtbp_theater_type', $type );
                update_post_meta( $updated_post_id, 'wtbp_theater_rows', $rows );
                update_post_meta( $updated_post_id, 'wtbp_theater_seatsPerRow', $seatsPerRow );
                update_post_meta( $updated_post_id, 'wtbp_theater_soundSystem', $soundSystem );
                update_post_meta( $updated_post_id, 'wtbp_theater_status', $status );

                if ( isset( $_POST['seat_maps_meta_data'] ) ) {
                    update_post_meta( $updated_post_id, 'wtbp_theater_seat_map', $seat_plan_data );
                }
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
            if ( ! WTBM_Booking_Service::can( 'manage' ) ) {
                wp_send_json_error( __( 'Permission denied.', 'wptheaterly' ), 403 );
            }
            if ( ! empty( $_POST['post_id'] ) && get_post_type( absint( $_POST['post_id'] ) ) !== WTBM_Function::get_theater_cpt() ) {
                wp_send_json_error( __( 'Invalid theater.', 'wptheaterly' ), 400 );
            }

            $post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
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
