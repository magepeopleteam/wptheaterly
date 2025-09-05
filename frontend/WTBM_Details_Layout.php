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

                ob_start();
                ?>
                <div class="wtbm_booking_date_section" id="wtbm_bookingDateSection">
                    <h2 class="tbm_booking_date_section_title"><?php esc_attr_e( 'Select Date', 'wptheaterly' );?></h2>
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

                return ob_get_clean();
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
                        $i = 0;
                        foreach ( $movie_data as $movie ){
                            if( $i === 0 ){
                                $active = 'wtbm_movieActive';
                            }else{
                                $active = '';
                            }
                            ?>
                            <div class="wtbm_booking_movie_card"
                                 data-movie-name="<?php echo esc_attr( $movie['title'] );?>"
                                 data-movie-id="<?php echo esc_attr( $movie['movie_id'] );?>"
                                 data-movie-duration="<?php echo esc_attr( $movie['movie_duration'] );?>"
                            >
                                <div class="wtbm_booking_movies_poster">🎬</div>
                                <div class="wtbm_booking_movies_info">
                                    <div class="wtbm_booking_movies_title"><?php echo esc_attr( $movie['title'] );?></div>
                                    <div class="wtbm_booking_movies_details"><?php esc_attr_e( 'Duration', 'wptheaterly' );?> - <?php echo esc_attr( $movie['movie_duration'] );?></div>
                                </div>
                            </div>
                        <?php
                            $i++;
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
                    foreach ( $show_times as $theater_id => $show_time  ){
                        $post_title = get_the_title( $theater_id );
                        ?>
                        <div class="wtbm_hallCard">
                            <div class="wtbm_hallName"><?php echo esc_attr( $post_title );?></div>
                            <div class="wtbm_timeSlots">
                                <?php
                                if( is_array( $show_time ) && !empty( $show_time ) ){
                                    foreach ( $show_time as $time ){
                                        $formatted_time = date('h:i A', strtotime( esc_attr( $time ) ));
                                        ?>
                                        <div class="wtbm_timeSlot" data-wtbm-theater-name = "<?php echo esc_attr( $post_title );?>" data-wtbm-theater="<?php echo esc_attr( $theater_id );?>" data-time-slot="<?php echo esc_attr( $time );?>">
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


            public static function display_theater_seat_mapping( $post_id, $not_available_seats = [] ): string{

                $content = '';
                global $post;
//                $post_id = $post->ID;
                $plan_data = get_post_meta($post_id, 'wtbp_theater_seat_map', true);

                $plan_seats = isset( $plan_data['seat_data'] ) ? $plan_data['seat_data'] : array();
                $plan_seat_texts = isset( $plan_data['seat_text_data'] ) ? $plan_data['seat_text_data'] : array();
                $dynamic_shapes = isset( $plan_data['dynamic_shapes'] ) ? $plan_data['dynamic_shapes'] : '';


                if (!empty($plan_seats) && is_array( $plan_seats )) {
                    $leastLeft = PHP_INT_MAX;
                    $leastTop = PHP_INT_MAX;

                    foreach ($plan_seats as $item) {
                        if (isset($item["left"])) {
                            $currentLeft = (int)rtrim($item["left"], "px");
                            $currentTop = (int)rtrim($item["top"], "px");

                            if ($currentLeft < $leastLeft) {
                                $leastLeft = $currentLeft;
                            }
                            if ($currentTop < $leastTop) {
                                $leastTop = $currentTop;
                            }
                        }
                    }

                    $data_tableBindIds = [];
                    $height = $leastTop + 200;
                    $seat_grid_width = $leastLeft + 1;
                    $seat_grid_height = $leastTop + 1;
                    // Start building custom content
                    $custom_content = '
                        <div id="wtbm_seatGrid" /*style="height: '.$height.'px"*/>
                            <div id="mptrs_seatMapHolder-'.$post_id.'" class="mptrs_seatMapHolder">';
                                if( is_array( $plan_seat_texts ) && count( $plan_seat_texts ) > 0 ) {
                                    foreach ($plan_seat_texts as $plan_seat_text) {
                                        $text_rotate_deg = isset($plan_seat_text['textRotateDeg']) ? $plan_seat_text['textRotateDeg'] : 0;
                                        $custom_content .= '
                                                <div class="mptrs_dynamicTextWrapper" data-text-degree=' . $text_rotate_deg . '
                                                    style="
                                                    position: absolute; 
                                                    left: ' . ((int)$plan_seat_text['textLeft'] - $leastLeft) . 'px; 
                                                    top: ' . ((int)$plan_seat_text['textTop'] - $leastTop) . 'px; 
                                                    transform: rotate(' . $text_rotate_deg . 'deg);
                                                    ">
                                                    <span class="mptrs_dynamicText" 
                                                        style="
                                                            display: inline-block; 
                                                            color: ' . $plan_seat_text['color'] . '; 
                                                            font-size: ' . $plan_seat_text['fontSize'] . ';
                                                            cursor: pointer;">
                                                       ' . $plan_seat_text['text'] . '
                                                    </span>
                                                </div>';
                                    }
                                }
                                foreach ($plan_seats as $seat) {
                                if ( isset($seat["left"] ) ) {
                                    $data_tableBind = isset( $seat['data_tableBind'] ) ? $seat['data_tableBind'] : '';
                                    if( $data_tableBind !== '' ){
                                        $data_tableBindIds[] = $seat['data_tableBind'];
                                    }

                                    $seat_id = isset( $seat['id'] ) ? $seat['id'] : 0;
                                    if( $seat_id !== 0 ){
                                        $seat_id = 'seat_'.$seat['id'];
                                    }

                                    $parent_class_name = 'wtbm_mappedSeat';
                                    $class_name = 'wtbm_mappedSeatInfo';
                                    $seat_bg_color = esc_attr( $seat['color']);
                                    if( in_array(  $seat_id, $not_available_seats ) ) {
                                        $seat_bg_color = '#333333';
                                        $class_name = 'wtbm_reservedMappedSeatInfo';
                                        $parent_class_name = 'wtbm_reservedMappedSeat';
                                    }
                                    $icon_url = '';
                                    $width = isset($seat['width']) ? (int)$seat['width'] : 0;
                                    $height = isset($seat['height']) ? (int)$seat['height'] : 0;
                                    $uniqueId = "seat_{$seat['id']}"; // Unique ID for each seat
                                    $border_radius = isset( $seat['border_radius'] ) ? $seat['border_radius'] : '';

                                    if( isset( $seat['backgroundImage'] ) && $seat['backgroundImage'] !== '' ){
                                        $icon_url = WTBM_Plan_ASSETS."images/icons/seatIcons/".$seat['backgroundImage'].".png";
                                    }

                                    $tableBind = isset( $seat['data_tableBind'] ) ? $seat['data_tableBind'] : '';

                                    $custom_content .= '<div class="'.esc_attr( $parent_class_name ).'" 
                                                                id="' . esc_attr($uniqueId) . '" 
                                                                data-price="' . esc_attr($seat['price']) . '" 
                                                                data-seat-num="' . esc_attr($seat['seat_number']) . '" 
                                                                data-tableBind="' . esc_attr( $tableBind ) . '"
                                                                style="
                                                                    width: ' . $width . 'px;
                                                                    height: ' . $height . 'px;
                                                                    left: ' . ((int)$seat['left'] - $leastLeft) . 'px;
                                                                    top: ' . ((int)$seat['top'] - $leastTop) . 'px;
                                                                    border-radius: ' .$border_radius. ';
                                                                    transform: rotate('.(int)$seat['data_degree'].'deg);"
                                                                title="Price: $' . esc_attr($seat['price']) . '">
                                                                <div class="'.esc_attr( $class_name ).'" 
                                                                    style="
                                                                        background-color: ' . $seat_bg_color . ';
                                                                        background-image: url('.$icon_url.');
                                                                        width: ' . $width . 'px;
                                                                        height: ' . $height . 'px;">
                                                                    <span class="mptrs_seatNumber">' . esc_html($seat['seat_number'] ?? '') . '</span>
                                                                </div>
                                                            </div>';
                                    }
                                }
                                if ( is_array( $dynamic_shapes ) && count( $dynamic_shapes ) > 0 ) {
                                    foreach ( $dynamic_shapes as $dynamic_shape ) {
                                        /* if( in_array( $dynamic_shape['tableBindID'], $data_tableBindIds)){
                                             $shape_class = 'mptrs_selectedDynamicShape';
                                         }else{
                                             $shape_class = 'mptrs_dynamicShape';
                                         }*/

                                        $shape_class = 'mptrs_dynamicShape';
                                        if ( isset( $dynamic_shape['backgroundImage'] ) && $dynamic_shape['backgroundImage'] !== '' ) {
                                            $table_background_img_url = esc_url( WTBM_Plan_ASSETS . 'images/icons/tableIcon/' . $dynamic_shape['backgroundImage'] . '.png' );
                                        }else{
                                            $table_background_img_url = '';
                                        }

                                        $shape_rotate_deg = isset( $dynamic_shape['shapeRotateDeg'] ) ? $dynamic_shape['shapeRotateDeg'] : 0;
                                        $custom_content .= '<div id="'.esc_attr( $dynamic_shape['tableBindID'] ).'" class="'.$shape_class.'" style=" 
                                                                        left: ' . esc_attr( $dynamic_shape['textLeft']  - $leastLeft ) . 'px; 
                                                                        top: ' . esc_attr( $dynamic_shape['textTop']  - $leastTop ) . 'px; 
                                                                        width: ' . esc_attr( $dynamic_shape['width'] ) . 'px;
                                                                        height: ' . esc_attr( $dynamic_shape['height'] ) . 'px;
                                                                        background-color: ' . esc_attr( $dynamic_shape['backgroundColor'] ).'; 
                                                                        border-radius: ' . esc_attr( $dynamic_shape['borderRadius'] ).';
                                                                        clip-path: ' . esc_attr( $dynamic_shape['clipPath'] ).';
                                                                        transform: rotate(' . $shape_rotate_deg . 'deg);
                                                                        background-image:url(' . esc_url( $table_background_img_url ) . ');
                                                                    ">
                                                                    </div>';
                                    }
                                }
                                $custom_content .= '
                            </div>
                        </div>
                        ';
                        $content .= $custom_content;
                    }

                return $content;
            }



        }
		new WTBM_Details_Layout();
	}