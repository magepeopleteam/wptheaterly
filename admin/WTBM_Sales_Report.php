<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WTBM_Sales_Report' ) ) {
    class WTBM_Sales_Report{
        public function __construct(){
            add_action('wtbm_sales_report', [ $this, 'sales_report_display' ]);

        }


        /**
         * Get sales report for custom post type wtbm_booking
         *
         * @param string $date (format Y-m-d)
         * @param string $type (today|week)
         * @return array
         */
        public static function pa_get_wtbm_booking_report( $date, $type = 'today' ) {
            $results = array(
                'orders'  => 0,
                'revenue' => 0,
            );

            // Convert date string to DateTime
            try {
                $dt = new DateTime( $date );
            } catch ( Exception $e ) {
                return $results;
            }

            // Prepare date range
            if ( $type === 'today' ) {
                $start_date = $dt->format( 'Y-m-d' );
                $end_date   = $dt->format( 'Y-m-d' );
            } elseif ( $type === 'week' ) {
                // Monday of current week
                $week_start = clone $dt;
                $week_start->modify( 'monday this week' );

                // Sunday of current week
                $week_end = clone $dt;
                $week_end->modify( 'sunday this week' );

                $start_date = $week_start->format( 'Y-m-d' );
                $end_date   = $week_end->format( 'Y-m-d' );
            } else {
                return $results;
            }

            // Build query
            $args = array(
                'post_type'      => 'wtbm_booking',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'meta_query'     => array(
                    array(
                        'key'     => 'wtbm_order_date',
                        'value'   => array( $start_date, $end_date ),
                        'compare' => 'BETWEEN',
                        'type'    => 'DATE',
                    ),
                ),
            );

            $query = new WP_Query( $args );

            $total_price = 0;

            if ( $query->have_posts() ) {
                foreach ( $query->posts as $post_id ) {
                    $price = get_post_meta( $post_id, 'wtbm_tp', true );
                    $total_price += floatval( $price );
                }
            }

            wp_reset_postdata();

            return array(
                'orders'  => count( $query->posts ),
                'revenue' => $total_price,
            );
        }

        public static function pa_get_wtbm_booking_movie_report( $date, $type = 'today' ) {
            $results = array();

            // Convert date string to DateTime
            try {
                $dt = new DateTime( $date );
            } catch ( Exception $e ) {
                return $results;
            }

            // Prepare date range
            if ( $type === 'today' ) {
                $start_date = $dt->format( 'Y-m-d' );
                $end_date   = $dt->format( 'Y-m-d' );
            } elseif ( $type === 'week' ) {
                // Monday of current week
                $week_start = clone $dt;
                $week_start->modify( 'monday this week' );

                // Sunday of current week
                $week_end = clone $dt;
                $week_end->modify( 'sunday this week' );

                $start_date = $week_start->format( 'Y-m-d' );
                $end_date   = $week_end->format( 'Y-m-d' );
            } else {
                return $results;
            }

            // Query bookings
            $args = array(
                'post_type'      => 'wtbm_booking',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'meta_query'     => array(
                    array(
                        'key'     => 'wtbm_order_date',
                        'value'   => array( $start_date, $end_date ),
                        'compare' => 'BETWEEN',
                        'type'    => 'DATE',
                    ),
                ),
            );

            $query = new WP_Query( $args );

            $report_data = array();

            if ( $query->have_posts() ) {
                foreach ( $query->posts as $post_id ) {
                    $movie_id = get_post_meta( $post_id, 'wtbm_movie_id', true );
                    $movie_name  = get_the_title( $movie_id );
                    $ticket_qty  = (int) get_post_meta( $post_id, 'wtbm_number_of_seats', true );
                    $total_price = (float) get_post_meta( $post_id, 'wtbm_tp', true );

                    if ( ! $movie_name ) {
                        $movie_name = __( 'Unknown Movie', 'wptheaterly' );
                    }

                    if ( ! isset( $report_data[ $movie_name ] ) ) {
                        $report_data[ $movie_name ] = array(
                            'tickets' => 0,
                            'revenue' => 0,
                        );
                    }

                    $report_data[ $movie_name ]['tickets'] += $ticket_qty;
                    $report_data[ $movie_name ]['revenue'] += $total_price;
                }
            }

            wp_reset_postdata();

            // Format output like: "Movie Name (10 tickets - $1000)"
            $output = array();
            foreach ( $report_data as $movie => $data ) {
                $output[] = sprintf(
                    '%s (%d tickets - $%0.2f)',
                    esc_html( $movie ),
                    $data['tickets'],
                    $data['revenue']
                );
            }

            return $output;
        }

        public static function pa_get_showtimes_by_theater( $date ) {
            $results = array();
            error_log( print_r( [ '$date' => $date ], true ) );
            $args = array(
                'post_type'      => 'wtbm_show_time',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'meta_query'     => array(
                    'relation' => 'AND',
                    array(
                        'key'     => 'wtbp_showtime_start_date',
                        'value'   => $date,
                        'compare' => '<=',
                        'type'    => 'DATE',
                    ),
                    array(
                        'key'     => 'wtbp_showtime_end_date',
                        'value'   => $date,
                        'compare' => '>=',
                        'type'    => 'DATE',
                    ),
                ),
            );

            $query = new WP_Query( $args );

            if ( $query->have_posts() ) {
                foreach ( $query->posts as $post_id ) {

                    $theater_id = get_post_meta( $post_id, 'wtbp_show_time_theaterId', true );
                    $movie_id   = get_post_meta( $post_id, 'wtbp_show_time_movieId', true );
                    $start_time = get_post_meta( $post_id, 'wtbp_show_time_start_date', true );

                    $theater_seats = get_post_meta( $theater_id, 'wtbp_theater_seat_map', array() );

                    $theater_seats = isset( $theater_seats[0]['seat_data'] )
                        ? count( $theater_seats[0]['seat_data'] )
                        : 0;

                    if ( ! $theater_id ) {
                        $theater_id = 'unknown';
                    }

                    if ( ! isset( $results[ $theater_id ] ) ) {
                        $results[ $theater_id ] = array();
                    }

                    $results[ $theater_id ][] = array(
                        'theater_id'            => $theater_id,
                        'theater_seat_count'    => $theater_seats,
                        'movie_id'              => $movie_id,
                        'start_time'            => $start_time,
                    );
                }
            }

            wp_reset_postdata();

            return $results;
        }

        public static function pa_get_theater_performance( $date, $showtimes_by_theater ) {
            $results = $showtimes_by_theater;
            foreach ( $results as $theater_id => &$showtimes ) {
                foreach ( $showtimes as &$showtime ) {
                    $showtime['booked_seats'] = 0;
                    $showtime['revenue']      = 0;
                }
            }


            // Query all bookings for this date
            $args = array(
                'post_type'      => 'wtbm_booking',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'meta_query'     => array(
                    array(
                        'key'     => 'wtbm_order_date',
                        'value'   => $date,
                        'compare' => '=',
                        'type'    => 'DATE',
                    ),
                ),
            );

            $bookings = new WP_Query( $args );

            if ( $bookings->have_posts() ) {
                foreach ( $bookings->posts as $booking_id ) {
                    $theater_id  = get_post_meta( $booking_id, 'wtbm_theater_id', true );
                    $movie_id    = get_post_meta( $booking_id, 'wtbm_movie_id', true );
                    $order_time  = get_post_meta( $booking_id, 'wtbm_order_time', true ); // ex: 15:36
                    $seat_count  = intval( get_post_meta( $booking_id, 'wtbm_number_of_seats', true ) );
                    $total_price = floatval( get_post_meta( $booking_id, 'wtbm_tp', true ) );

                    if ( isset( $results[ $theater_id ] ) ) {
                        foreach ( $results[ $theater_id ] as &$showtime ) {
                            if (
                                $showtime['movie_id'] == $movie_id &&
                                $showtime['start_time'] == $order_time
                            ) {
                                if ( ! isset( $showtime['booked_seats'] ) ) {
                                    $showtime['booked_seats'] = 0;
                                }
                                if ( ! isset( $showtime['revenue'] ) ) {
                                    $showtime['revenue'] = 0;
                                }

                                $showtime['booked_seats'] += $seat_count;
                                $showtime['revenue']      += $total_price;
                            }
                        }
                    }
                }
            }

            wp_reset_postdata();

            return $results;
        }




        public function sales_report_display(){
            $today = 'today';
            $week = 'week';
            $date = date( 'Y-m-d' );
            $today_sales_report = self::pa_get_wtbm_booking_report( $date, $today );
            $weekly_sales_report = self::pa_get_wtbm_booking_report( $date, $week );


            $movie_report = self::pa_get_wtbm_booking_movie_report( $date, $type = 'today' );

            $showtimes_by_theater = self::pa_get_showtimes_by_theater( $date );

            $sale_by_theater = self::pa_get_theater_performance( $date, $showtimes_by_theater );
            error_log( print_r( [ '$sale_by_theater' => $sale_by_theater ], true ) );



//            error_log( print_r( [ '$showtimes_by_theater' => $showtimes_by_theater ], true ) );
            ?>

            <div id="wtbm_sales_report_content" class="tab-content">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">Sales Report</h2>
                            <p class="text-gray-600">View sales analytics and generate detailed reports</p>
                        </div>

                        <div class="wtbm_sales_report_card">
                            <div class="flex items-center mb-4">
                                <span style="width: 24px; height: 24px; color: #9333ea; margin-right: 12px;">📊</span>
                                <h3 class="text-xl font-bold text-gray-900">Sales Analytics</h3>
                            </div>

                            <div class="grid grid-4 gap-6 mb-8">
                                <div class="report-metric bg-primary-50 border" style="border-color: #bfdbfe;">
                                    <div class="text-3xl font-bold text-primary mb-2"><?php echo esc_attr( $today_sales_report['orders'] )?></div>
                                    <div class="text-sm font-medium" style="color: #1e40af;">Today's Sales</div>
                                    <div class="text-xs text-primary mt-1">Tickets Sold</div>
                                </div>
                                <div class="report-metric bg-success-50 border" style="border-color: #bbf7d0;">
                                    <div class="text-3xl font-bold text-success mb-2"><?php echo esc_html( get_woocommerce_currency() );?> <?php echo esc_attr( $today_sales_report['revenue'] )?></div>
                                    <div class="text-sm font-medium" style="color: #047857;">Today's Revenue</div>
                                    <div class="text-xs text-success mt-1">Total Earnings</div>
                                </div>
                                <div class="report-metric" style="background: #faf5ff; border-color: #e9d5ff;">
                                    <div class="text-3xl font-bold" style="color: #9333ea;"><?php echo esc_attr( $weekly_sales_report['orders'] )?></div>
                                    <div class="text-sm font-medium" style="color: #7c3aed;">This Week</div>
                                    <div class="text-xs" style="color: #9333ea;">Weekly Sales</div>
                                </div>
                                <div class="report-metric" style="background: #fff7ed; border-color: #fed7aa;">
                                    <div class="text-3xl font-bold" style="color: #ea580c;"><?php echo esc_html( get_woocommerce_currency() );?> <?php echo esc_attr( $weekly_sales_report['revenue'] )?></div>
                                    <div class="text-sm font-medium" style="color: #c2410c;">This Month</div>
                                    <div class="text-xs" style="color: #ea580c;">Monthly Total</div>
                                </div>
                            </div>

                            <div class="flex gap-4 mb-6">
                                <div class="form-group">
                                    <label class="form-label">From Date</label>
                                    <input type="date" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">To Date</label>
                                    <input type="date" class="form-input">
                                </div>
                                <div style="display: flex; align-items: end;">
                                    <button class="btn" style="background: #9333ea; color: white;">
                                        Generate Report
                                    </button>
                                </div>
                            </div>

                            <div class="border rounded p-6" style="border-color: var(--gray-200);">
                                <h4 class="font-semibold text-gray-900 mb-4">Quick Reports</h4>
                                <div class="grid grid-4 gap-4">
                                    <button class="report-btn" data-report="daily">
                                        <div class="font-medium text-gray-900">Daily Sales</div>
                                        <div class="text-sm text-gray-600 mt-1">Today's summary</div>
                                        <div class="text-xs text-primary mt-2 font-medium">▶ Click to view</div>
                                    </button>

                                    <button class="report-btn" data-report="movie">
                                        <div class="font-medium text-gray-900">Movie Performance</div>
                                        <div class="text-sm text-gray-600 mt-1">By movie analysis</div>
                                        <div class="text-xs text-success mt-2 font-medium">▶ Click to view</div>
                                    </button>

                                    <button class="report-btn" data-report="theater">
                                        <div class="font-medium text-gray-900">Theater Utilization</div>
                                        <div class="text-sm text-gray-600 mt-1">Occupancy rates</div>
                                        <div class="text-xs mt-2 font-medium" style="color: #9333ea;">▶ Click to view</div>
                                    </button>

                                    <button class="report-btn" data-report="payment">
                                        <div class="font-medium text-gray-900">Payment Methods</div>
                                        <div class="text-sm text-gray-600 mt-1">Transaction breakdown</div>
                                        <div class="text-xs mt-2 font-medium" style="color: #ea580c;">▶ Click to view</div>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-between items-center">
                                <div class="text-sm text-gray-600">
                                    Last updated: <span id="last-updated">9/18/2025, 3:33:34 PM</span>
                                </div>
                                <div class="flex gap-3">
                                    <button class="btn btn-secondary text-sm">Export PDF</button>
                                    <button class="btn btn-success text-sm">Export Excel</button>
                                    <button class="btn btn-primary text-sm" onclick="window.print()">Print Report</button>
                                </div>
                            </div>
                        </div>
                    </div>

        <?php }

    }

    new WTBM_Sales_Report();
}