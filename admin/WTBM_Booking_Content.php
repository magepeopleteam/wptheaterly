<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if( !class_exists( 'WTBM_Booking_Content' ) ){
    class WTBM_Booking_Content{

        public function __construct(){
            add_action( 'wtbm_bookings_content', [$this, 'my_bookings_content_handler'], 10, 1 );
            add_action( 'wtbm_booking_header', [$this, 'wtbm_booking_header_display'], 10, 1 );
            add_action( 'wtbm_booking_filter', [$this, 'wtbm_booking_filter_display'], 10, 1 );

            add_action( 'wtbm_booking_content', [$this, 'wtbm_booking_data_display'], 10, 2 );

            add_action('wp_ajax_wtbm_get_load_more_booking_data', [ $this, 'wtbm_get_load_more_booking_data' ]);
            add_action('wp_ajax_nopriv_wtbm_get_load_more_booking_data', [ $this, 'wtbm_get_load_more_booking_data' ]);

            add_action('wp_ajax_wtbm_filter_bookings', [ $this, 'wtbm_filter_bookings' ] );
            add_action('wp_ajax_nopriv_wtbm_filter_bookings', [ $this, 'wtbm_filter_bookings' ] );

            add_action('wp_ajax_wtbm_get_booking_html', [ $this, 'wtbm_get_booking_html' ] );
            add_action('wp_ajax_nopriv_wtbm_get_booking_html', [ $this, 'wtbm_get_booking_html' ] );
            add_action('wp_ajax_wtbm_update_booking_data', [ $this, 'wtbm_update_booking_data' ] );

            add_action( 'wp_ajax_wtbm_get_theater_available_seats', [ $this, 'wtbm_get_theater_available_seats' ] );
            add_action( 'wp_ajax_nopriv_wtbm_get_theater_available_seats', [ $this, 'wtbm_get_theater_available_seats' ] );

            add_action( 'wp_ajax_wtbm_delete_booking', [ $this, 'wtbm_delete_booking' ] );
            add_action( 'wp_ajax_nopriv_wtbm_delete_booking', [ $this, 'wtbm_delete_booking' ] );

//            add_action('wp_ajax_wtbm_bookings_data_display', [ $this, 'wtbm_bookings_data_display' ]);
//            add_action('wp_ajax_nopriv_wtbm_bookings_data_display', [ $this, 'wtbm_bookings_data_display' ]);
        }

        function wtbm_filter_bookings() {

            check_ajax_referer('mptrs_admin_nonce', 'nonce');

//            $search = sanitize_text_field($_POST['order_search'] ?? '');

            $filters_data = array(
                'wtbm_movie_id'       => intval($_POST['movie_id'] ?? 0),
                'wtbm_theater_id'     => intval($_POST['theater_id'] ?? 0),
                'wtbm_order_time'      => sanitize_text_field($_POST['show_time'] ?? ''),
                'wtbm_order_date'      => sanitize_text_field($_POST['show_date'] ?? ''),
                'wtbm_order_status' => sanitize_text_field($_POST['booking_status'] ?? ''),
            );

            $filters_data = WTBM_Layout_Functions::wtbm_get_filtered_booking_data( $filters_data,[], 10 );
            $filters_booking_data = $filters_data['booking_data'];
            $filter_booking_count = $filters_data['total_booking'];

//            $filter_booking_count = count($filters_booking_data);
            if( $filter_booking_count > 0 ){
                $html = self::movie_booking_data( $filters_booking_data, $filter_booking_count );
            }else{
                $html = 'No Booking Found';
            }

            wp_send_json_success(array(
                'html' => $html,
                'total_booking' => $filter_booking_count,
            ));
        }

        public function my_bookings_content_handler( $header_title ){

            /*$args = array(
                'post_type'      => 'wtbm_booking',
                'post_status'    => 'publish',
                'posts_per_page' => 10,
            );

            $query = new WP_Query( $args );*/
            $data = WTBM_Layout_Functions::wtbm_get_filtered_booking_data( $filters_data = [],[], 10 );
            $booking_data = $data['booking_data'];
            $total_booking = $data['total_booking'];

            $header_data = array(
              'total_booking' => $total_booking,
              'total_revenue' => 1522,
              'total_completed_order' => 10,
              'total_canceled_order' => 0,
            );

            /*$booking_data = [];

            if ( $query->have_posts() ) {
                foreach ( $query->posts as $booking ) {
                    $booking_date = []; // single booking's meta
                    $meta_data = get_post_meta( $booking->ID );
                    foreach ( $meta_data as $key => $value ) {
                        $booking_date[$key] = maybe_unserialize( $value[0] );
                    }
                    $booking_data[$booking->ID] = $booking_date;
                }
            }

            wp_reset_postdata();*/

            $filter_data = [];

            ?>
            <div id="wtbm_bookings_content" class="tab-content">
                <?php
                do_action('wtbm_booking_header', $header_data );
                ?>

                <div class="section">
                    <div class="section-header">
                        <div>
                            <h3 class="section-title"><?php echo esc_attr( $header_title );?></h3>
                            <p class="text-sm text-gray-500" id="bookings-count"><?php esc_attr_e( 'Total:', 'wptheaterly' ); ?> <?php echo esc_attr( $total_booking );?> <?php esc_attr_e( 'bookings', 'wptheaterly' ); ?></p>
                        </div>
                        <button class="btn btn-secondary wtbm_show_filter">
                            🔍 Filters
                        </button>

                        
                    </div>
                </div>

                <!-- Stats Grid - Moved to top -->
                <?php
                do_action('wtbm_booking_filter', $filter_data );

                do_action( 'wtbm_booking_content', $booking_data, $total_booking );
                ?>

            </div>
        <?php
        }
        public function wtbm_booking_data_display( $booking_date, $total_booking ){

            $display_count = count( $booking_date );
            ?>
            <div class="section">
                <?php if( $display_count > 0 ){?>
                    <table class="table">
                        <thead>
                        <tr>
                            <th><?php esc_attr_e( 'Booking ID', 'wptheaterly' ); ?></th>
                            <th><?php esc_attr_e( 'Customer', 'wptheaterly' ); ?></th>
                            <th><?php esc_attr_e( 'Movie', 'wptheaterly' ); ?></th>
                            <th><?php esc_attr_e( 'Theater', 'wptheaterly' ); ?></th>
                            <th><?php esc_attr_e( 'Show Date', 'wptheaterly' ); ?></th>
                            <th><?php esc_attr_e( 'Seats', 'wptheaterly' ); ?></th>
                            <th><?php esc_attr_e( 'Amount', 'wptheaterly' ); ?></th>
                            <th><?php esc_attr_e( 'Booking Status', 'wptheaterly' ); ?></th>
                            <th><?php esc_attr_e( 'Actions', 'wptheaterly' ); ?></th>
                        </tr>
                        </thead>
                        <tbody id="wtbm_bookings_table_body">
                        <?php
                        echo self::movie_booking_data( $booking_date, $display_count );
                        ?>
                        </tbody>
                    </table>

                        <div id="load-more-section" class="text-center py-4 border-t border-gray-200">
                            <div class="mb-3" id="wtbm_showing_number_of_booking" style="display: block">
                                <span class="text-sm text-gray-600" id="showing-info" data-shoing-info ="">
                                    <?php esc_attr_e( 'Showing 1', 'wptheaterly' ); ?>-
                                    <span id="wtbm_showing_count"><?php echo esc_attr($display_count); ?></span>
                                    <?php esc_attr_e( 'of ', 'wptheaterly' ); ?>
                                    <span id="wtbm_total_booking_count"><?php echo esc_attr( $total_booking );?></span>
                                    <?php esc_attr_e( 'Bookings', 'wptheaterly' ); ?>
                                </span>
                            </div>
                            <div class="wtbm_loadmore_csv_pdf_btn_holder" style="display: flex; justify-content: space-between; gap: 10px">
                                <?php if( $display_count > 9 ){?>
                                    <button id="wtbm_booking_load_more_btn" class="btn btn-primary" style="display: block">
                                        📄 <?php esc_attr_e( 'Load More Bookings (+10)', 'wptheaterly' ); ?>
                                    </button>
                                <?php }?>
                            </div>

                        </div>

                <?php }else{ ?>
                    <div class="wtbm_empty_booking"><?php esc_attr_e( 'No Booking Data Found', 'wptheaterly' ); ?></div>
                <?php }?>
            </div>
        <?php }

        public function wtbm_bookings_data_display(){

        }

        public static function movie_booking_data( $booking_dates, $display_count ) {
            ob_start();

            if( $display_count > 0 ){
                foreach ( $booking_dates as $booking_id => $meta ) {
                $order = wc_get_order( $meta['wtbm_order_id'] );

                $status = 'pending';
                if ( $order ) {
                    $status = wc_get_order_status_name( $order->get_status() );
                }
                // Example meta keys, adjust to match your actual saved meta keys
                $booking_code   = 'BK' . str_pad(  $meta['wtbm_order_id'], 6, '0', STR_PAD_LEFT );
                $customer_name  = $meta['wtbm_billing_name'] ?? 'N/A';
                $customer_email = $meta['wtbm_billing_email'] ?? 'N/A';
                $customer_phone = $meta['wtbm_billing_phone'] ?? 'N/A';
                $movie_name     = get_the_title( $meta['wtbm_movie_id'] ) ?? 'N/A';
                $movie_genre    = $meta['_movie_genre'] ?? '';
                $screen         = get_the_title( $meta['wtbm_theater_id'] ) ?? 'N/A';
                $date           = $meta['wtbm_order_date'] ?? 'N/A';
                $time           = $meta['wtbm_order_time'] ?? '';
                $seats          = is_array( $meta['wtbm_seats'] ?? '' ) ? implode(', ', $meta['wtbm_seats']) : ( $meta['wtbm_seats'] ?? 'N/A' );
                $price          = $meta['wtbm_tp'] ?? '0.00';


                ?>
                <tr data-order-id="<?php echo esc_attr( $booking_id );?>">
                    <td class="text-sm font-medium text-gray-900"><?php echo esc_html( $booking_code ); ?></td>
                    <td>
                        <div class="text-sm font-medium text-gray-900"><?php echo esc_html( $customer_name ); ?></div>
                        <div class="text-sm text-gray-500"><?php echo esc_html( $customer_email ); ?></div>
                    </td>
                    <td>
                        <div class="text-sm font-medium text-gray-900"><?php echo esc_html( $movie_name ); ?></div>
                        <div class="text-sm text-gray-500"><?php echo esc_html( $movie_genre ); ?></div>
                    </td>
                    <td class="text-sm text-gray-900"><?php echo esc_html( $screen ); ?></td>
                    <td>
                        <div class="text-sm text-gray-900"><?php echo esc_html( $date ); ?></div>
                        <div class="text-sm text-gray-500"><?php echo esc_html( $time ); ?></div>
                    </td>
                    <td class="text-sm text-gray-900"><?php echo esc_html( $seats ); ?></td>
                    <td class="text-sm font-medium text-gray-900"><?php echo esc_html( $price ); echo esc_attr( get_woocommerce_currency_symbol() ) ?></td>
                    <td>
                        <span class="status-badge status-<?php echo esc_attr( strtolower( $status ) ); ?>">
                            <?php echo esc_html( $status ); ?>
                        </span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <?php do_action( 'wtbm_pdf_button', $meta['wtbm_order_id'], 'booking_list' ); ?>
                            <button class="btn-icon edit wtbm_edit_booking" title="Edit Booking">✏️</button>
                            <button class="btn-icon wtbm_delete_booking" style="color: #2563eb;" title="Delete Booking">🗑️</button>
                        </div>
                    </td>
                </tr>
                <?php
                }
            }

            return ob_get_clean();
        }

        public function wtbm_booking_filter_display( $filter_data ) {

            $movie_data     = WTBM_Layout_Functions::get_and_display_movies();
            $theater_data   = WTBM_Layout_Functions::get_and_display_theater_date();
            $show_time_data = WTBM_Layout_Functions::get_show_time_data();
            ?>

            <div id="wtbm_booking_filters" class="wtbm_filter_section" style="display: none">
                <h4 class="wtbm_filter_title">
                    <?php esc_attr_e( 'Filters', 'wptheaterly' ); ?>
                </h4>

                <!-- Row 1 -->
                <div class="wtbm_filter_grid wtbm_filter_grid_4">
                    <div class="wtbm_filter_group">
                        <label class="wtbm_filter_label"><?php esc_attr_e( 'Movie', 'wptheaterly' ); ?></label>
                        <select id="wtbm_movie_filter" name="wtbm_movie_filter" class="wtbm_filter_input">
                            <option value=""><?php esc_html_e( 'Select Movie', 'wptheaterly' ); ?></option>
                            <?php if ( ! empty( $movie_data ) ) :
                                foreach ( $movie_data as $movie ) : ?>
                                    <option value="<?php echo esc_attr( $movie['id'] ); ?>">
                                        <?php echo esc_html( $movie['title'] ); ?>
                                    </option>
                                <?php endforeach;
                            endif; ?>
                        </select>
                    </div>

                    <div class="wtbm_filter_group">
                        <label class="wtbm_filter_label"><?php esc_attr_e( 'Theater', 'wptheaterly' ); ?></label>
                        <select id="wtbm_theater_filter" name="wtbm_theater_filter" class="wtbm_filter_input">
                            <option value=""><?php esc_html_e( 'Select Theater', 'wptheaterly' ); ?></option>
                            <?php if ( ! empty( $theater_data ) ) :
                                foreach ( $theater_data as $theater ) : ?>
                                    <option value="<?php echo esc_attr( $theater['id'] ); ?>">
                                        <?php echo esc_html( $theater['name'] ); ?>
                                    </option>
                                <?php endforeach;
                            endif; ?>
                        </select>
                    </div>

                    <div class="wtbm_filter_group">
                        <label class="wtbm_filter_label"><?php esc_attr_e( 'Show Time', 'wptheaterly' ); ?></label>
                        <select id="wtbm_showtime_filter" name="wtbm_showtime_filter" class="wtbm_filter_input">
                            <option value=""><?php esc_attr_e( 'All Show Times', 'wptheaterly' ); ?></option>
                            <?php if ( ! empty( $show_time_data ) ) :
                                foreach ( $show_time_data as $show_time ) : ?>
                                    <option value="<?php echo esc_attr( $show_time['show_time_start'] ); ?>">
                                        <?php echo esc_html( $show_time['show_time_start'] ); ?>
                                    </option>
                                <?php endforeach;
                            endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Row 2 -->
                <div class="wtbm_filter_grid wtbm_filter_grid_4">
                    <div class="wtbm_filter_group">
                        <label class="wtbm_filter_label"><?php esc_attr_e( 'Booking Status', 'wptheaterly' ); ?></label>
                        <select id="wtbm_order_status_filter" name="wtbm_order_status_filter" class="wtbm_filter_input">
                            <option value=""><?php esc_attr_e( 'All Bookings', 'wptheaterly' ); ?></option>
                            <option value="confirmed"><?php esc_attr_e( 'Confirmed', 'wptheaterly' ); ?></option>
                            <option value="pending"><?php esc_attr_e( 'Pending', 'wptheaterly' ); ?></option>
                            <option value="complete"><?php esc_attr_e( 'Complete', 'wptheaterly' ); ?></option>
                            <option value="cancelled"><?php esc_attr_e( 'Cancelled', 'wptheaterly' ); ?></option>
                        </select>
                    </div>

                    <div class="wtbm_filter_group">
                        <label class="wtbm_filter_label"><?php esc_attr_e( 'Show Date', 'wptheaterly' ); ?></label>
                        <input type="date"
                               id="wtbm_booking_date_filter"
                               name="wtbm_booking_date_filter"
                               class="wtbm_filter_input">
                    </div>
                </div>

                <!-- Buttons -->
                <div class="wtbm_filter_actions" style="display: flex; justify-content: space-between">
                    <div class="wtbm_filter_button_holder">
                        <button type="button" class="wtbm_filter_btn wtbm_filter_btn_primary" id="wtbm_find_booking">
                            <?php esc_attr_e( 'Apply Filters', 'wptheaterly' ); ?>
                        </button>
                        <button type="button" class="wtbm_filter_btn wtbm_filter_btn_secondary" id="wtbm_clear_find_booking">
                            <?php esc_attr_e( 'Clear Filters', 'wptheaterly' ); ?>
                        </button>
                    </div>


                    <div class="wtbm_pdf_csv_btn_holder" style="display: flex; gap: 5px">
                        <button id="wtbm_booking_data_download_btn" data-id="123">
                            <?php esc_attr_e( 'Export PDF', 'wptheaterly' ); ?>
                        </button>
                        <button id="wtbm_booking_data_csv_btn" data-id="1223">
                            <?php esc_attr_e( 'Export CSV', 'wptheaterly' ); ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php
         }

        public function wtbm_booking_header_display( $header_data ){

            ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" style="color: #2563eb;" id="stat-total-bookings"><?php echo esc_attr( $header_data['total_booking']);?></div>
                    <div class="stat-label"><?php esc_attr_e( 'Total Bookings', 'wptheaterly' ); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #059669;" id="stat-total-revenue"><?php echo esc_attr( $header_data['total_revenue']);?></div>
                    <div class="stat-label"><?php esc_attr_e( 'Total Revenue', 'wptheaterly' ); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #d97706;" id="stat-paid-bookings"><?php echo esc_attr( $header_data['total_completed_order']);?></div>
                    <div class="stat-label"><?php esc_attr_e( 'Paid Bookings', 'wptheaterly' ); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #dc2626;" id="stat-cancelled-bookings"><?php echo esc_attr( $header_data['total_canceled_order']);?></div>
                    <div class="stat-label"><?php esc_attr_e( 'Cancelled', 'wptheaterly' ); ?></div>
                </div>
            </div>
        <?php }

        public function wtbm_get_load_more_booking_data(){
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $loaded_booking_id = isset( $_POST['already_loaded_booking_ids'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['already_loaded_booking_ids'] ) ) ) : [];
            $display_limit = isset( $_POST['display_limit'] ) ? sanitize_text_field( wp_unslash( $_POST['display_limit'] ) ) : [];
            $filters_data = array(
                'wtbm_movie_id'       => intval($_POST['movie_id'] ?? 0),
                'wtbm_theater_id'     => intval($_POST['theater_id'] ?? 0),
                'wtbm_order_time'     => sanitize_text_field($_POST['show_time'] ?? ''),
                'wtbm_order_date'     => sanitize_text_field($_POST['show_date'] ?? ''),
                'wtbm_order_status'   => sanitize_text_field($_POST['booking_status'] ?? ''),
            );
                $data = WTBM_Layout_Functions::wtbm_get_filtered_booking_data( $filters_data, $loaded_booking_id, $display_limit );
                $booking_data = $data['booking_data'];
                $booking_count = $data['total_post_count'];

//                $booking_count  = count( $booking_data );

                $booking_html = self::movie_booking_data( $booking_data, $booking_count );
                $result = array(
                        'booking_count' => $booking_count,
                        'booking_data' => $booking_html,
                );
                wp_send_json_success(  $result );
            /*}else{
                wp_send_json_error("Invalid ction");
            }*/
        }

        function wtbm_get_booking_html() {
            check_ajax_referer('mptrs_admin_nonce', 'nonce');

            $booking_id = isset( $_POST['booking_id'] ) ? intval( wp_unslash( $_POST['booking_id'] ) ) : '';
            $booking =  WTBM_Layout_Functions::wtbm_get_booking_data_by_booking_id( $booking_id );
            $order_id = $booking['wtbm_order_id'];

//            error_log( print_r( [ '$booking' => $booking ], true ) );
            $seats = '';
            $seat_ids = '';
            if( is_array( $booking['wtbm_seats'] ) && !empty( $booking['wtbm_seats'] ) ) {
                $seats = implode(', ', $booking['wtbm_seats']);
                $seat_ids = implode(', ', $booking['wtbm_seat_ids']);
            }

            ob_start();
            ?>
            <div class="wtbm_booking_edit_overlay" id="wtbm_booking_edit_overlay">
                <div class="wtbm_booking_edit_modal">

                    <div class="wtbm_booking_edit_header">
                        <h3><?php esc_html_e( 'Edit Booking', 'wptheaterly' );?></h3>
                        <span class="wtbm_booking_edit_close_icon">&times;</span>
                    </div>

                    <div class="wtbm_booking_edit_body">
                        <label><?php esc_html_e( 'Attendee Name', 'wptheaterly' );?></label>
                        <input type="text" class="wtbm_booking_edit_input" name="wtbm_booking_attendee_name" value="<?php echo esc_attr($booking['wtbm_billing_name']); ?>">

                        <label><?php esc_html_e( 'Phone', 'wptheaterly' );?></label>
                        <input type="text" class="wtbm_booking_edit_input" name="wtbm_booking_attendee_phone" value="<?php echo esc_attr($booking['wtbm_billing_phone']); ?>">

                        <label><?php esc_html_e( 'Email', 'wptheaterly' );?></label>
                        <input type="text" class="wtbm_booking_edit_input" name="wtbm_booking_attendee_email" value="<?php echo esc_attr($booking['wtbm_billing_email']); ?>">

                        <label><?php esc_html_e( 'Status', 'wptheaterly' );?></label>
                        <select class="wtbm_booking_edit_select" name="wtbm_booking_status">
                            <option value="confirmed" <?php selected($booking['wtbm_order_status'], 'confirmed'); ?>><?php esc_html_e( 'Confirmed', 'wptheaterly' );?></option>
                            <option value="pending" <?php selected($booking['wtbm_order_status'], 'pending'); ?>><?php esc_html_e( 'Pending', 'wptheaterly' );?></option>
                            <option value="cancelled" <?php selected($booking['wtbm_order_status'], 'cancelled'); ?>><?php esc_html_e( 'Cancelled', 'wptheaterly' );?></option>
                            <option value="completed" <?php selected($booking['wtbm_order_status'], 'completed'); ?>><?php esc_html_e( 'Completed', 'wptheaterly' );?></option>
                        </select>

                        <label><?php esc_html_e( 'Seat Number', 'wptheaterly' );?></label>
                        <input type="hidden" class="wtbm_booking_edit_input" name="wtbm_booking_seat_number" value="<?php echo esc_attr( $seats ); ?>">
                        <input type="hidden" class="wtbm_booking_edit_input" name="wtbm_booking_seat_ids" value="<?php echo esc_attr( $seat_ids ); ?>">
                        <div class="wtbm_booked_seats_display">
                            <?php foreach ( $booking['wtbm_seats'] as $key => $seat_name ){ ?>
                                <span class="wtbm_seat_name" data-seat-id="<?php echo esc_attr( $booking['wtbm_seat_ids'][$key]);?>" data-seat="<?php echo esc_attr( $seat_name ); ?>">
                                    <?php echo esc_attr( $seat_name ); ?>
                                    <span class="remove-seat">✕</span>
                                </span>
                            <?php }?>
                        </div>
                        <div class="wtbm_get_available_seat_holder">
                            <span class="wtbm_get_available_seat" style="cursor:pointer;">Available Sates</span>
                        </div>
                        <div class="wtbm_available_seats" id="wtbm_available_seats"></div>

                        <input type="hidden" class="wtbm_booking_edit_id" value="<?php echo esc_attr( $booking_id ); ?>">
                        <input type="hidden" class="wtbm_order_edit_id" value="<?php echo esc_attr( $order_id ); ?>">
                        <input type="hidden" class="wtbm_movie_edit_id" name="wtbm_edit_movie_id" value="<?php echo esc_attr( $booking['wtbm_movie_id'] ); ?>">
                        <input type="hidden" class="wtbm_theater_edit_id" name="wtbm_edit_theater_id" value="<?php echo esc_attr( $booking['wtbm_theater_id'] ); ?>">
                        <input type="hidden" class="wtbm_movie_time" name="wtbm_movie_time_slot" value="<?php echo esc_attr( $booking['wtbm_order_time'] ); ?>">
                    </div>

                    <div class="wtbm_booking_edit_footer">
                        <button class="wtbm_booking_edit_update_btn" id="wtbm_update_booking"><?php esc_html_e( 'Update', 'wptheaterly' );?></button>
                        <button class="wtbm_booking_edit_close_btn"><?php esc_html_e( 'Close', 'wptheaterly' );?></button>
                    </div>

                </div>
            </div>
            <?php

            echo ob_get_clean();
            wp_die();
        }

        public function wtbm_get_theater_available_seats(){
            $seat_map = '';

            if ( isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wtbm_nonce') ) {
                $theater_id = isset( $_POST['theater_id']) ? sanitize_text_field( wp_unslash($_POST['theater_id'] ) ) : '';
                $movie_id = isset( $_POST['theater_id']) ? sanitize_text_field( wp_unslash($_POST['activeMovieId'] ) ) : '';
                $search_time = isset( $_POST['movie_time_slot']) ? sanitize_text_field( wp_unslash($_POST['movie_time_slot'] ) ) : '';
                $get_date = isset( $_POST['movie_date']) ? sanitize_text_field( wp_unslash($_POST['movie_date'] ) ) : '';

                if( $theater_id && $movie_id &&  $get_date && $search_time ){
                    $not_available = WTBM_Manage_Ajax::getAvailableSeats( $theater_id, $movie_id, $get_date, $search_time );
                }else{
                    $not_available = [];
                }

                $plan_data = get_post_meta( $theater_id, 'wtbp_theater_seat_map', true);

                $plan_seats = isset( $plan_data['seat_data'] ) ? $plan_data['seat_data'] : array();

                ob_start();
                ?>
                <div class="wtbm_booked_seats_display">
                    <?php if( is_array( $plan_seats ) && !empty( $plan_seats ) ) {
                        foreach ($plan_seats as $seat) {
                            $seat_key = 'seat_' . $seat['id'];
                            if (in_array($seat_key, $not_available, true)) {
                                continue;
                            } ?>
                            <span class="wtbm_add_seat_name" data-seat-id="seat_<?php echo esc_attr( $seat['id'] );?>" data-seat="<?php echo esc_attr( $seat['seat_number'] );?>">
                                <?php echo esc_attr( $seat['seat_number'] );?>
                             </span>
                            <?php
                        }

                    } ?>
                </div>
                <?php
                $seat_map = ob_get_clean();
            }

            wp_send_json_success([
                'message' => 'Categories Data getting successfully.!',
                'wtbm_seatMaps' => $seat_map,
            ]);
        }

        function wtbm_delete_booking(){

            check_ajax_referer('mptrs_admin_nonce', 'nonce');
            if (empty($_POST['booking_id'])) {
                wp_send_json_error('Invalid booking ID');
            }
            $booking_id = isset( $_POST['booking_id'] ) ? intval( wp_unslash( $_POST['booking_id'] ) ) : '';
            if( $booking_id ){
                wp_update_post([
                    'ID' => $booking_id,
                    'post_status' => 'draft'
                ]);
            }

        }

        function wtbm_update_booking_data() {

            check_ajax_referer('mptrs_admin_nonce', 'nonce');

            if ( empty($_POST['booking_id']) ) {
                wp_send_json_error('Invalid booking ID');
            }

            $booking_id = isset( $_POST['booking_id'] ) ? intval( wp_unslash( $_POST['booking_id'] ) ) : '';
            $order_id   = isset( $_POST['order_id'] ) ? intval(  wp_unslash( $_POST['order_id'] ) ) : '';
            $seat_number   = isset( $_POST['seat_number'] ) ? sanitize_text_field(  wp_unslash( $_POST['seat_number'] ) ) : '';
            $seat_ids   = isset( $_POST['seat_ids'] ) ? sanitize_text_field(  wp_unslash( $_POST['seat_ids'] ) ) : '';
            $booking_status   = isset( $_POST['booking_status'] ) ? sanitize_text_field(  wp_unslash( $_POST['booking_status'] ) ) : '';

            if( $seat_number ){
                $seat_numbers = array_map('trim', explode(',', $seat_number));
                $seat_ids_ary = array_map('trim', explode(',', $seat_ids));
                update_post_meta($booking_id, 'wtbm_seats', $seat_numbers );
                update_post_meta($booking_id, 'wtbm_seat_ids', $seat_ids_ary );
            }

            update_post_meta($booking_id, 'wtbm_billing_name', sanitize_text_field($_POST['attendee_name']));
            update_post_meta($booking_id, 'wtbm_billing_phone', sanitize_text_field($_POST['attendee_phone']));
            update_post_meta($booking_id, 'wtbm_billing_email', sanitize_email($_POST['attendee_email']));

            update_post_meta($booking_id, 'wtbm_order_status', sanitize_text_field($_POST['booking_status']));
            if ( $order_id ) {
                $order = wc_get_order($order_id);
                if ( $order ) {
                    $order->update_status( $booking_status  );
                    if( $booking_status === 'cancelled' ){
                        wp_update_post([
                            'ID' => $booking_id,
                            'post_status' => 'draft'
                        ]);
                    }else{
                        wp_update_post([
                            'ID' => $booking_id,
                            'post_status' => 'publish'
                        ]);
                    }
                }
            }

            wp_send_json_success('Booking updated');
        }

    }

    new WTBM_Booking_Content();
}