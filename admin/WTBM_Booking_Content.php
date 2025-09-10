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
        }

        public function my_bookings_content_handler( $header_title ){

            $args = array(
                'post_type'      => 'wtbm_booking',
                'post_status'    => 'publish',
                'posts_per_page' => 10,
            );

            $query = new WP_Query( $args );
            $total_booking = $query->found_posts;
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

            $header_data = [];
            $filter_data = [];

            ?>
            <div id="wtbm_bookings_content" class="tab-content">
                <div class="section">
                    <div class="section-header">
                        <div>
                            <h3 class="section-title"><?php echo esc_attr( $header_title );?></h3>
                            <p class="text-sm text-gray-500" id="bookings-count">Total: 0 bookings</p>
                        </div>
                        <button class="btn btn-secondary" onclick="toggleFilters()">
                            🔍 Filters
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
                <table class="table">
                    <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Customer</th>
                        <th>Movie</th>
                        <th>Theater</th>
                        <th>Show Date</th>
                        <th>Seats</th>
                        <th>Amount</th>
                        <th>Payment Status</th>
                        <!--                            <th>Status</th>-->
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody id="wtbm_bookings_table_body">
                    <?php
                    echo self::movie_booking_data( $booking_date );
                    ?>
                    </tbody>
                </table>

                <!-- Load More Section -->
                <div id="load-more-section" class="text-center py-4 border-t border-gray-200">
                    <div class="mb-3">
                        <span class="text-sm text-gray-600" id="showing-info" data-shoing-info ="<?php echo esc_attr( $total_booking );?>">Showing 1-<span id="wtbm_showing_count"><?php echo esc_attr( $display_count );?></span> of <?php echo esc_attr( $total_booking );?> bookings</span>
                    </div>
                    <button id="wtbm_booking_load_more_btn" class="btn btn-primary">
                        📄 Load More Bookings (+10)
                    </button>
                    <div id="no-more-data" class="text-sm text-gray-500 hidden">
                        ✅ All bookings loaded
                    </div>
                </div>
            </div>
        <?php }
        public static function movie_booking_data( $booking_dates ) {
            ob_start();
            foreach ( $booking_dates as $booking_id => $meta ) {
                $order = wc_get_order( $meta['wtbm_order_id'] );

                $status = 'pending';
                if ( $order ) {
                    $status = wc_get_order_status_name( $order->get_status() );
                }
                // Example meta keys, adjust to match your actual saved meta keys
                $booking_code  = 'BK' . str_pad(  $meta['wtbm_order_id'], 6, '0', STR_PAD_LEFT );
                $customer_name = $meta['wtbm_billing_name'] ?? 'N/A';
                $customer_email= $meta['wtbm_billing_email'] ?? 'N/A';
                $customer_phone= $meta['wtbm_billing_phone'] ?? 'N/A';
                $movie_name    = get_the_title( $meta['wtbm_movie_id'] ) ?? 'N/A';
                $movie_genre   = $meta['_movie_genre'] ?? '';
                $screen        = get_the_title( $meta['wtbm_theater_id'] ) ?? 'N/A';
                $date          = $meta['wtbm_order_date'] ?? 'N/A';
                $time          = $meta['wtbm_order_time'] ?? '';
                $seats         = is_array( $meta['wtbm_seats'] ?? '' ) ? implode(', ', $meta['wtbm_seats']) : ( $meta['wtbm_seats'] ?? 'N/A' );
                $price         = $meta['wtbm_tp'] ?? '0.00';


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

            return ob_get_clean();
        }
        public function wtbm_booking_filter_display( $filter_data ){ ?>
            <div id="booking-filters" class="filters-section hidden">
                <h4 class="mb-4 font-semibold">Filters</h4>
                <div class="grid grid-cols-4 mb-4">
                    <div class="form-group">
                        <label class="form-label">Search</label>
                        <input type="text" id="search-filter" class="form-input" placeholder="Name, Email, ID">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Movie</label>
                        <select id="movie-filter" class="form-input"><option value="">All Movies</option><option value="1">Guardians of the Galaxy Vol. 3</option><option value="2">Spider-Man: Across the Spider-Verse</option></select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Theater</label>
                        <select id="theater-filter" class="form-input"><option value="">All Theaters</option><option value="1">Screen 1</option><option value="2">Screen 2</option><option value="3">Screen 3</option></select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Show Time</label>
                        <select id="showtime-filter" class="form-input">
                            <option value="">All Show Times</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-4 mb-4">
                    <div class="form-group">
                        <label class="form-label">Show Date</label>
                        <input type="date" id="date-filter" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Payment Status</label>
                        <select id="payment-status-filter" class="form-input">
                            <option value="">All Payments</option>
                            <option value="paid">💚 Paid</option>
                            <option value="pending">🟡 Pending</option>
                            <option value="processing">🔄 Processing</option>
                            <option value="partially_paid">🟠 Partially Paid</option>
                            <option value="failed">❌ Failed</option>
                            <option value="refunded">🔙 Refunded</option>
                            <option value="overdue">🔴 Overdue</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Booking Status</label>
                        <select id="status-filter" class="form-input">
                            <option value="">All Bookings</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount Range</label>
                        <div class="grid grid-cols-2 gap-1">
                            <input type="number" id="min-amount-filter" class="form-input" placeholder="Min $" step="0.01">
                            <input type="number" id="max-amount-filter" class="form-input" placeholder="Max $" step="0.01">
                        </div>
                    </div>
                </div>
                <button class="btn btn-secondary" onclick="clearFilters()">Clear Filters</button>
            </div>
      <?php }
        public function wtbm_booking_header_display( $header_data ){ ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value" style="color: #2563eb;" id="stat-total-bookings">17</div>
                    <div class="stat-label">Total Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #059669;" id="stat-total-revenue">705.57</div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #d97706;" id="stat-paid-bookings">7</div>
                    <div class="stat-label">Paid Bookings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" style="color: #dc2626;" id="stat-cancelled-bookings">3</div>
                    <div class="stat-label">Cancelled</div>
                </div>
            </div>
        <?php }

        public function wtbm_get_load_more_booking_data(){
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $loaded_booking_id = isset( $_POST['already_loaded_booking_ids'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['already_loaded_booking_ids'] ) ) ) : [];
            $display_limit = isset( $_POST['display_limit'] ) ? sanitize_text_field( wp_unslash( $_POST['display_limit'] ) ) : [];
            if( $loaded_booking_id ){
                $args = array(
                    'post_type'      => 'wtbm_booking',
                    'post_status'    => 'publish',
                    'posts_per_page' => $display_limit,
                    'post__not_in'   => $loaded_booking_id, // বাদ দিবে already loaded bookings
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
                $booking_html = self::movie_booking_data( $booking_data );
                $result = array(
                        'booking_count' => $booking_count,
                        'booking_data' => $booking_html,
                );
                wp_send_json_success(  $result );
            }else{
                wp_send_json_error("Invalid ction");
            }


        }

    }

    new WTBM_Booking_Content();
}