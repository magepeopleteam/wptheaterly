<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPTRS_Date_Time_Settings')) {
		class MPTRS_Date_Time_Settings {
			public function __construct() {
				add_action('add_mptrs_settings_tab_content', [$this, 'date_time_settings'], 10, 1);
				/************************/
				add_action('wp_ajax_get_mptrs_end_time_slot', array($this, 'get_mptrs_end_time_slot'));
				add_action('wp_ajax_nopriv_get_mptrs_end_time_slot', array($this, 'get_mptrs_end_time_slot'));
				/***/
				add_action('wp_ajax_get_mptrs_start_break_time', array($this, 'get_mptrs_start_break_time'));
				add_action('wp_ajax_nopriv_get_mptrs_start_break_time', array($this, 'get_mptrs_start_break_time'));
				/***/
				add_action('wp_ajax_get_mptrs_end_break_time', array($this, 'get_mptrs_end_break_time'));
				add_action('wp_ajax_nopriv_get_mptrs_end_break_time', array($this, 'get_mptrs_end_break_time'));
			}
			public function date_time_settings($post_id) {
				$date_format = MPTRS_Function::date_picker_format();
				$now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
				$date_type = MPTRS_Function::get_post_info($post_id, 'mptrs_date_type', 'repeated');
				$time_slot = MPTRS_Function::get_post_info($post_id, 'mptrs_time_slot_length');
				$capacity = MPTRS_Function::get_post_info($post_id, 'mptrs_capacity_per_session', 1);
				$repeated_start_date = MPTRS_Function::get_post_info($post_id, 'mptrs_repeated_start_date');
				$hidden_repeated_start_date = $repeated_start_date ? date_i18n('Y-m-d', strtotime($repeated_start_date)) : '';
				$visible_repeated_start_date = $repeated_start_date ? date_i18n($date_format, strtotime($repeated_start_date)) : '';
				$repeated_after = MPTRS_Function::get_post_info($post_id, 'mptrs_repeated_after', 1);
				$active_days = MPTRS_Function::get_post_info($post_id, 'mptrs_active_days', 10);
				?>
                <div class="tabsItem mptrs_settings_date_time" data-tabs="#mptrs_settings_date_time">
                    <header>
                        <h2><?php esc_html_e('Date & Time Settings', 'theaterly'); ?></h2>
                        <span><?php MPTRS_Settings::info_text('date_time_desc'); ?></span>
                    </header>
                    <section class="section">
                        <h2><?php esc_html_e('General date time settings', 'theaterly'); ?></h2>
                        <span><?php MPTRS_Settings::info_text('general_date_time_desc'); ?></span>
                    </section>
                    <section>
                        <label class="label">
                            <div>
                                <p><?php esc_html_e('Date Type', 'theaterly'); ?> <span class="textRequired">&nbsp;*</span></p>
                            </div>
                            <select class="" name="mptrs_date_type" data-mptrs-collapse required>
                                <option disabled selected><?php esc_html_e('Please select ...', 'theaterly'); ?></option>
                                <option value="particular" data-option-target="#mptrs_particular" <?php echo esc_attr($date_type == 'particular' ? 'selected' : ''); ?>><?php esc_html_e('Particular', 'theaterly'); ?></option>
                                <option value="repeated" data-option-target="#mptrs_repeated" <?php echo esc_attr($date_type == 'repeated' ? 'selected' : ''); ?>><?php esc_html_e('Repeated', 'theaterly'); ?></option>
                            </select>
                        </label>
                    </section>
                    <section class="<?php echo esc_attr($date_type == 'particular' ? 'mActive' : ''); ?>" data-collapse="#mptrs_particular">
                        <label class="_dFlex_justifyBetween">
                            <div>
                                <p><?php esc_html_e('Particular Dates', 'theaterly'); ?> <span class="textRequired">&nbsp;*</span></p>
                            </div>
                            <div class="settings_area">
                                <div class="item_insert sortable_area">
									<?php
										$particular_date_lists = MPTRS_Function::get_post_info($post_id, 'mptrs_particular_dates', array());
										if (sizeof($particular_date_lists)) {
											foreach ($particular_date_lists as $particular_date) {
												if ($particular_date) {
													self::particular_date_item('mptrs_particular_dates[]', $particular_date);
												}
											}
										}
									?>
                                </div>
								<?php MPTRS_Layout::add_new_button(esc_html__('Add New Particular date', 'theaterly')); ?>
                                <div class="hidden_content">
                                    <div class="hidden_item">
										<?php self::particular_date_item('mptrs_particular_dates[]'); ?>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </section>
                    <section class="<?php echo esc_attr($date_type == 'repeated' ? 'mActive' : ''); ?>" data-collapse="#mptrs_repeated">
                        <label class="label">
                            <div>
                                <p><?php esc_html_e('Repeated Start Date', 'theaterly'); ?> <span class="textRequired">&nbsp;*</span></p>
                            </div>
                            <div>
                                <input type="hidden" name="mptrs_repeated_start_date" value="<?php echo esc_attr($hidden_repeated_start_date); ?>" required/>
                                <input type="text" readonly required name="" class="date_type" value="<?php echo esc_attr($visible_repeated_start_date); ?>" placeholder="<?php echo esc_attr($now); ?>"/>
                            </div>
                        </label>
                    </section>
                    <section class="<?php echo esc_attr($date_type == 'repeated' ? 'mActive' : ''); ?>" data-collapse="#mptrs_repeated">
                        <label class="label">
                            <div>
                                <p><?php esc_html_e('Repeated after', 'theaterly'); ?> <span class="textRequired">&nbsp;*</span></p>
                            </div>
                            <input type="text" name="mptrs_repeated_after" class="mp_number_validation" value="<?php echo esc_attr($repeated_after); ?>"/>
                        </label>
                    </section>
                    <!-- ================ -->
                    <section class="section">
                        <h2><?php esc_html_e('Shedule settings', 'theaterly'); ?></h2>
                        <span><?php MPTRS_Settings::info_text('general_date_time_desc'); ?></span>
                    </section>
                    <section>
                        <table>
                            <thead>
                            <tr>
                                <th style="text-align: left;"><?php esc_html_e('Day', 'theaterly'); ?></th>
                                <th><?php esc_html_e('Start Time', 'theaterly'); ?></th>
                                <th><?php esc_html_e('To', 'theaterly'); ?></th>
                                <th><?php esc_html_e('End Time', 'theaterly'); ?></th>
                                <th colspan="3" class="bg-sky-light"><?php esc_html_e('Break Time', 'theaterly'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php
								$this->time_slot_tr($post_id, 'default');
								$days = MPTRS_Function::week_day();
								foreach ($days as $key => $day) {
									$this->time_slot_tr($post_id, $key);
								}
							?>
                            </tbody>
                        </table>
                    </section>
                    <!-- ================ -->
                    <section class="section">
                        <h2><?php esc_html_e('Offdays and date settings', 'theaterly'); ?></h2>
                        <span><?php MPTRS_Settings::info_text('general_date_time_desc'); ?></span>
                    </section>
                    <section>
                        <label class="label">
                            <div class="groupCheckBox flexWrap">
								<?php
									$off_days = MPTRS_Function::get_post_info($post_id, 'mptrs_off_days');
									$days = MPTRS_Function::week_day();
									$off_day_array = explode(',', $off_days);
								?>
                                <input type="hidden" name="mptrs_off_days" value="<?php echo esc_attr($off_days); ?>"/>
								<?php foreach ($days as $key => $day) { ?>
                                    <label class="customCheckboxLabel ">
                                        <input type="checkbox" <?php echo esc_attr(in_array($key, $off_day_array) ? 'checked' : ''); ?> data-checked="<?php echo esc_attr($key); ?>"/>
                                        <span class="customCheckbox"><?php echo esc_html($day); ?></span>
                                    </label>
								<?php } ?>
                            </div>
                        </label>
                    </section>
                    <section>
                        <label class="_dFlex_justifyBetween">
                            <p>
								<?php esc_html_e('Off date', 'theaterly'); ?>
                            </p>
                            <div class="settings_area">
                                <div class="item_insert sortable_area">
									<?php
										$off_day_lists = MPTRS_Function::get_post_info($post_id, 'mptrs_off_dates', array());
										if (sizeof($off_day_lists) > 0) {
											foreach ($off_day_lists as $off_day) {
												if ($off_day) {
													MPTRS_Date_Time_Settings::particular_date_item('mptrs_off_dates[]', $off_day);
												}
											}
										}
									?>
                                </div>
								<?php MPTRS_Layout::add_new_button(esc_html__('Add New Off date', 'theaterly')); ?>
                                <div class="hidden_content">
                                    <div class="hidden_item">
										<?php MPTRS_Date_Time_Settings::particular_date_item('mptrs_off_dates[]'); ?>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </section>
                </div>
				<?php
			}
			public static function particular_date_item($name, $date = '') {
				$date_format = MPTRS_Function::date_picker_format();
				$now = date_i18n($date_format, strtotime(current_time('Y-m-d')));
				$hidden_date = $date ? date_i18n('Y-m-d', strtotime($date)) : '';
				$visible_date = $date ? date_i18n($date_format, strtotime($date)) : '';
				?>
                <div class="mp_remove_area  _mB_xs">
                    <div class="justifyBetween">
                        <label class="col_8">
                            <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($hidden_date); ?>"/>
                            <input value="<?php echo esc_attr($visible_date); ?>" class="formControl date_type" placeholder="<?php echo esc_attr($now); ?>"/>
                        </label>
						<?php MPTRS_Layout::move_remove_button(); ?>
                    </div>
                </div>
				<?php
			}
			public function time_slot_tr($post_id, $day) {
				$start_name = 'mptrs_' . $day . '_start_time';
				$default_start_time = $day == 'default' ? 10 : '';
				$start_time = MPTRS_Function::get_post_info($post_id, $start_name, $default_start_time);
				$end_name = 'mptrs_' . $day . '_end_time';
				$default_end_time = $day == 'default' ? 18 : '';
				$end_time = MPTRS_Function::get_post_info($post_id, $end_name, $default_end_time);
				$start_name_break = 'mptrs_' . $day . '_start_break_time';
				$start_time_break = MPTRS_Function::get_post_info($post_id, $start_name_break);
				?>
                <tr>
                    <th style="text-transform: capitalize;"><?php echo esc_html($day); ?></th>
                    <td class="mptrs_start_time" data-day-name="<?php echo esc_attr($day); ?>">
						<?php //echo '<pre>'; print_r( $start_time );echo '</pre>'; ?>
                        <label>
                            <select class="formControl" name="<?php echo esc_attr($start_name); ?>">
                                <option value="" <?php echo esc_attr($start_time == '' ? 'selected' : ''); ?>>
									<?php $this->default_text($day); ?>
                                </option>
								<?php $this->time_slot($start_time); ?>
                            </select>
                        </label>
                    </td>
                    <td class="textCenter">
                        <strong><?php esc_html_e('To', 'theaterly'); ?></strong>
                    </td>
                    <td class="mptrs_end_time">
						<?php $this->end_time_slot($post_id, $day, $start_time); ?>
                    </td>
                    <td class="bg-sky-light" class="mptrs_start_break_time">
						<?php $this->start_break_time_slot($post_id, $day, $start_time, $end_time) ?>
                    </td>
                    <td class="textCenter bg-sky-light">
                        <strong><?php esc_html_e('To', 'theaterly'); ?></strong>
                    </td>
                    <td class="bg-sky-light" class="mptrs_end_break_time">
						<?php $this->end_break_time_slot($post_id, $day, $start_time_break, $end_time) ?>
                    </td>
                </tr>
				<?php
			}
			public function end_time_slot($post_id, $day, $start_time) {
				$end_name = 'mptrs_' . $day . '_end_time';
				$default_end_time = $day == 'default' ? 18 : '';
				$end_time = MPTRS_Function::get_post_info($post_id, $end_name, $default_end_time);
				?>
                <label>
                    <select class="formControl " name="<?php echo esc_attr($end_name); ?>">
						<?php if ($start_time == '') { ?>
                            <option value="" selected><?php $this->default_text($day); ?></option>
						<?php } ?>
						<?php $this->time_slot($end_time, $start_time); ?>
                    </select>
                </label>
				<?php
			}
			public function start_break_time_slot($post_id, $day, $start_time, $end_time = '') {
				$start_name_break = 'mptrs_' . $day . '_start_break_time';
				$start_time_break = MPTRS_Function::get_post_info($post_id, $start_name_break);
				?>
                <label>
                    <select class="formControl" name="<?php echo esc_attr($start_name_break); ?>">
                        <option value="" <?php echo esc_attr(!$start_time_break ? 'selected' : ''); ?>><?php esc_html_e('No Break', 'theaterly'); ?></option>
						<?php $this->time_slot($start_time_break, $start_time, $end_time); ?>
                    </select>
                </label>
				<?php
			}
			public function end_break_time_slot($post_id, $day, $start_time_break, $end_time) {
				$end_name_break = 'mptrs_' . $day . '_end_break_time';
				$end_time_break = MPTRS_Function::get_post_info($post_id, $end_name_break);
				?>
                <label>
                    <select class="formControl" name="<?php echo esc_attr($end_name_break); ?>">
						<?php if ($start_time_break == '') { ?>
                            <option value="" selected><?php esc_html_e('No Break', 'theaterly'); ?></option>
						<?php } ?>
						<?php $this->time_slot($end_time_break, $start_time_break, $end_time); ?>
                    </select>
                </label>
				<?php
			}
			public function time_slot($time, $stat_time = '', $end_time = '') {
				if ($stat_time >= 0 || $stat_time == '') {
					$time_count = $stat_time == '' ? 0 : $stat_time;
					$end_time = $end_time != '' ? $end_time : 23.5;
					for ($i = $time_count; $i <= $end_time; $i = $i + 0.5) {
						if ($stat_time == 'yes' || $i > $time_count) {
							?>
                            <option value="<?php echo esc_attr($i); ?>" <?php echo esc_attr($time != '' && $time == $i ? 'selected' : ''); ?>><?php echo esc_html(date_i18n('h:i A', $i * 3600)); ?></option>
							<?php
						}
					}
				}
			}
			public function default_text($day) {
				if ($day == 'default') {
					esc_html_e('Please select', 'theaterly');
				} else {
					esc_html_e('Default', 'theaterly');
				}
			}
			/*************************************/
			public function get_mptrs_end_time_slot() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'mprts_admin_nonce')) {
					wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
				}
				$post_id = isset($_POST['post_id']) ? sanitize_text_field(wp_unslash($_POST['post_id'])) : '';
				$day = isset($_POST['day_name']) ? sanitize_text_field(wp_unslash($_POST['day_name'])) : '';
				$start_time = isset($_POST['start_time']) ? sanitize_text_field(wp_unslash($_POST['start_time'])) : '';
				$this->end_time_slot($post_id, $day, $start_time);
				die();
			}
			public function get_mptrs_start_break_time() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'mprts_admin_nonce')) {
					wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
				}
				$post_id = isset($_POST['post_id']) ? sanitize_text_field(wp_unslash($_POST['post_id'])) : '';
				$day = isset($_POST['day_name']) ? sanitize_text_field(wp_unslash($_POST['day_name'])) : '';
				$start_time = isset($_POST['start_time']) ? sanitize_text_field(wp_unslash($_POST['start_time'])) : '';
				$end_time = isset($_POST['end_time']) ? sanitize_text_field(wp_unslash($_POST['end_time'])) : '';
				$this->start_break_time_slot($post_id, $day, $start_time, $end_time);
				die();
			}
			public function get_mptrs_end_break_time() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'mprts_admin_nonce')) {
					wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
				}
				$post_id = isset($_POST['post_id']) ? sanitize_text_field(wp_unslash($_POST['post_id'])) : '';
				$day = isset($_POST['day_name']) ? sanitize_text_field(wp_unslash($_POST['day_name'])) : '';
				$start_time = isset($_POST['start_time']) ? sanitize_text_field(wp_unslash($_POST['start_time'])) : '';
				$end_time = isset($_POST['end_time']) ? sanitize_text_field(wp_unslash($_POST['end_time'])) : '';
				$this->end_break_time_slot($post_id, $day, $start_time, $end_time);
				die();
			}

		}
		new MPTRS_Date_Time_Settings();
	}