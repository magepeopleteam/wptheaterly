<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPTRS_Dummy_Import')) {
		class MPTRS_Dummy_Import {
			public function __construct() {
				$this->dummy_import();
			}
			private function dummy_import() {
				$dummy_post = get_option('mptrs_dummy_already_inserted');
				$all_post = WTBM_Function::query_post_type('mptrs_item');
				if ($all_post->post_count == 0 && $dummy_post != 'yes') {
					$dummy_data = $this->dummy_data();
					foreach ($dummy_data as $type => $dummy) {
						if ($type == 'taxonomy') {
							foreach ($dummy as $taxonomy => $dummy_taxonomy) {
								$check_taxonomy = WTBM_Function::get_taxonomy($taxonomy);
								if (is_string($check_taxonomy) || sizeof($check_taxonomy) == 0) {
									foreach ($dummy_taxonomy as $taxonomy_data) {
										wp_insert_term($taxonomy_data['name'], $taxonomy);
									}
								}
							}
						}
						if ($type == 'custom_post') {
							foreach ($dummy as $custom_post => $dummy_post) {
								$post = WTBM_Function::query_post_type($custom_post);
								if ($post->post_count == 0) {
									foreach ($dummy_post as $key => $dummy_data) {
										$title = $dummy_data['name'];
										$post_id = wp_insert_post([
											'post_title' => $title,
											'post_status' => 'publish',
											'post_type' => $custom_post
										]);
										if (array_key_exists('post_data', $dummy_data)) {
											foreach ($dummy_data['post_data'] as $meta_key => $data) {
												update_post_meta($post_id, $meta_key, $data);
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
						'mptrs_item' => [
							0 => [
								'name' => 'Hair Cut Salon Booking',
								'post_data' => [
									// Gallery Settings
									'mptrs_display_slider' => 'on',
									'mptrs_slider_images' => [
										0 => ''
									],
									'mptrs_faq_active' => 'on',
									'mptrs_faq' => [
										[
											'title' => 'What services can I book?',
											'content' => '<p>You can book a variety of services, including haircuts, coloring, styling, manicures, pedicures, facials, and massages.</p>',
										],
										[
											'title' => 'Is the booking system easy to use?',
											'content' => '<p>Yes, our online booking system is user-friendly, allowing you to navigate and schedule appointments effortlessly.</p>',
										],
										[
											'title' => 'Can I choose my stylist?',
											'content' => '<ul><li><p>Yes, you can select your preferred stylist based on their expertise and availability.</p></li></ul>',
										],
										[
											'title' => 'What if I need to cancel or reschedule my appointment?',
											'content' => '<ul><li><p>You can easily cancel or reschedule your appointment online, ideally 24 hours in advance.</p></li></ul>',
										]
									],

								],
							],
						]
					]
				];
			}
		}
	}