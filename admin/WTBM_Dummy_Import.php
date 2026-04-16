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

			public function insert_posts($posts, $post_type) {
				$post_ids = [];

				// Ensure $posts is an array to avoid foreach errors
				if (!is_array($posts)) {
					return $post_ids;
				}

				foreach ($posts as $data) {
					// Use ?? to provide a default empty string if the key is missing
					$post = [
						'post_type' => $post_type,
						'post_title' => $data['post_title'],
						'post_content' => isset($data['post_content']) ? $data['post_content'] : '',
						'post_status' => 'publish',
					];

					$post_id = wp_insert_post($post);

					// Add meta data only if the ID is valid and the key exists
					if (!is_wp_error($post_id)) {
						$meta_data = $data['meta_data'] ?? []; // Default to empty array if missing
						
						if (is_array($meta_data)) {
							foreach ($meta_data as $meta_key => $meta_value) {
								update_post_meta($post_id, $meta_key, $meta_value);
							}
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
				$show_times_data = $this->show_time_data();
				foreach ( $show_times_data as $show_time_data ) {
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
			}

			public function movie_data(){
				return[
						[
							'post_title' => 'Midnight Café',
							'post_content' => 'A cozy story set in a quaint café where strangers become friends over coffee and conversations.',
							'meta_data' => [
								'wtbp_movie_release_date' => '2020-12-05',
								'wtbp_movie_genre'        => 'Comedy',
								'wtbm_movie_status'			=>'showing',
								'wtbp_movie_duration'     => '1h 45m',
								'wtbp_movie_director'     => 'John Doe',
								'wtbp_movie_actors'       => 'Jane Smith, Bob Johnson',
								'wtbp_movie_writer'       => 'Alice Brown',
								'wtbp_movie_rating'       => '7.5',
								'wtbp_movie_active'       => 'true',
								'wtbp_movie_poster_id'    => [0],
							],
						],
						[
							'post_title' => 'The Final Pitch',
							'post_content' => 'An inspiring sports drama about a young team striving to win their last championship.',
							'meta_data' => [
								'wtbp_movie_release_date' => '2021-08-20',
								'wtbp_movie_genre'        => 'Sports',
								'wtbm_movie_status'        => 'showing',
								'wtbp_movie_duration'     => '1h 58m',
								'wtbp_movie_director'     => 'Michael Green',
								'wtbp_movie_actors'       => 'Tom Hardy, Chris Evans',
								'wtbp_movie_writer'       => 'Laura King',
								'wtbp_movie_rating'       => '7.8',
								'wtbp_movie_active'       => 'true',
								'wtbp_movie_poster_id'    => [1],
							],
						],
						[
							'post_title' => 'Neon City',
							'post_content' => 'A cyberpunk adventure exploring the dark and vibrant streets of a futuristic metropolis.',
							'meta_data' => [
								'wtbp_movie_release_date' => '2024-06-01',
								'wtbp_movie_genre'        => 'Sci-Fi',
								'wtbm_movie_status'        => 'showing',
								'wtbp_movie_duration'     => '2h 20m',
								'wtbp_movie_director'     => 'Samantha Lee',
								'wtbp_movie_actors'       => 'Keanu Reeves, Emma Stone',
								'wtbp_movie_writer'       => 'David Wong',
								'wtbp_movie_rating'       => '8.6',
								'wtbp_movie_active'       => 'true',
								'wtbp_movie_poster_id'    => [2],
							],
						],
						[
							'post_title' => 'Iron Divide',
							'post_content' => 'A gripping historical war drama portraying the struggles of divided nations.',
							'meta_data' => [
								'wtbp_movie_release_date' => '2019-09-09',
								'wtbp_movie_genre'        => 'History',
								'wtbm_movie_status'        => 'showing',
								'wtbp_movie_duration'     => '2h 50m',
								'wtbp_movie_director'     => 'Robert Martin',
								'wtbp_movie_actors'       => 'Matt Damon, Scarlett Johansson',
								'wtbp_movie_writer'       => 'Helen Clark',
								'wtbp_movie_rating'       => '8.0',
								'wtbp_movie_active'       => 'true',
								'wtbp_movie_poster_id'    => [3],
							],
						],
						[
							'post_title' => 'Echoes of Time',
							'post_content' => 'A fantasy romance that travels across timelines, exploring love and destiny.',
							'meta_data' => [
								'wtbp_movie_release_date' => '2023-02-14',
								'wtbp_movie_genre'        => 'Romance',
								'wtbm_movie_status'        => 'showing',
								'wtbp_movie_duration'     => '2h 25m',
								'wtbp_movie_director'     => 'Natalie Portman',
								'wtbp_movie_actors'       => 'Ryan Gosling, Emma Watson',
								'wtbp_movie_writer'       => 'Sophia Turner',
								'wtbp_movie_rating'       => '8.5',
								'wtbp_movie_active'       => 'true',
								'wtbp_movie_poster_id'    => [4],
							],
						],
						[
							'post_title' => 'Velocity',
							'post_content' => 'A high-octane action thriller about a heist that tests the limits of speed and trust.',
							'meta_data' => [
								'wtbp_movie_release_date' => '2022-05-18',
								'wtbp_movie_genre'        => 'Action',
								'wtbm_movie_status'        => 'showing',
								'wtbp_movie_duration'     => '2h 10m',
								'wtbp_movie_director'     => 'Chris Nolan',
								'wtbp_movie_actors'       => 'Tom Cruise, Gal Gadot',
								'wtbp_movie_writer'       => 'James Cameron',
								'wtbp_movie_rating'       => '8.1',
								'wtbp_movie_active'       => 'true',
								'wtbp_movie_poster_id'    => [5],
							],
						]
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
							'wtbp_theater_category' => $this->theater_seat_category_data(),
							'wtbp_theater_seat_map' => $this->theater_seat_data(),
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
							'wtbp_theater_category' => $this->theater_seat_category_data(),
							'wtbp_theater_seat_map' => $this->theater_seat_data(),
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
							'wtbp_theater_category' => $this->theater_seat_category_data(),
							'wtbp_theater_seat_map' => $this->theater_seat_data(),
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
							'wtbp_theater_category' => $this->theater_seat_category_data(),
							'wtbp_theater_seat_map' => $this->theater_seat_data(),
						],
					],
					[
						'post_title' => 'Screen 5',
						'meta_data' => [
							'wtbp_theater_type' => 'VIP',
							'wtbp_theater_rows' => '10',
							'wtbp_theater_seatsPerRow' => '10',
							'wtbp_theater_soundSystem' => 'IMAX Enhanced',
							'wtbp_theater_status' => 'active',
							'wtbp_theater_category' => $this->theater_seat_category_data(),
							'wtbp_theater_seat_map' => $this->theater_seat_data(),
						],
					],
				];
			}

			public function theater_seat_category_data(){
				return [
						[
							'category_id'   => 'regular',
							'category_name' => 'Regular',
							'seats'         => '50',
							'price'         => '70',
							'color'         => '#28a745',
						]
					];
			}

			public function theater_seat_data(){
				$seat_data = [];
				$seat_no = 1;
				for($row=0; $row<10; $row++){
					for($col=0; $col<10; $col++){
						$seat_data[] = [
							'id'            => $row.'_'.$col,
							'row'           => $row,
							'col'           => $col,
							'color'         => '#28a745',
							'price'         => 70,
							'width'         => '25px',
							'height'        => '30px',
							'seat_number'   => $seat_no++,
							'left'          => (10 + ($col * 30)).'px',
							'top'           => (10 + ($row * 40)).'px',
							'z_index'       => 10,
							'data_degree'   => 0,
							'data_tableBind'=> '',
							'border_radius' => '5px',
							'seatText'      => '',
							'backgroundImage'=> '',
							'seat_category' => 'regular'
						];
					}
				}

				return [
					'seat_data' => $seat_data,
					'seat_text_data' => [],
					'dynamic_shapes' => [],
				];
			}

			public function show_time_data() {
				return [
					
					[
						'meta_data'  => [
							'wtbp_show_time_date'        => gmdate( 'Y-m-d'),
							'wtbp_showtime_start_date'   => gmdate( 'Y-m-d'),
							'wtbp_showtime_end_date'     => gmdate( 'Y-m-d', strtotime('+30 day') ),

							'wtbp_show_starting_time'  => '21:00',
							'wtbp_show_ending_time'  => '22:30',

							'wtbp_show_time_price'       => 10,
							'wtbp_showtime_off_days'     => ['monday'],
						],
					],
					[
						'meta_data'  => [
							'wtbp_show_time_date'        => gmdate( 'Y-m-d'),
							'wtbp_showtime_start_date'   => gmdate( 'Y-m-d'),
							'wtbp_showtime_end_date'     => gmdate( 'Y-m-d', strtotime('+30 day') ),

							'wtbp_show_starting_time'  => '18:00',
							'wtbp_show_ending_time'  => '20:30',

							'wtbp_show_time_price'       => 10,
							'wtbp_showtime_off_days'     => ['monday'],
						],
					],
					[
						'meta_data'  => [
							'wtbp_show_time_date'        => gmdate( 'Y-m-d'),
							'wtbp_showtime_start_date'   => gmdate( 'Y-m-d'),
							'wtbp_showtime_end_date'     => gmdate( 'Y-m-d', strtotime('+30 day') ),

							'wtbp_show_starting_time'  => '15:00',
							'wtbp_show_ending_time'  => '17:30',

							'wtbp_show_time_price'       => 10,
							'wtbp_showtime_off_days'     => ['monday'],
						],
					],
					[
						'meta_data'  => [
							'wtbp_show_time_date'        => gmdate( 'Y-m-d'),
							'wtbp_showtime_start_date'   => gmdate( 'Y-m-d'),
							'wtbp_showtime_end_date'     => gmdate( 'Y-m-d', strtotime('+30 day') ),

							'wtbp_show_starting_time'  => '11:00',
							'wtbp_show_ending_time'  => '14:30',

							'wtbp_show_time_price'       => 10,
							'wtbp_showtime_off_days'     => ['monday'],
						],
					]
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