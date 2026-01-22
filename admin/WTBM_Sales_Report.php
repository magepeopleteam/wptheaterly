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

            // Initialize booked seats and revenue
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

            foreach ( $results as $theater_id => &$showtimes ) {
                usort( $showtimes, function( $a, $b ) {
                    return $b['booked_seats'] <=> $a['booked_seats'];
                });
            }

            return $results;
        }

        function get_top_selling_times1( $sale_by_theater ) {
            $top_times = [];

            foreach ( $sale_by_theater as $theater_id => $shows ) {
                $best_show = null;

                foreach ( $shows as $show ) {
                    if ( ! $best_show || $show['booked_seats'] > $best_show['booked_seats'] ) {
                        $best_show = $show;
                    }
                }

                if ( $best_show ) {
                    $top_times[] = [
                        'theater_id'        => $theater_id,
                        'start_time'        => $best_show['start_time'],
                        'movie_id'          => $best_show['movie_id'],
                        'booked_seats'      => $best_show['booked_seats'],
                        'revenue'           => $best_show['revenue'],
                        'theater_capacity'  => $best_show['theater_seat_count'],
                    ];
                }
            }

            return $top_times;
        }


        function get_top_selling_times( $sale_by_theater ) {
            $all_shows = [];

            foreach ( $sale_by_theater as $theater_id => $shows ) {
                foreach ( $shows as $show ) {
                    $all_shows[] = $show;
                }
            }

            // Sort all shows by booked_seats descending
            usort( $all_shows, function( $a, $b ) {
                return $b['booked_seats'] <=> $a['booked_seats'];
            });

            return $all_shows;
        }
        function get_shows_grouped_by_movie( $sale_by_theater ) {
            $grouped_by_movie = [];

            // Flatten all showtimes and group by movie_id
            foreach ( $sale_by_theater as $theater_id => $shows ) {
                foreach ( $shows as $show ) {
                    $movie_id = $show['movie_id'];
                    if ( ! isset( $grouped_by_movie[ $movie_id ] ) ) {
                        $grouped_by_movie[ $movie_id ] = [];
                    }
                    $grouped_by_movie[ $movie_id ][] = $show;
                }
            }

            // Sort each movie's showtimes by booked_seats descending
            foreach ( $grouped_by_movie as $movie_id => &$shows ) {
                usort( $shows, function( $a, $b ) {
                    return $b['booked_seats'] <=> $a['booked_seats'];
                });
            }

            return $grouped_by_movie;
        }


        public function sales_report_display(){
            $today = 'today';
            $week = 'week';
            $date = date( 'Y-m-d' );
//            $date = '2025-09-23';
            $today_sales_report = self::pa_get_wtbm_booking_report( $date, $today );
            $weekly_sales_report = self::pa_get_wtbm_booking_report( $date, $week );

            $movie_reports = self::pa_get_wtbm_booking_movie_report( $date, $type = 'today' );

            $showtimes_by_theater = self::pa_get_showtimes_by_theater( $date );

            $theater_performance = self::pa_get_theater_performance( $date, $showtimes_by_theater );
            $top_selling_times = self::get_top_selling_times( $theater_performance );

            $movies_performance = self::get_shows_grouped_by_movie( $theater_performance );

            $wc_currency = get_woocommerce_currency();

            $today_order = $today_sales_report['orders'];
            if( $today_order === 0 ) {
                $today_order = 1;
            }

            ?>

            <div id="wtbm_sales_report_content"  class="tab-content">
                        <div class="mb-6">
                            <h2 class="text-2xl font-bold text-gray-900"><?php esc_attr_e( 'Sales Report', 'wptheaterly' );?></h2>
                            <p class="text-gray-600"><?php esc_attr_e( 'View sales analytics and generate detailed reports', 'wptheaterly' );?></p>
                        </div>

                        <div class="wtbm_sales_report_card">
                            <div class="flex items-center mb-4">
                                <span style="width: 24px; height: 24px; color: #9333ea; margin-right: 12px;">📊</span>
                                <h3 class="text-xl font-bold text-gray-900"><?php esc_attr_e( 'Sales Analytics', 'wptheaterly' );?></h3>
                            </div>

                            <div class="grid grid-4 gap-6 mb-8">
                                <div class="report-metric bg-primary-50 border" style="border-color: #bfdbfe;">
                                    <div class="text-3xl font-bold text-primary mb-2"><?php echo esc_attr( $today_sales_report['orders'] )?></div>
                                    <div class="text-sm font-medium" style="color: #1e40af;"><?php esc_attr_e( "Today's Sales", 'wptheaterly' );?></div>
                                    <div class="text-xs text-primary mt-1"><?php esc_attr_e( 'Tickets Sold', 'wptheaterly' );?></div>
                                </div>
                                <div class="report-metric bg-success-50 border" style="border-color: #bbf7d0;">
                                    <div class="text-3xl font-bold text-success mb-2"><?php echo esc_html( get_woocommerce_currency() );?> <?php echo esc_attr( $today_sales_report['revenue'] )?></div>
                                    <div class="text-sm font-medium" style="color: #047857;"><?php esc_attr_e( "Today's Revenue", 'wptheaterly' );?></div>
                                    <div class="text-xs text-success mt-1"><?php esc_attr_e( 'Total Earnings', 'wptheaterly' );?></div>
                                </div>
                                <div class="report-metric" style="background: #faf5ff; border-color: #e9d5ff;">
                                    <div class="text-3xl font-bold" style="color: #9333ea;"><?php echo esc_attr( $weekly_sales_report['orders'] )?></div>
                                    <div class="text-sm font-medium" style="color: #7c3aed;"><?php esc_attr_e( 'This Week', 'wptheaterly' );?></div>
                                    <div class="text-xs" style="color: #9333ea;"><?php esc_attr_e( 'Weekly Sales', 'wptheaterly' );?></div>
                                </div>
                                <div class="report-metric" style="background: #fff7ed; border-color: #fed7aa;">
                                    <div class="text-3xl font-bold" style="color: #ea580c;"><?php echo esc_html( get_woocommerce_currency() );?> <?php echo esc_attr( $weekly_sales_report['revenue'] )?></div>
                                    <div class="text-sm font-medium" style="color: #c2410c;"><?php esc_attr_e( 'This Month', 'wptheaterly' );?></div>
                                    <div class="text-xs" style="color: #ea580c;"><?php esc_attr_e( 'Monthly Total', 'wptheaterly' );?></div>
                                </div>
                            </div>

                            <div class="flex gap-4 mb-6">
                                <div class="form-group">
                                    <label class="form-label"><?php esc_attr_e( 'From Date', 'wptheaterly' );?></label>
                                    <input type="date" class="form-input">
                                </div>
                                <div class="form-group">
                                    <label class="form-label"><?php esc_attr_e( 'To Date', 'wptheaterly' );?></label>
                                    <input type="date" class="form-input">
                                </div>
                                <div style="display: flex; align-items: end;">
                                    <button class="btn" style="background: #9333ea; color: white;">
                                        <?php esc_attr_e( 'Generate Report', 'wptheaterly' );?>
                                    </button>
                                </div>
                            </div>

                            <div class="border rounded p-6" style="border-color: var(--gray-200);">

                                <div class="wtbm_sales_overlay" id="wtbm_sales_popup">
                                    <div class="wtbm_sales_popup">
                                        <button class="wtbm_sales_close-btn">×</button>

                                        <div class="wtbm_sales_report" id="wtbm_dily_sales_report" style="display: none">
                                            <div class="">
                                                <h2 class=""><?php esc_attr_e( 'DAILY SALES REPORT', 'wptheaterly' );?></h2>
                                                <h2 class=""><?php echo esc_attr( date("d M, Y") );?></h2>
                                            </div>
                                            <div class="wtbm_sales_popup_content" id="wtbm_sales_popup_content">

                                                <div class="">
                                                    <h3><?php esc_attr_e( 'REVENUE SUMMERY', 'wptheaterly' );?> </h3>
                                                    <span class=""><?php esc_attr_e( 'Today Sales', 'wptheaterly' );?> <?php echo esc_attr( $today_sales_report['orders']  )?></span>
                                                    <span class=""><?php esc_attr_e( 'today Revenue', 'wptheaterly' );?><?php echo esc_attr( $today_sales_report['revenue'] );?></span>
                                                    <span class=""><?php esc_attr_e( 'Average Ticket Price', 'wptheaterly' );?><?php echo esc_attr( $today_sales_report['revenue']/$today_order );?></span>
                                                </div>

                                                <div class="wtbm_pick_hours">
                                                    <h3><?php esc_attr_e( 'Sales Report', 'wptheaterly' );?><?php esc_attr_e( 'PICK HOURS', 'wptheaterly' );?> </h3>
                                                    <?php foreach ( $top_selling_times as $key => $top_selling_time ){
                                                        $time = $time_am_pm = date("h:i A", strtotime( $top_selling_time['start_time'] ) );
                                                        $number_ticket = $top_selling_time['booked_seats'];
                                                        ?>
                                                        <li class=""><?php echo esc_attr( $time ); ?>(<?php echo esc_attr( $number_ticket );?><?php esc_attr_e( 'Tickets', 'wptheaterly' );?>)</li>
                                                    <?php }?>
                                                </div>

                                                <div class="wtbm_movie_report_holder">
                                                    <h3><?php esc_attr_e( 'TOP MOVIES', 'wptheaterly' );?> </h3>
                                                    <?php foreach ( $movie_reports as $key => $movie_report  ){
                                                        ?>
                                                        <li class="wtbm_movie_report"><?php echo esc_attr( $movie_report ); ?></li>
                                                    <?php }?>
                                                </div>

                                                <div class="wtbm_theater_report_holder">
                                                    <h3><?php esc_attr_e( 'THEATER PERFORMANCE', 'wptheaterly' );?> </h3>
                                                    <?php foreach ( $theater_performance as $key => $theater_data  ){
                                                        $theater_name = get_the_title( $key );
                                                        $total_seat_theater = 0;
                                                        $total_booked_seat_theater = 0;
                                                        if( is_array( $theater_data ) && !empty( $theater_data ) ){
                                                            foreach ( $theater_data as $theater ){
                                                                $total_seat_theater += $theater['theater_seat_count'];
                                                                $total_booked_seat_theater += $theater['booked_seats'];
                                                                if( $total_seat_theater > 0 ) {
                                                                    $percentage = 100 * ($total_booked_seat_theater / $total_seat_theater);
                                                                    $percentage = round($percentage, 2);
                                                                }else{
                                                                    $percentage = 0;
                                                                }

                                                                ?>
                                                            <?php } ?>
                                                            <li class="wtbm_movie_report">
                                                                <?php echo esc_attr( $theater_name ) . ': ' . esc_attr( $percentage ).'% capacity'; ?>
                                                            </li>
                                                        <?php  }
                                                    }?>
                                                </div>

                                            </div>
                                        </div>
                                        <div class="wtbm_sales_report" id="wtbm_dily_movies_performance" style="display: none">
                                            <div class="">
                                                <h2 class=""><?php esc_attr_e( 'MOVIE PERFORMANCE REPORT', 'wptheaterly' );?></h2>
                                                <h2 class="">PERIOD: <?php echo esc_attr( date("d M, Y") );?></h2>
                                            </div>
                                            <div class="wtbm_movie_report_holder">
                                                <h3><?php esc_attr_e( 'TOP PERFORMING MOVIE', 'wptheaterly' );?> </h3>
                                                <?php
                                                if( is_array( $movies_performance ) && !empty( $movies_performance ) ){
                                                    foreach ( $movies_performance as $movie_id => $movies_data  ){
                                                        $movie_name = get_the_title( $movie_id );
                                                        $rating = get_post_meta( $movie_id, 'wtbp_movie_rating', true );
                                                        $total_revenue = 0;
                                                        $total_booked_seat = 0;
                                                        $best_show_time = '';
                                                        if( is_array( $movies_data ) && !empty( $movies_data ) ){
                                                            foreach ( $movies_data as $key => $movie ){
                                                                if( $key === 0 ){
                                                                    $best_show_time = date("h:i A", strtotime( $movie['start_time'] ) );
                                                                }
                                                                $total_revenue += $movie['revenue'];
                                                                $total_booked_seat += $movie['booked_seats'];
                                                                ?>
                                                            <?php } ?>
                                                            <ul class="wtbm_movies_performance">
                                                                <h3> <?php echo esc_attr( $movie_name );?></h3>
                                                                <li class="wtbm_movie_report">
                                                                    Sales: <?php echo esc_attr( $total_booked_seat );?> Tickets
                                                                </li>
                                                                <li class="wtbm_movie_report">
                                                                    Revenue: <?php echo esc_attr( $wc_currency ); echo esc_attr( $total_revenue );?>
                                                                </li>
                                                                <li class="wtbm_movie_report">
                                                                    Average Rating: <?php echo esc_attr( $rating );?>
                                                                </li>
                                                                <li class="wtbm_movie_report">
                                                                    Best Show Time: <?php echo esc_attr( $best_show_time );?>
                                                                </li>
                                                            </ul>

                                                        <?php  }
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                        <div class="wtbm_sales_report" id="wtbm_daily_theater_performance" style="display: none">
                                            <div class="">
                                                <h2 class=""><?php esc_attr_e( 'THEATER PERFORMANCE REPORT', 'wptheaterly' );?></h2>
                                                <h2 class="">PERIOD: <?php echo esc_attr( date("d M, Y") );?></h2>
                                            </div>
                                            <div class="wtbm_theater_report_holder">
                                                <h3><?php esc_attr_e( 'THEATER PERFORMING', 'wptheaterly' );?> </h3>
                                                <?php
                                                if( is_array( $theater_performance ) && !empty( $theater_performance ) ){
                                                    foreach ( $theater_performance as $key => $theater_performance_data  ){

                                                        $show_time_count = count( $theater_performance_data );
                                                        $theater_title = get_the_title( $key );
                                                        $total_seat_in_theater = 0;
                                                        $total_booked_seat_in_theater = 0;
                                                        $total_revenue_in_theater = 0;
                                                        if( is_array( $theater_performance_data ) && !empty( $theater_performance_data ) ){
                                                            foreach ( $theater_performance_data as $theater ){
                                                                $total_seat_in_theater += $theater['theater_seat_count'];
                                                                $total_booked_seat_in_theater += $theater['booked_seats'];
                                                                $total_revenue_in_theater += $theater['revenue'];
                                                                if( $total_seat_in_theater > 0 ){
                                                                    $percentage = 100 * ( $total_booked_seat_in_theater / $total_seat_in_theater );
                                                                    $percentage = round( $percentage, 2 );
                                                                }else{
                                                                    $percentage = 0;
                                                                }


                                                                ?>
                                                            <?php } ?>
                                                            <ul class="wtbm_movies_performance">
                                                                <h3> <?php echo esc_attr( $theater_title );?>( <?php echo esc_attr( $total_seat_in_theater );?>  Seats )</h3>
                                                                <li class="wtbm_movie_report">
                                                                    Occupancy: <?php echo esc_attr( $percentage );?> (<?php echo esc_attr( $total_booked_seat_in_theater )?>/ <?php echo esc_attr( $total_seat_in_theater );?>) Seats
                                                                </li>
                                                                <li class="wtbm_movie_report">
                                                                    Revenue: <?php echo esc_attr( $wc_currency ); echo esc_attr( $total_revenue_in_theater );?>
                                                                </li>
                                                                <li class="wtbm_movie_report">
                                                                    Shows: <?php echo esc_attr( $show_time_count );?> Daily
                                                                </li>
                                                            </ul>
                                                        <?php  }
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>

                                        <button class="wtbm_sales_open-btn" ><?php esc_attr_e( 'Okay', 'wptheaterly' );?></button>
                                    </div>
                                </div>

                                <h4 class="font-semibold text-gray-900 mb-4"><?php esc_attr_e( 'Quick Reports', 'wptheaterly' );?></h4>
                                <div class="grid grid-4 gap-4">
                                    <button class="report-btn wtbm_get_daily_report" id="wtbm_get_daily_report" data-wtbm-report="daily">
                                        <div class="font-medium text-gray-900"><?php esc_attr_e( 'Daily Sales', 'wptheaterly' );?></div>
                                        <div class="text-sm text-gray-600 mt-1"><?php esc_attr_e( "Today's summary", 'wptheaterly' );?></div>
                                        <div class="text-xs text-primary mt-2 font-medium">▶ <?php esc_attr_e( 'Click to view', 'wptheaterly' );?></div>
                                    </button>

                                    <button class="report-btn wtbm_get_daily_report" data-wtbm-report="movie">
                                        <div class="font-medium text-gray-900"><?php esc_attr_e( 'Movie Performance', 'wptheaterly' );?></div>
                                        <div class="text-sm text-gray-600 mt-1"><?php esc_attr_e( 'By movie analysis', 'wptheaterly' );?></div>
                                        <div class="text-xs text-success mt-2 font-medium">▶ <?php esc_attr_e( 'Click to view', 'wptheaterly' );?></div>
                                    </button>

                                    <button class="report-btn wtbm_get_daily_report" data-wtbm-report="theater">
                                        <div class="font-medium text-gray-900"><?php esc_attr_e( 'Theater Utilization', 'wptheaterly' );?></div>
                                        <div class="text-sm text-gray-600 mt-1"><?php esc_attr_e( 'Occupancy rates', 'wptheaterly' );?></div>
                                        <div class="text-xs mt-2 font-medium" style="color: #9333ea;">▶ <?php esc_attr_e( 'Click to view', 'wptheaterly' );?></div>
                                    </button>

                                    <button class="report-btn" data-wtbm-report="payment">
                                        <div class="font-medium text-gray-900"><?php esc_attr_e( 'Payment Methods', 'wptheaterly' );?></div>
                                        <div class="text-sm text-gray-600 mt-1"><?php esc_attr_e( 'Transaction breakdown', 'wptheaterly' );?></div>
                                        <div class="text-xs mt-2 font-medium" style="color: #ea580c;">▶ <?php esc_attr_e( 'Click to view', 'wptheaterly' );?></div>
                                    </button>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-between items-center">
                                <div class="text-sm text-gray-600">
                                    <?php
                                    $datetime_now = date("Y-m-d H:i:s");
                                    ?>
                                    <?php esc_attr_e( 'Last updated', 'wptheaterly' );?>: <span id="last-updated"><?php echo esc_attr( $datetime_now )?></span>
                                </div>
                                <div class="flex gap-3">
                                    <button class="btn btn-secondary text-sm"><?php esc_attr_e( 'Export PDF', 'wptheaterly' );?></button>
                                    <button class="btn btn-success text-sm"><?php esc_attr_e( 'Export Excel', 'wptheaterly' );?></button>
                                    <button class="btn btn-primary text-sm" ><?php esc_attr_e( 'Print Report', 'wptheaterly' );?></button>
                                </div>
                            </div>
                        </div>
                    </div>

        <?php }

    }

    new WTBM_Sales_Report();
}