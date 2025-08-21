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

        public static function add_edit_new_movie_html( $add, $data = [] ) {
            $defaults = [
                'id'       => '',
                'title'       => '',
                'genre'       => '',
                'duration'    => '',
                'rating'      => '',
                'releaseDate' => '',
                'poster'      => '',
                'description' => '',
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
                        <label class="form-label"><?php esc_html_e( 'Movie Title', 'theaterly' ); ?></label>
                        <input type="text" id="movie-title" class="form-input"
                               placeholder="<?php esc_attr_e( 'Movie Title', 'theaterly' ); ?>"
                               value="<?php echo esc_attr( $data['title'] ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Genre', 'theaterly' ); ?></label>
                        <input type="text" id="movie-genre" class="form-input"
                               placeholder="<?php esc_attr_e( 'Genre', 'theaterly' ); ?>"
                               value="<?php echo esc_attr( $data['genre'] ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Duration', 'theaterly' ); ?></label>
                        <input type="text" id="movie-duration" class="form-input"
                               placeholder="<?php esc_attr_e( '2h 30m', 'theaterly' ); ?>"
                               value="<?php echo esc_attr( $data['duration'] ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Rating', 'theaterly' ); ?></label>
                        <input type="number" id="movie-rating" class="form-input" step="0.1"
                               placeholder="<?php esc_attr_e( '8.5', 'theaterly' ); ?>"
                               value="<?php echo esc_attr( $data['rating'] ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Release Date', 'theaterly' ); ?></label>
                        <input type="date" id="movie-release-date" class="form-input"
                               value="<?php echo esc_attr( $data['releaseDate'] ); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label"><?php esc_html_e( 'Poster URL', 'theaterly' ); ?></label>
                        <input type="url" id="movie-poster" class="form-input"
                               placeholder="https://..."
                               value="<?php echo esc_url( $data['poster'] ); ?>">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label"><?php esc_html_e( 'Description', 'theaterly' ); ?></label>
                    <textarea id="movie-description" class="form-input" rows="3"
                              placeholder="<?php esc_attr_e( 'Movie description', 'theaterly' ); ?>"><?php
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
                <button class="btn btn-secondary" >Cancel</button>
            </div>
            <?php

            return ob_get_clean();
        }
        public static function get_and_display_movies() {
            // WP_Query args
            $args = [
                'post_type'      => 'wtbm_movie',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ];

            $query = new WP_Query( $args );

            if ( ! $query->have_posts() ) {
                echo '<tr><td colspan="6">' . esc_html__( 'No movies found.', 'theaterly' ) . '</td></tr>';
                return;
            }

            $movie_data = [];

            while ( $query->have_posts() ) {
                $query->the_post();

                $movie_data[] = [
                    'id'          => get_the_ID(),
                    'title'       => get_the_title(),
                    'genre'       => get_post_meta( get_the_ID(), 'wtbp_movie_genre', true ),
                    'duration'    => get_post_meta( get_the_ID(), 'wtbp_movie_duration', true ),
                    'rating'      => get_post_meta( get_the_ID(), 'wtbp_movie_rating', true ),
                    'releaseDate' => get_post_meta( get_the_ID(), 'wtbp_movie_release_date', true ),
                    'poster'      => get_post_meta( get_the_ID(), 'wtbp_movie_poster', true ),
                    'status'      => get_post_meta( get_the_ID(), 'wtbm_status', true ) ?: 'inactive',
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
                    'id'          => get_the_ID(),
                    'title'       => get_the_title(),
                    'description' => get_the_content(),
                    'genre'       => get_post_meta( get_the_ID(), 'wtbp_movie_genre', true ),
                    'duration'    => get_post_meta( get_the_ID(), 'wtbp_movie_duration', true ),
                    'rating'      => get_post_meta( get_the_ID(), 'wtbp_movie_rating', true ),
                    'releaseDate' => get_post_meta( get_the_ID(), 'wtbp_movie_release_date', true ),
                    'poster'      => get_post_meta( get_the_ID(), 'wtbp_movie_poster', true ),
                    'status'      => get_post_meta( get_the_ID(), 'wtbm_status', true ) ?: 'active',
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
                'post_type'      => MPTRS_Function::get_theater_cpt(),
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
                    'id'          => get_the_ID(),
                    'name'       => get_the_title(),
                    'description' => get_the_content(),
                    'status'       => get_post_meta( get_the_ID(), 'wtbp_theater_status', true ),
                    'sound'    => get_post_meta( get_the_ID(), 'wtbp_theater_soundSystem', true ),
                    'seats_per_row'      => get_post_meta( get_the_ID(), 'wtbp_theater_seatsPerRow', true ),
                    'releaseDate' => get_post_meta( get_the_ID(), 'wtbp_theater_rows', true ),
                    'type'      => get_post_meta( get_the_ID(), 'wtbp_theater_type', true ),
                ];
            }
            wp_reset_postdata();

            return $movie_data;
        }
        public static function display_movies_data( $movie_data = [] ) {
            if ( empty( $movie_data ) || ! is_array( $movie_data ) ) {
                return;
            }

            foreach ( $movie_data as $movie ) {
                $defaults = [
                    'id'       => '',
                    'title'       => '',
                    'genre'       => '',
                    'duration'    => '',
                    'rating'      => '',
                    'releaseDate' => '',
                    'poster'      => 'https://via.placeholder.com/200x300/4A90E2/ffffff?text=No+Poster',
                    'status'      => 'active',
                ];
                $movie = wp_parse_args( $movie, $defaults );

                // Escape outputs
                $id          = esc_html( $movie['id'] );
                $title       = esc_html( $movie['title'] );
                $genre       = esc_html( $movie['genre'] );
                $duration    = esc_html( $movie['duration'] );
                $rating      = esc_html( $movie['rating'] );
                $releaseDate = esc_html( $movie['releaseDate'] );
                $poster      = esc_url( $movie['poster'] );
                $status      = esc_html( $movie['status'] );
                ?>
                <tr class="twbm_movie_content" id="movie_content_<?php echo esc_attr( $id );?>" date-movie-id="<?php echo esc_attr( $id );?>">
                    <td>
                        <div class="flex items-center">
                            <img src="<?php echo $poster; ?>"
                                 alt="<?php echo $title; ?>"
                                 class="movie-poster" loading="lazy">
                            <div>
                                <div class="font-medium text-gray-900"><?php echo $title; ?></div>
                                <?php if ( $releaseDate ) : ?>
                                    <div class="text-sm text-gray-500">
                                        <?php echo sprintf( __( 'Released: %s', 'theaterly' ), $releaseDate ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm text-gray-900"><?php echo $genre; ?></td>
                    <td class="text-sm text-gray-900"><?php echo $duration; ?></td>
                    <td class="text-sm font-medium">⭐ <?php echo $rating; ?></td>
                    <td>
                <span class="status-badge status-<?php echo esc_attr( strtolower( $status ) ); ?>">
                    <?php echo $status; ?>
                </span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <button class="btn-icon edit wrbm_edit_movie" id="wrbm_edit_<?php echo esc_attr( $id );?>"
                                    title="<?php esc_attr_e( 'Edit Movie', 'theaterly' ); ?>">✏️</button>
                            <button class="btn-icon delete" id="wrbm_delete_<?php echo esc_attr( $id );?>"
                                    title="<?php esc_attr_e( 'Delete Movie', 'theaterly' ); ?>">🗑️</button>
                        </div>
                    </td>
                </tr>
                <?php
            }
        }

        public static function add_edit_theater_html_old(){

            ob_start();
            ?>
            <h4 class="mb-4 font-semibold">Add New Theater</h4>
            <div class="grid grid-cols-2 mb-4">
                <div class="form-group">
                    <label class="form-label">Theater Name</label>
                    <input type="text" id="theater-name" class="form-input" placeholder="Screen 1">
                </div>
                <div class="form-group">
                    <label class="form-label">Theater Type</label>
                    <select id="theater-type" class="form-input">
                        <option value="Standard">Standard</option>
                        <option value="Premium">Premium</option>
                        <option value="IMAX">IMAX</option>
                        <option value="VIP">VIP</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Rows</label>
                    <input type="number" id="theater-rows" class="form-input" placeholder="8">
                </div>
                <div class="form-group">
                    <label class="form-label">Seats per Row</label>
                    <input type="number" id="theater-seats-per-row" class="form-input" placeholder="12">
                </div>
                <div class="form-group">
                    <label class="form-label">Sound System</label>
                    <select id="theater-sound" class="form-input">
                        <option value="Dolby Digital">Dolby Digital</option>
                        <option value="Dolby Atmos">Dolby Atmos</option>
                        <option value="IMAX Enhanced">IMAX Enhanced</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select id="theater-status" class="form-input">
                        <option value="active">Active</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="form-group mb-4">
                <label class="form-label">Description</label>
                <textarea id="theater-description" class="form-input" rows="3" placeholder="theater description"></textarea>
            </div>
            <div class="flex gap-2">
                <button class="btn btn-success" id="wtbp_add_new_theater">Add Theater</button>
                <button class="btn btn-secondary" >Cancel</button>
            </div>
            <?php
            return ob_get_clean();
        }

        public static function add_edit_theater_html( $action, $theater = null ) {
            // Prefill data if editing
            $theater_name        = $theater ? esc_attr( $theater['name'] ?? '' ) : '';
            $theater_type        = $theater ? esc_attr( $theater['type'] ?? 'Standard' ) : 'Standard';
            $theater_rows        = $theater ? intval( $theater['rows'] ?? 0 ) : '';
            $theater_seats       = $theater ? intval( $theater['seats_per_row'] ?? 0 ) : '';
            $theater_sound       = $theater ? esc_attr( $theater['sound'] ?? 'Dolby Digital' ) : 'Dolby Digital';
            $theater_status      = $theater ? esc_attr( $theater['status'] ?? 'active' ) : 'active';
            $theater_description = $theater ? esc_textarea( $theater['description'] ?? '' ) : '';

            ob_start(); ?>

            <h4 class="mb-4 font-semibold">
                <?php echo $theater ? esc_html__( 'Edit Theater', 'your-textdomain' ) : esc_html__( 'Add New Theater', 'your-textdomain' ); ?>
            </h4>

            <?php wp_nonce_field( 'wtbp_theater_action', 'wtbp_theater_nonce' ); ?>

            <div class="grid grid-cols-2 mb-4">
                <div class="form-group">
                    <label class="form-label"><?php esc_html_e( 'Theater Name', 'your-textdomain' ); ?></label>
                    <input type="text" id="theater-name" class="form-input"
                           value="<?php echo $theater_name; ?>"
                           placeholder="<?php esc_attr_e( 'Screen 1', 'your-textdomain' ); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label"><?php esc_html_e( 'Theater Type', 'your-textdomain' ); ?></label>
                    <select id="theater-type" class="form-input">
                        <?php
                        $types = [ 'Standard', 'Premium', 'IMAX', 'VIP' ];
                        foreach ( $types as $type ) {
                            echo '<option value="' . esc_attr( $type ) . '" ' .
                                selected( $theater_type, $type, false ) . '>' .
                                esc_html( $type ) .
                                '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php esc_html_e( 'Rows', 'your-textdomain' ); ?></label>
                    <input type="number" id="theater-rows" class="form-input"
                           value="<?php echo $theater_rows; ?>"
                           placeholder="<?php esc_attr_e( '8', 'your-textdomain' ); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label"><?php esc_html_e( 'Seats per Row', 'your-textdomain' ); ?></label>
                    <input type="number" id="theater-seats-per-row" class="form-input"
                           value="<?php echo $theater_seats; ?>"
                           placeholder="<?php esc_attr_e( '12', 'your-textdomain' ); ?>">
                </div>

                <div class="form-group">
                    <label class="form-label"><?php esc_html_e( 'Sound System', 'your-textdomain' ); ?></label>
                    <select id="theater-sound" class="form-input">
                        <?php
                        $sounds = [ 'Dolby Digital', 'Dolby Atmos', 'IMAX Enhanced' ];
                        foreach ( $sounds as $sound ) {
                            $selected = selected( $theater_sound, $sound, false );
                            echo '<option value="' . esc_attr( $sound ) . '"' . $selected . '>' . esc_html( $sound ) . '</option>';
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php esc_html_e( 'Status', 'your-textdomain' ); ?></label>
                    <select id="theater-status" class="form-input">
                        <?php
                        $statuses = [ 'active', 'maintenance', 'inactive' ];
                        foreach ( $statuses as $status ) {
                            echo '<option value="' . esc_attr( $status ) . '" ' .
                                selected( $theater_status, $status, false ) . '>' .
                                esc_html( ucfirst( $status ) ) .
                                '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>

            <div class="form-group mb-4">
                <label class="form-label"><?php esc_html_e( 'Description', 'your-textdomain' ); ?></label>
                <textarea id="theater-description" class="form-input" rows="3"
                          placeholder="<?php esc_attr_e( 'Theater description', 'your-textdomain' ); ?>"><?php echo $theater_description; ?></textarea>
            </div>

            <div class="flex gap-2">
                <button type="button" class="btn btn-success" id="wtbp_add_new_theater">
                    <?php echo $theater ? esc_html__( 'Update Theater', 'your-textdomain' ) : esc_html__( 'Add Theater', 'your-textdomain' ); ?>
                </button>
                <button type="button" class="btn btn-secondary"><?php esc_html_e( 'Cancel', 'your-textdomain' ); ?></button>
            </div>

            <?php
            return ob_get_clean();
        }


    }

    new WTBM_Layout_Functions();
}