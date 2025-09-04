<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPTRS_Dependencies')) {
		class MPTRS_Dependencies {
			public function __construct() {
				add_action('init', [$this, 'language_load']);
				$this->load_file();
				add_action('wp_enqueue_scripts', [$this, 'frontend_script'], 90);
				add_action('admin_enqueue_scripts', [$this, 'admin_scripts'], 90);
				add_action('admin_head', array($this, 'add_admin_head'), 5);
				add_action('wp_head', array($this, 'add_frontend_head'), 5);
			}
			public function language_load(): void {
				$plugin_dir = basename(dirname(__DIR__)) . "/languages/";
				load_plugin_textdomain('theaterly', false, $plugin_dir);
			}
			private function load_file(): void {
				require_once MPTRS_PLUGIN_DIR . '/inc/MPTRS_Function.php';
				require_once MPTRS_PLUGIN_DIR . '/inc/WTBM_Layout_Functions.php';
				require_once MPTRS_PLUGIN_DIR . '/inc/MPTRS_Slider.php';
				require_once MPTRS_PLUGIN_DIR . '/inc/MPTRS_Style.php';
				require_once MPTRS_PLUGIN_DIR . '/inc/MPTRS_Select_Icon_image.php';
				require_once MPTRS_PLUGIN_DIR . '/inc/MPTRS_Query.php';
				require_once MPTRS_PLUGIN_DIR . '/inc/MPTRS_Layout.php';
				require_once MPTRS_PLUGIN_DIR . '/admin/MPTRS_Admin.php';
				require_once MPTRS_PLUGIN_DIR . '/frontend/MPTRS_Frontend.php';
			}
			public function global_enqueue() {
                wp_enqueue_script( 'jquery' );
                wp_enqueue_script( 'jquery-ui-core' );
                wp_enqueue_script( 'jquery-ui-datepicker' );
                wp_enqueue_script('jquery-ui-mouse');
                wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_script('jquery-ui-dialog');
				wp_enqueue_style('mp_jquery_ui', MPTRS_PLUGIN_URL . '/assets/jquery-ui.min.css', array(), '1.13.2');
				wp_enqueue_style('mp_font_awesome', MPTRS_PLUGIN_URL . '/assets/admin/all.min.css', array(), '5.15.3');
				wp_enqueue_style('mp_select_2', MPTRS_PLUGIN_URL . '/assets/select_2/select2.min.css', array(), '4.0.13');
				wp_enqueue_script('mp_select_2', MPTRS_PLUGIN_URL . '/assets/select_2/select2.min.js', array(), '4.0.13',true);
				wp_enqueue_style('mp_owl_carousel', MPTRS_PLUGIN_URL . '/assets/owl_carousel/owl.carousel.min.css', array(), '2.3.4');
				wp_enqueue_script('mp_owl_carousel', MPTRS_PLUGIN_URL . '/assets/owl_carousel/owl.carousel.min.js', array(), '2.3.4',true);
				wp_enqueue_style('mptrs_global', MPTRS_PLUGIN_URL . '/assets/mp_style/mptrs_global.css', array(), time());
				wp_enqueue_script('mptrs_global', MPTRS_PLUGIN_URL . '/assets/mp_style/mptrs_global.js',  ['jquery'], time());
				do_action('add_mptrs_global_enqueue');
			}
			public function admin_scripts() {
				$this->global_enqueue();
				wp_enqueue_editor();
				wp_enqueue_media();
				//admin script
				wp_enqueue_script('jquery-ui-sortable');
				wp_enqueue_style('wp-color-picker');
				wp_enqueue_script('wp-color-picker');
				wp_enqueue_style('wp-codemirror');
				wp_enqueue_script('wp-codemirror');
				//loading Time picker
				wp_enqueue_script('jquery.timepicker.min', MPTRS_PLUGIN_URL . '/assets/admin/jquery.timepicker.min.js', array('jquery'), time(), true);
				wp_enqueue_style('jquery.timepicker.min', MPTRS_PLUGIN_URL . '/assets/admin/jquery.timepicker.min.css', array(), time());
				//=====================//
				wp_enqueue_script('form-field-dependency', MPTRS_PLUGIN_URL . '/assets/admin/form-field-dependency.js', array('jquery'), '1.0', true);
				// admin setting global
				wp_enqueue_script('mptrs_admin_settings', MPTRS_PLUGIN_URL . '/assets/admin/mptrs_admin_settings.js', array('jquery'), time(), true);
				wp_enqueue_script('mptrs_admin_menu', MPTRS_PLUGIN_URL . '/assets/admin/mptrs_admin_menu.js', array('jquery'), time(), true);
				wp_enqueue_style('mptrs_admin_settings', MPTRS_PLUGIN_URL . '/assets/admin/mptrs_admin_settings.css', array(), time());
				wp_enqueue_style('mptrs_admin_menu', MPTRS_PLUGIN_URL . '/assets/admin/mptrs_admin_menu.css', array(), time());
				// ****custom************//
				wp_enqueue_style('mptrs_admin', MPTRS_PLUGIN_URL . '/assets/admin/mptrs_admin.css', [], time());
				wp_enqueue_script('mptrs_admin', MPTRS_PLUGIN_URL . '/assets/admin/mptrs_admin.js', ['jquery'], time(), true);


                wp_enqueue_script('create_seat_plan', MPTRS_PLUGIN_URL . '/assets/admin/create_seat_plan.js', ['jquery'], time(), true);
                wp_enqueue_style('create_seat_plan', MPTRS_PLUGIN_URL . '/assets/admin/create_seat_plan.css', array(), time());

				wp_localize_script('mptrs_admin', 'mptrs_admin_ajax', array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce'    => wp_create_nonce('mptrs_admin_nonce')
				));
				do_action('add_mptrs_admin_script');
			}
			public function frontend_script() {
				$this->global_enqueue();
				wp_enqueue_style('mptrs', MPTRS_PLUGIN_URL . '/assets/frontend/mptrs.css', [], time());
				wp_enqueue_script('mptrs', MPTRS_PLUGIN_URL . '/assets/frontend/mptrs.js', ['jquery'], time(), true);
				wp_enqueue_style('wtbm_registration', MPTRS_PLUGIN_URL . '/assets/frontend/wtbm_registration.css', [], time());
				wp_enqueue_script('wtbm_registration', MPTRS_PLUGIN_URL . '/assets/frontend/wtbm_registration.js', ['jquery'], time(), true);
				wp_localize_script('wtbm_registration', 'wtbm_ajax', array(
					'ajax_url' => admin_url('admin-ajax.php'),
					'nonce'    => wp_create_nonce('wtbm_nonce')
				));
				do_action('add_mptrs_frontend_script');
			}
			public function add_admin_head() {
				$this->js_constant();
			}
			public function add_frontend_head() {
				$this->js_constant();
				$this->custom_css();
			}
			public function js_constant() {
				?>
				<script type="text/javascript">
                    let mptrs_currency_symbol = "";
                    let mptrs_currency_position = "";
                    let mptrs_currency_decimal = "";
                    let mptrs_currency_thousands_separator = "";
                    let mptrs_num_of_decimal = "";
                    let mptrs_empty_image_url = "<?php echo esc_js(MPTRS_PLUGIN_URL . '/assets/images/no_image.png'); ?>";
                    let mptrs_date_format = "<?php echo esc_js(MPTRS_Function::get_settings('mptrs_global_settings', 'date_format', 'D d M , yy')); ?>";
                    let mptrs_date_format_without_year = "<?php echo esc_js(MPTRS_Function::get_settings('mptrs_global_settings', 'date_format_without_year', 'D d M')); ?>";
				</script>
				<?php
				if (MPTRS_Function::check_woocommerce() == 1) {
					?>
					<script type="text/javascript">
                        mptrs_currency_symbol = "<?php echo esc_js(get_woocommerce_currency_symbol()); ?>";
                        mptrs_currency_position = "<?php echo esc_js(get_option('woocommerce_currency_pos')); ?>";
                        mptrs_currency_decimal = "<?php echo esc_js(wc_get_price_decimal_separator()); ?>";
                        mptrs_currency_thousands_separator = "<?php echo esc_js(wc_get_price_thousand_separator()); ?>";
                        mptrs_num_of_decimal = "<?php echo esc_js(get_option('woocommerce_price_num_decimals', 2)); ?>";
					</script>
					<?php
				}
			}
			public function custom_css() {
				$custom_css = MPTRS_Function::get_settings('mptrs_add_custom_css', 'custom_css');
				ob_start();
				?>
				<style>
					<?php echo wp_kses_post($custom_css); ?>
				</style>
				<?php
				echo wp_kses_post(ob_get_clean());
			}
		}
		new MPTRS_Dependencies();
	}