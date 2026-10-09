<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WTBM_Sales_Report' ) ) {
    class WTBM_Sales_Report{
        public function __construct(){
            add_action('wtbm_sales_report', [ $this, 'sales_report_display' ], 10, 2);

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
                'tickets' => 0,
                'revenue' => 0,
            );

            try {
                $dt = new DateTime( $date );
            } catch ( Exception $e ) {
                return $results;
            }

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

            $data = WTBM_Operations_Report::data( array( 'from' => $start_date, 'to' => $end_date, 'basis' => 'sale' ) );
            return is_wp_error( $data ) ? $results : array( 'orders' => $data['orders'], 'tickets' => $data['tickets'], 'revenue' => $data['net'] );
        }

        public static function pa_get_wtbm_booking_movie_report( $date, $type = 'today', $structured = false ) {
            $results = array();

            try {
                $dt = new DateTime( $date );
            } catch ( Exception $e ) {
                return $results;
            }

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
                    $order = wc_get_order( get_post_meta( $post_id, 'wtbm_order_id', true ) );
                    if ( ! $order || ! $order->is_paid() ) { continue; }
                    $movie_id = get_post_meta( $post_id, 'wtbm_movie_id', true );
                    $movie_name  = get_the_title( $movie_id );
                    $ticket_qty  = (int) get_post_meta( $post_id, 'wtbm_number_of_seats', true );
                    $item = $order->get_item( absint( get_post_meta( $post_id, 'wtbm_item_id', true ) ) );
                    $total_price = $item ? max( 0, (float) $item->get_total() - (float) $order->get_total_refunded_for_item( $item->get_id() ) ) : (float) get_post_meta( $post_id, 'wtbm_tp', true );

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

            if ( $structured ) {
                return $report_data;
            }

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
                    $start_time = get_post_meta( $post_id, 'wtbp_show_starting_time', true );

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
                    $order = wc_get_order( get_post_meta( $booking_id, 'wtbm_order_id', true ) );
                    if ( ! $order || ! $order->is_paid() ) { continue; }
                    $theater_id  = get_post_meta( $booking_id, 'wtbm_theater_id', true );
                    $movie_id    = get_post_meta( $booking_id, 'wtbm_movie_id', true );
                    $order_time  = get_post_meta( $booking_id, 'wtbm_order_time', true ); // ex: 15:36
                    $seat_count  = intval( get_post_meta( $booking_id, 'wtbm_number_of_seats', true ) );
                    $item = $order->get_item( absint( get_post_meta( $booking_id, 'wtbm_item_id', true ) ) );
                    $total_price = $item ? max( 0, (float) $item->get_total() - (float) $order->get_total_refunded_for_item( $item->get_id() ) ) : floatval( get_post_meta( $booking_id, 'wtbm_tp', true ) );

                    if ( isset( $results[ $theater_id ] ) ) {
                        foreach ( $results[ $theater_id ] as &$showtime ) {
                            if (
                                $showtime['movie_id'] == $movie_id &&
                                WTBM_Booking_Service::show_time( $showtime['start_time'] ) === WTBM_Booking_Service::show_time( $order_time )
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


        private static function render_performance_report( $date, $kind, $theaters ) {
            $data = WTBM_Operations_Report::data( array( 'from' => $date, 'to' => $date, 'basis' => 'show' ) );
            $groups = array();
            foreach ( $theaters as $theater_id => $shows ) {
                foreach ( $shows as $show ) {
                    $id = 'movie' === $kind ? $show['movie_id'] : $theater_id;
                    if ( ! isset( $groups[$id] ) ) { $groups[$id] = array( 'name' => get_the_title( $id ), 'shows' => 0, 'capacity' => 0, 'tickets' => 0, 'net' => 0, 'best' => '', 'best_count' => 0 ); }
                    $groups[$id]['shows']++;
                    $groups[$id]['capacity'] += (int) $show['theater_seat_count'];
                    if ( (int) $show['booked_seats'] > $groups[$id]['best_count'] ) { $groups[$id]['best'] = $show['start_time']; $groups[$id]['best_count'] = (int) $show['booked_seats']; }
                }
            }
            foreach ( $data['rows'] as $row ) {
                $bid = $row['booking']; $id = (int) get_post_meta( $bid, 'movie' === $kind ? 'wtbm_movie_id' : 'wtbm_theater_id', true );
                if ( ! isset( $groups[$id] ) ) { $groups[$id] = array( 'name' => get_the_title( $id ), 'shows' => 0, 'capacity' => 0, 'tickets' => 0, 'net' => 0, 'best' => '', 'best_count' => 0 ); }
                $groups[$id]['tickets'] += $row['tickets']; $groups[$id]['net'] += $row['net'];
            }
            uasort( $groups, function( $a, $b ) { return $b['tickets'] <=> $a['tickets'] ?: $b['net'] <=> $a['net']; } );
            $title = 'movie' === $kind ? __( 'Movie Performance Report', 'wptheaterly' ) : __( 'Theater Performance Report', 'wptheaterly' );
            ?>
            <header class="wtbm-daily-header"><span class="wtbm-report-symbol" aria-hidden="true"><span class="dashicons <?php echo esc_attr( 'movie' === $kind ? 'dashicons-video-alt2' : 'dashicons-building' ); ?>"></span></span><div><span class="wtbm-report-eyebrow"><?php esc_html_e( 'Performance overview', 'wptheaterly' ); ?></span><h2><?php echo esc_html( $title ); ?></h2><p><?php esc_html_e( 'Paid tickets, net revenue and scheduled capacity by show date.', 'wptheaterly' ); ?></p></div><time datetime="<?php echo esc_attr( $date ); ?>"><?php echo esc_html( wp_date( 'd M Y', strtotime( $date . ' 12:00:00' ) ) ); ?></time></header>
            <div class="wtbm-daily-body"><div class="wtbm-daily-metrics">
                <article class="wtbm-daily-metric"><span class="wtbm-metric-label"><?php esc_html_e( 'Tickets sold', 'wptheaterly' ); ?></span><strong><?php echo esc_html( number_format_i18n( $data['tickets'] ) ); ?></strong><span class="wtbm-metric-note"><?php esc_html_e( 'Active paid seats', 'wptheaterly' ); ?></span></article>
                <article class="wtbm-daily-metric wtbm-metric-revenue"><span class="wtbm-metric-label"><?php esc_html_e( 'Net revenue', 'wptheaterly' ); ?></span><strong><?php echo wp_kses_post( wc_price( $data['net'] ) ); ?></strong><span class="wtbm-metric-note"><?php esc_html_e( 'After refunds, excluding tax', 'wptheaterly' ); ?></span></article>
                <article class="wtbm-daily-metric"><span class="wtbm-metric-label"><?php echo esc_html( 'movie' === $kind ? __( 'Movies scheduled', 'wptheaterly' ) : __( 'Screens scheduled', 'wptheaterly' ) ); ?></span><strong><?php echo esc_html( number_format_i18n( count( $groups ) ) ); ?></strong><span class="wtbm-metric-note"><?php printf( esc_html__( '%s shows in this period', 'wptheaterly' ), esc_html( number_format_i18n( array_sum( array_column( $groups, 'shows' ) ) ) ) ); ?></span></article>
            </div>
            <?php if ( ! $data['tickets'] ) { ?><div class="wtbm-daily-empty-notice"><strong><?php esc_html_e( 'No paid ticket sales for this show date', 'wptheaterly' ); ?></strong><span><?php esc_html_e( 'Scheduled shows remain visible below so you can compare capacity.', 'wptheaterly' ); ?></span></div><?php } ?>
            <section class="wtbm-report-panel"><div class="wtbm-panel-heading"><h3><?php esc_html_e( 'Performance ranking', 'wptheaterly' ); ?></h3><span><?php esc_html_e( 'Sorted by tickets sold', 'wptheaterly' ); ?></span></div><div class="wtbm-report-table-wrap"><table class="wtbm-report-table wtbm-performance-table"><thead><tr><th scope="col"><?php echo esc_html( 'movie' === $kind ? __( 'Movie', 'wptheaterly' ) : __( 'Theater', 'wptheaterly' ) ); ?></th><th scope="col"><?php esc_html_e( 'Shows', 'wptheaterly' ); ?></th><th scope="col"><?php esc_html_e( 'Tickets', 'wptheaterly' ); ?></th><th scope="col"><?php esc_html_e( 'Occupancy', 'wptheaterly' ); ?></th><th scope="col"><?php esc_html_e( 'Net revenue', 'wptheaterly' ); ?></th><th scope="col"><?php esc_html_e( 'Best showtime', 'wptheaterly' ); ?></th></tr></thead><tbody>
            <?php $rank = 0; foreach ( $groups as $group ) { $rank++; $occupancy = $group['capacity'] ? min( 100, 100 * $group['tickets'] / $group['capacity'] ) : 0; ?>
                <tr><th scope="row"><span class="wtbm-performance-rank"><?php echo esc_html( $rank ); ?></span><?php echo esc_html( $group['name'] ); ?></th><td><?php echo esc_html( $group['shows'] ); ?></td><td><?php echo esc_html( $group['tickets'] ); ?></td><td><div class="wtbm-occupancy"><span><?php echo esc_html( number_format_i18n( $occupancy, 1 ) ); ?>%</span><span class="wtbm-report-progress"><span style="width:<?php echo esc_attr( $occupancy ); ?>%"></span></span><small><?php echo esc_html( $group['tickets'] . ' / ' . $group['capacity'] ); ?></small></div></td><td><?php echo wp_kses_post( wc_price( $group['net'] ) ); ?></td><td><?php echo esc_html( $group['best'] ?: '—' ); ?></td></tr>
            <?php } if ( ! $groups ) { ?><tr><td colspan="6" class="wtbm-report-empty"><?php esc_html_e( 'No shows scheduled for this date.', 'wptheaterly' ); ?></td></tr><?php } ?>
            </tbody></table></div></section></div><footer class="wtbm-daily-footer"><span><?php esc_html_e( 'Occupancy uses total seat capacity across scheduled shows.', 'wptheaterly' ); ?></span><button type="button" class="button wtbm_sales_close-btn-inline"><?php esc_html_e( 'Close report', 'wptheaterly' ); ?></button></footer>
            <?php
        }

        private static function render_daily_report( $date, $summary, $movies, $theaters, $times ) {
            $tickets = (int) ( $summary['tickets'] ?? 0 );
            $average = $tickets > 0 ? $summary['revenue'] / $tickets : 0;
            $hours = array();
            foreach ( $times as $show ) {
                if ( (int) $show['booked_seats'] > 0 ) {
                    $time = $show['start_time'];
                    $hours[ $time ] = ( $hours[ $time ] ?? 0 ) + (int) $show['booked_seats'];
                }
            }
            arsort( $hours );
            $hours = array_slice( $hours, 0, 5, true );
            uasort( $movies, function ( $a, $b ) { return $b['tickets'] <=> $a['tickets']; } );
            ?>
            <header class="wtbm-daily-header">
                <span class="wtbm-report-symbol" aria-hidden="true"><i class="mi mi-stats"></i></span>
                <div>
                    <span class="wtbm-report-eyebrow"><?php esc_html_e( 'Sales overview', 'wptheaterly' ); ?></span>
                    <h2 id="wtbm-daily-title"><?php esc_html_e( 'Daily Sales Report', 'wptheaterly' ); ?></h2>
                    <p><?php esc_html_e( 'Paid tickets and net sales by show date. Use the date filters for sales made in a period.', 'wptheaterly' ); ?></p>
                </div>
                <time datetime="<?php echo esc_attr( $date ); ?>"><?php echo esc_html( wp_date( 'd M Y', strtotime( $date . ' 12:00:00' ) ) ); ?></time>
            </header>
            <div class="wtbm-daily-body">
                <div class="wtbm-daily-metrics">
                    <article class="wtbm-daily-metric">
                        <span class="wtbm-metric-label"><?php esc_html_e( 'Tickets sold', 'wptheaterly' ); ?></span>
                        <strong><?php echo esc_html( number_format_i18n( $tickets ) ); ?></strong>
                        <span class="wtbm-metric-note"><?php printf( esc_html__( '%s bookings today', 'wptheaterly' ), esc_html( number_format_i18n( $summary['orders'] ) ) ); ?></span>
                    </article>
                    <article class="wtbm-daily-metric wtbm-metric-revenue">
                        <span class="wtbm-metric-label"><?php esc_html_e( 'Total revenue', 'wptheaterly' ); ?></span>
                        <strong><?php echo wp_kses_post( wc_price( $summary['revenue'] ) ); ?></strong>
                        <span class="wtbm-metric-note"><?php esc_html_e( 'Revenue from today’s bookings', 'wptheaterly' ); ?></span>
                    </article>
                    <article class="wtbm-daily-metric">
                        <span class="wtbm-metric-label"><?php esc_html_e( 'Average ticket price', 'wptheaterly' ); ?></span>
                        <strong><?php echo wp_kses_post( wc_price( $average ) ); ?></strong>
                        <span class="wtbm-metric-note"><?php esc_html_e( 'Revenue per ticket sold', 'wptheaterly' ); ?></span>
                    </article>
                </div>
                <?php if ( 0 === $tickets && 0 === (int) $summary['orders'] ) { ?>
                    <div class="wtbm-daily-empty-notice">
                        <strong><?php esc_html_e( 'No sales recorded today', 'wptheaterly' ); ?></strong>
                        <span><?php esc_html_e( 'Sales figures will appear here as bookings are recorded.', 'wptheaterly' ); ?></span>
                    </div>
                <?php } ?>
                <div class="wtbm-daily-columns">
                    <section class="wtbm-report-panel">
                        <div class="wtbm-panel-heading"><h3><?php esc_html_e( 'Movie performance', 'wptheaterly' ); ?></h3><span><?php esc_html_e( 'By tickets sold', 'wptheaterly' ); ?></span></div>
                        <div class="wtbm-report-table-wrap">
                            <table class="wtbm-report-table">
                                <thead><tr><th scope="col"><?php esc_html_e( 'Movie', 'wptheaterly' ); ?></th><th scope="col"><?php esc_html_e( 'Tickets', 'wptheaterly' ); ?></th><th scope="col"><?php esc_html_e( 'Revenue', 'wptheaterly' ); ?></th></tr></thead>
                                <tbody>
                                <?php foreach ( $movies as $movie => $values ) { ?>
                                    <tr><th scope="row"><?php echo esc_html( $movie ); ?></th><td><?php echo esc_html( number_format_i18n( $values['tickets'] ) ); ?></td><td><?php echo wp_kses_post( wc_price( $values['revenue'] ) ); ?></td></tr>
                                <?php } ?>
                                <?php if ( empty( $movies ) ) { ?>
                                    <tr><td colspan="3" class="wtbm-report-empty"><?php esc_html_e( 'No movie sales to display yet.', 'wptheaterly' ); ?></td></tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <section class="wtbm-report-panel">
                        <div class="wtbm-panel-heading"><h3><?php esc_html_e( 'Peak sales hours', 'wptheaterly' ); ?></h3><span><?php esc_html_e( 'Top 5 showtimes', 'wptheaterly' ); ?></span></div>
                        <div class="wtbm-peak-hours">
                            <?php foreach ( $hours as $hour => $sold ) {
                                $max_hour = max( $hours );
                                $timestamp = strtotime( $hour );
                                $hour_label = false !== $timestamp ? gmdate( 'h:i A', $timestamp ) : $hour;
                                ?>
                                <div class="wtbm-hour-row"><span><?php echo esc_html( $hour_label ); ?></span><div class="wtbm-report-progress" aria-hidden="true"><span style="width: <?php echo esc_attr( round( $sold / $max_hour * 100 ) ); ?>%"></span></div><strong><?php printf( esc_html__( '%s tickets', 'wptheaterly' ), esc_html( number_format_i18n( $sold ) ) ); ?></strong></div>
                            <?php } ?>
                            <?php if ( empty( $hours ) ) { ?><p class="wtbm-report-empty"><?php esc_html_e( 'Peak hours will appear after tickets are sold.', 'wptheaterly' ); ?></p><?php } ?>
                        </div>
                    </section>
                </div>
                <section class="wtbm-report-panel wtbm-theater-panel">
                    <div class="wtbm-panel-heading"><h3><?php esc_html_e( 'Theater performance', 'wptheaterly' ); ?></h3><span><?php esc_html_e( 'Across today’s scheduled shows', 'wptheaterly' ); ?></span></div>
                    <div class="wtbm-report-table-wrap">
                        <table class="wtbm-report-table">
                            <thead><tr><th scope="col"><?php esc_html_e( 'Theater', 'wptheaterly' ); ?></th><th scope="col"><?php esc_html_e( 'Shows', 'wptheaterly' ); ?></th><th scope="col"><?php esc_html_e( 'Seats sold', 'wptheaterly' ); ?></th><th scope="col"><?php esc_html_e( 'Occupancy', 'wptheaterly' ); ?></th><th scope="col"><?php esc_html_e( 'Revenue', 'wptheaterly' ); ?></th></tr></thead>
                            <tbody>
                            <?php foreach ( $theaters as $theater_id => $shows ) {
                                $capacity = array_sum( array_column( $shows, 'theater_seat_count' ) );
                                $sold = array_sum( array_column( $shows, 'booked_seats' ) );
                                $revenue = array_sum( array_column( $shows, 'revenue' ) );
                                $occupancy = $capacity > 0 ? round( $sold / $capacity * 100, 1 ) : 0;
                                ?>
                                <tr>
                                    <th scope="row"><?php echo esc_html( get_the_title( $theater_id ) ?: __( 'Unknown theater', 'wptheaterly' ) ); ?></th>
                                    <td><?php echo esc_html( number_format_i18n( count( $shows ) ) ); ?></td>
                                    <td><?php echo esc_html( number_format_i18n( $sold ) ); ?><span class="wtbm-table-muted"> / <?php echo esc_html( number_format_i18n( $capacity ) ); ?></span></td>
                                    <td><div class="wtbm-occupancy"><span><?php echo esc_html( number_format_i18n( $occupancy, 1 ) ); ?>%</span><div class="wtbm-report-progress" aria-hidden="true"><span style="width: <?php echo esc_attr( min( 100, max( 0, $occupancy ) ) ); ?>%"></span></div></div></td>
                                    <td><?php echo wp_kses_post( wc_price( $revenue ) ); ?></td>
                                </tr>
                            <?php } ?>
                            <?php if ( empty( $theaters ) ) { ?><tr><td colspan="5" class="wtbm-report-empty"><?php esc_html_e( 'No theater shows scheduled today.', 'wptheaterly' ); ?></td></tr><?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
            <footer class="wtbm-daily-footer"><span><?php esc_html_e( 'Figures reflect recorded bookings for this date.', 'wptheaterly' ); ?></span><button type="button" class="button wtbm_sales_close-btn-inline"><?php esc_html_e( 'Close report', 'wptheaterly' ); ?></button></footer>
            <?php
        }

        public function sales_report_display( $content_title ,$current_tab ){
            $today = 'today';
            $week = 'week';
            $date = current_time( 'Y-m-d' );
//            $date = '2025-09-23';
            $today_sales_report = self::pa_get_wtbm_booking_report( $date, $today );
            $weekly_sales_report = self::pa_get_wtbm_booking_report( $date, $week );

            $movie_reports = self::pa_get_wtbm_booking_movie_report( $date, 'today', true );

            $showtimes_by_theater = self::pa_get_showtimes_by_theater( $date );

            $theater_performance = self::pa_get_theater_performance( $date, $showtimes_by_theater );
            $top_selling_times = self::get_top_selling_times( $theater_performance );

            $movies_performance = self::get_shows_grouped_by_movie( $theater_performance );

            $wc_currency = get_woocommerce_currency();

            ?>

            <div id="wtbm_sales_report_content"  class="tab-content <?php echo ($current_tab == 'wtbm_sales_report') ? 'active' : ''; ?>">


                        <div class="wtbm_sales_report_card">
                            <div class="flex items-center mb-4">
                                <span style="font-size: 1.5rem; color: #9333ea; margin-right:3px;"><i class="mi mi-stats"></i></span>
                                <h3 class="text-xl font-bold text-gray-900"><?php esc_attr_e( 'Sales Analytics', 'wptheaterly' );?></h3>
                            </div>

                            <div class="grid grid-4 gap-6 mb-8">
                                <div class="report-metric bg-primary-50 border" style="border-color: #bfdbfe;">
                                    <div class="text-3xl font-bold text-primary mb-2"><?php echo esc_attr( $today_sales_report['tickets'] )?></div>
                                    <div class="text-sm font-medium" style="color: #1e40af;"><?php esc_attr_e( "Today's Sales", 'wptheaterly' );?></div>
                                    <div class="text-xs text-primary mt-1"><?php esc_attr_e( 'Tickets Sold', 'wptheaterly' );?></div>
                                </div>
                                <div class="report-metric bg-success-50 border" style="border-color: #bbf7d0;">
                                    <div class="text-3xl font-bold text-success mb-2"><?php echo esc_html( get_woocommerce_currency() );?> <?php echo esc_attr( $today_sales_report['revenue'] )?></div>
                                    <div class="text-sm font-medium" style="color: #047857;"><?php esc_attr_e( "Today's Revenue", 'wptheaterly' );?></div>
                                    <div class="text-xs text-success mt-1"><?php esc_attr_e( 'Total Earnings', 'wptheaterly' );?></div>
                                </div>
                                <div class="report-metric" style="background: #faf5ff; border-color: #e9d5ff;">
                                    <div class="text-3xl font-bold" style="color: #9333ea;"><?php echo esc_attr( $weekly_sales_report['tickets'] )?></div>
                                    <div class="text-sm font-medium" style="color: #7c3aed;"><?php esc_attr_e( 'This Week', 'wptheaterly' );?></div>
                                    <div class="text-xs" style="color: #9333ea;"><?php esc_attr_e( 'Weekly Sales', 'wptheaterly' );?></div>
                                </div>
                                <div class="report-metric" style="background: #fff7ed; border-color: #fed7aa;">
                                    <div class="text-3xl font-bold" style="color: #ea580c;"><?php echo esc_html( get_woocommerce_currency() );?> <?php echo esc_attr( $weekly_sales_report['revenue'] )?></div>
                                    <div class="text-sm font-medium" style="color: #c2410c;"><?php esc_attr_e( 'Weekly Revenue', 'wptheaterly' );?></div>
                                    <div class="text-xs" style="color: #ea580c;"><?php esc_attr_e( 'This week’s total', 'wptheaterly' );?></div>
                                </div>
                            </div>

                            <section class="wtbm-ops-card"><?php WTBM_Operations_Admin::report_filters(); ?></section>

                            <div class="border rounded" style="border-color: var(--gray-200);">

                                <div class="wtbm_sales_overlay" id="wtbm_sales_popup">
                                    <div class="wtbm_sales_popup" role="dialog" aria-modal="true" tabindex="-1">
                                        <button type="button" class="wtbm_sales_close-btn" aria-label="<?php esc_attr_e( 'Close report', 'wptheaterly' ); ?>">&times;</button>

                                        <div class="wtbm_sales_report" id="wtbm_dily_sales_report" style="display: none">
                                            <?php $show_summary = WTBM_Operations_Report::data( array( 'from' => $date, 'to' => $date, 'basis' => 'show' ) ); self::render_daily_report( $date, array( 'tickets' => $show_summary['tickets'], 'revenue' => $show_summary['net'], 'orders' => $show_summary['orders'] ), $movie_reports, $theater_performance, $top_selling_times ); ?>
                                        </div>
                                        <div class="wtbm_sales_report" id="wtbm_dily_movies_performance" style="display: none"><?php self::render_performance_report( $date, 'movie', $theater_performance ); ?></div>
                                        <div class="wtbm_sales_report" id="wtbm_daily_theater_performance" style="display: none"><?php self::render_performance_report( $date, 'theater', $theater_performance ); ?></div>

                                        <button type="button" class="btn btn-primary wtbm_sales_open-btn wtbm_sales_close-btn-inline" ><?php esc_attr_e( 'Okay', 'wptheaterly' );?></button>
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
                                    $datetime_now = current_time("Y-m-d H:i:s");
                                    ?>
                                    <?php esc_attr_e( 'Last updated', 'wptheaterly' );?>: <span id="last-updated"><?php echo esc_attr( $datetime_now )?></span>
                                </div>
                                <div class="d-flex gap-3">



                                </div>
                            </div>
                        </div>
                    </div>

        <?php }

    }

    new WTBM_Sales_Report();
}
