<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPTRS_Settings')) {
		class MPTRS_Settings {
			public function __construct() {
				add_action('add_meta_boxes', [$this, 'settings_meta']);
				add_action('save_post', array($this, 'save_settings'), 99, 1);
			}
			//************************//
			public function settings_meta() {
				$label = MPTRS_Function::get_name();
				$cpt = MPTRS_Function::get_cpt();
				add_meta_box('mptrs_meta_box', $label . esc_html__(' Information Settings : ', 'theaterly') . get_the_title(get_the_id()), array($this, 'settings'), $cpt, 'normal', 'high');
			}
			//******************************//
			public function settings() {
				$post_id = get_the_id();
				wp_nonce_field('mptrs_nonce', 'mptrs_nonce');
				?>
                <div class="mptrs_area">
                    <div class="mptrs_tab">
                        <div class="tabLists">
                            <ul class="_fullWidth">
                                <li data-tabs-target="#mptrs_general_info">
                                    <i class="fas fa-tools _mR_xs"></i><?php esc_html_e('General Info', 'theaterly'); ?>
                                </li>
                                <li data-tabs-target="#mptrs_settings_date_time">
                                    <i class="fas fa-clock _mR_xs"></i><?php esc_html_e('Date & Time', 'theaterly'); ?>
                                </li>
                                <li data-tabs-target="#mptrs_extra_service_settings">
                                    <i class="fas fa-funnel-dollar _mR_xs"></i><?php esc_html_e('Extra Service', 'theaterly'); ?>
                                </li>
                                <li data-tabs-target="#mptrs_faq_settings">
                                    <i class="fas fa-question-circle _mR_xs"></i><?php esc_html_e('FAQ', 'theaterly'); ?>
                                </li>
								<?php do_action('add_mptrs_settings_tab_after_date', $post_id); ?>
                            </ul>
                        </div>
                        <div class="tabsContent">
							<?php do_action('add_mptrs_settings_tab_content', $post_id); ?>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function save_settings($post_id) {
				if (!isset($_POST['mptrs_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mptrs_nonce'])), 'mptrs_nonce') && defined('DOING_AUTOSAVE') && DOING_AUTOSAVE && !current_user_can('edit_post', $post_id)) {
					return;
				}
				if (get_post_type($post_id) == MPTRS_Function::get_cpt()) {
					//************************************//
					$date_type = isset($_POST['mptrs_date_type']) ? sanitize_text_field(wp_unslash($_POST['mptrs_date_type'])) : '';
					update_post_meta($post_id, 'mptrs_date_type', $date_type);
					//**********************//
					$particular_dates = isset($_POST['mptrs_particular_dates']) ? array_map('sanitize_text_field', wp_unslash($_POST['mptrs_particular_dates'])) : [];
					$particular = array();
					if (sizeof($particular_dates) > 0) {
						foreach ($particular_dates as $particular_date) {
							if ($particular_date) {
								$particular[] = date_i18n('Y-m-d', strtotime($particular_date));
							}
						}
					}
					update_post_meta($post_id, 'mptrs_particular_dates', $particular);
					//*************************//
					$repeated_start_date = isset($_POST['mptrs_repeated_start_date']) ? sanitize_text_field(wp_unslash($_POST['mptrs_repeated_start_date'])) : '';
					$repeated_start_date = $repeated_start_date ? date_i18n('Y-m-d', strtotime($repeated_start_date)) : '';
					update_post_meta($post_id, 'mptrs_repeated_start_date', $repeated_start_date);
					$repeated_after = isset($_POST['mptrs_repeated_after']) ? sanitize_text_field(wp_unslash($_POST['mptrs_repeated_after'])) : 1;
					update_post_meta($post_id, 'mptrs_repeated_after', $repeated_after);
					$active_days = isset($_POST['mptrs_active_days']) ? sanitize_text_field(wp_unslash($_POST['mptrs_active_days'])) : '';
					update_post_meta($post_id, 'mpwpb_active_days', $active_days);
					//**********************//
					$time_slot_length = isset($_POST['mpwpb_time_slot_length']) ? sanitize_text_field(wp_unslash($_POST['mpwpb_time_slot_length'])) : '';
					$capacity_per_session = isset($_POST['mpwpb_capacity_per_session']) ? sanitize_text_field(wp_unslash($_POST['mpwpb_capacity_per_session'])) : '';
					update_post_meta($post_id, 'mpwpb_time_slot_length', $time_slot_length);
					update_post_meta($post_id, 'mpwpb_capacity_per_session', $capacity_per_session);
					//**********************//
					$this->save_schedule($post_id, 'default');
					$days = MPTRS_Function::week_day();
					foreach ($days as $key => $day) {
						$this->save_schedule($post_id, $key);
					}
					//**********************//
					$off_days = isset($_POST['mpwpb_off_days']) ? array_map('sanitize_text_field', wp_unslash($_POST['mpwpb_off_days'])) : [];
					update_post_meta($post_id, 'mpwpb_off_days', $off_days);
					//**********************//
					$off_dates = isset($_POST['mpwpb_off_dates']) ? array_map('sanitize_text_field', wp_unslash($_POST['mpwpb_off_dates'])) : [];
					$_off_dates = array();
					if (sizeof($off_dates) > 0) {
						foreach ($off_dates as $off_date) {
							if ($off_date) {
								$_off_dates[] = date_i18n('Y-m-d', strtotime($off_date));
							}
						}
					}
					update_post_meta($post_id, 'mpwpb_off_dates', $_off_dates);
				}
				if (get_post_type($post_id) == MPTRS_Function::get_cpt()) {
					$mpwpb_faq_active = isset($_POST['mpwpb_faq_active']) ? sanitize_text_field(wp_unslash($_POST['mpwpb_faq_active'])) : '';
					update_post_meta($post_id, 'mpwpb_faq_active', $mpwpb_faq_active);
				}
				if (get_post_type($post_id) == MPTRS_Function::get_cpt()) {
					$slider = isset($_POST['mpwpb_display_slider']) && sanitize_text_field(wp_unslash($_POST['mpwpb_display_slider'])) ? 'on' : 'off';
					update_post_meta($post_id, 'mpwpb_display_slider', $slider);
					$images = isset($_POST['mpwpb_slider_images']) ? sanitize_text_field(wp_unslash($_POST['mpwpb_slider_images'])) : '';
					$all_images = explode(',', $images);
					update_post_meta($post_id, 'mpwpb_slider_images', $all_images);
				}
				do_action('mptrs_settings_save', $post_id);
			}
			public function save_schedule($post_id, $day) {
				if (!isset($_POST['mpwpb_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mpwpb_nonce'])), 'mpwpb_nonce') && defined('DOING_AUTOSAVE') && DOING_AUTOSAVE && !current_user_can('edit_post', $post_id)) {
					return;
				}
				$start_name = 'mpwpb_' . $day . '_start_time';
				$start_time = isset($_POST[$start_name]) ? sanitize_text_field(wp_unslash($_POST[$start_name])) : '';
				update_post_meta($post_id, $start_name, $start_time);
				$end_name = 'mpwpb_' . $day . '_end_time';
				$end_time = isset($_POST[$end_name]) ? sanitize_text_field(wp_unslash($_POST[$end_name])) : '';
				update_post_meta($post_id, $end_name, $end_time);
				$start_name_break = 'mpwpb_' . $day . '_start_break_time';
				$start_time_break = isset($_POST[$start_name_break]) ? sanitize_text_field(wp_unslash($_POST[$start_name_break])) : '';
				update_post_meta($post_id, $start_name_break, $start_time_break);
				$end_name_break = 'mpwpb_' . $day . '_end_break_time';
				$end_time_break = isset($_POST[$end_name_break]) ? sanitize_text_field(wp_unslash($_POST[$end_name_break])) : '';
				update_post_meta($post_id, $end_name_break, $end_time_break);
			}
			public static function description_array($key) {
				$des = array(
					'mpwpb_display_slider' => esc_html__('By default slider is ON but you can keep it off by switching this option', 'theaterly'),
					'mpwpb_slider_images' => esc_html__('Please upload images for gallery', 'theaterly'),
					'date_time_desc' => esc_html__('Date & time settings', 'theaterly'),
					'general_date_time_desc' => esc_html__('Date & time settings', 'theaterly'),
					//''          => esc_html__( '', 'theaterly' ),
				);
				$des = apply_filters('mpwpb_filter_description_array', $des);
				return $des[$key];
			}
			public static function info_text($key) {
				$data = self::description_array($key);
				if ($data) {
					echo esc_html($data);
				}
			}
		}
		new MPTRS_Settings();
	}