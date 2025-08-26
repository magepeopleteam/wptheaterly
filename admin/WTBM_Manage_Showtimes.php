<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WTBM_Manage_Showtimes' ) ) {
    class WTBM_Manage_Showtimes {

        public function __construct() {

            add_action('wp_ajax_wtbp_insert_show_time_post', [ $this, 'wtbp_insert_show_time_post' ]);
            add_action('wp_ajax_nopriv_wtbp_insert_show_time_post', [ $this, 'wtbp_insert_show_time_post' ]);

            add_action('wp_ajax_wtbm_add_edit_show_time_form', [ $this, 'wtbm_add_edit_show_time_form' ]);
            add_action('wp_ajax_nopriv_wtbm_add_edit_show_time_form', [ $this, 'wtbm_add_edit_show_time_form' ]);
        }

        function wtbp_insert_show_time_post() {

            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce' );

            $cpt = MPTRS_Function::get_show_time_cpt();
            $title          = sanitize_text_field( $_POST['title'] );
            $movieId        = sanitize_text_field( $_POST['movieId'] );
            $theaterId      = sanitize_text_field( $_POST['theaterId'] );
            $date           = sanitize_text_field( $_POST['date']);
            $startTime      = sanitize_text_field( $_POST['startTime'] );
            $endTime        = sanitize_text_field( $_POST['endTime'] );
            $price          = floatval( $_POST['price'] );
            $description    = sanitize_textarea_field( $_POST['description'] );

            $showTimeId = isset( $_POST['showTimeId'] ) ? sanitize_text_field( $_POST['showTimeId'] ) : '';

            if( $showTimeId ){
                $post_data = [
                    'post_title'   => $title,
                    'post_type'    => $cpt,
                    'post_status'  => 'publish',
                    'post_content' => $description,
                ];
                $post_data['ID'] = $showTimeId;
                $post_id = wp_update_post( $post_data );
            }else {
                $post_id = wp_insert_post([
                    'post_title' => $title,
                    'post_type' => $cpt,
                    'post_status' => 'publish',
                    'post_content' => $description,
                ]);
            }
            if ( $post_id ) {
                // Save meta data
                update_post_meta($post_id, 'wtbp_show_time_movieId', $movieId);
                update_post_meta($post_id, 'wtbp_show_time_theaterId', $theaterId);
                update_post_meta($post_id, 'wtbp_show_time_date', $date);
                update_post_meta($post_id, 'wtbp_show_time_start_date', $startTime);
                update_post_meta($post_id, 'wtbp_show_time_end_date', $endTime);
                update_post_meta($post_id, 'wtbp_show_time_price', $price);

                wp_send_json_success( get_post( $post_id ) );
            } else {
                wp_send_json_error("Failed to insert post");
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
            $add_form = WTBM_Manage_Showtimes::add_edit_show_time_html( $type, $post_id );

            wp_send_json_success(  $add_form );

        }
        public static function add_edit_show_time_html( $action_type, $showtime_id ) {
            ob_start();
            if ( ! current_user_can( 'manage_options' ) ) {
                return '<p>' . esc_html__( 'You do not have permission to access this page.', 'wptheaterly' ) . '</p>';
            }
            $title = ( $action_type === 'edit' ) ? __( 'Edit Showtime', 'wptheaterly' ) : __( 'Create New Showtime', 'wptheaterly' );

            $add_action = 'wtbm_add_new_show_time';

            $show_time_id = '';
            $show_time_data = [];
            if ( $action_type === 'edit' && $showtime_id ) {
                $add_action = 'wtbm_edit_show_time';
                $show_time_data = WTBM_Layout_Functions::get_show_time_data_by_id( absint( $showtime_id ) );
                $show_time_id = isset( $show_time_data['id'] ) ? $show_time_data['id'] : '';
            }

            ?>
            <h4 class="mb-4 font-semibold"><?php echo esc_html( $title ); ?></h4>

            <form id="wtbm-showtime-form" method="post">
                <?php wp_nonce_field( 'wtbm_save_showtime_action', 'wtbm_showtime_nonce' ); ?>

                <input type="hidden" name="action_type" value="<?php echo esc_attr( $action_type ); ?>">
                <input type="hidden" name="showtime_id" value="<?php echo esc_attr( $showtime_id ); ?>">

                <div class="form-group">
                    <label class="form-label"><?php esc_html_e( 'Showtime Name', 'wptheaterly' ); ?></label>
                    <input type="text" name="showtime_name" id="showTimeName" class="form-input"
                           value="<?php echo isset( $show_time_data['name'] ) ? esc_attr( $show_time_data['name'] ) : ''; ?>"
                           placeholder="<?php esc_attr_e( 'Show time 1', 'wptheaterly' ); ?>">
                </div>

                <div class="grid grid-cols-3 mb-4">
                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Movie', 'wptheaterly' ); ?></label>
                        <select id="showtime-movie" name="showtime_movie" class="form-input">
                            <option value=""><?php esc_html_e( 'Select Movie', 'wptheaterly' ); ?></option>
                            <option value="1" <?php selected( $show_time_data['movie_id'] ?? '', 1 ); ?>>Guardians of the Galaxy Vol. 3</option>
                            <option value="2" <?php selected( $show_time_data['movie_id'] ?? '', 2 ); ?>>Spider-Man: Across the Spider-Verse</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Theater', 'wptheaterly' ); ?></label>
                        <select id="showtime-theater" name="showtime_theater" class="form-input">
                            <option value=""><?php esc_html_e( 'Select Theater', 'wptheaterly' ); ?></option>
                            <option value="1" <?php selected( $show_time_data['theater_id'] ?? '', 1 ); ?>>Screen 1</option>
                            <option value="2" <?php selected( $show_time_data['theater_id'] ?? '', 2 ); ?>>Screen 2</option>
                            <option value="3" <?php selected( $show_time_data['theater_id'] ?? '', 3 ); ?>>Screen 3</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Date', 'wptheaterly' ); ?></label>
                        <input id="showtime-date" type="date" name="showtime_date" class="form-input"
                               value="<?php echo isset( $show_time_data['show_time_date'] ) ? esc_attr( $show_time_data['show_time_date'] ) : ''; ?>"
                               min="<?php echo esc_attr( date( 'Y-m-d' ) ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Start Time', 'wptheaterly' ); ?></label>
                        <input type="time" id="showtime-time-start" name="showtime_time_start" class="form-input"
                               value="<?php echo isset( $show_time_data['show_time_start'] ) ? esc_attr( $show_time_data['show_time_start'] ) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'End Time', 'wptheaterly' ); ?></label>
                        <input type="time" id="showtime-time-end" name="showtime_time_end" class="form-input"
                               value="<?php echo isset( $show_time_data['show_time_end'] ) ? esc_attr( $show_time_data['show_time_end'] ) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Price ($)', 'wptheaterly' ); ?></label>
                        <input type="number" id="showtime-price" name="showtime_price" class="form-input" step="0.01"
                               value="<?php echo isset( $show_time_data['price'] ) ? esc_attr( $show_time_data['price'] ) : ''; ?>"
                               placeholder="12.99">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label"><?php esc_html_e( 'Description', 'wptheaterly' ); ?></label>
                    <textarea id="showTime-description" name="showtime_description" class="form-input" rows="3"
                              placeholder="<?php esc_attr_e( 'Show time description', 'wptheaterly' ); ?>"><?php echo isset( $show_time_data['description'] ) ? esc_textarea( $show_time_data['description'] ) : ''; ?></textarea>
                </div>

                <div class="flex gap-2">
                    <button class="btn btn-success" id="<?php echo esc_attr( $add_action );?>" data-showTimeId="<?php echo esc_attr( $show_time_id );?>">
                        <?php echo ( $action_type === 'edit' ) ? esc_html__( 'Update Showtime', 'wptheaterly' ) : esc_html__( 'Add Showtime', 'wptheaterly' ); ?>
                    </button>
                    <button class="btn btn-secondary">Cancel</button>
                </div>
            </form>
            <?php

            return ob_get_clean();
        }
        public static function display_show_times_data() {
            $show_time_data = WTBM_Layout_Functions::get_show_time_data();
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
                            <?php echo 'Screen ' . esc_html( $showtime['theater_id'] ); ?>
                        </td>
                        <td class="text-sm text-gray-900">
                            <?php echo esc_html( $showtime['show_time_date'] ); ?>
                        </td>
                        <td class="text-sm text-gray-900">
                            <?php echo esc_html( $showtime['show_time_start'] . ' - ' . $showtime['show_time_end'] ); ?>
                        </td>
                        <td class="text-sm font-medium text-gray-900">
                            <?php echo esc_html( number_format( (float) $showtime['price'], 2 ) ); ?>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <button class="btn-icon editwtbm_edit_show_time"
                                        data-editShowtime="<?php echo esc_attr( $showtime['id'] ); ?>"
                                        title="<?php esc_attr_e( 'Edit Showtime', 'wtbm' ); ?>">✏️</button>
                                <button class="btn-icon delete wtbm_delete_show_time"
                                        data-delete-showtime-id="<?php echo esc_attr( $showtime['id'] ); ?>"
                                        title="<?php esc_attr_e( 'Delete Showtime', 'wtbm' ); ?>">🗑️</button>
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
