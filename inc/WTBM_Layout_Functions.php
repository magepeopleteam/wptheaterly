<?php
/*
   * @Author 		rubelcuet10@gmail.com
   * Copyright: 	mage-people.com
   */
if (!defined('ABSPATH')) {
    die;
}
if( !class_exists( 'WTBM_Layout_Functions ') ){
    class WTBM_Layout_Functions
    {
        public function __construct(){}


        public static function get_and_display_movies( $limit = -1) {
            // WP_Query args
            $args = [
                'post_type'      => 'wtbm_movie',
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ];

            $query = new WP_Query( $args );

            if ( ! $query->have_posts() ) {
                echo '<tr><td colspan="6">' . esc_html__( 'No movies found.', 'wptheaterly' ) . '</td></tr>';
                return;
            }

            $movie_data = [];

            while ( $query->have_posts() ) {
                $query->the_post();
                $poster_id   = get_post_meta( get_the_ID(), 'wtbp_movie_poster_id', true );
                $movie_data[] = [
                    'id'                => get_the_ID(),
                    'title'             => get_the_title(),
                    'movie_url'         => get_permalink(get_the_ID()),
                    'genre'             => get_post_meta( get_the_ID(), 'wtbp_movie_genre', true ),
                    'duration'          => get_post_meta( get_the_ID(), 'wtbp_movie_duration', true ),
                    'rating'            => get_post_meta( get_the_ID(), 'wtbp_movie_rating', true ),
                    'releaseDate'       => get_post_meta( get_the_ID(), 'wtbp_movie_release_date', true ),
                    'poster'            => get_post_meta( get_the_ID(), 'wtbp_movie_poster', true ),
                    'poster_image_url'  => esc_url( wp_get_attachment_url( $poster_id ) ),
                    'poster_id'         => get_post_meta( get_the_ID(), 'wtbp_movie_poster_id', true ),
                    'status'            => get_post_meta( get_the_ID(), 'wtbp_movie_active', true ) == 'true' ? 'active' : 'inactive',

                ];
            }

            wp_reset_postdata();

            return $movie_data;
        }
        public static function get_movies_data_by_id( $post_id ) {
            $post_id = intval( $post_id );
            if ( ! $post_id ) {
                return null;
            }
            $args = [
                'post_type'      => 'wtbm_movie',
                'post_status'    => 'publish',
                'p'              => $post_id,
                'posts_per_page' => 1,
            ];
            $query = new WP_Query( $args );
            if ( ! $query->have_posts() ) {
                wp_reset_postdata();
                return null;
            }
            $movie_data = [];
            while ( $query->have_posts() ) {
                $query->the_post();

                $movie_data = [
                    'id'            => get_the_ID(),
                    'title'         => get_the_title(),
                    'description'   => get_the_content(),
                    'genre'         => get_post_meta( get_the_ID(), 'wtbp_movie_genre', true ),
                    'duration'      => get_post_meta( get_the_ID(), 'wtbp_movie_duration', true ),
                    'rating'        => get_post_meta( get_the_ID(), 'wtbp_movie_rating', true ),
                    'releaseDate'   => get_post_meta( get_the_ID(), 'wtbp_movie_release_date', true ),
                    'poster'        => get_post_meta( get_the_ID(), 'wtbp_movie_poster', true ),
                    'poster_id'     => get_post_meta( get_the_ID(), 'wtbp_movie_poster_id', true ),
                    'status'        => get_post_meta( get_the_ID(), 'wtbp_movie_active', true ) ?: false,
                    'director'      => get_post_meta( get_the_ID(), 'wtbp_movie_director', true ) ?: false,
                    'actor_actress' => get_post_meta( get_the_ID(), 'wtbp_movie_actors', true ) ?: false,
                    'movie_writer'  => get_post_meta( get_the_ID(), 'wtbp_movie_writer', true ) ?: false,
                ];
            }
            wp_reset_postdata();

            return $movie_data;
        }
        public static function get_theater_data_by_id( $post_id ) {
            $post_id = intval( $post_id );
            if ( ! $post_id ) {
                return null;
            }
            $args = [
                'post_type'      => WTBM_Function::get_theater_cpt(),
                'post_status'    => 'publish',
                'p'              => $post_id,
                'posts_per_page' => 1,
            ];
            $query = new WP_Query( $args );
            if ( ! $query->have_posts() ) {
                wp_reset_postdata();
                return null;
            }
            $movie_data = [];

            while ( $query->have_posts() ) {
                $query->the_post();

                $movie_data = [
                    'id'                => get_the_ID(),
                    'name'              => get_the_title(),
                    'description'       => get_the_content(),
                    'status'            => get_post_meta( get_the_ID(), 'wtbp_theater_status', true ),
                    'sound'             => get_post_meta( get_the_ID(), 'wtbp_theater_soundSystem', true ),
                    'seats_per_row'     => get_post_meta( get_the_ID(), 'wtbp_theater_seatsPerRow', true ),
                    'theater_row'       => get_post_meta( get_the_ID(), 'wtbp_theater_rows', true ),
                    'type'              => get_post_meta( get_the_ID(), 'wtbp_theater_type', true ),
                    'theater_category'  => get_post_meta( get_the_ID(), 'wtbp_theater_category', true ),
                ];
            }
            wp_reset_postdata();

            return $movie_data;
        }
        public static function get_and_display_theater_date( $limit = -1 ) {
            // WP_Query args
            $args = [
                'post_type'      => WTBM_Function::get_theater_cpt(),
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ];

            $query = new WP_Query( $args );

            if ( ! $query->have_posts() ) {
                echo wp_kses_post('<tr><td colspan="6">' . esc_html__( 'No movies found.', 'wptheaterly' ) . '</td></tr>' ) ;
                return;
            }

            $theater_date = [];

            while ( $query->have_posts() ) {
                $query->the_post();

                $theater_date[] = [
                    'id'            => get_the_ID(),
                    'name'          => get_the_title(),
                    'description'   => get_the_content(),
                    'status'        => get_post_meta( get_the_ID(), 'wtbp_theater_status', true ),
                    'sound'         => get_post_meta( get_the_ID(), 'wtbp_theater_soundSystem', true ),
                    'theater_row'   => get_post_meta( get_the_ID(), 'wtbp_theater_rows', true ),
                    'seats_per_row' => get_post_meta( get_the_ID(), 'wtbp_theater_seatsPerRow', true ),
                    'type'          => get_post_meta( get_the_ID(), 'wtbp_theater_type', true ),
                ];
            }

            wp_reset_postdata();

            return $theater_date;
        }
        public static function get_show_time_data() {

            $args = [
                'post_type'      => WTBM_Function::get_show_time_cpt(),
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ];
            $query = new WP_Query( $args );
            if ( ! $query->have_posts() ) {
                wp_reset_postdata();
                return null;
            }
            $show_time_data = [];

            while ( $query->have_posts() ) {
                $query->the_post();

                $show_time_data[] = [
                    'id'                    => get_the_ID(),
                    'name'                  => get_the_title(),
                    'description'           => get_the_content(),
                    'price'                 => get_post_meta( get_the_ID(), 'wtbp_show_time_price', true ),
                    'show_time_end'         => get_post_meta( get_the_ID(), 'wtbp_show_time_end_date', true ),
                    'show_time_start'       => get_post_meta( get_the_ID(), 'wtbp_show_time_start_date', true ),
                    'theater_id'            => get_post_meta( get_the_ID(), 'wtbp_show_time_theaterId', true ),
                    'movie_id'              => get_post_meta( get_the_ID(), 'wtbp_show_time_movieId', true ),
                    'show_time_date'        => get_post_meta( get_the_ID(), 'wtbp_show_time_date', true ),
                    'showtime_start_date'   => get_post_meta( get_the_ID(), 'wtbp_showtime_start_date', true ),
                    'showtime_end_date'     => get_post_meta( get_the_ID(), 'wtbp_showtime_end_date', true ),
                ];
            }
            wp_reset_postdata();

            return $show_time_data;
        }
        public static function get_show_time_data_by_id( $post_id ) {
            $post_id = intval( $post_id );
            if ( ! $post_id ) {
                return null;
            }
            $args = [
                'post_type'      => WTBM_Function::get_show_time_cpt(),
                'post_status'    => 'publish',
                'p'              => $post_id,
                'posts_per_page' => 1,
            ];
            $query = new WP_Query( $args );
            if ( ! $query->have_posts() ) {
                wp_reset_postdata();
                return null;
            }
            $movie_data = [];

            while ( $query->have_posts() ) {
                $query->the_post();

                $movie_data = [
                    'id'                    => get_the_ID(),
                    'name'                  => get_the_title(),
                    'description'           => get_the_content(),
                    'price'                 => get_post_meta( get_the_ID(), 'wtbp_show_time_price', true ),
                    'show_time_end'         => get_post_meta( get_the_ID(), 'wtbp_show_time_end_date', true ),
                    'show_time_start'       => get_post_meta( get_the_ID(), 'wtbp_show_time_start_date', true ),
                    'theater_id'            => get_post_meta( get_the_ID(), 'wtbp_show_time_theaterId', true ),
                    'movie_id'              => get_post_meta( get_the_ID(), 'wtbp_show_time_movieId', true ),
                    'show_time_date'        => get_post_meta( get_the_ID(), 'wtbp_show_time_date', true ),
                    'showtime_start_date'   => get_post_meta( get_the_ID(), 'wtbp_showtime_start_date', true ),
                    'showtime_end_date'     => get_post_meta( get_the_ID(), 'wtbp_showtime_end_date', true ),
                ];
            }
            wp_reset_postdata();

            return $movie_data;
        }
        public static function get_pricing_rules_data_by_id( $post_id ) {
            $post_id = intval( $post_id );
            if ( ! $post_id ) {
                return null;
            }
            $args = [
                'post_type'      => WTBM_Function::get_pricing_cpt(),
                'post_status'    => 'publish',
                'p'              => $post_id,
                'posts_per_page' => 1,
            ];
            $query = new WP_Query( $args );
            if ( ! $query->have_posts() ) {
                wp_reset_postdata();
                return null;
            }
            $pricing_rules_data = [];

            while ( $query->have_posts() ) {
                $query->the_post();

                $pricing_rules_data = [
                    'id'                    => get_the_ID(),
                    'name'                  => get_the_title(),
                    'description'           => get_the_content(),
                    'rules_theater_type'    => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_theaterType', true ),
                    'rules_type'            => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_type', true ),
                    'rules_date_rang'        => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_dateRange', true ),
                    'rules_end_date'       => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_endDate', true ),
                    'rules_start_date'      => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_startDate', true ),
                    'rules_days'            => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_days', true ),
                    'rules_time_range'      => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_timeRange', true ),
                    'rules_combinable'      => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_combinable', true ),
                    'rules_min_seats'       => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_minSeats', true ),
                    'rules_priority'        => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_priority', true ),
                    'rules_active'          => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_active', true ),
                    'rules_multiplier'      => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_multiplier', true ),
                ];
            }
            wp_reset_postdata();

            return $pricing_rules_data;
        }

        public static function get_pricing_rules_data() {

            $args = [
                'post_type'      => WTBM_Function::get_pricing_cpt(),
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ];
            $query = new WP_Query( $args );
            if ( ! $query->have_posts() ) {
                wp_reset_postdata();
                return null;
            }
            $pricing_rules_data = [];

            while ( $query->have_posts() ) {
                $query->the_post();

                $pricing_rules_data[] = [
                    'id'                    => get_the_ID(),
                    'name'                  => get_the_title(),
                    'description'           => get_the_content(),
                    'rules_theater_type'    => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_theaterType', true ),
                    'rules_type'            => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_type', true ),
                    'rules_date_range'        => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_dateRange', true ),
                    'rules_end_date'       => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_endDate', true ),
                    'rules_start_date'      => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_startDate', true ),
                    'rules_days'            => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_days', true ),
                    'rules_time_range'      => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_timeRange', true ),
                    'rules_combinable'      => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_combinable', true ),
                    'rules_min_seats'       => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_minSeats', true ),
                    'rules_priority'        => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_priority', true ),
                    'rules_active'          => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_active', true ),
                    'rules_multiplier'      => get_post_meta( get_the_ID(), 'wtbp_pricing_rules_multiplier', true ),
                ];
            }
            wp_reset_postdata();

            return $pricing_rules_data;
        }

        public static function add_edit_new_movie_html( $add, $data = [] ) {
            $defaults = [
                'id'          => '',
                'title'       => '',
                'genre'       => '',
                'status'      => false,
                'duration'    => '',
                'rating'      => '',
                'releaseDate' => '',
                'poster'      => '',
                'poster_id'   => '',
                'description' => '',
                'director'    => '',
                'actor_actress' => '',
                'movie_writer'  => '',
            ];
            $data = wp_parse_args( $data, $defaults );
            $post_id = $data['id'];
            $nonce = wp_create_nonce( 'wtbp_movie_action' );

            ob_start();
            ?>
            <div class="wtbp_add_movie_holder">
                <!-- Nonce field for security -->
                <input type="hidden" name="wtbp_movie_nonce" value="<?php echo esc_attr( $nonce ); ?>">

                <div class="grid grid-cols-2 mb-4">
                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Movie Title', 'wptheaterly' ); ?></label>
                        <input type="text" id="movie-title" class="form-input"
                               placeholder="<?php esc_attr_e( 'Movie Title', 'wptheaterly' ); ?>"
                               value="<?php echo esc_attr( $data['title'] ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Genre', 'wptheaterly' ); ?></label>
                        <input type="text" id="movie-genre" class="form-input"
                               placeholder="<?php esc_attr_e( 'Action, Drama, Comedy, Romance, Horror', 'wptheaterly' ); ?>"
                               value="<?php echo esc_attr( $data['genre'] ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Duration', 'wptheaterly' ); ?></label>
                        <input type="text" id="movie-duration" class="form-input"
                               placeholder="<?php esc_attr_e( '2h 30m', 'wptheaterly' ); ?>"
                               value="<?php echo esc_attr( $data['duration'] ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Rating', 'wptheaterly' ); ?></label>
                        <input type="number" id="movie-rating" class="form-input" step="0.1"
                               placeholder="<?php esc_attr_e( '8.5', 'wptheaterly' ); ?>"
                               value="<?php echo esc_attr( $data['rating'] ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Director', 'wptheaterly' ); ?></label>
                        <input type="text" id="movie_director" class="form-input" step="0.1"
                               placeholder="<?php esc_attr_e( 'Director name', 'wptheaterly' ); ?>"
                               value="<?php echo esc_attr( $data['director'] ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Cast / Actors', 'wptheaterly' ); ?></label>
                        <input type="text" id="movie_actor_actress" class="form-input" step="0.1"
                               placeholder="<?php esc_attr_e( 'Actor Actress name', 'wptheaterly' ); ?>"
                               value="<?php echo esc_attr( $data['actor_actress'] ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Movie Writer', 'wptheaterly' ); ?></label>
                        <input type="text" id="movie_writer" class="form-input" step="0.1"
                               placeholder="<?php esc_attr_e( 'Actor Actress name', 'wptheaterly' ); ?>"
                               value="<?php echo esc_attr( $data['movie_writer'] ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Release Date', 'wptheaterly' ); ?></label>
                        <input type="date" id="movie-release-date" class="form-input"
                               value="<?php echo esc_attr( $data['releaseDate'] ); ?>">
                    </div>

                    <input type="hidden" id="wtbm_movie_poster_id" name="wtbm_movie_poster_id"
                           value="<?php echo esc_attr( $data['poster_id'] ); ?>">
                    <div id="wtbm_movie_poste_preview" style="margin-bottom:10px;">
                        <?php if ( ! empty( $data['poster_id'] ) ) : ?>
                            <img src="<?php echo esc_url( wp_get_attachment_url( $data['poster_id'] ) ); ?>"
                                 style="max-width:150px; height:auto;" />
                        <?php endif; ?>
                    </div>
                    <button type="button" class="button" id="wtbm_upload_movie_poster">
                        <?php esc_html_e( 'Upload Poster', 'wptheaterly' ); ?>
                    </button>
                    <button type="button" class="button" id="wtbm_remove_movie_poster"
                            style="<?php echo empty( $data['poster_id'] ) ? 'display:none;' : ''; ?>">
                        <?php esc_html_e( 'Remove', 'wptheaterly' ); ?>
                    </button>

                </div>

                <!-- Active -->
                <div class="form-group mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="wtbm_movie_active" class="mr-2"
                            <?php
                            checked( $data['status'] == 'true' );
                            ?>>
                        <span><?php esc_html_e( 'Active', 'wptheaterly' ); ?></span>
                    </label>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label"><?php esc_html_e( 'Description', 'wptheaterly' ); ?></label>
                    <textarea id="movie-description" class="form-input" rows="3"
                              placeholder="<?php esc_attr_e( 'Movie description', 'wptheaterly' ); ?>"><?php
                        echo esc_textarea( $data['description'] );
                        ?></textarea>
                </div>
            </div>
            <div class="flex gap-2">
                <?php if( $add === 'add' ){?>
                    <button class="btn btn-success mptrs_add_new_movie" id="mptrs_add_new_movie">Add Movie</button>
                <?php }else{?>
                    <button class="btn btn-success mptrs_add_new_movie" data-edited-post-id=<?php echo esc_attr( $post_id );?> id="mptrs_edit_movie">Update Movie</button>
                <?php }?>
                <button class="btn btn-secondary" id="wtbm_clear_add_movie_form" ><?php esc_attr_e( 'Cancel', 'wptheaterly' ); ?></button>
            </div>
            <?php

            return ob_get_clean();
        }
        public static function display_movies_data( $movie_data = [] ) {

            if ( empty( $movie_data ) || ! is_array( $movie_data ) ) {
                return;
            }

            foreach ( $movie_data as $movie ) {
                $defaults = [
                    'id'                => '',
                    'title'             => '',
                    'movie_url'         => '',
                    'genre'             => '',
                    'duration'          => '',
                    'rating'            => '',
                    'releaseDate'       => '',
                    'poster_image_url'  => '',
                    'poster'            => 'https://via.placeholder.com/200x300/4A90E2/ffffff?text=No+Poster',
                    'status'            => 'active',
                ];
                $movie = wp_parse_args( $movie, $defaults );

                // Escape outputs
                $id             = esc_html( $movie['id'] );
                $title          = esc_html( $movie['title'] );
                $movie_url      = esc_html( $movie['movie_url'] );
                $genre          = esc_html( $movie['genre'] );
                $duration       = esc_html( $movie['duration'] );
                $rating         = esc_html( $movie['rating'] );
                $releaseDate    = esc_html( $movie['releaseDate'] );
                $poster         = esc_url( $movie['poster'] );
                $poster_img_url = esc_url( $movie['poster_image_url'] );
                $status         = esc_html( $movie['status'] );

                if( $poster_img_url === '' ){
                    $poster_img_url = $poster;
                }

                ob_start();
                ?>
                <tr class="wtbm_movie_content" id="movie_content_<?php echo esc_attr( $id );?>" date-movie-id="<?php echo esc_attr( $id );?>">
                    <td>
                        <div class="flex items-center">
                            <a href="<?php echo esc_url($movie_url);?>">
                            <img src="<?php echo esc_url( $poster_img_url );?>"
                                 alt="<?php echo esc_html( $title ) ; ?>"
                                 class="movie-poster" loading="lazy"> </a>
                            <div>
                                <div class="font-medium text-gray-900"><?php echo esc_html( $title); ?></div>
                                <?php if ( $releaseDate ) : ?>
                                    <div class="text-sm text-gray-500">

                                        <?php
                                        // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
                                          esc_attr_e( 'Released: ', 'wptheaterly' );
                                          echo esc_html( $releaseDate );
                                          ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm text-gray-900"><?php echo esc_html( $genre ); ?></td>
                    <td class="text-sm text-gray-900"><?php echo esc_html( $duration) ; ?></td>
                    <td class="text-sm font-medium">⭐ <?php echo esc_html( $rating ); ?></td>
                    <td>
                <span class="status-badge status-<?php echo esc_attr( strtolower( $status ) ); ?>">
                    <?php echo esc_attr( $status); ?>
                </span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <button class="btn-icon edit wtbm_edit_movie" data-edit-movie-id="<?php echo esc_attr( $id );?>" id="wrbm_edit_<?php echo esc_attr( $id );?>"
                                    title="<?php esc_attr_e( 'Edit Movie', 'wptheaterly' ); ?>"><i class="mi mi-pencil"></i></button>
                            <button class="btn-icon delete wtbm_delete_movie" id="wrbm_delete_<?php echo esc_attr( $id );?>"
                                    title="<?php esc_attr_e( 'Delete Movie', 'wptheaterly' ); ?>" data-delete-movie-id="<?php echo esc_attr( $id );?>"><i class="mi mi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php
            }

            return ob_get_clean();
        }

        public static function add_category( $action, $category_data, $default_color ){
            ob_start();
            if( $action === 'add' ){
            ?>
            <div class="wtbm_theater_category_box" data-id="1">
                <h4><?php esc_attr_e( 'Seating Category 1', 'wptheaterly' ); ?></h4>

                <div class="wtbm_theater_form_group_holder">
                    <div class="wtbm_theater_form_group">
                        <label><?php esc_attr_e( 'Category Name', 'wptheaterly' ); ?></label>
                        <input type="text" placeholder="e.g., Regular, Premium" name="wtbm_theater_category_name" required>
                    </div>

                    <div class="wtbm_theater_form_group">
                        <label><?php esc_attr_e( 'Number of Seats', 'wptheaterly' ); ?></label>
                        <input type="number" placeholder="50" name="wtbm_theater_seats" required>
                    </div>

                    <div class="wtbm_theater_form_group">
                        <label><?php esc_attr_e( 'Base Price ($)', 'wptheaterly' ); ?></label>
                        <input type="number" step="0.01" placeholder="12.99" name="wtbm_theater_price" required>
                    </div>
                    <div class="wtbm_theater_form_group">
                        <label><?php esc_attr_e( 'Set Color', 'wptheaterly' ); ?></label>
                        <input type="color" name="wtbm_theater_color" class="wtbm_theater_color" value="<?php echo esc_attr( $default_color );?>" required>
                    </div>
                </div>

            </div>
            <?php
            }else{
                $count_category = 1;
                if( is_array( $category_data ) && ! empty( $category_data ) ){
                    foreach ( $category_data as $category ) {

                        $color = isset( $category['color'] ) ? $category['color'] : '';
                        ?>
                        <div class="wtbm_theater_category_box" data-id="<?php echo esc_attr( $count_category );?>">
                            <?php if( $count_category > 1 ){?>
                                <button type="button" class="wtbm_theater_remove_btn">&times;</button>
                            <?php }?>
                            <h4><?php esc_attr_e( 'Seating Category', 'wptheaterly' ); ?> <?php echo esc_attr( $count_category );?></h4>
                            <div class="wtbm_theater_form_group_holder">
                                <div class="wtbm_theater_form_group">
                                    <label><?php esc_attr_e( 'Category Name', 'wptheaterly' ); ?></label>
                                    <input type="text" placeholder="e.g., Regular, Premium" name="wtbm_theater_category_name" value="<?php echo esc_attr( $category['category_name']);?>" required>
                                </div>

                                <div class="wtbm_theater_form_group">
                                    <label><?php esc_attr_e( 'Number of Seats', 'wptheaterly' ); ?></label>
                                    <input type="number" placeholder="50" name="wtbm_theater_seats" value="<?php echo esc_attr( $category['seats']);?>" required>
                                </div>

                                <div class="wtbm_theater_form_group">
                                    <label><?php esc_attr_e( 'Base Price ($)', 'wptheaterly' ); ?></label>
                                    <input type="number" step="0.01" placeholder="12.99" name="wtbm_theater_price" value="<?php echo esc_attr( $category['price']);?>" required>
                                </div>

                                <div class="wtbm_theater_form_group">
                                    <label><?php esc_attr_e( 'Set Color', 'wptheaterly' ); ?></label>
                                    <input type="color" name="wtbm_theater_color" class="wtbm_theater_color" value="<?php echo esc_attr( $color );?>" required>
                                </div>
                            </div>

                        </div>
                    <?php
                        $count_category++;
                    }
                }

            }

            return ob_get_clean();
        }
        public static function add_edit_theater_html( $action, $theater = null ) {
            // Prefill data if editing
            $theater_id          = $theater ? esc_attr( $theater['id'] ?? '' ) : '';
            $theater_name        = $theater ? esc_attr( $theater['name'] ?? '' ) : '';
            $theater_type        = $theater ? esc_attr( $theater['type'] ?? 'Standard' ) : 'Standard';
            $theater_rows        = $theater ? intval( $theater['theater_row'] ?? 0 ) : '';
            $theater_seats       = $theater ? intval( $theater['seats_per_row'] ?? 0 ) : '';
            $theater_sound       = $theater ? esc_attr( $theater['sound'] ?? 'Dolby Digital' ) : 'Dolby Digital';
            $theater_status      = $theater ? esc_attr( $theater['status'] ?? 'active' ) : 'active';
            $theater_description = $theater ? esc_textarea( $theater['description'] ?? '' ) : '';
            $theater_category    = $theater ?  $theater['theater_category'] ?? '' : '';

            /*$defaults = [
                'id'       => '',
                'name'       => '',
                'description'       => '',
                'status'    => '',
                'sound'      => '',
                'seats_per_row' => '',
                'releaseDate'      => '',
                'type' => '',
            ];
            $data = wp_parse_args( $theater, $defaults );*/

            $allowed_tags = WTBM_Details_Layout::show_html_element();
            ob_start(); ?>

            <div class="wtbm_add_edit_theater_container" id="wtbm_add_edit_theater_container">
                <h4 class="mb-4 font-semibold">
                    <?php
                    echo ! empty( $theater )
                        ? esc_html__( 'Edit Theater', 'wptheaterly' )
                        : esc_html__( 'Add New Theater', 'wptheaterly' );
                    ?>
                </h4>
                <input type="hidden" name="wtbp_theater_id" value="<?php echo esc_attr( $theater_id ); ?>">

                <?php wp_nonce_field( 'wtbp_theater_action', 'wtbp_theater_nonce' ); ?>

                <div class="grid grid-cols-2 mb-4">
                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Theater Name', 'wptheaterly' ); ?></label>
                        <input type="text" id="theater-name" class="form-input"
                               value="<?php echo esc_attr( $theater_name ) ; ?>"
                               placeholder="<?php esc_attr_e( 'Screen 1', 'wptheaterly' ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Theater Type', 'wptheaterly' ); ?></label>
                        <select id="theater-type" class="form-input">
                            <?php
                            $types = [ 'Standard', 'Premium', 'IMAX', 'VIP' ];
                            foreach ( $types as $type ) {
                                echo wp_kses('<option value="' . esc_attr( $type ) . '" ' .
                                    selected( $theater_type, $type, false ) . '>' .
                                    esc_html( $type ) .
                                    '</option>', $allowed_tags) ;

                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Rows', 'wptheaterly' ); ?></label>
                        <input type="number" id="theater-rows" class="form-input"
                               value="<?php echo esc_attr( $theater_rows ); ?>"
                               placeholder="<?php esc_attr_e( '8', 'wptheaterly' ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Seats per Row', 'wptheaterly' ); ?></label>
                        <input type="number" id="theater-seats-per-row" class="form-input"
                               value="<?php echo esc_attr( $theater_seats ); ?>"
                               placeholder="<?php esc_attr_e( '12', 'wptheaterly' ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Sound System', 'wptheaterly' ); ?></label>
                        <select id="theater-sound" class="form-input">
                            <?php
                            $sounds = [ 'Dolby Digital', 'Dolby Atmos', 'IMAX Enhanced' ];
                            foreach ( $sounds as $sound ) {
                                $selected = selected( $theater_sound, $sound, false );
                                echo  wp_kses('<option value="' . esc_attr( $sound ) . '"' . $selected . '>' . esc_html( $sound ) . '</option>', $allowed_tags);
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Status', 'wptheaterly' ); ?></label>
                        <select id="theater-status" class="form-input">
                            <?php
                            $statuses = [ 'active', 'maintenance', 'inactive' ];
                            foreach ( $statuses as $status ) {
                                echo wp_kses('<option value="' . esc_attr( $status ) . '" ' .
                                    selected( $theater_status, $status, false ) . '>' .
                                    esc_html( ucfirst( $status ) ) .
                                    '</option>', $allowed_tags );
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-group mb-4 wtbm_category_add_section">
                    <div id="wtbm_theater_categories_wrapper">
                        <!--Add category-->
                        <?php
                            $default_color = '#2e8708';
                            echo wp_kses( self::add_category( $action, $theater_category, $default_color ), $allowed_tags );
                        ?>
                    </div>

                    <button type="button" class="wtbm_theater_add_btn">+ <?php esc_html_e( 'Add Seating Category', 'wptheaterly' ); ?></button>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label"><?php esc_html_e( 'Description', 'wptheaterly' ); ?></label>
                    <textarea id="theater-description" class="form-input" rows="3"
                              placeholder="<?php esc_attr_e( 'Theater description', 'wptheaterly' ); ?>"><?php echo esc_html( $theater_description ); ?></textarea>
                </div>
                <?php if( $action === 'edit' ) { ?>
                <div class="tabsItem" data-tabs="#mptrs_seat_mapping">
                    <header>
                        <h2><?php esc_html_e('Seat Mapping', 'wptheaterly'); ?></h2>
                        <span><?php esc_html_e('In this section you will make table and seat for reservation.', 'wptheaterly'); ?></span>
                    </header>
                    <section class="mptrs-seat-mapping-section " id="wtbm_SeatMappingSection">
                        <?php
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            echo WTBM_Theater_Seat_Mapping::render_seat_mapping_meta_box( $theater_id, 'edit', $theater_rows, $theater_seats);
                        ?>
                    </section>
                </div>
                <?php } ?>

                <div class="flex gap-2">
                <?php if( $action === 'add' ){?>
                <button type="button" class="btn btn-success" id="wtbp_add_new_theater">
                    <?php echo $theater ? esc_html__( 'Update Theater', 'wptheaterly' ) : esc_html__( 'Save Theater & Load Seat Map', 'wptheaterly' ); ?>
                </button>
                <?php } else{?>
                    <button type="button" class="btn btn-success" id="wtbm_update_theater" date-theater-id="<?php echo esc_attr( $theater_id );?>">
                        <?php echo $theater ? esc_html__( 'Update Theater', 'wptheaterly' ) : esc_html__( 'Add Theater', 'wptheaterly' ); ?>
                    </button>
                <?php }?>
                <button type="button" class="btn btn-secondary" id="wtbm_clear_theater_from"><?php esc_html_e( 'Cancel', 'wptheaterly' ); ?></button>
            </div>
            </div>

            <?php if( $action === 'add' ) { ?>
                <div class="tabsItem" data-tabs="#mptrs_seat_mapping">
                    <header>
                        <h2><?php esc_html_e('Seat Mapping', 'wptheaterly'); ?></h2>
                        <span><?php esc_html_e('In this section you will make table and seat for reservation.', 'wptheaterly'); ?></span>
                    </header>
                    <section class="mptrs-seat-mapping-section " id="wtbm_SeatMappingSection"></section>
                </div>
            <?php } ?>
            <?php
            return ob_get_clean();
        }

        public static function display_theater_date( $theater_data ) {
            if ( empty( $theater_data ) || ! is_array( $theater_data ) ) {
                return ''; // security: no unexpected output
            }

            ob_start();

            foreach ( $theater_data as $theater ) {
                // Sanitize / escape values
                $id          = isset( $theater['id'] ) ? intval( $theater['id'] ) : 0;
                $name        = isset( $theater['name'] ) ? esc_html( $theater['name'] ) : '';
                $desc        = isset( $theater['description'] ) ? esc_html( $theater['description'] ) : '';
//                $status      = isset( $theater['status'] ) && $theater['status'] == 1 ? 'active' : 'inactive';
                $status      = isset( $theater['status'] ) ? $theater['status'] : 'inactive';
                $sound       = isset( $theater['sound'] ) ? esc_html( $theater['sound'] ) : '';
                $rows        = isset( $theater['theater_row'] ) ? intval( $theater['theater_row'] ) : 0;
                $seats_row   = isset( $theater['seats_per_row'] ) ? intval( $theater['seats_per_row'] ) : 0;
                $type        = isset( $theater['type'] ) ? esc_html( $theater['type'] ) : '';

                // Total seats
                $total_seats = $rows * $seats_row;
                ?>
                <tr id="theater_content_<?php echo esc_attr( $id );?>" data-id="<?php echo esc_attr( $id ); ?>">
                    <td>
                        <div class="font-medium text-gray-900"><?php echo esc_html( $name ); ?></div>
                        <div class="text-sm text-gray-500">
                            <?php echo esc_html( "{$rows} × {$seats_row} layout" ); ?>
                        </div>
                        <div class="text-xs text-gray-400"><?php echo esc_html( $desc ); ?></div>
                    </td>
                    <td class="text-sm text-gray-900"><?php echo esc_html( $type ); ?></td>
                    <td class="text-sm text-gray-900"><?php echo esc_html( "{$total_seats} seats" ); ?></td>
                    <td class="text-sm text-gray-900"><?php echo esc_html( $sound ); ?></td>
                    <td>
                <span class="status-badge status-<?php echo esc_attr( $status ); ?>">
                    <?php echo esc_html( $status ); ?>
                </span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <button
                                    class="btn-icon edit wtbm_edit_theater"
                                    data-theater-id="<?php echo esc_attr( $id ); ?>"
                                    title="<?php esc_attr_e( 'Edit Theater', 'wptheaterly' ); ?>">
                                <i class="mi mi-pencil"></i>
                            </button>
                            <button
                                    class="btn-icon delete wtbm_delete_theater"
                                    data-delete-theater-id="<?php echo esc_attr( $id ); ?>"
                                    title="<?php esc_attr_e( 'Delete Theater', 'wptheaterly' ); ?>">
                                <i class="mi mi-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php
            }

            return ob_get_clean();
        }

        /**
         * Get filtered booking data by meta keys
         *
         * @param array $filters_data
         * @return array
         */
        public static function wtbm_get_filtered_booking_data( array $filters_data = [], $loaded_booking_id = [], $display_limit = 20 ) {

            $meta_query = array( 'relation' => 'AND' );

            // Build meta query dynamically
            foreach ( $filters_data as $meta_key => $meta_value ) {

                if ( empty( $meta_value ) ) {
                    continue;
                }

                $meta_query[] = array(
                    'key'     => $meta_key,
                    'value'   => $meta_value,
                    'compare' => is_numeric( $meta_value ) ? '=' : 'LIKE',
                );
            }

            $args = array(
                'post_type'      => 'wtbm_booking',
                'post_status'    => 'publish',
                'posts_per_page' => $display_limit,
                'post__not_in'   => $loaded_booking_id,
                'fields'         => 'ids',
            );

            if ( count( $meta_query ) > 1 ) {
                $args['meta_query'] = $meta_query;
            }

            $query = new WP_Query( $args );
            $booking_data = array();
            $total_posts  = (int) $query->found_posts;
            $post_count = $query->post_count;

            if ( $query->have_posts() ) {
                foreach ( $query->posts as $booking_id ) {
                    $booking_meta = array();
                    $meta_data    = get_post_meta( $booking_id );
                    foreach ( $meta_data as $key => $value ) {
                        $booking_meta[ $key ] = maybe_unserialize( $value[0] );
                    }
                    $booking_data[ $booking_id ] = $booking_meta;
                }
            }

            wp_reset_postdata();

            return array(
                'booking_data' => $booking_data,
                'total_booking' => $total_posts,
                'total_post_count' => $post_count,
            );
        }

        public static function wtbm_get_booking_data_by_booking_id( $booking_id ) {

            $args = array(
                'post_type'   => 'wtbm_booking',
                'post_status' => 'publish',
                'p'           => $booking_id, // fetch this specific post
                'fields'      => 'ids',
            );

            $query = new WP_Query( $args );
            $booking_data = array();

            if ( $query->have_posts() ) {
                foreach ( $query->posts as $post_id ) {
                    $meta_data = get_post_meta( $post_id );
                    $booking_meta = array();

                    foreach ( $meta_data as $key => $value ) {
                        $booking_meta[ $key ] = maybe_unserialize( $value[0] );
                    }

                    $booking_data = $booking_meta; // only one booking, no array needed
                }
            }

            wp_reset_postdata();

            return $booking_data;
        }

        public static function display_single_movie_data( $movie_id ){
            if( $movie_id ){
                $movie_description = get_the_excerpt( $movie_id );
                $movie_poster_id = WTBM_Function::get_post_info( $movie_id, 'wtbp_movie_poster_id', '');
                $poster_url = wp_get_attachment_url( $movie_poster_id );
                $movie_active = WTBM_Function::get_post_info( $movie_id, 'wtbp_movie_active', '');
                $movie_release_date = WTBM_Function::get_post_info( $movie_id, 'wtbp_movie_release_date', '');
                $movie_rating = WTBM_Function::get_post_info( $movie_id, 'wtbp_movie_rating', '');
                $movie_duration = WTBM_Function::get_post_info( $movie_id, 'wtbp_movie_duration', '');
                $movie_genre = WTBM_Function::get_post_info( $movie_id, 'wtbp_movie_genre', '');
//                    $date = '2026-02-02';
                $date = gmdate("Y-m-d");
                $theater_show_times = WTBM_Details_Layout::display_theater_show_time_single_movie( $movie_id, $date );

                ?>
                <div class="wtbm_single_movie_wrapper">

                    <div class="wtbm_booking_movie_card wtbm_movieActive" style="visibility: hidden"
                         data-movie-id="<?php echo esc_attr( $movie_id ); ?>">
                    </div>

                    <!-- <div class="wtbm_single_movie_title"><?php /*echo esc_attr( get_the_title( $movie_id ) );*/?></div>
                        <div class="wtbm_single_movie_subtitle"><?php /*echo esc_attr( get_the_title( $movie_id ) );*/?></div>
-->
                    <?php if( 1 === 2 ){?>
                    <div class="wtbm_single_movie_card_description">
                        <div class="wtbm_single_movie_poster">
                            <img src="<?php echo esc_url( $poster_url );?>" alt="Avatar Poster" />
                        </div>
                        <div class="wtbm_single_movie_container">
                            <h1 class="wtbm_single_movie_movie_title"><?php echo esc_attr( get_the_title( $movie_id ) );?></h1>
                            <div class="" style="display: flex; gap: 10px">
                                <div class="wtbm_single_movie_meta">
                                    <?php esc_attr_e( 'Runtime', 'wptheaterly' );?>: <?php echo esc_attr( $movie_duration );?> | <?php esc_attr_e( 'Genre', 'wptheaterly' );?>: <?php echo esc_attr( $movie_genre );?> | <?php esc_attr_e( 'Rating', 'wptheaterly' );?>: <?php echo esc_attr( $movie_rating );?><br>
                                    <?php esc_attr_e( 'Director', 'wptheaterly' );?>: Sofia Martinez | <?php esc_attr_e( 'Starring', 'wptheaterly' );?>: James Chen, Elena Rodriguez<br>
                                    <?php esc_attr_e( 'Description', 'wptheaterly' );?>: <?php echo esc_html( $movie_description );?>
                                </div>
                                <!--<div class="wtbm_single_movie_meta" style="display: flex; flex-direction: column">
                                    <span class="wtbm_single_movie_badge">Thriller / Mystery</span>
                                    <span class="wtbm_single_movie_badge">Action</span>
                                    <span class="wtbm_single_movie_badge">Adventure</span>
                                    <span class="wtbm_single_movie_badge">Fantasy</span>
                                    <span>PG‑13</span>
                                    <span>3h 12m</span>
                                </div>-->
                            </div>

                        </div>

                    </div>
                    <?php }?>

                    <div class="wtbm_single_movie_card">
                        <div class="wtbm_single_movie_section_title"><?php esc_attr_e( 'Select Date', 'wptheaterly' );?></div>
                        <div class="wtbm_single_movie_options wtbm_date">
                            <?php
                            echo wp_kses_post( WTBM_Details_Layout::booking_date_display_single_movie( $movie_id ) );
                            ?>
                        </div>

                        <div class="wtbm_single_movie_section_title" style="margin-top:20px"><?php esc_attr_e( 'Select Time', 'wptheaterly' );?></div>
                        <div class="wtbm_single_movie_options wtbm_time">
                            <?php
                            $today_date = gmdate('M d, y');
                            echo wp_kses_post( $theater_show_times );
                            ?>
                        </div>
                    </div>

                    <div class="" style="display: flex; gap: 20px">
                        <div class="wtbm_single_movie_card" style="width: calc( 100% - 170px)">
                            <div class="wtbm_single_movie_section_title"><?php esc_attr_e( 'Select Your Seats', 'wptheaterly' );?></div>
                            <div class="wtbm_single_movie_screen"><?php esc_attr_e( 'SCREEN', 'wptheaterly' );?></div>
                            <div class="wtbm_single_movie_screen_bar"></div>
                            <div class="wtbm_single_movie_seats" id="wtbm_single_movie_seats">

                            </div>
                        </div>
                        <div class="wtbm_single_movie_card" id="wtbm_single_movie_booking_card" style="width: 170px; display: none">
                            <div class="wtbm_single_movie_summary">
                                <div class="wtbm_singleRegistrationSidebar" id="wtbm_registrationSidebar">
                                    <h2 class="section-title"><?php esc_attr_e( 'Tickets Summary', 'wptheaterly' );?></h2>

                                    <div class="wtbm_registrationSummaryCard">
                                        <input type="hidden" name="wtbm_summeryMovieId" id="wtbm_summeryMovieId" value="<?php echo esc_attr( $movie_id );?>">
                                        <input type="hidden" name="wtbm_summeryTheaterId" id="wtbm_summeryTheaterId" value="">
                                        <input type="hidden" name="wtbm_summeryDate" id="wtbm_summeryDate" value="<?php echo esc_attr( gmdate("Y-m-d") );?>">
                                        <input type="hidden" name="wtbm_summeryTime" id="wtbm_summeryTime" value="">
                                        <input type="hidden" name="wtbm_summerySeatType" id="wtbm_summerySeatType" value="">
                                        <input type="hidden" name="wtbm_summerySeatNumber" id="wtbm_summerySeatNumber" value="">
                                        <input type="hidden" name="wtbm_summerySeatIds" id="wtbm_summerySeatIds" value="">
                                        <input type="hidden" name="wtbm_summeryTotalAmount" id="wtbm_summeryTotalAmount" value="">

                                        <!--                                            <div id="wtbm_selectedMovieDisplay"></div>-->

                                        <div class="wtbm_registrationSummaryItem" style="display: none">
                                            <span><i class="mi mi-stage-theatre"></i> <?php esc_attr_e( 'Hall Name:', 'wptheaterly' );?></span>
                                            <span id="wtbm_summaryTheaterName">--</span>
                                        </div>
                                        <div class="wtbm_registrationSummaryItem" style="display: none; flex-direction: column">
                                            <span><i class="mi mi-calendar"></i> <?php esc_attr_e( 'Show Date:', 'wptheaterly' );?></span>
                                            <span id="wtbm_summaryDateDisplay"><?php echo esc_attr( $today_date );?></span>
                                        </div>
                                        <div class="wtbm_registrationSummaryItem" style="display: none; flex-direction: column">
                                            <span><i class="mi mi-clock-three"></i> <?php esc_attr_e( 'Show Time:', 'wptheaterly' );?></span>
                                            <span id="wtbm_summaryTimeSlot">--</span>
                                        </div>

                                        <div class="wtbm_registrationSummaryItem" style="display: flex; flex-direction: column">
                                            <span><i class="mi mi-loveseat"></i> <?php esc_attr_e( 'Selected Seat:', 'wptheaterly' );?></span>
                                            <span id="wtbm_summarySeats">--</span>
                                        </div>

                                        <div class="wtbm_registrationSummaryItem" style="display: flex; flex-direction: column">
                                            <span><i class="mi mi-ticket"></i> <?php esc_attr_e( 'Ticket Quantity:', 'wptheaterly' );?></span>
                                            <span id="wtbm_summaryQuantity">0</span>
                                        </div>

                                        <div class="wtbm_registrationSummaryItem" style="display: flex; flex-direction: column">
                                            <span><i class="mi mi-coins"></i> <?php esc_attr_e( 'Total Amount:', 'wptheaterly' );?></span>
                                            <div class="wtbm_totalPriceSymbol">
                                                <span id="wtbm_summaryTotal">0 </span>
                                                <span class="wtbm_currency"><?php echo esc_attr( get_woocommerce_currency_symbol());?></span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="booking-form">
                                        <h2 class="section-title"><?php esc_attr_e( 'Ticket For', 'wptheaterly' );?></h2>
                                        <div class="form-group">
                                            <label class="form-label"><?php esc_attr_e( 'Full Name', 'wptheaterly' );?></label>
                                            <input type="text" class="form-input" id="wtbm_getUserName" placeholder="Enter your name">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label"><?php esc_attr_e( 'Mobile Number', 'wptheaterly' );?></label>
                                            <input type="tel" class="form-input" id="wtbm_getUserPhone" placeholder="Enter mobile number">
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!--                            <button class="wtbm_single_movie_button">COMPLETE BOOKING</button>-->
                            <button class="wtbm_single_movie_button" id="wtbm_ticketPurchaseBtn" ><?php esc_attr_e( 'PURCHASE TICKET', 'wptheaterly' );?></button>
                            <div class="purchase-info">
                                <?php esc_attr_e( 'By clicking the Purchase Tickets you are accepting Terms &amp; Conditions of Star Cineplex', 'wptheaterly' );?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php
            }
        }


    }

    new WTBM_Layout_Functions();
}