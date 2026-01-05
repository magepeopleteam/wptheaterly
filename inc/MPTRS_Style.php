<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPTRS_Style')) {
		class MPTRS_Style {
			public function __construct() {
				add_action('wp_head', array($this, 'add_global_style'), 100);
				add_action('admin_head', array($this, 'add_global_style'), 100);
			}
			public function add_global_style() {
				$default_color = MPTRS_Function::get_style_settings('default_text_color', '#333');
				$theme_color = MPTRS_Function::get_style_settings('theme_color', '#a855f7');
				$alternate_color = MPTRS_Function::get_style_settings('theme_alternate_color', '#fff');
				?>
                <style>
					:root {
						--mptrs_container_Width: 1320px;
						--mptrs_sidebar_left: 280px;
						--mptrs_sidebar_right: 300px;
						--mptrs_main_section: calc(100% - 300px);
						--mptrs_dmpl: 40px;
						--mptrs_dmp: 20px;
						--mptrs_dmp_negetive: -20px;
						--mptrs_dmp_xs: 10px;
						--mptrs_dmp_xs_negative: -10px;
						--mptrs_dbrl: 10px;
						--mptrs_dbr: 5px;
						--mptrs_shadow: 0 0 2px #665F5F7A;
					}
					/*******Color***********/
					:root {
						--mptrs_color_d: <?php echo esc_attr($default_color); ?>;
						--mptrs_color_border: #DDD;
						--mptrs_color_active: <?php echo esc_attr($theme_color); ?>;
						--mptrs_color_theme: <?php echo esc_attr($theme_color); ?>;
						--mptrs_color_theme_ee: <?php echo esc_attr($theme_color).'ee'; ?>;
						--mptrs_color_theme_cc: <?php echo esc_attr($theme_color).'cc'; ?>;
						--mptrs_color_theme_aa: <?php echo esc_attr($theme_color).'aa'; ?>;
						--mptrs_color_theme_88: <?php echo esc_attr($theme_color).'88'; ?>;
						--mptrs_color_theme_77: <?php echo esc_attr($theme_color).'77'; ?>;
						--mptrs_color_theme_66: <?php echo esc_attr($theme_color).'66'; ?>;
						--mptrs_color_theme_55: <?php echo esc_attr($theme_color).'55'; ?>;
						--mptrs_color_theme_44: <?php echo esc_attr($theme_color).'44'; ?>;
						--mptrs_color_theme_33: <?php echo esc_attr($theme_color).'33'; ?>;
						--mptrs_color_theme_22: <?php echo esc_attr($theme_color).'22'; ?>;
						--mptrs_color_theme_11: <?php echo esc_attr($theme_color).'11'; ?>;
						--mptrs_color_theme_alter: <?php echo esc_attr($alternate_color); ?>;
						--mptrs_color_black: #000;
						--mptrs_color_success: <?php echo esc_attr($theme_color); ?>;
						--mptrs_color_danger: #C00;
						--mptrs_color_required: #C00;
						--mptrs_color_white: #FFFFFF;
						--mptrs_color_light: #F2F2F2;
						--mptrs_color_light_1: #BBB;
						--mptrs_color_light_2: #EAECEE;
						--mptrs_color_light_3: #878787;
						--mptrs_color_light_4: #f9f9f9;
						--mptrs_color_info: #666;
						--mptrs_color_yellow: #FEBB02;
						--mptrs_color_blue: #815DF2;
						--mptrs_color_navy_blue: #007CBA;
						--mptrs_color_1: #0C5460;
						--mptrs_color_2: #0CB32612;
						--mptrs_color_3: #FAFCFE;
						--mptrs_color_4: #6148BA;
						--mptrs_color_5: #BCB;
					}
                </style>
				<?php
			}
		}
		new MPTRS_Style();
	}