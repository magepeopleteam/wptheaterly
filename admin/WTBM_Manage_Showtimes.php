<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WTBM_Manage_Showtimes' ) ) {
    class WTBM_Manage_Showtimes {

        public function __construct() {

            add_action('wp_ajax_wtbm_insert_show_time_post', [ $this, 'wtbm_insert_show_time_post' ]);
            add_action('wp_ajax_nopriv_wtbm_insert_show_time_post', [ $this, 'wtbm_insert_show_time_post' ]);

            add_action('wp_ajax_wtbm_update_show_time_post', [ $this, 'wtbm_update_show_time_post' ]);
            add_action('wp_ajax_nopriv_wtbm_update_show_time_post', [ $this, 'wtbm_update_show_time_post' ]);

            add_action('wp_ajax_wtbm_add_edit_show_time_form', [ $this, 'wtbm_add_edit_show_time_form' ]);
            add_action('wp_ajax_nopriv_wtbm_add_edit_show_time_form', [ $this, 'wtbm_add_edit_show_time_form' ]);

            add_action('wp_ajax_wtbm_get_theater_categories', [ $this, 'wtbm_get_theater_categories' ]);
            add_action('wp_ajax_nopriv_wtbm_get_theater_categories', [ $this, 'wtbm_get_theater_categories' ]);
        }

        function wtbm_insert_show_time_post() {

            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce' );
            $cpt = MPTRS_Function::get_show_time_cpt();
            $movieId            = isset( $_POST['movieId'] ) ? sanitize_text_field( $_POST['movieId'] ) : '';
            $movie_title = get_the_title( $movieId );
            $theaterId          = isset( $_POST['theaterId'] ) ? sanitize_text_field( $_POST['theaterId'] ) : '';
            $date               = isset( $_POST['date'] ) ? sanitize_text_field( $_POST['date']) : '';
            $start_date         = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date']) : '';
            $end_date           = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['end_date']) : '';
            $startTime          = isset( $_POST['startTime'] ) ? sanitize_text_field( $_POST['startTime'] ) : '';
            $endTime            = isset( $_POST['endTime'] ) ? sanitize_text_field( $_POST['endTime'] ) : '';
            $showtime_off_days  = isset( $_POST['showtime_off_days'] ) ? sanitize_text_field( $_POST['showtime_off_days'] ) : '';
            $action_type        = isset( $_POST['action_type'] ) ? sanitize_text_field( $_POST['action_type'] ) : '';
            $price              = isset( $_POST['price'] ) ? floatval( $_POST['price'] ) : '';
            $description        = isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '';
            $showtime_off_days_ary = [];
            if( $showtime_off_days ){
                $showtime_off_days_ary = explode( ',', $showtime_off_days );
            }

            if( $end_date === '' ){
                $end_date = $start_date;
            }
            $post_id = wp_insert_post([
                'post_title' => $movie_title,
                'post_type' => $cpt,
                'post_status' => 'publish',
                'post_content' => $description,
            ]);
            if ( $post_id ) {
                // Save meta data
                update_post_meta( $post_id, 'wtbp_show_time_movieId', $movieId );
                update_post_meta( $post_id, 'wtbp_show_time_theaterId', $theaterId );
                update_post_meta( $post_id, 'wtbp_show_time_date', $date );
                update_post_meta( $post_id, 'wtbp_show_time_start_date', $startTime );
                update_post_meta( $post_id, 'wtbp_show_time_end_date', $endTime );
                update_post_meta( $post_id, 'wtbp_show_time_price', $price );
                update_post_meta( $post_id, 'wtbp_showtime_start_date', $start_date );
                update_post_meta( $post_id, 'wtbp_showtime_end_date', $end_date );
                update_post_meta( $post_id, 'wtbp_showtime_off_days', $showtime_off_days_ary );

                $new_show_time = array(
                    0=>WTBM_Layout_Functions::get_show_time_data_by_id( $post_id ),
                );
                $display_show_time = self::display_show_times_data( $new_show_time );

                wp_send_json_success( $display_show_time );
            } else {
                wp_send_json_error("Failed to insert post");
            }
        }

        function wtbm_update_show_time_post() {
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $cpt = MPTRS_Function::get_show_time_cpt();

            $movieId            = isset( $_POST['movieId'] ) ? sanitize_text_field( $_POST['movieId'] ) : '';
            $movie_title = get_the_title( $movieId );
            $theaterId          = isset( $_POST['theaterId'] ) ? sanitize_text_field( $_POST['theaterId'] ) : '';
            $start_date         = isset( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date']) : '';
            $end_date           = isset( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date']) : '';
            $date               = isset( $_POST['date'] ) ? sanitize_text_field( $_POST['date']) : '';
            $startTime          = isset( $_POST['startTime'] ) ? sanitize_text_field( $_POST['startTime'] ) : '';
            $endTime            = isset( $_POST['endTime'] ) ? sanitize_text_field( $_POST['endTime'] ) : '';
            $action_type        = isset( $_POST['action_type'] ) ? sanitize_text_field( $_POST['action_type'] ) : '';
            $price              = isset( $_POST['price'] ) ? floatval( $_POST['price'] ) : '';
            $description        = isset( $_POST['description'] ) ? sanitize_textarea_field( $_POST['description'] ) : '';
            $showtime_off_days  = isset( $_POST['showtime_off_days'] ) ? sanitize_text_field( $_POST['showtime_off_days'] ) : '';
            $showtime_off_days_ary = [];
            if( $showtime_off_days ){
                $showtime_off_days_ary = explode( ',', $showtime_off_days );
            }
            if( $end_date === '' ){
                $end_date = $start_date;
            }
            $showTimeId = isset( $_POST['showTimeId'] ) ? sanitize_text_field( $_POST['showTimeId'] ) : '';
            $post_data = [
                'ID'           => $showTimeId,
                'post_title'   => $movie_title,
                'post_type'    => $cpt,
                'post_status'  => 'publish',
                'post_content' => $description,
            ];

            $updated_post_id = wp_update_post( $post_data );

            if ( $updated_post_id ) {

                update_post_meta( $updated_post_id, 'wtbp_show_time_movieId', $movieId );
                update_post_meta( $updated_post_id, 'wtbp_show_time_theaterId', $theaterId );
                update_post_meta( $updated_post_id, 'wtbp_show_time_date', $date );
                update_post_meta( $updated_post_id, 'wtbp_show_time_start_date', $startTime );
                update_post_meta( $updated_post_id, 'wtbp_show_time_end_date', $endTime );
                update_post_meta( $updated_post_id, 'wtbp_show_time_price', $price );
                update_post_meta( $updated_post_id, 'wtbp_showtime_start_date', $start_date );
                update_post_meta( $updated_post_id, 'wtbp_showtime_end_date', $end_date );
                update_post_meta( $updated_post_id, 'wtbp_showtime_off_days', $showtime_off_days_ary );

                $new_show_time = array(
                    0=>WTBM_Layout_Functions::get_show_time_data_by_id( $updated_post_id ),
                );
                $display_show_time = self::display_show_times_data( $new_show_time );

                wp_send_json_success( $display_show_time );
            } else {
                wp_send_json_error("Failed to edit theater" );
            }
        }


        public function wtbm_add_edit_show_time_form(){
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
            if( $post_id == '' ){
                $type = 'add';
            }else{
                $type = 'edit';
            }
            $add_form = self::add_edit_show_time_html( $type, $post_id );

            wp_send_json_success(  $add_form );

        }
        public function wtbm_get_theater_categories(){
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( $_POST['post_id'] ) : '';
            if( $post_id ){
                $categories_html = self::get_theater_categories_html( $post_id );
                wp_send_json_success(  $categories_html );
            }else{
                wp_send_json_error("Invalid Theater");
            }


        }

        public static function get_theater_categories( $theater_id ){
            return get_post_meta( $theater_id, 'wtbp_theater_category', true );
        }
        public static function get_theater_categories_html( $post_id ) {

            $categories = self::get_theater_categories( $post_id );
            ob_start();
            echo '<div class="wtbm_theater_categories_list">';
            foreach ( $categories as $index => $cat ) {
                $cat_name = isset( $cat['category_name'] ) ? esc_html( $cat['category_name'] ) : '';
                $seats    = isset( $cat['seats'] ) ? intval( $cat['seats'] ) : 0;
                $price    = isset( $cat['price'] ) ? esc_html( $cat['price'] ) : '0.00';
                ?>
                <div class="pricing-item">
                    <div
                            data-cat-name="<?php echo esc_attr( $cat_name );?>"
                            data-cat-seats="<?php echo esc_attr( $seats );?>"
                            data-cat-base-price="<?php echo esc_attr( $price );?>"
                    >
                        <div class="font-medium"><?php echo esc_attr( $cat_name );?></div>
                        <div class="text-sm text-gray-500"><?php echo esc_attr( $seats );?> seats</div>
                        <div class="text-sm text-gray-500">Base: <?php echo esc_attr( $price );?></div>
                    </div>
                    <div>
                        <input type="number" class="form-input pricing-input" data-category="Regular" placeholder="12.99" value="<?php echo esc_attr( $price );?>" step="0.01" min="0" style="width: 100px;">
                    </div>
                </div>
                <?php

            }

            echo '</div>';
            return ob_get_clean();
        }

        public static function add_edit_show_time_html( $action_type, $showtime_id ) {
            ob_start();
            if ( ! current_user_can( 'manage_options' ) ) {
                return '<p>' . esc_html__( 'You do not have permission to access this page.', 'wptheaterly' ) . '</p>';
            }
            $title = ( $action_type === 'edit' ) ? __( 'Edit Showtime', 'wptheaterly' ) : __( 'Create New Showtime', 'wptheaterly' );

            $add_action = 'wtbm_add_new_show_time';

            $show_time_id = $categories_html = '';
            $show_time_data = [];
            if ( $action_type === 'edit' && $showtime_id ) {
                $add_action = 'wtbm_edit_show_time';
                $show_time_data = WTBM_Layout_Functions::get_show_time_data_by_id( absint( $showtime_id ) );
//                $show_time_id = isset( $show_time_data['theater_id'] ) ? $show_time_data['theater_id'] : '';
                $theater_id = isset( $show_time_data['theater_id'] ) ? $show_time_data['theater_id'] : '';

                if( $theater_id ){
                    $categories_html = self::get_theater_categories_html( $theater_id );
                }

            }

            $movie_data = WTBM_Layout_Functions::get_and_display_movies( 30 );
            $theater_data = WTBM_Layout_Functions::get_and_display_theater_date( 30 );
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            if ( $action_type === 'edit' && $showtime_id ) {
                $checked_days = get_post_meta( $showtime_id, 'wtbp_showtime_off_days', true );
                if( $checked_days === '' ){
                    $checked_days = [];
                }
            }else{
                $checked_days = [];
            }
            $checked_days_str = implode( ',', $checked_days);
//            error_log( print_r( [ '$checked_days_str' => $checked_days_str ], true ) );

            ?>
            <h4 class="mb-4 font-semibold"><?php echo esc_html( $title ); ?></h4>

            <form id="wtbm-showtime-form" method="post">
                <?php wp_nonce_field( 'wtbm_save_showtime_action', 'wtbm_showtime_nonce' ); ?>

                <input type="hidden" name="action_type" value="<?php echo esc_attr( $action_type ); ?>">
                <input type="hidden" name="showtime_id" value="<?php echo esc_attr( $showtime_id ); ?>">

                <!--<div class="form-group">
                    <label class="form-label"><?php /*esc_html_e( 'Showtime Name', 'wptheaterly' ); */?></label>
                    <input type="text" name="showtime_name" id="showTimeName" class="form-input"
                           value="<?php /*echo isset( $show_time_data['name'] ) ? esc_attr( $show_time_data['name'] ) : ''; */?>"
                           placeholder="<?php /*esc_attr_e( 'Show time 1', 'wptheaterly' ); */?>">
                </div>-->

                <div class="grid grid-cols-3 mb-4">
                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Movie', 'wptheaterly' ); ?></label>
                        <select id="showtime-movie" name="showtime_movie" class="form-input">
                            <option value=""><?php esc_html_e( 'Select Movie', 'wptheaterly' ); ?></option>
                            <?php if( is_array( $movie_data ) && !empty( $movie_data ) ){
                                foreach ( $movie_data as $movie ){
                                ?>
                                <option value="<?php echo esc_attr( $movie['id'] )?>" <?php selected( $show_time_data['movie_id'] ?? '', $movie['id']); ?>><?php echo esc_attr(  $movie['title'] )?></option>
                            <?php } }?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Theater', 'wptheaterly' ); ?></label>
                        <select id="wtbm_showtime_theater" name="showtime_theater" class="form-input">
                            <option value=""><?php esc_html_e( 'Select Theater', 'wptheaterly' ); ?></option>
                            <?php if( is_array( $theater_data ) && !empty( $theater_data ) ){
                                foreach ( $theater_data as $theater ){
                                ?>
                                <option value="<?php echo esc_attr( $theater['id'] )?>" <?php selected( $show_time_data['theater_id'] ?? '', $theater['id']); ?>><?php echo esc_attr( $theater['name'] )?></option>
                            <?php } }?>
                        </select>
                    </div>

                    <div class="form-group" style="display: none">
                        <label class="form-label"><?php esc_html_e( 'Date', 'wptheaterly' ); ?></label>
                        <input id="showtime-date" type="date" name="showtime_date" class="form-input"
                               value="<?php echo isset( $show_time_data['show_time_date'] ) ? esc_attr( $show_time_data['show_time_date'] ) : ''; ?>"
                               min="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Start Date', 'wptheaterly' ); ?></label>
                        <input type="date" id="showtime_date_start" name="showtime_date_start" class="form-input"
                               value="<?php echo isset( $show_time_data['showtime_start_date'] ) ? esc_attr( $show_time_data['showtime_start_date'] ) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'End Date', 'wptheaterly' ); ?></label>
                        <input type="date" id="showtime_date_end" name="showtime_date_end" class="form-input"
                               value="<?php echo isset( $show_time_data['showtime_end_date'] ) ? esc_attr( $show_time_data['showtime_end_date'] ) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Start Time', 'wptheaterly' ); ?></label>
                        <input type="time" id="showtime-time-start" name="showtime_time_start" class="form-input"
                               value="<?php echo isset( $show_time_data['show_time_start'] ) ? esc_attr( $show_time_data['show_time_start'] ) : ''; ?>">
                    </div>



                    <div class="form-group" style="display: none">
                        <label class="form-label"><?php esc_html_e( 'Price ($)', 'wptheaterly' ); ?></label>
                        <input type="number" id="showtime-price" name="showtime_price" class="form-input" step="0.01"
                               value="<?php echo isset( $show_time_data['price'] ) ? esc_attr( $show_time_data['price'] ) : ''; ?>"
                               placeholder="12.99">
                    </div>
                </div>

                <div id="wtbm_pricing-section" class="wtbm_pricing-section ">
                    <h5 class="font-semibold mb-3">Pricing by Seating Category</h5>
                    <div id="wtbm_pricing_categories">
                        <?php  if ( $action_type === 'edit' && $showtime_id ) {
                            echo  $categories_html ;
                        }?>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label"><?php esc_html_e( 'Description', 'wptheaterly' ); ?></label>
                    <textarea id="showTime-description" name="showtime_description" class="form-input" rows="3"
                              placeholder="<?php esc_attr_e( 'Show time description', 'wptheaterly' ); ?>"><?php echo isset( $show_time_data['description'] ) ? esc_textarea( $show_time_data['description'] ) : ''; ?></textarea>
                </div>



                <div data-collapse="#mp_repeated" class="form-group mb-4" style="">
                    <div class="wtbm_showTimeOffDaysTitle">
                        <h2>Off Day</h2>
                        <span class="desc">Select checkbox for off day</span>
                    </div>
                    <div class="wtbm_showTimeOffDaysTitle">
                        <div class="wtbm_groupCheckBox">
                            <input type="hidden" name="wtbm_showtime_off_days" id="wtbm_showtime_off_days" value="<?php echo esc_attr( $checked_days_str );?>">
                            <?php
                            foreach ( $days as $day ) : ?>
                                <label class="customCheckboxLabel">
                                    <input type="checkbox" data-checked="<?php echo esc_attr( $day ) ?>"
                                        <?php
                                        if ( is_array( $checked_days ) && !empty( $checked_days ) && in_array( $day, $checked_days ) ) {
                                            echo 'checked';
                                        }
                                        ?>>
                                    <span class="customCheckbox me-1"><?php echo esc_attr( ucfirst( $day ) ); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>


                <div class="flex gap-2">
                    <button class="btn btn-success" id="<?php echo esc_attr( $add_action );?>" data-showTimeId="<?php echo esc_attr( $showtime_id );?>">
                        <?php echo ( $action_type === 'edit' ) ? esc_html__( 'Update Showtime', 'wptheaterly' ) : esc_html__( 'Add Showtime', 'wptheaterly' ); ?>
                    </button>
                    <button class="btn btn-secondary" id="wtbm_clear_show_time_form"><?php esc_attr_e( 'Cancel', 'wptheaterly' )?></button>
                </div>


            </form>
            <?php

            return ob_get_clean();
        }
        public static function display_show_times_data( $show_time_data ) {
            ob_start();

            if ( ! empty( $show_time_data ) && is_array( $show_time_data ) ) {
                foreach ( $show_time_data as $showtime ) {
                    ?>
                    <tr id="show_time_content_<?php echo esc_attr( $showtime['id'] );?>">
                        <td>
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo esc_html( $showtime['name'] ); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo esc_html( $showtime['description'] ); ?>
                            </div>
                        </td>
                        <td class="text-sm text-gray-900">
                            <?php echo 'Screen ' . esc_html( get_the_title( $showtime['theater_id'] ) ); ?>
                        </td>
                        <?php if( $showtime['showtime_end_date'] ){

                            $start_date_formatted = date('d M, y', strtotime( $showtime['showtime_start_date'] ));
                            $end_date_formatted = date('d M, y', strtotime( $showtime['showtime_end_date'] ));
                            ?>
                            <td class="text-sm text-gray-900">
                                <?php echo esc_html( $start_date_formatted ).' - '. $end_date_formatted; ?>
                            </td>
                        <?php }else{?>
                            <td class="text-sm text-gray-900">
                                <?php echo esc_html( $start_date_formatted ); ?>
                            </td>
                        <?php }?>
                        <td class="text-sm text-gray-900">
                            <?php echo esc_html( $showtime['show_time_start'] . ' - ' . $showtime['show_time_end'] ); ?>
                        </td>
                        <td class="text-sm font-medium text-gray-900">
                            <?php echo esc_html( number_format( (float) $showtime['price'], 2 ) ); ?>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <button class="btn-icon edit editwtbm_edit_show_time"
                                        data-editShowtime="<?php echo esc_attr( $showtime['id'] ); ?>"
                                        title="<?php esc_attr_e( 'Edit Showtime', 'wtbm' ); ?>"><i class="mi mi-pencil"></i></button>
                                <button class="btn-icon delete wtbm_delete_show_time"
                                        data-delete-showtime-id="<?php echo esc_attr( $showtime['id'] ); ?>"
                                        title="<?php esc_attr_e( 'Delete Showtime', 'wtbm' ); ?>"><i class="mi mi-trash"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="6" class="text-center text-gray-500">
                        <?php esc_html_e( 'No showtimes found.', 'wtbm' ); ?>
                    </td>
                </tr>
                <?php
            }

            return ob_get_clean();
        }

    }

    new WTBM_Manage_Showtimes();
}
