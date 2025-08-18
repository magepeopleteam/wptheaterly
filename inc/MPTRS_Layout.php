<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPTRS_Layout')) {
		class MPTRS_Layout {
			public function __construct() {
				add_action('add_mptrs_hidden_table', array($this, 'hidden_table'), 10, 2);
				add_action('add_mptrs_pagination', array($this, 'pagination'), 10, 3);
            }
			public static function post_select() {
				$label = MPTRS_Function::get_name();
				?>
                <label class="min_400 mptrs_id">
                    <select name="mptrs_id" class="formControl mp_select2" id="mptrs_id" required>
                        <option value="0"><?php esc_html_e('Select', 'theaterly') . ' ' . esc_html($label); ?></option>
						<?php
							$loop = MPTRS_Function::query_post_type(MPTRS_Function::get_cpt());
							$posts = $loop->posts;
							foreach ($posts as $post) {
								?>
                                <option value="<?php echo esc_attr($post->ID); ?>">
									<?php echo esc_html(get_the_title($post->ID)); ?>
                                </option>
								<?php
							}
							wp_reset_postdata();
						?>
                    </select>
                </label>
				<?php
			}
			/*****************************/
			public function hidden_table($hook_name, $data = array()) {
				?>
                <div class="hidden_content">
                    <table>
                        <tbody class="hidden_item">
						<?php do_action($hook_name, $data); ?>
                        </tbody>
                    </table>
                </div>
				<?php
			}
			public function pagination($params, $total_item, $active_page = 0) {
				$per_page = $params['show'] > 1 ? $params['show'] : $total_item;
				?>
                <input type="hidden" name="pagination_per_page" value="<?php echo esc_attr($per_page); ?>"/>
                <input type="hidden" name="pagination_style" value="<?php echo esc_attr($params['pagination-style']); ?>"/>
                <input type="hidden" name="mp_total_item" value="<?php echo esc_attr($total_item); ?>"/>
				<?php if ($total_item > $per_page) { ?>
                    <div class="allCenter pagination_area" data-placeholder>
						<?php
							if ($params['pagination-style'] == 'load_more') {
								?>
                                <button type="button" class="_mpBtn_xs_min_200 pagination_load_more" data-load-more="0">
									<?php esc_html_e('Load More', 'theaterly'); ?>
                                </button>
								<?php
							}
							else {
								$page_mod = $total_item % $per_page;
								$total_page = (int)($total_item / $per_page) + ($page_mod > 0 ? 1 : 0);
								$current_page=$active_page<3?0:$active_page-2;
								$last_page=$active_page<3?5:$active_page+3;
								?>
                                <div class="buttonGroup">
									<?php if ($total_page > 2) { ?>
                                        <button class="_mpBtn_xs page_prev" type="button" title="<?php esc_html_e('GoTO Previous Page', 'theaterly'); ?>" disabled>
                                            <span class="fas fa-chevron-left mp_zero"></span>
                                        </button>
									<?php } ?>
									<?php if ($total_page > 5) { ?>
                                        <button class="_mpBtn_xs ellipse_left" type="button" disabled>
                                            <span class="fas fa-ellipsis-h mp_zero"></span>
                                        </button>
									<?php } ?>
									<?php for ($i = $current_page; $i < $last_page; $i++) { ?>
                                        <button class="_mpBtn_xs <?php echo esc_html($i) == $active_page ? 'active_pagination' : ''; ?>" type="button" data-pagination="<?php echo esc_html($i); ?>"><?php echo esc_html($i + 1); ?></button>
									<?php } ?>

									<?php if ($total_page > 5) { ?>
                                        <button class="_mpBtn_xs ellipse_right" type="button" disabled>
                                            <span class="fas fa-ellipsis-h mp_zero"></span>
                                        </button>
									<?php } ?>

									<?php if ($total_page > 2) { ?>
                                        <button class="_mpBtn_xs page_next" type="button" title="<?php esc_html_e('GoTO Next Page', 'theaterly'); ?>">
                                            <span class="fas fa-chevron-right mp_zero"></span>
                                        </button>
									<?php } ?>
                                </div>
							<?php } ?>
                    </div>
					<?php
				}
			}
			/*****************************/
			public static function switch_button($name, $checked = '') {
				?>
                <label class="roundSwitchLabel">
                    <input type="checkbox" name="<?php echo esc_attr($name); ?>" <?php echo esc_attr($checked); ?>>
                    <span class="roundSwitch" data-mptrs-collapse="#<?php echo esc_attr($name); ?>"></span>
                </label>
				<?php
			}
			public static function popup_button($target_popup_id, $text) {
				?>
                <button type="button" class="_dButton_bgBlue" data-target-popup="<?php echo esc_attr($target_popup_id); ?>">
                    <span class="fas fa-plus-square"></span>
					<?php echo esc_html($text); ?>
                </button>
				<?php
			}
			public static function popup_button_xs($target_popup_id, $text) {
				?>
                <button type="button" class="_dButton_xs_bgBlue" data-target-popup="<?php echo esc_attr($target_popup_id); ?>">
                    <span class="fas fa-plus-square"></span>
					<?php echo esc_html($text); ?>
                </button>
				<?php
			}
			/*****************************/
			public static function add_new_button($button_text, $class = 'add_item', $button_class = '_themeButton_xs_mT_xs', $icon_class = 'fas fa-plus-square') {
				?>
                <button class="<?php echo esc_attr($button_class . ' ' . $class); ?>" type="button">
                    <span class="<?php echo esc_attr($icon_class); ?>"></span>
                    <span class="mL_xs"><?php echo wp_kses_post($button_text); ?></span>
                </button>
				<?php
			}
			public static function move_remove_button() {
				?>
                <div class="allCenter">
                    <div class="buttonGroup max_100">
						<?php
							self::remove_button();
							self::move_button();
						?>
                    </div>
                </div>
				<?php
			}
			public static function remove_button() {
				?>
                <button class="_themeButton_xs item_remove" type="button">
                    <span class="fas fa-trash-alt mp_zero"></span>
                </button>
				<?php
			}
			public static function move_button() {
				?>
                <div class="_themeButton_xs sortable_button" type="">
                    <span class="fas fa-expand-arrows-alt mp_zero"></span>
                </div>
				<?php
			}
			/*****************************/
			public static function bg_image($post_id='',$url='') {
				$thumbnail = $post_id>0?MPTRS_Function::get_image_url($post_id):$url;
				$post_url=$post_id>0?get_the_permalink($post_id):'';
				?>
                <div class="bg_image_area" data-href="<?php echo esc_attr($post_url); ?>" data-placeholder>
                    <div data-bg-image="<?php echo esc_attr($thumbnail); ?>"></div>
                </div>
				<?php
			}
			/*****************************/
			public static function load_more_text($text = '', $length = 150) {
				$text_length = strlen($text);
				if ($text && $text_length > $length) {
					?>
                    <div class="mp_load_more_text_area">
                        <span data-read-close><?php echo esc_html(substr($text, 0, $length)); ?> ....</span>
                        <span data-read-open class="dNone"><?php echo esc_html($text); ?></span>
                        <div data-read data-open-text="<?php esc_attr_e('Load More', 'theaterly'); ?>" data-close-text="<?php esc_attr_e('Less More', 'theaterly'); ?>">
                            <span data-text><?php esc_html_e('Load More', 'theaterly'); ?></span>
                        </div>
                    </div>
					<?php
				}
				else {
					?>
                    <span><?php echo esc_html($text); ?></span>
					<?php
				}
			}
			/*****************************/
			public static function qty_input($input_name, $price, $available_seat = 1, $default_qty = 0, $min_qty = 0, $max_qty = '', $input_type = '',$text='') {
				$min_qty = max($default_qty, $min_qty);
				if ($available_seat > $min_qty) {
					if ($input_type != 'dropdown') {
						?>
                        <div class="groupContent qtyIncDec">
                            <div class="decQty addonGroupContent">
                                <span class="fas fa-minus"></span>
                            </div>
                            <label>
                                <input type="text"
                                       class="formControl inputIncDec mp_number_validation"
                                       data-price="<?php echo esc_attr($price); ?>"
                                       name="<?php echo esc_attr($input_name); ?>"
                                       value="<?php echo esc_attr(max(0, $default_qty)); ?>"
                                       min="<?php echo esc_attr($min_qty); ?>"
                                       max="<?php echo esc_attr($max_qty > 0 ? $max_qty : $available_seat); ?>"
                                />
                            </label>
                            <div class="incQty addonGroupContent">
                                <span class="fas fa-plus"></span>
                            </div>
                        </div>
						<?php
					}
					else {
						?>
                        <label>
                            <select name="<?php echo esc_attr($input_name); ?>" data-price="<?php echo esc_attr($price); ?>" class="formControl">
                                <option selected value="0"><?php echo esc_html__('Please select', 'theaterly').' '.esc_html($text); ?></option>
								<?php
									$max_total = $max_qty > 0 ? $max_qty : $available_seat;
									$min_value=max(1,$min_qty);
									for ($i = $min_value; $i <= $max_total; $i++) {
										?>
                                        <option value="<?php echo esc_html($i); ?>"> <?php echo esc_html($i).' '.esc_html($text); ?> </option>
									<?php } ?>
                            </select>
                        </label>
						<?php
					}
				}
			}
		}
		new MPTRS_Layout();
	}