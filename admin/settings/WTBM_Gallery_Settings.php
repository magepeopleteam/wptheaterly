<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPTRS_Gallery_Settings')) {
		class MPTRS_Gallery_Settings {
			public function __construct() {
				add_action('wtbm_add_settings_tab_content', [$this, 'gallery_settings'], 10, 1);
			}
			public function gallery_settings($post_id) {
				$display = WTBM_Function::get_post_info($post_id, 'mptrs_display_slider', 'off');
				$active = $display == 'off' ? '' : 'mActive';
				$checked = $display == 'off' ? '' : 'checked';
				$image_ids = WTBM_Function::get_post_info($post_id, 'mptrs_slider_images', array());
				?>
				<div class="tabsItem" data-tabs="#mptrs_settings_gallery">
					<h5 class="dFlex">
						<span class="mR"><?php esc_html_e('On/Off Slider', 'wptheaterly'); ?></span>
						<?php WTBM_Layout::switch_button('mptrs_display_slider', $checked); ?>
					</h5>
					<?php WTBM_Settings::info_text('mptrs_display_slider'); ?>
					<div class="divider"></div>
					<div data-collapse="#mptrs_display_slider" class="<?php echo esc_attr($active); ?>">
						<table>
							<tbody>
							<tr>
								<th><?php esc_html_e('Gallery Images ', 'wptheaterly'); ?></th>
								<td colspan="3"><?php do_action('mptrs_add_multi_image', 'mptrs_slider_images', $image_ids); ?></td>
							</tr>
							<tr>
								<td colspan="4"><?php WTBM_Settings::info_text('mptrs_slider_images'); ?></td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
				<?php
			}
		}
		new MPTRS_Gallery_Settings();
	}