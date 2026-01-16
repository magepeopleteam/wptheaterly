<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('WTBM_Dummy_Import')) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		class WTBM_Dummy_Import {
			public function __construct() {
				$this->dummy_import();
				// add_action('admin_init', [$this, 'dummy_import'], 99);
			}

			public function dummy_import() {
				$dummy_post_inserted = get_option('mptrs_dummy_already_inserted');
				if ($dummy_post_inserted) {
					return;
				}
				$count_existing_event = wp_count_posts('wtbm_movie')->publish;
				$plugin_active = self::check_plugin('wptheaterly', 'wp_theater.php');
				if ($count_existing_event == 0 && $plugin_active == 1 && $dummy_post_inserted != 'yes') {
					$this->create_dummy_page();
					$movie_data = $this->movie_data();
					$theater_data = $this->theater_data();
					$dummy_pricing = $this->dummy_pricing();
					$movie_ids = $this->insert_posts($movie_data, 'wtbm_movie');
					$this->insert_posts($dummy_pricing, 'wtbm_pricing');
					$this->insert_thumbnails($movie_ids,'wtbp_movie_poster_id');
					$theater_ids = $this->insert_posts($theater_data, 'wtbm_theater');
					$this->insert_showtime($movie_ids,$theater_ids);
					update_option('mptrs_dummy_already_inserted', 'yes');
				}
			}
			public function dummy_pricing(){
				return [
					'taxonomy' => [],
					'custom_post' => [
						'wtbm_pricing' =>[
							[
								'post_title' => 'Rule 1',
								'meta_data' => [
									'wtbp_pricing_rules_theaterType' => '101',
									'wtbp_pricing_rules_type' => '144',
									'wtbp_pricing_rules_dateRange' => gmdate('Y-m-d', strtotime(' +1 day')),
									'wtbp_pricing_rules_startDate' => gmdate('Y-m-d', strtotime(' +1 day')),
									'wtbp_pricing_rules_endDate' => gmdate('Y-m-d', strtotime(' +30 day')),
									'wtbp_pricing_rules_priority' => '5',
									'wtbp_pricing_rules_multiplier' => '5',
									'wtbp_pricing_rules_active' => 'yes',
									
								],
							],
						]
					]
				];
			}
			public function insert_posts($posts,$post_type){
				foreach ($posts as $data) {
					// Insert post
					$post = [
						'post_type' => $post_type,
						'post_title' => $data['post_title'],
						'post_status' => 'publish',
					];
					$post_id = wp_insert_post($post);
					// Add meta data
					if (!is_wp_error($post_id)) {
						foreach ($data['meta_data'] as $meta_key => $meta_value) {
							update_post_meta($post_id, $meta_key, $meta_value);
						}
					}
					$post_ids[] = $post_id;
				}
				return $post_ids;
			}
			public function insert_thumbnails($postsids,$meta_key=''){
				$attachment_ids = self::dummy_images();
				foreach ( $postsids as $index => $post_id ) {
					$attachment_id = $attachment_ids[ $index ];
					set_post_thumbnail( $post_id, $attachment_id );
					if($meta_key!=''){
						update_post_meta( $post_id, $meta_key, $attachment_id );
					}
					
				}
			}
			public function insert_showtime( $movie_ids, $theater_ids ) {
				$show_time_data = $this->show_time_data();
				foreach ( $movie_ids as $index => $movie_id ) {
					$post_id = wp_insert_post([
						'post_type'   => 'wtbm_show_time',
						'post_title'  => get_the_title( $movie_id ),
						'post_status' => 'publish',
					], true);
					if ( is_wp_error( $post_id ) ) {
						continue;
					}
					update_post_meta( $post_id, 'wtbp_show_time_movieId', $movie_id );
					update_post_meta(
						$post_id,
						'wtbp_show_time_theaterId',
						$theater_ids[ $index % count( $theater_ids ) ]
					);
					foreach ( $show_time_data['meta_data'] as $meta_key => $meta_value ) {
						update_post_meta( $post_id, $meta_key, $meta_value );
					}
				}
			}

			public function movie_data(){
				return [
					[
						'post_title' => 'Midnight Café',
						'meta_data' => [
							'wtbp_movie_release_date' => '2020-12-05',
							'wtbp_movie_genre'        => 'Comedy, Slice of Life',
							'wtbp_movie_duration'     => '1h 45m',
							'wtbp_movie_rating'       => '7.5',
							'wtbp_movie_active'       => 'true',
							'wtbp_movie_poster_id'    => [0],
						],
					],
					[
						'post_title' => 'The Final Pitch',
						'meta_data' => [
							'wtbp_movie_release_date' => '2021-08-20',
							'wtbp_movie_genre'        => 'Sports, Drama',
							'wtbp_movie_duration'     => '1h 58m',
							'wtbp_movie_rating'       => '7.8',
							'wtbp_movie_active'       => 'true',
							'wtbp_movie_poster_id'    => [1],
						],
					],
					[
						'post_title' => 'Neon City',
						'meta_data' => [
							'wtbp_movie_release_date' => '2024-06-01',
							'wtbp_movie_genre'        => 'Cyberpunk, Sci-Fi',
							'wtbp_movie_duration'     => '2h 20m',
							'wtbp_movie_rating'       => '8.6',
							'wtbp_movie_active'       => 'true',
							'wtbp_movie_poster_id'    => [2],
						],
					],
					[
						'post_title' => 'Iron Divide',
						'meta_data' => [
							'wtbp_movie_release_date' => '2019-09-09',
							'wtbp_movie_genre'        => 'War, History',
							'wtbp_movie_duration'     => '2h 50m',
							'wtbp_movie_rating'       => '8.0',
							'wtbp_movie_active'       => 'true',
							'wtbp_movie_poster_id'    => [3],
						],
					],
					[
						'post_title' => 'Echoes of Time',
						'meta_data' => [
							'wtbp_movie_release_date' => '2023-02-14',
							'wtbp_movie_genre'        => 'Fantasy, Romance',
							'wtbp_movie_duration'     => '2h 25m',
							'wtbp_movie_rating'       => '8.5',
							'wtbp_movie_active'       => 'true',
							'wtbp_movie_poster_id'    => [4],
						],
					],
					[
						'post_title' => 'Velocity',
						'meta_data' => [
							'wtbp_movie_release_date' => '2022-05-18',
							'wtbp_movie_genre'        => 'Action, Crime',
							'wtbp_movie_duration'     => '2h 10m',
							'wtbp_movie_rating'       => '8.1',
							'wtbp_movie_active'       => 'true',
							'wtbp_movie_poster_id'    => [5],
						],
					],
					[
						'post_title' => 'Crimson Night',
						'meta_data' => [
							'wtbp_movie_release_date' => '2020-10-31',
							'wtbp_movie_genre'        => 'Horror, Mystery',
							'wtbp_movie_duration'     => '2h 05m',
							'wtbp_movie_rating'       => '7.6',
							'wtbp_movie_active'       => 'true',
							'wtbp_movie_poster_id'    => [6],
						],
					],
					[
						'post_title' => 'Broken Silence',
						'meta_data' => [
							'wtbp_movie_release_date' => '2021-11-21',
							'wtbp_movie_genre'        => 'Drama',
							'wtbp_movie_duration'     => '1h 55m',
							'wtbp_movie_rating'       => '7.9',
							'wtbp_movie_active'       => 'true',
							'wtbp_movie_poster_id'    => [7],
						],
					],
					[
						'post_title' => 'Last Horizon',
						'meta_data' => [
							'wtbp_movie_release_date' => '2023-07-08',
							'wtbp_movie_genre'        => 'Sci-Fi, Adventure',
							'wtbp_movie_duration'     => '2h 40m',
							'wtbp_movie_rating'       => '8.7',
							'wtbp_movie_active'       => 'true',
							'wtbp_movie_poster_id'    => [8],
						],
					],
					[
						'post_title' => 'Shadow Protocol',
						'meta_data' => [
							'wtbp_movie_release_date' => '2022-03-12',
							'wtbp_movie_genre'        => 'Action, Thriller',
							'wtbp_movie_duration'     => '2h 15m',
							'wtbp_movie_rating'       => '8.3',
							'wtbp_movie_active'       => 'true',
							'wtbp_movie_poster_id'    => [9],
						],
					],
				];
			}
			public function theater_data(){
				return [
					[
						'post_title' => 'Screen 1',
						'meta_data' => [
							'wtbp_theater_type' => 'Standard',
							'wtbp_theater_rows' => '12',
							'wtbp_theater_seatsPerRow' => '20',
							'wtbp_theater_soundSystem' => 'Dolby Digital',
							'wtbp_theater_status' => 'active',
							'wtbp_theater_category' => [
								[
									'category_id'   => 'vip',
									'category_name' => 'VIP',
									'seats'         => '20',
									'price'         => '1500',
									'color'         => '#ff0000',
								],
								[
									'category_id'   => 'premium',
									'category_name' => 'Premium',
									'seats'         => '30',
									'price'         => '1000',
									'color'         => '#0000ff',
								],
								[
									'category_id'   => 'regular',
									'category_name' => 'Regular',
									'seats'         => '50',
									'price'         => '700',
									'color'         => '#00ff00',
								]
							],
						],
					],
					[
						'post_title' => 'Screen 2',
						'meta_data' => [
							'wtbp_theater_type' => 'Premium',
							'wtbp_theater_rows' => '12',
							'wtbp_theater_seatsPerRow' => '20',
							'wtbp_theater_soundSystem' => 'Dolby Atmos',
							'wtbp_theater_status' => 'active',
							'wtbp_theater_category' => [
								[
									'category_id'   => 'vip',
									'category_name' => 'VIP',
									'seats'         => '20',
									'price'         => '1500',
									'color'         => '#ff0000',
								],
								[
									'category_id'   => 'premium',
									'category_name' => 'Premium',
									'seats'         => '30',
									'price'         => '1000',
									'color'         => '#0000ff',
								],
								[
									'category_id'   => 'regular',
									'category_name' => 'Regular',
									'seats'         => '50',
									'price'         => '700',
									'color'         => '#00ff00',
								]
							],
						],
					],
					[
						'post_title' => 'Screen 3',
						'meta_data' => [
							'wtbp_theater_type' => 'IMAX',
							'wtbp_theater_rows' => '10',
							'wtbp_theater_seatsPerRow' => '10',
							'wtbp_theater_soundSystem' => 'Dolby Atmos',
							'wtbp_theater_status' => 'active',
							'wtbp_theater_category' => [
								[
									'category_id'   => 'vip',
									'category_name' => 'VIP',
									'seats'         => '20',
									'price'         => '1500',
									'color'         => '#ff0000',
								],
								[
									'category_id'   => 'premium',
									'category_name' => 'Premium',
									'seats'         => '30',
									'price'         => '1000',
									'color'         => '#0000ff',
								],
								[
									'category_id'   => 'regular',
									'category_name' => 'Regular',
									'seats'         => '50',
									'price'         => '700',
									'color'         => '#00ff00',
								]
							],
						],
					],
					[
						'post_title' => 'Screen 4',
						'meta_data' => [
							'wtbp_theater_type' => 'VIP',
							'wtbp_theater_rows' => '10',
							'wtbp_theater_seatsPerRow' => '10',
							'wtbp_theater_soundSystem' => 'IMAX Enhanced',
							'wtbp_theater_status' => 'active',
							'wtbp_theater_category' => [
								[
									'category_id'   => 'vip',
									'category_name' => 'VIP',
									'seats'         => '20',
									'price'         => '1500',
									'color'         => '#ff0000',
								],
								[
									'category_id'   => 'premium',
									'category_name' => 'Premium',
									'seats'         => '30',
									'price'         => '1000',
									'color'         => '#0000ff',
								],
								[
									'category_id'   => 'regular',
									'category_name' => 'Regular',
									'seats'         => '50',
									'price'         => '700',
									'color'         => '#00ff00',
								]
							],
						],
					],
				];
			}
			public function show_time_data() {
				return [
					'post_title' => 'Show Time',
					'meta_data'  => [
						'wtbp_show_time_date'        => gmdate( 'Y-m-d', strtotime('+1 day') ),
						'wtbp_show_time_start_date'  => '11.00',
						'wtbp_showtime_start_date'   => gmdate( 'Y-m-d', strtotime('+1 day') ),
						'wtbp_showtime_end_date'     => gmdate( 'Y-m-d', strtotime('+30 day') ),
						'wtbp_show_time_price'       => '11',
						'wtbp_showtime_off_days'     => 'monday',
					],
				];
			}

			private function dummy_images() {
				$urls = array(
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/wptheaterly/midnight-cafe.jpg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/wptheaterly/the-final-pitch.jpg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/wptheaterly/neon-city.jpg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/wptheaterly/iron-divide.jpg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/wptheaterly/echoes-of-time.jpg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/wptheaterly/velocity.jpg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/wptheaterly/crimson-night.jpg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/wptheaterly/broken-silence.jpg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/wptheaterly/last-horizon.jpg',
						'https://raw.githubusercontent.com/magepeopleteam/dummy-images/main/wptheaterly/shadow-protocol.jpg',
					);

				unset($image_ids);
				$image_ids = array();
				foreach ($urls as $url) {
					$image_ids[] = media_sideload_image($url, '0', 'Dummy Images', 'id');
				}
				return $image_ids;
			}
			public function create_dummy_page() {
				$pages_to_create = [
					'find' => [
						'slug' => 'booking',
						'title' => 'Booking Ticket',
						'content' => '[wtbm_ticket_booking]',
						'option_key' => 'mptrs_booking_page_created',
					]
				];
				foreach ($pages_to_create as $page_data) {
					$existing_page = get_page_by_path( $page_data['slug'], OBJECT, 'page' );
					if ( $existing_page ) {
						return;
					}
					$page = [
						'post_type' => 'page',
						'post_name' => $page_data['slug'],
						'post_title' => $page_data['title'],
						'post_content' => $page_data['content'],
						'post_status' => 'publish',
					];
					$page_id = wp_insert_post($page);
					if (is_wp_error($page_id)) {
						printf('<div class="notice notice-error"><p>%s</p></div>', esc_html($page_id->get_error_message()));
					} else {
						update_option($page_data['option_key'], true);
					}
					
				}
			}
			public static function check_plugin($plugin_dir_name, $plugin_file): int {
				include_once ABSPATH . 'wp-admin/includes/plugin.php';
				$plugin_dir = ABSPATH . 'wp-content/plugins/' . $plugin_dir_name;
				if (is_plugin_active($plugin_dir_name . '/' . $plugin_file)) {
					return 1;
				}
				elseif (is_dir($plugin_dir)) {
					return 2;
				}
				else {
					return 0;
				}
			}
		}
				
	}