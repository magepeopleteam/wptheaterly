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
				$default_color = MPTRS_Function::get_style_settings('default_text_color', '#303030');
				$theme_color = MPTRS_Function::get_style_settings('theme_color', '#f12971');
				$alternate_color = MPTRS_Function::get_style_settings('theme_alternate_color', '#fff');
				$warning_color = MPTRS_Function::get_style_settings('warning_color', '#E67C30');
				$default_fs = MPTRS_Function::get_style_settings('default_font_size', '14') . 'px';
				$fs_h1 = MPTRS_Function::get_style_settings('font_size_h1', '35') . 'px';
				$fs_h2 = MPTRS_Function::get_style_settings('font_size_h2', '30') . 'px';
				$fs_h3 = MPTRS_Function::get_style_settings('font_size_h3', '25') . 'px';
				$fs_h4 = MPTRS_Function::get_style_settings('font_size_h4', '22') . 'px';
				$fs_h5 = MPTRS_Function::get_style_settings('font_size_h5', '18') . 'px';
				$fs_h6 = MPTRS_Function::get_style_settings('font_size_h6', '16') . 'px';
				$fs_label = MPTRS_Function::get_style_settings('font_size_label', '16') . 'px';
				$button_fs = MPTRS_Function::get_style_settings('button_font_size', '16') . 'px';
				$button_color = MPTRS_Function::get_style_settings('button_color', $alternate_color);
				$button_bg = MPTRS_Function::get_style_settings('button_bg', '#ea8125');
				$section_bg = MPTRS_Function::get_style_settings('section_bg', '#FAFCFE');
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
					/*****Font size********/
					:root {
						--mptrs_fs: <?php echo esc_attr($default_fs); ?>;
						--mptrs_fs_small: 10px;
						--mptrs_fs_label: <?php echo esc_attr($fs_label); ?>;
						--mptrs_fs_h6: <?php echo esc_attr($fs_h6); ?>;
						--mptrs_fs_h5: <?php echo esc_attr($fs_h5); ?>;
						--mptrs_fs_h4: <?php echo esc_attr($fs_h4); ?>;
						--mptrs_fs_h3: <?php echo esc_attr($fs_h3); ?>;
						--mptrs_fs_h2: <?php echo esc_attr($fs_h2); ?>;
						--mptrs_fs_h1: <?php echo esc_attr($fs_h1); ?>;
						--mptrs_fw: normal;
						--mptrs_fw-thin: 300; /*font weight medium*/
						--mptrs_fw-normal: 500; /*font weight medium*/
						--mptrs_fw-medium: 600; /*font weight medium*/
						--mptrs_fw-bold: bold; /*font weight bold*/
					}
					/*****Button********/
					:root {
						--mptrs_button_bg: <?php echo esc_attr($button_bg); ?>;
						--mptrs_button_color: <?php echo esc_attr($button_color); ?>;
						--mptrs_button_fs: <?php echo esc_attr($button_fs); ?>;
						--mptrs_button_height: 40px;
						--mptrs_button_height_xs: 30px;
						--mptrs_button_width: 120px;
						--mptrs_button_shadows: 0 8px 12px rgb(51 65 80 / 6%), 0 14px 44px rgb(51 65 80 / 11%);
					}
					/*******Color***********/
					:root {
						--mptrs_color_d: <?php echo esc_attr($default_color); ?>;
						--mptrs_color_border: #DDD;
						--mptrs_color_active: #0E6BB7;
						--mptrs_color_section: <?php echo esc_attr($section_bg); ?>;
						--mptrs_color_theme: <?php echo esc_attr($theme_color); ?>;
						--mptrs_color_theme_ee: <?php echo esc_attr($theme_color).'ee'; ?>;
						--mptrs_color_theme_cc: <?php echo esc_attr($theme_color).'cc'; ?>;
						--mptrs_color_theme_aa: <?php echo esc_attr($theme_color).'aa'; ?>;
						--mptrs_color_theme_88: <?php echo esc_attr($theme_color).'88'; ?>;
						--mptrs_color_theme_77: <?php echo esc_attr($theme_color).'77'; ?>;
						--mptrs_color_theme_alter: <?php echo esc_attr($alternate_color); ?>;
						--mptrs_color_warning: <?php echo esc_attr($warning_color); ?>;
						--mptrs_color_black: #000;
						--mptrs_color_success: #03A9F4;
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
					@media only screen and (max-width: 1100px) {
						:root {
							--mptrs_fs: 14px;
							--mptrs_fs_small: 12px;
							--mptrs_fs_label: 15px;
							--mptrs_fs_h4: 20px;
							--mptrs_fs_h3: 22px;
							--mptrs_fs_h2: 25px;
							--mptrs_fs_h1: 30px;
							--mptrs_dmpl: 32px;
							--mptrs_dmp: 16px;
							--mptrs_dmp_negetive: -16px;
							--mptrs_dmp_xs: 8px;
							--mptrs_dmp_xs_negative: -8px;
						}
					}
					@media only screen and (max-width: 700px) {
						:root {
							--mptrs_fs: 12px;
							--mptrs_fs_small: 10px;
							--mptrs_fs_label: 13px;
							--mptrs_fs_h6: 15px;
							--mptrs_fs_h5: 16px;
							--mptrs_fs_h4: 18px;
							--mptrs_fs_h3: 20px;
							--mptrs_fs_h2: 22px;
							--mptrs_fs_h1: 24px;
							--mptrs_dmp: 10px;
							--mptrs_dmp_xs: 5px;
							--mptrs_dmp_xs_negative: -5px;
							--mptrs_button_fs: 14px;
						}
					}
                </style>
				<?php
			}
		}
		new MPTRS_Style();
	}