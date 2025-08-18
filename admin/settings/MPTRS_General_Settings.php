<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPTRS_General_Settings')) {
		class MPTRS_General_Settings {
			public function __construct() {
				add_action('add_mptrs_settings_tab_content', [$this, 'general_settings'], 10, 1);
			}
			public function general_settings($post_id) {
				?>
                <div class="tabsItem" data-tabs="#mptrs_general_info">
                    <h2><?php esc_html_e('General Information Settings', 'theaterly'); ?></h2>
                </div>
				<?php
			}
		}
		new MPTRS_General_Settings();
	}