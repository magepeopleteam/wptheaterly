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

//            add_action('wp_ajax_wtbm_bookings_data_display', [ $this, 'wtbm_bookings_data_display' ]);
//            add_action('wp_ajax_nopriv_wtbm_bookings_data_display', [ $this, 'wtbm_bookings_data_display' ]);
        }

        public function my_bookings_content_handler( $header_title ){

            $args = array(
                'post_type'      => 'wtbm_booking',
                'post_status'    => 'publish',
                'posts_per_page' => 10,
            );

            $query = new WP_Query( $args );
            $total_booking = $query->found_posts;

            $header_data = array(
              'total_booking' => $total_booking,
              'total_revenue' => 1522,
              'total_completed_order' => 10,
              'total_canceled_order' => 0,
            );

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

            wp_reset_postdata();

            $filter_data = [];

            ?>
            <div id="wtbm_bookings_content" class="tab-content">
                <div class="section">
                    <div class="section-header">
                        <div>
                            <h3 class="section-title"><?php echo esc_attr( $header_title );?></h3>
                            <p class="text-sm text-gray-500" id="bookings-count"><?php esc_attr_e( 'Total:', 'wptheaterly' ); ?> <?php echo esc_attr( $total_booking );?> <?php esc_attr_e( 'bookings', 'wptheaterly' ); ?></p>
                        </div>
                        <button class="btn btn-secondary">
                            🔍 <?php esc_attr_e( 'Filters', 'wptheaterly' ); ?>
                        </button>
                    </div>
                </div>

                <!-- Stats Grid - Moved to top -->
                <?php
                do_action('wtbm_booking_header', $header_data );
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
//                        echo self::movie_booking_data( $booking_date, $display_count );
                        ?>
                        </tbody>
                    </table>

                        <div id="load-more-section" class="text-center py-4 border-t border-gray-200">
                            <div class="mb-3" id="wtbm_showing_number_of_booking" style="display: none">
                                <span class="text-sm text-gray-600" id="showing-info" data-shoing-info =""><?php esc_attr_e( 'Showing 1', 'wptheaterly' ); ?>-<span id="wtbm_showing_count"></span> <?php esc_attr_e( 'of ', 'wptheaterly' ); ?> <?php echo esc_attr( $total_booking );?> <?php esc_attr_e( 'Bookings', 'wptheaterly' ); ?></span>
                            </div>
                            <button id="wtbm_booking_load_more_btn" class="btn btn-primary" style="display: none">
                                📄 <?php esc_attr_e( 'Load More Bookings (+10)', 'wptheaterly' ); ?>
                            </button>
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
                            <button class="btn-icon" style="color: #2563eb;" title="View Booking">👁️</button>
<!--                            <button class="btn-icon edit" title="Edit Booking">✏️</button>-->
                        </div>
                    </td>
                </tr>
                <?php
                }
            }

            return ob_get_clean();
        }
        public function wtbm_booking_filter_display( $filter_data ){ ?>
            <div id="booking-filters" class="filters-section hidden">
                <h4 class="mb-4 font-semibold"><?php esc_attr_e( 'Filters', 'wptheaterly' ); ?></h4>
                <div class="grid grid-cols-4 mb-4">
                    <div class="form-group">
                        <label class="form-label"><?php esc_attr_e( 'Search', 'wptheaterly' ); ?></label>
                        <input type="text" id="search-filter" class="form-input" placeholder="Name, Email, ID">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php esc_attr_e( 'Movie', 'wptheaterly' ); ?></label>
                        <select id="movie-filter" class="form-input"><option value=""><?php esc_attr_e( 'All Movies', 'wptheaterly' ); ?></option><option value="1">Guardians of the Galaxy Vol. 3</option><option value="2">Spider-Man: Across the Spider-Verse</option></select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php esc_attr_e( 'Theater', 'wptheaterly' ); ?></label>
                        <select id="theater-filter" class="form-input"><option value=""><?php esc_attr_e( 'All Theaters', 'wptheaterly' ); ?></option><option value="1">Screen 1</option><option value="2">Screen 2</option><option value="3">Screen 3</option></select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php esc_attr_e( 'Show Time', 'wptheaterly' ); ?></label>
                        <select id="showtime-filter" class="form-input">
                            <option value=""><?php esc_attr_e( 'All Show Times', 'wptheaterly' ); ?></option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-4 mb-4">
                    <div class="form-group">
                        <label class="form-label"><?php esc_attr_e( 'Show Date', 'wptheaterly' ); ?></label>
                        <input type="date" id="date-filter" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php esc_attr_e( 'Payment Status', 'wptheaterly' ); ?></label>
                        <select id="payment-status-filter" class="form-input">
                            <option value=""><?php esc_attr_e( 'All Payments', 'wptheaterly' ); ?></option>
                            <option value="paid">💚 <?php esc_attr_e( 'Paid', 'wptheaterly' ); ?></option>
                            <option value="pending">🟡 <?php esc_attr_e( 'Pending', 'wptheaterly' ); ?></option>
                            <option value="processing">🔄 <?php esc_attr_e( 'Processing', 'wptheaterly' ); ?></option>
                            <option value="partially_paid">🟠 <?php esc_attr_e( 'Partially Paid', 'wptheaterly' ); ?></option>
                            <option value="failed">❌ <?php esc_attr_e( 'Failed', 'wptheaterly' ); ?></option>
                            <option value="refunded">🔙 <?php esc_attr_e( 'Refunded', 'wptheaterly' ); ?></option>
                            <option value="overdue">🔴 <?php esc_attr_e( 'Overdue', 'wptheaterly' ); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php esc_attr_e( 'Booking Status', 'wptheaterly' ); ?></label>
                        <select id="status-filter" class="form-input">
                            <option value=""><?php esc_attr_e( 'All Bookings', 'wptheaterly' ); ?></option>
                            <option value="confirmed"><?php esc_attr_e( 'Confirmed', 'wptheaterly' ); ?></option>
                            <option value="pending"><?php esc_attr_e( 'Pending', 'wptheaterly' ); ?></option>
                            <option value="cancelled"><?php esc_attr_e( 'Cancelled', 'wptheaterly' ); ?></option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label"><?php esc_attr_e( 'Amount Range', 'wptheaterly' ); ?></label>
                        <div class="grid grid-cols-2 gap-1">
                            <input type="number" id="min-amount-filter" class="form-input" placeholder="Min $" step="0.01">
                            <input type="number" id="max-amount-filter" class="form-input" placeholder="Max $" step="0.01">
                        </div>
                    </div>
                </div>
                <button class="btn btn-secondary" onclick="clearFilters()"><?php esc_attr_e( 'Clear Filters', 'wptheaterly' ); ?></button>
            </div>
      <?php }
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
//            if( $loaded_booking_id ){
                $args = array(
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
                wp_reset_postdata();

                $booking_count  = count( $booking_data );

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