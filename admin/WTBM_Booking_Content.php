<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if( !class_exists( 'WTBM_Booking_Content' ) ){
    class WTBM_Booking_Content{

        public function __construct(){
            add_action( 'wtbm_bookings_content', [$this, 'my_bookings_content_handler'], 10, 2 );
            add_action( 'wtbm_booking_header', [$this, 'wtbm_booking_header_display'], 10, 1 );
            add_action( 'wtbm_booking_filter', [$this, 'wtbm_booking_filter_display'], 10, 1 );

            add_action( 'wtbm_booking_content', [$this, 'wtbm_booking_data_display'], 10, 2 );

            add_action('wp_ajax_wtbm_get_load_more_booking_data', [ $this, 'wtbm_get_load_more_booking_data' ]);

            add_action('wp_ajax_wtbm_filter_bookings', [ $this, 'wtbm_filter_bookings' ] );

            add_action('wp_ajax_wtbm_get_booking_html', [ $this, 'wtbm_get_booking_html' ] );
            add_action('wp_ajax_wtbm_update_booking_data', [ $this, 'wtbm_update_booking_data' ] );

            add_action( 'wp_ajax_wtbm_get_theater_available_seats', [ $this, 'wtbm_get_theater_available_seats' ] );
            add_action( 'wp_ajax_nopriv_wtbm_get_theater_available_seats', [ $this, 'wtbm_get_theater_available_seats' ] );

            add_action( 'wp_ajax_wtbm_delete_booking', [ $this, 'wtbm_delete_booking' ] );

//            add_action('wp_ajax_wtbm_bookings_data_display', [ $this, 'wtbm_bookings_data_display' ]);
        }

        /** Normalize filters shared by AJAX results, pagination and exports. */
        public static function booking_filters( $request ) {
            $time = sanitize_text_field( wp_unslash( $request['show_time'] ?? '' ) );
            $date = sanitize_text_field( wp_unslash( $request['show_date'] ?? '' ) );
            $status = sanitize_key( $request['booking_status'] ?? '' );
            $statuses = array_map( static function ( $key ) { return substr( $key, 3 ); }, array_keys( wc_get_order_statuses() ) );
            if ( $status && ! in_array( $status, $statuses, true ) ) {
                return new WP_Error( 'status', __( 'Choose a valid booking status.', 'wptheaterly' ) );
            }
            if ( $date && ( ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $date, $parts ) || ! checkdate( (int) $parts[2], (int) $parts[3], (int) $parts[1] ) ) ) {
                return new WP_Error( 'date', __( 'Choose a valid show date.', 'wptheaterly' ) );
            }
            if ( $time ) {
                $time = WTBM_Booking_Service::show_time( $time );
                if ( ! $time ) { return new WP_Error( 'time', __( 'Choose a valid show time.', 'wptheaterly' ) ); }
            }
            return array(
                'wtbm_movie_id' => absint( $request['movie_id'] ?? 0 ),
                'wtbm_theater_id' => absint( $request['theater_id'] ?? 0 ),
                'wtbm_order_time' => $time,
                'wtbm_order_date' => $date,
                'wtbm_order_status' => $status,
            );
        }

        function wtbm_filter_bookings() {
            check_ajax_referer( 'mptrs_admin_nonce', 'nonce' );
            if ( ! WTBM_Booking_Service::can( 'manage' ) ) { wp_send_json_error( 'Permission denied.', 403 ); }
            $filters = self::booking_filters( $_POST );
            if ( is_wp_error( $filters ) ) { wp_send_json_error( $filters->get_error_message(), 400 ); }
            $excluded = array_map( 'absint', (array) ( $_POST['loaded_ids'] ?? array() ) );
            $data = WTBM_Layout_Functions::wtbm_get_filtered_booking_data( $filters, $excluded, 10 );
            wp_send_json_success( array(
                'html' => self::movie_booking_data( $data['booking_data'], count( $data['booking_data'] ) ),
                'count' => count( $data['booking_data'] ),
                'total_booking' => $data['total_booking'] + count( $excluded ),
            ) );
        }

        public function my_bookings_content_handler( $header_title, $current_tab ){

            /*$args = array(
                'post_type'      => 'wtbm_booking',
                'post_status'    => 'publish',
                'posts_per_page' => 10,
            );

            $query = new WP_Query( $args );*/
            $data = WTBM_Layout_Functions::wtbm_get_filtered_booking_data( $filters_data = [],[], 10 );
            $booking_header_data = WTBM_Layout_Functions::wtbm_get_booking_header_statistics( );

            $booking_data = $data['booking_data'];
            $total_booking = $data['total_booking'];

            $completed_orders = $booking_header_data['total_orders'] - $booking_header_data['cancelled_orders'];
            $header_data = array(
              'total_booking'           => $booking_header_data['total_orders'],
              'total_revenue'           => $booking_header_data['total_revenue'],
              'total_completed_order'   => $completed_orders,
              'total_canceled_order'    => $booking_header_data['cancelled_orders'],
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
            <div id="wtbm_bookings_content" class="tab-content <?php echo ($current_tab == 'wtbm_bookings') ? 'active' : ''; ?>">
                <?php
                do_action('wtbm_booking_header', $header_data );
                ?>

                <div class="section">
                    <div class="section-header">
                        <div>
                            <h3 class="section-title"><?php echo esc_attr( $header_title );?></h3>
                            <p class="text-sm text-gray-500" id="bookings-count"><?php esc_attr_e( 'Total:', 'wptheaterly' ); ?> <?php echo esc_attr( $total_booking );?> <?php esc_attr_e( 'bookings', 'wptheaterly' ); ?></p>
                        </div>
                        <button type="button" class="btn btn-secondary wtbm_show_filter" aria-expanded="false" aria-controls="wtbm_booking_filters">
                            <i class="mi mi-search"></i> <?php esc_html_e('Filters','wptheaterly'); ?>
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
                            <th><?php esc_html_e( 'Check-in', 'wptheaterly' ); ?></th>
                            <th><?php esc_attr_e( 'Actions', 'wptheaterly' ); ?></th>
                        </tr>
                        </thead>
                        <tbody id="wtbm_bookings_table_body">
                        <?php
                        echo wp_kses_post( $display_count ? self::movie_booking_data( $booking_date, $display_count ) : '<tr class="wtbm-booking-empty"><td colspan="10">' . esc_html__( 'No bookings found. Try different filters.', 'wptheaterly' ) . '</td></tr>' );
                        ?>
                        </tbody>
                    </table>

                        <div id="load-more-section" class="text-center py-4 border-t border-gray-200">
                            <div class="mb-3" id="wtbm_showing_number_of_booking" style="display: block">
                                <span class="text-sm text-gray-600" id="showing-info" data-shoing-info ="">
                                    <?php echo $display_count ? esc_html__( 'Showing 1', 'wptheaterly' ) : esc_html__( 'Showing 0', 'wptheaterly' ); ?>-
                                    <span id="wtbm_showing_count"><?php echo esc_attr($display_count); ?></span>
                                    <?php esc_attr_e( 'of ', 'wptheaterly' ); ?>
                                    <span id="wtbm_total_booking_count"><?php echo esc_attr( $total_booking );?></span>
                                    <?php esc_attr_e( 'Bookings', 'wptheaterly' ); ?>
                                </span>
                            </div>
                            <div class="wtbm_loadmore_csv_pdf_btn_holder" style="display: flex; justify-content: space-between; gap: 10px">

                                    <button type="button" id="wtbm_booking_load_more_btn" class="btn btn-primary" <?php echo $total_booking > $display_count ? '' : 'hidden'; ?>>
                                        📄 <?php esc_attr_e( 'Load More Bookings (+10)', 'wptheaterly' ); ?>
                                    </button>

                            </div>

                        </div>

            </div>
        <?php }

        public function wtbm_bookings_data_display(){

        }

        public static function movie_booking_data( $booking_dates, $display_count ) {
            ob_start();
            $checkins = WTBM_Tickets::checkin_data( array_keys( $booking_dates ) );

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
                    <td class="wtbm-booking-checkin">
                        <?php $checkin = $checkins[ $booking_id ]; $checked = $checkin['count']; ?>
                        <span class="wtbm-checkin-badge <?php echo $checked ? ( $checked === $checkin['total'] ? 'wtbm-checkin-complete' : 'wtbm-checkin-partial' ) : 'wtbm-checkin-pending'; ?>"><?php echo esc_html( $checked ? ( $checked === $checkin['total'] ? __( 'Checked in', 'wptheaterly' ) : __( 'Partially checked in', 'wptheaterly' ) ) : __( 'Not checked in', 'wptheaterly' ) ); ?></span>
                        <?php if ( $checked ) { ?>
                            <small class="wtbm-checkin-count"><?php /* translators: 1: checked-in seat count, 2: booked seat count. */ printf( esc_html__( '%1$d of %2$d seats', 'wptheaterly' ), (int) $checked, (int) $checkin['total'] ); ?></small>
                            <details class="wtbm-checkin-details"><summary><?php esc_html_e( 'Check-in details', 'wptheaterly' ); ?></summary>
                            <?php foreach ( $checkin['groups'] as $group ) { ?><div><strong><?php echo esc_html( implode( ', ', $group['seats'] ) ); ?></strong><small><?php echo esc_html( $group['time'] ); ?></small><small><?php echo esc_html( $group['staff'] ); ?></small></div><?php } ?>
                            </details>
                        <?php } ?>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <?php do_action( 'wtbm_pdf_button', $meta['wtbm_order_id'], 'booking_list' ); ?>
                            <button class="btn-icon edit wtbm_edit_booking" title="Edit Booking"><i class="mi mi-pencil"></i></button>
                            <button class="btn-icon delete wtbm_delete_booking"  title="Delete Booking"><i class="mi mi-trash"></i></button>
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
            $show_time_data = array();
            foreach ( WTBM_Layout_Functions::get_show_time_data() as $show ) {
                $time = WTBM_Booking_Service::show_time( $show['show_time_start'] );
                if ( $time ) { $show_time_data[ $time ] = array( 'show_time_start' => $time ); }
            }
            ksort( $show_time_data );
            ?>

            <div id="wtbm_booking_filters" class="wtbm_filter_section" style="display: none">
                <h4 class="wtbm_filter_title">
                    <?php esc_html_e( 'Find your bookings', 'wptheaterly' ); ?>
                </h4><p class="wtbm-filter-description"><?php esc_html_e( 'Narrow the booking list. Exports include every booking matching these filters.', 'wptheaterly' ); ?></p>

                <!-- Row 1 -->
                <div class="wtbm_filter_grid wtbm_filter_grid_4">
                    <div class="wtbm_filter_group">
                        <label for="wtbm_movie_filter" class="wtbm_filter_label"><?php esc_attr_e( 'Movie', 'wptheaterly' ); ?></label>
                        <select id="wtbm_movie_filter" name="wtbm_movie_filter" class="wtbm_filter_input">
                            <option value=""><?php esc_html_e( 'All movies', 'wptheaterly' ); ?></option>
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
                        <label for="wtbm_theater_filter" class="wtbm_filter_label"><?php esc_attr_e( 'Theater', 'wptheaterly' ); ?></label>
                        <select id="wtbm_theater_filter" name="wtbm_theater_filter" class="wtbm_filter_input">
                            <option value=""><?php esc_html_e( 'All theaters', 'wptheaterly' ); ?></option>
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
                        <label for="wtbm_showtime_filter" class="wtbm_filter_label"><?php esc_attr_e( 'Show Time', 'wptheaterly' ); ?></label>
                        <select id="wtbm_showtime_filter" name="wtbm_showtime_filter" class="wtbm_filter_input">
                            <option value=""><?php esc_attr_e( 'All Show Times', 'wptheaterly' ); ?></option>
                            <?php if ( ! empty( $show_time_data ) ) :
                                foreach ( $show_time_data as $show_time ) : ?>
                                    <option value="<?php echo esc_attr( WTBM_Booking_Service::show_time( $show_time['show_time_start'] ) ); ?>">
                                        <?php echo esc_html( $show_time['show_time_start'] ); ?>
                                    </option>
                                <?php endforeach;
                            endif; ?>
                        </select>
                    </div>

                    <div class="wtbm_filter_group">
                        <label for="wtbm_order_status_filter" class="wtbm_filter_label"><?php esc_attr_e( 'Booking Status', 'wptheaterly' ); ?></label>
                        <select id="wtbm_order_status_filter" name="wtbm_order_status_filter" class="wtbm_filter_input">
                            <option value=""><?php esc_attr_e( 'All Bookings', 'wptheaterly' ); ?></option>
                            <?php foreach ( wc_get_order_statuses() as $key => $label ) { ?><option value="<?php echo esc_attr( substr( $key, 3 ) ); ?>"><?php echo esc_html( $label ); ?></option><?php } ?>
                        </select>
                    </div>

                    <div class="wtbm_filter_group">
                        <label for="wtbm_booking_date_filter" class="wtbm_filter_label"><?php esc_attr_e( 'Show Date', 'wptheaterly' ); ?></label>
                        <input type="date"
                               id="wtbm_booking_date_filter"
                               name="wtbm_booking_date_filter"
                               class="wtbm_filter_input">
                    </div>
                </div>

                <!-- Buttons -->
                <div class="wtbm_filter_actions">
                    <div class="wtbm_filter_button_holder">
                        <button type="button" class="wtbm_filter_btn wtbm_filter_btn_primary" id="wtbm_find_booking">
                            <?php esc_attr_e( 'Apply Filters', 'wptheaterly' ); ?>
                        </button>
                        <button type="button" class="wtbm_filter_btn wtbm_filter_btn_secondary" id="wtbm_clear_find_booking">
                            <?php esc_attr_e( 'Clear Filters', 'wptheaterly' ); ?>
                        </button>
                    </div>


                    <div class="wtbm_pdf_csv_btn_holder">
                        <button type="button" id="wtbm_booking_data_download_btn">
                            <?php esc_attr_e( 'Export PDF', 'wptheaterly' ); ?>
                        </button>
                        <button type="button" id="wtbm_booking_data_csv_btn">
                            <?php esc_attr_e( 'Export CSV', 'wptheaterly' ); ?>
                        </button>
                    </div>
                </div><p id="wtbm-booking-feedback" role="status" aria-live="polite"></p>
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
                    <div class="stat-value" style="color: #059669;" id="stat-total-revenue"><?php echo wp_kses_post( wc_price( $header_data['total_revenue'] ) );?></div>
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
            if ( ! WTBM_Booking_Service::can( 'manage' ) ) { wp_send_json_error( 'Permission denied.', 403 ); }

            $loaded_booking_id = isset( $_POST['already_loaded_booking_ids'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['already_loaded_booking_ids'] ) ) ) : [];
            $display_limit = isset( $_POST['display_limit'] ) ? sanitize_text_field( wp_unslash( $_POST['display_limit'] ) ) : [];

            $show_time = isset( $_POST['show_time'] ) ? sanitize_text_field(wp_unslash( $_POST['show_time'] ) ) : '';
            $show_date = isset( $_POST['show_date'] ) ? sanitize_text_field(wp_unslash( $_POST['show_date'] ) ) : '';
            $booking_status = isset( $_POST['booking_status'] ) ? sanitize_text_field(wp_unslash( $_POST['booking_status'] ) ) : '';

            $filters_data = array(
                'wtbm_movie_id'       => intval($_POST['movie_id'] ?? 0),
                'wtbm_theater_id'     => intval($_POST['theater_id'] ?? 0),
                'wtbm_order_time'     => $show_time,
                'wtbm_order_date'     => $show_date,
                'wtbm_order_status'   => $booking_status,
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
            if ( ! WTBM_Booking_Service::can( 'manage' ) ) { wp_send_json_error( 'Permission denied.', 403 ); }
            if ( ! WTBM_Booking_Service::can( 'manage' ) ) {
                wp_send_json_error( __( 'Permission denied.', 'wptheaterly' ), 403 );
            }

            $booking_id = isset( $_POST['booking_id'] ) ? intval( wp_unslash( $_POST['booking_id'] ) ) : '';
            $booking =  WTBM_Layout_Functions::wtbm_get_booking_data_by_booking_id( $booking_id );
            if ( empty( $booking ) ) {
                wp_send_json_error( __( 'Booking not found.', 'wptheaterly' ), 404 );
            }
            $booking['wtbm_seats'] = is_array( $booking['wtbm_seats'] ?? null ) ? $booking['wtbm_seats'] : array();
            $booking['wtbm_seat_ids'] = is_array( $booking['wtbm_seat_ids'] ?? null ) ? $booking['wtbm_seat_ids'] : array();
            $order_id = $booking['wtbm_order_id'];

            $seats = '';
            $seat_ids = '';
            if( is_array( $booking['wtbm_seats'] ) && !empty( $booking['wtbm_seats'] ) ) {
                $seats = implode(', ', $booking['wtbm_seats']);
                $seat_ids = implode(', ', $booking['wtbm_seat_ids']);
            }

            ob_start();
            ?>
            <div class="wtbm_booking_edit_overlay" id="wtbm_booking_edit_overlay">
                <div class="wtbm_booking_edit_modal" role="dialog" aria-modal="true" aria-labelledby="wtbm-booking-edit-title" tabindex="-1">
                    <header class="wtbm_booking_edit_header">
                        <div class="wtbm-edit-heading"><span class="wtbm-edit-symbol" aria-hidden="true"><i class="mi mi-pencil"></i></span><div><span class="wtbm-edit-eyebrow"><?php esc_html_e( 'Booking management', 'wptheaterly' ); ?></span><h3 id="wtbm-booking-edit-title"><?php esc_html_e( 'Edit Booking', 'wptheaterly' ); ?></h3><p><?php printf( esc_html__( 'Booking #%s', 'wptheaterly' ), esc_html( 'BK' . str_pad( $order_id, 6, '0', STR_PAD_LEFT ) ) ); ?></p></div></div>
                        <button type="button" class="wtbm_booking_edit_close_icon" aria-label="<?php esc_attr_e( 'Close booking editor', 'wptheaterly' ); ?>">&times;</button>
                    </header>
                    <div class="wtbm_booking_edit_body">
                        <div class="wtbm-edit-context">
                            <div><span><?php esc_html_e( 'Movie', 'wptheaterly' ); ?></span><strong><?php echo esc_html( get_the_title( $booking['wtbm_movie_id'] ) ); ?></strong></div>
                            <div><span><?php esc_html_e( 'Theater', 'wptheaterly' ); ?></span><strong><?php echo esc_html( get_the_title( $booking['wtbm_theater_id'] ) ); ?></strong></div>
                            <div><span><?php esc_html_e( 'Show date', 'wptheaterly' ); ?></span><strong><?php echo esc_html( $booking['wtbm_order_date'] ); ?></strong></div>
                            <div><span><?php esc_html_e( 'Showtime', 'wptheaterly' ); ?></span><strong><?php echo esc_html( $booking['wtbm_order_time'] ); ?></strong></div>
                        </div>
                        <div class="wtbm-edit-columns">
                            <section class="wtbm-edit-card">
                                <div class="wtbm-edit-card-heading"><h4><?php esc_html_e( 'Attendee details', 'wptheaterly' ); ?></h4><p><?php esc_html_e( 'Contact information for this booking.', 'wptheaterly' ); ?></p></div>
                                <div class="wtbm-edit-fields">
                                    <div class="wtbm-edit-field wtbm-edit-full"><label for="wtbm-edit-name"><?php esc_html_e( 'Full name', 'wptheaterly' ); ?></label><input id="wtbm-edit-name" type="text" class="wtbm_booking_edit_input" name="wtbm_booking_attendee_name" value="<?php echo esc_attr( $booking['wtbm_billing_name'] ); ?>"></div>
                                    <div class="wtbm-edit-field"><label for="wtbm-edit-phone"><?php esc_html_e( 'Phone number', 'wptheaterly' ); ?></label><input id="wtbm-edit-phone" type="tel" class="wtbm_booking_edit_input" name="wtbm_booking_attendee_phone" value="<?php echo esc_attr( $booking['wtbm_billing_phone'] ); ?>"></div>
                                    <div class="wtbm-edit-field"><label for="wtbm-edit-email"><?php esc_html_e( 'Email address', 'wptheaterly' ); ?></label><input id="wtbm-edit-email" type="email" class="wtbm_booking_edit_input" name="wtbm_booking_attendee_email" value="<?php echo esc_attr( $booking['wtbm_billing_email'] ); ?>"></div>
                                    <div class="wtbm-edit-field wtbm-edit-full"><label for="wtbm-edit-status"><?php esc_html_e( 'Booking status', 'wptheaterly' ); ?></label>
                                        <select id="wtbm-edit-status" class="wtbm_booking_edit_select" name="wtbm_booking_status">
                                            <option value="confirmed" <?php selected( $booking['wtbm_order_status'], 'confirmed' ); ?>><?php esc_html_e( 'Confirmed', 'wptheaterly' ); ?></option>
                                            <option value="pending" <?php selected( $booking['wtbm_order_status'], 'pending' ); ?>><?php esc_html_e( 'Pending', 'wptheaterly' ); ?></option>
                                            <option value="cancelled" <?php selected( $booking['wtbm_order_status'], 'cancelled' ); ?>><?php esc_html_e( 'Cancelled', 'wptheaterly' ); ?></option>
                                            <option value="completed" <?php selected( $booking['wtbm_order_status'], 'completed' ); ?>><?php esc_html_e( 'Completed', 'wptheaterly' ); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </section>
                            <section class="wtbm-edit-card wtbm-edit-seat-card">
                                <div class="wtbm-edit-card-heading"><h4><?php esc_html_e( 'Seat selection', 'wptheaterly' ); ?><span id="wtbm-edit-seat-count" aria-live="polite"><?php echo esc_html( count( $booking['wtbm_seats'] ) ); ?></span></h4><p><?php esc_html_e( 'Manage the seats assigned to this booking.', 'wptheaterly' ); ?></p></div>
                                <div class="wtbm-edit-seat-body">
                                    <span class="wtbm-edit-label"><?php esc_html_e( 'Selected seats', 'wptheaterly' ); ?></span>
                                    <input type="hidden" name="wtbm_booking_seat_number" value="<?php echo esc_attr( $seats ); ?>">
                                    <input type="hidden" name="wtbm_booking_seat_ids" value="<?php echo esc_attr( $seat_ids ); ?>">
                                    <div class="wtbm_booked_seats_display" id="wtbm-edit-selected-seats" data-remove-label="<?php esc_attr_e( 'Remove seat', 'wptheaterly' ); ?>">
                                        <?php foreach ( $booking['wtbm_seats'] as $key => $seat_name ) { ?>
                                            <span class="wtbm_seat_name" data-seat-id="<?php echo esc_attr( $booking['wtbm_seat_ids'][ $key ] ?? '' ); ?>" data-seat="<?php echo esc_attr( $seat_name ); ?>">
                                                <?php echo esc_html( $seat_name ); ?>
                                                <button type="button" class="remove-seat" aria-label="<?php echo esc_attr( sprintf( __( 'Remove seat %s', 'wptheaterly' ), $seat_name ) ); ?>">&times;</button>
                                            </span>
                                        <?php } ?>
                                    </div>
                                    <div class="wtbm_get_available_seat_holder"><button type="button" class="wtbm_get_available_seat" aria-controls="wtbm_available_seats" data-loading-label="<?php esc_attr_e( 'Loading seats…', 'wptheaterly' ); ?>" data-empty-label="<?php esc_attr_e( 'No available seats for this show.', 'wptheaterly' ); ?>" data-error-label="<?php esc_attr_e( 'Unable to load seats. Please try again.', 'wptheaterly' ); ?>"><?php esc_html_e( 'Browse available seats', 'wptheaterly' ); ?></button></div>
                                    <div class="wtbm_available_seats" id="wtbm_available_seats" aria-live="polite"></div>
                                    <p class="wtbm-edit-seat-hint"><?php esc_html_e( 'Select an available seat to add it. Use × to remove a selected seat.', 'wptheaterly' ); ?></p>
                                </div>
                            </section>
                        </div>
                        <input type="hidden" class="wtbm_booking_edit_id" value="<?php echo esc_attr( $booking_id ); ?>">
                        <input type="hidden" class="wtbm_order_edit_id" value="<?php echo esc_attr( $order_id ); ?>">
                        <input type="hidden" class="wtbm_movie_edit_id" name="wtbm_edit_movie_id" value="<?php echo esc_attr( $booking['wtbm_movie_id'] ); ?>">
                        <input type="hidden" class="wtbm_theater_edit_id" name="wtbm_edit_theater_id" value="<?php echo esc_attr( $booking['wtbm_theater_id'] ); ?>">
                        <input type="hidden" class="wtbm_movie_time" name="wtbm_movie_time_slot" value="<?php echo esc_attr( $booking['wtbm_order_time'] ); ?>">
                        <input type="hidden" class="wtbm_movie_order_date" name="wtbm_movie_order_date" value="<?php echo esc_attr( $booking['wtbm_order_date'] ); ?>">
                    </div>
                    <footer class="wtbm_booking_edit_footer">
                        <span><a href="<?php echo esc_url( add_query_arg( array( 'order_id' => $booking['wtbm_order_id'] ), get_permalink( get_option( 'wtbm_tickets_page' ) ) ) ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Cancellation and rescheduling', 'wptheaterly' ); ?></a></span>
                        <div><button type="button" class="wtbm_booking_edit_close_btn"><?php esc_html_e( 'Cancel', 'wptheaterly' ); ?></button><button type="button" class="wtbm_booking_edit_update_btn" id="wtbm_update_booking"><?php esc_html_e( 'Update Booking', 'wptheaterly' ); ?></button></div>
                    </footer>
                </div>
            </div>
            <?php

            $edit_data = ob_get_clean();
            wp_send_json_success([
                'message' => 'getting edit data successfully.!',
                'edit_data' => $edit_data,
            ]);
        }

        public function wtbm_get_theater_available_seats(){
            $seat_map = '';

            if ( isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wtbm_nonce') ) {
                $theater_id = isset( $_POST['theater_id']) ? sanitize_text_field( wp_unslash($_POST['theater_id'] ) ) : '';
                $movie_id = isset( $_POST['activeMovieId']) ? sanitize_text_field( wp_unslash($_POST['activeMovieId'] ) ) : '';
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
                            <button type="button" class="wtbm_add_seat_name" data-seat-id="seat_<?php echo esc_attr( $seat['id'] );?>" data-seat="<?php echo esc_attr( $seat['seat_number'] );?>">
                                <?php echo esc_attr( $seat['seat_number'] );?>
                             </button>
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
            check_ajax_referer( 'mptrs_admin_nonce', 'nonce' );
            if ( ! WTBM_Booking_Service::can( 'manage' ) ) { wp_send_json_error( 'Permission denied.', 403 ); }
            WTBM_Operations::response( WTBM_Tickets::cancel( absint( $_POST['booking_id'] ?? 0 ), array(), true ) );
        }
        function wtbm_update_booking_data() {
            check_ajax_referer( 'mptrs_admin_nonce', 'nonce' );
            $request = wp_unslash( $_POST );
            $status = sanitize_key( $request['booking_status'] ?? '' );
            if ( 'confirmed' === $status ) { $status = 'processing'; }
            if ( 'cancelled' === $status ) { WTBM_Operations::response( WTBM_Tickets::cancel( absint( $request['booking_id'] ?? 0 ), array(), true ) ); }
            $changes = array( 'ids' => array_filter( array_map( 'trim', explode( ',', sanitize_text_field( $request['seat_ids'] ?? '' ) ) ) ), 'name' => $request['attendee_name'] ?? '', 'phone' => $request['attendee_phone'] ?? '', 'email' => $request['attendee_email'] ?? '', 'status' => $status );
            WTBM_Operations::response( WTBM_Operations::edit( absint( $request['booking_id'] ?? 0 ), $changes ) );
        }
    }
    new WTBM_Booking_Content();
}
