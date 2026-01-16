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
                            🔍 <?php esc_attr_e( 'Filters', 'wptheaterly' ); ?>
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
                            <th><?php esc_attr_e( 'Payment Status', 'wptheaterly' ); ?></th>
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
                            <?php if( $display_count > 9 ){?>
                                <button id="wtbm_booking_load_more_btn" class="btn btn-primary" style="display: block">
                                    📄 <?php esc_attr_e( 'Load More Bookings (+10)', 'wptheaterly' ); ?>
                                </button>
                            <?php }?>
                            <!--<div id="no-more-data" class="text-sm text-gray-500 hidden">
                                ✅ <?php /*esc_attr_e( 'All bookings loaded', 'wptheaterly' ); */?>
                            </div>-->
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
<!--                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>-->
                            <?php do_action( 'wtbm_pdf_button', $meta['wtbm_order_id'], 'booking_list' ); ?>
<!--                            <button class="btn-icon edit" title="Edit Booking">✏️</button>-->
                        </div>
                    </td>
                </tr>
                <?php
                }
            }

            return ob_get_clean();
        }
        public function wtbm_booking_filter_display1( $filter_data ){
            $movie_data = WTBM_Layout_Functions::get_and_display_movies();
            $theater_data = WTBM_Layout_Functions::get_and_display_theater_date();
            $show_time_data = WTBM_Layout_Functions::get_show_time_data();

            ?>
            <div id="wtbm_booking_filters" class="wtbm_filters_section">
                <h4 class="mb-4 font-semibold"><?php esc_attr_e( 'Filters', 'wptheaterly' ); ?></h4>
                <div class="grid grid-cols-4 mb-4">
                    <div class="form-group">
                        <label class="form-label"><?php esc_attr_e( 'Movie', 'wptheaterly' ); ?></label>
                        <select id="wtbm_movie_filter" name="wtbm_movie_filter" class="form-input">
                            <option value=""><?php esc_html_e( 'Select Movie', 'wptheaterly' ); ?></option>
                            <?php if( is_array( $movie_data ) && !empty( $movie_data ) ){
                                foreach ( $movie_data as $movie ){
                                    ?>
                                    <option value="<?php echo esc_attr( $movie['id'] )?>" <?php selected( $show_time_data['movie_id'] ?? '', $movie['id']); ?>><?php echo esc_attr(  $movie['title'] )?></option>
                                <?php } }?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php esc_attr_e( 'Theater', 'wptheaterly' ); ?></label>
                        <select id="wtbm_theater_filter" name="wtbm_theater_filter" class="form-input">
                            <option value=""><?php esc_html_e( 'Select Theater', 'wptheaterly' ); ?></option>
                            <?php if( is_array( $theater_data ) && !empty( $theater_data ) ){
                                foreach ( $theater_data as $theater ){
                                    ?>
                                    <option value="<?php echo esc_attr( $theater['id'] )?>" <?php selected( $show_time_data['theater_id'] ?? '', $theater['id']); ?>><?php echo esc_attr( $theater['name'] )?></option>
                                <?php }
                            }?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php esc_attr_e( 'Show Time', 'wptheaterly' ); ?></label>
                        <select id="wtbm_showtime_filter" name="wtbm_showtime_filter" class="form-input">
                            <option value=""><?php esc_attr_e( 'All Show Times', 'wptheaterly' ); ?></option>
                            <?php if( is_array( $show_time_data ) && !empty( $show_time_data ) ){
                                foreach ( $show_time_data as $show_time ){
                                    ?>
                                    <option value="<?php echo esc_attr( $show_time['show_time_start'] )?>" ><?php echo esc_attr( $show_time['show_time_start'] )?></option>
                                <?php }
                            }?>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-4 mb-4">
                    <div class="form-group">
                        <label class="form-label"><?php esc_attr_e( 'Booking Status', 'wptheaterly' ); ?></label>
                        <select id="wtbm_order_status_filter" name="wtbm_order_status_filter" class="form-input">
                            <option value=""><?php esc_attr_e( 'All Bookings', 'wptheaterly' ); ?></option>
                            <option value="confirmed"><?php esc_attr_e( 'Confirmed', 'wptheaterly' ); ?></option>
                            <option value="pending"><?php esc_attr_e( 'Pending', 'wptheaterly' ); ?></option>
                            <option value="cancelled"><?php esc_attr_e( 'Cancelled', 'wptheaterly' ); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php esc_attr_e( 'Show Date', 'wptheaterly' ); ?></label>
                        <input type="date" id="wtbm_booking_date_filter" name="wtbm_booking_date_filter" class="form-input">
                    </div>
                </div>
                <button class="btn btn-secondary" id="wtbm_find_booking"><?php esc_attr_e( 'Filters', 'wptheaterly' ); ?></button>
                <button class="btn btn-secondary" id="wtbm_clear_find_booking"><?php esc_attr_e( 'Clear Filters', 'wptheaterly' ); ?></button>
            </div>
      <?php }

        public function wtbm_booking_filter_display( $filter_data ) {

            $movie_data     = WTBM_Layout_Functions::get_and_display_movies();
            $theater_data   = WTBM_Layout_Functions::get_and_display_theater_date();
            $show_time_data = WTBM_Layout_Functions::get_show_time_data();
            ?>

            <div id="wtbm_booking_filters" class="wtbm_filter_section">
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
                <div class="wtbm_filter_actions">
                    <button type="button" class="wtbm_filter_btn wtbm_filter_btn_primary" id="wtbm_find_booking">
                        <?php esc_attr_e( 'Apply Filters', 'wptheaterly' ); ?>
                    </button>
                    <button type="button" class="wtbm_filter_btn wtbm_filter_btn_secondary" id="wtbm_clear_find_booking">
                        <?php esc_attr_e( 'Clear Filters', 'wptheaterly' ); ?>
                    </button>
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
//            if( $loaded_booking_id ){
                /*$args = array(
                    'post_type'      => 'wtbm_booking',
                    'post_status'    => 'publish',
                    'posts_per_page' => $display_limit,
                    'post__not_in'   => $loaded_booking_id,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                );

                $query = new WP_Query( $args );
                $booking_data = [];

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

    }

    new WTBM_Booking_Content();
}