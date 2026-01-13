<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPTRS_Dummy_Import')) {
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
		class MPTRS_Dummy_Import {
			public function __construct() {
				// $this->dummy_import();
				add_action('admin_init', [$this, 'dummy_import']);
			}

			public function dummy_import() {
				$dummy_post = get_option('mptrs_dummy_already_inserted');
				$all_post = MPTRS_Function::query_post_type('wtbm_movie');
				if ($all_post->post_count == 0 && $dummy_post != 'yes') {
				// if ($dummy_post == 'yes') {
					$this->create_dummy_page();
					$dummy_data = $this->dummy_data();
					
					foreach ($dummy_data as $type => $dummy) {
						if ($type == 'taxonomy') {
							foreach ($dummy as $taxonomy => $dummy_taxonomy) {
								$check_taxonomy = MPTRS_Function::get_taxonomy($taxonomy);
								if (is_string($check_taxonomy) || sizeof($check_taxonomy) == 0) {
									foreach ($dummy_taxonomy as $taxonomy_data) {
										wp_insert_term($taxonomy_data['name'], $taxonomy);
									}
								}
							}
						}
						if ($type == 'custom_post') {
							foreach ($dummy as $post_type => $dummy_post) {
								$post = MPTRS_Function::query_post_type($post_type);
								if ($post->post_count == 0) {
									$dummy_images = $this->dummy_images();
									foreach ($dummy_post as $key => $dummy_data) {
										$post_id = wp_insert_post([
											'post_title' =>$dummy_data['post_title'],
											'post_status' => 'publish',
											'post_type' => $post_type
										]);
										if (array_key_exists('meta_data', $dummy_data)) {
											foreach ($dummy_data['meta_data'] as $meta_key => $data) {
												// update_post_meta($post_id, $meta_key, $data);
												if ($meta_key == 'wtbp_movie_poster_id') {
													$thumnail_ids = [];
													foreach ($data as $url_index) {
														if (isset($dummy_images[$url_index])) {
															$thumnail_ids[] = $dummy_images[$url_index];
														}
													}
													update_post_meta($post_id, $meta_key, $thumnail_ids[0]);
													if (count($thumnail_ids)) {
														set_post_thumbnail($post_id, $thumnail_ids[0]);
													}
												} else {
													update_post_meta($post_id, $meta_key, $data);
												}
											}
										};
									}
								}
							}
						}
					}
					update_option('mptrs_dummy_already_inserted', 'yes');
				}
			}
			public function dummy_data(): array {
				return [
					'taxonomy' => [],
					'custom_post' => [
						'wtbm_movie' => [
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
						],
						'wtbm_theater' => [
							[
								'post_title' => 'Screen 1',
								'meta_data' => [
									'wtbp_theater_type' => 'Standard',
									'wtbp_theater_rows' => '12',
									'wtbp_theater_seatsPerRow' => '20',
									'wtbp_theater_soundSystem' => 'Dolby Digital',
									'wtbp_theater_status' => 'active',
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
								],
							],
						],
						'wtbm_show_time' =>[
							[
								'post_title' => 'Screen 1',
								'meta_data' => [
									'wtbp_show_time_movieId' => '101',
									'wtbp_show_time_theaterId' => '144',
									'wtbp_show_time_date' => '2026-01-21',
									'wtbp_show_time_start_date' => '11.00',
									'wtbp_show_time_end_date' => '03.00',
									
									'wtbp_showtime_start_date' => '2026-01-21',
									'wtbp_showtime_end_date' => '2026-01-31',
									'wtbp_show_time_price' => '11',
									'wtbp_showtime_off_days' => 'monday',
								],
							],
						],
						'wtbm_pricing' =>[
							[
								'post_title' => 'Rule 1',
								'meta_data' => [
									'wtbp_pricing_rules_theaterType' => '101',
									'wtbp_pricing_rules_type' => '144',
									'wtbp_pricing_rules_dateRange' => '2026-01-21',
									'wtbp_pricing_rules_startDate' => '2026-01-21',
									'wtbp_pricing_rules_endDate' => '2026-01-31',
									'wtbp_pricing_rules_priority' => '5',
									'wtbp_pricing_rules_multiplier' => '5',
									'wtbp_pricing_rules_active' => 'yes',
									
								],
							],
						]
					]
				];
			}
			public function dummy_images() {
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
		}
				
		new MPTRS_Dummy_Import();		

	}