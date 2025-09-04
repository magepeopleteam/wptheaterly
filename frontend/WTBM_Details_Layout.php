<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'WTBM_Details_Layout' ) ) {
		class WTBM_Details_Layout {
			public function __construct() {
				/**************/
			}

            public static function booking_date_display() {
                ?>
                <div class="wtbm_booking_date_section" id="wtbm_bookingDateSection">
                    <h2 class="tbm_booking_date_section_title">Select Date</h2>
                    <div class="wtbm_booking_date_date_selector" id="wtbm_bookingDateSelector">
                        <?php
                        for ( $i = 0; $i < 7; $i++ ) {
                            $date = new DateTime();
                            $date->modify("+$i day");

                            $day   = $date->format('D');
                            $dayNo = $date->format('d');
                            $month = $date->format('M');
                            $full  = $date->format('Y-m-d');
                            ?>
                            <div class="wtbm_booking_date_date_card <?php echo $i === 0 ? 'active' : ''; ?>" data-date="<?php echo esc_attr($full); ?>">
                                <div class="day"><?php echo esc_html($day); ?></div>
                                <div class="date"><?php echo esc_html($dayNo); ?></div>
                                <div class="month"><?php echo esc_html($month); ?></div>
                            </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }

            public static function display_date_wise_movies( $date = '' ){
                $movie_ids = self::get_wtbm_show_time_movie_ids_by_date( $date );
                if( is_array( $movie_ids ) && !empty( $movie_ids ) ){
                    $movie_data = self::get_movies_data_by_ids( $movie_ids );
                    $total_movie = count( $movie_data );
                    ob_start(); ?>
                    <h2 class="wtbm_booking_date_section_title">Select Movie (<?php echo esc_attr( $total_movie );?>)</h2>
                    <div class="wtbm_booking_movies_grid" id="wtbm_moviesGrid">
                        <?php
                        foreach ( $movie_data as $movie ){
                            ?>
                            <div class="wtbm_booking_movie_card" data-movie-id="<?php echo esc_attr( $movie['movie_id'] );?>">
                                <div class="wtbm_booking_movies_poster">🎬</div>
                                <div class="wtbm_booking_movies_info">
                                    <div class="wtbm_booking_movies_title"><?php echo esc_attr( $movie['title'] );?></div>
                                    <div class="wtbm_booking_movies_details">Duration - <?php echo esc_attr( $movie['movie_duration'] );?></div>
                                </div>
                            </div>
                        <?php
                        } ?>
                    </div>
                    <?php
                }

                return ob_get_clean();
            }

            public static function display_theater_show_time( $movie_id, $date = ''  ){
                $show_times = self::get_wtbm_show_time_by_date_and_movie_id( $movie_id, $date );
                ob_start();
                if( is_array( $show_times ) && !empty( $show_times ) ){
                    foreach ( $show_times as $hall => $show_time  ){ ?>
                        <div class="hall-card">
                            <div class="hall-name">Hall 1</div>
                            <div class="time-slots">
                                <?php
                                if( is_array( $show_time ) && !empty( $show_time ) ){
                                    foreach ( $show_time as $time ){
                                        $formatted_time = date('h:i A', strtotime( esc_attr( $time ) ));
                                        ?>
                                        <div class="time-slot" data-hall="<?php echo esc_attr( $hall );?>" data-time-slot="<?php echo esc_attr( $time );?>">
                                            <?php echo esc_attr( $formatted_time );?>
                                        </div>
                                <?php } }?>
                            </div>
                        </div>
                    <?php
                    }
                }
                return ob_get_clean();
            }


            public static function get_movies_data_by_ids( $movie_ids = array() ) {
                if ( empty( $movie_ids ) || ! is_array( $movie_ids ) ) {
                    return array();
                }

                // Prepare query args
                $args = array(
                    'post_type'      => 'wtbm_movie',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'post__in'       => $movie_ids,
                    'orderby'        => 'post__in', // To keep the order of IDs
                );

                $query = new WP_Query( $args );

                $movies_data = array();

                if ( $query->have_posts() ) {
                    while ( $query->have_posts() ) {
                        $query->the_post();
                        $post_id = get_the_ID();

                        $movies_data[] = array(
                            'movie_id'          => $post_id,
                            'movie_description' => get_the_content(),
                            'title'             => get_the_title(),
                            'permalink'         => get_permalink(),
                            'movie_poster'      => get_post_meta( $post_id, 'wtbp_movie_poster', true ),
                            'release_date'      => get_post_meta( $post_id, 'wtbp_movie_release_date', true ),
                            'movie_rating'      => get_post_meta( $post_id, 'wtbp_movie_rating', true ),
                            'movie_duration'    => get_post_meta( $post_id, 'wtbp_movie_duration', true ),
                            'movie_genre'       => get_post_meta( $post_id, 'wtbp_movie_genre', true ),
                            'link_product_id'   => get_post_meta( $post_id, 'link_wc_product', true ),
                        );
                    }
                }

                wp_reset_postdata();

                return $movies_data;
            }
            public static function get_wtbm_show_time_movie_ids_by_date( $date = '' ) {
                global $wpdb;
                if ( empty( $date ) ) {
                    $date = current_time( 'Y-m-d' );
                }
                $args = array(
                    'post_type'      => 'wtbm_show_time',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'meta_query'     => array(
                        array(
                            'key'     => 'wtbp_show_time_date',
                            'value'   => $date,
                            'compare' => '=',
                            'type'    => 'DATE',
                        ),
                    ),
                );

                $query = new WP_Query( $args );

                $movie_ids = array();

                if ( $query->have_posts() ) {
                    while ( $query->have_posts() ) {
                        $query->the_post();
                        $movie_id = get_post_meta( get_the_ID(), 'wtbp_show_time_movieId', true );
                        if ( ! empty( $movie_id ) ) {
                            $movie_id = intval( $movie_id );
                            if ( ! in_array( $movie_id, $movie_ids, true ) ) {
                                $movie_ids[] = $movie_id;
                            }
                        }
                    }
                }

                wp_reset_postdata();

                return $movie_ids;
            }
            public static function get_wtbm_show_time_by_date_and_movie_id( $movie_id, $date = '' ) {
                global $wpdb;

                if ( empty( $date ) ) {
                    $date = current_time( 'Y-m-d' );
                }
                $args = array(
                    'post_type'      => 'wtbm_show_time',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'meta_query'     => array(
                        array(
                            'key'     => 'wtbp_show_time_date',
                            'value'   => $date,
                            'compare' => '=',
                            'type'    => 'DATE',
                        ),
                    ),
                );

                $query = new WP_Query( $args );
                $showtimes = array();
                if ( $query->have_posts() ) {
                    while ( $query->have_posts() ) {
                        $query->the_post();
                        $current_movie_id = get_post_meta( get_the_ID(), 'wtbp_show_time_movieId', true );
                        $theater_id = get_post_meta( get_the_ID(), 'wtbp_show_time_theaterId', true );
                        $show_time_start = get_post_meta( get_the_ID(), 'wtbp_show_time_start_date', true );

                        if ( empty( $current_movie_id ) || empty( $theater_id ) || empty( $show_time_start ) ) {
                            continue;
                        }

                        $current_movie_id = intval( $current_movie_id );
                        $theater_id = intval( $theater_id );
                        if ( $current_movie_id === intval( $movie_id ) ) {
                            if ( ! isset( $showtimes[ $theater_id ] ) ) {
                                $showtimes[ $theater_id ] = array();
                            }
                            $showtimes[ $theater_id ][] = $show_time_start;
                        }
                    }
                }

                wp_reset_postdata();

                return $showtimes;
            }
            public static function get_wtbm_show_time_by_date( $date = ''  ) {
                global $wpdb;

                if ( empty( $date ) ) {
                    $date = current_time( 'Y-m-d' ); // default = today
                }

                // Query posts by date
                $args = array(
                    'post_type'      => 'wtbm_show_time',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'meta_query'     => array(
                        array(
                            'key'     => 'wtbp_show_time_date',
                            'value'   => $date,
                            'compare' => '=',
                            'type'    => 'DATE',
                        ),
                    ),
                );

                $query = new WP_Query( $args );

                $result = array();

                if ( $query->have_posts() ) {
                    while ( $query->have_posts() ) {
                        $query->the_post();
                        $movie_id   = get_post_meta( get_the_ID(), 'wtbp_show_time_movieId', true );
                        $theater_id = get_post_meta( get_the_ID(), 'wtbp_show_time_theaterId', true );
                        $show_time_start = get_post_meta( get_the_ID(), 'wtbp_show_time_start_date', true );
                        if ( empty( $movie_id ) || empty( $theater_id ) || empty( $show_time_start ) ) {
                            continue;
                        }
                        $movie_id = intval( $movie_id );
                        $theater_id = intval( $theater_id );
                        if ( ! isset( $result[ $movie_id ] ) ) {
                            $result[ $movie_id ] = array();
                        }
                        if ( ! isset( $result[ $movie_id ][ $theater_id ] ) ) {
                            $result[ $movie_id ][ $theater_id ] = array();
                        }
                        $result[ $movie_id ][ $theater_id ][] = $show_time_start;
                    }
                }

                wp_reset_postdata();

                return $result;
            }


        }
		new WTBM_Details_Layout();
	}