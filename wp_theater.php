<?php
	/**
	 * Plugin Name: Online Theater System & Reservation - Theaterly
	 * Plugin URI: http://wptheaterly.com
	 * Description: A complete solution for Any kind of theater booking.
     * Requires at least: 5.6
     * Requires PHP: 7.2
	 * Version: 1.0.0
	 * Author: Vaincode
	 * Author URI: http://www.wptheaterly.com/
     * License: GPL v2 or later
	 * Text Domain: wptheaterly
	 * Domain Path: /languages/
	 */
	if (!defined('ABSPATH'))
		die;
	if (!class_exists('wptheater')) {
		class wptheater {
			public function __construct() {
				$this->load_plugin();
				$this->define_constants();
				add_filter('admin_body_class', [$this, 'add_body_class']);
				register_activation_hook( __FILE__, 'wtbm_on_plugin_activation' );

			}
			private function load_plugin() {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				if (!defined('WTBM_PLUGIN_DIR')) {
					define('WTBM_PLUGIN_DIR', dirname(__FILE__));
				}
				if (!defined('WTBM_PLUGIN_URL')) {
					define('WTBM_PLUGIN_URL', plugins_url() . '/' . plugin_basename(dirname(__FILE__)));
				}
													
					/**
					 * Set a transient on plugin activation to trigger the
					 * WooCommerce check / redirect on next admin page load.
					 */
					function wtbm_on_plugin_activation() {
						set_transient( 'wtbm_plugin_activated', true, 60 );
					}

					/**
					 * Always load the WooCommerce Installer module in admin.
					 * It handles: activation redirect when WooCommerce IS active,
					 * and shows the beautiful popup when WooCommerce is NOT active.
					 */
					if ( is_admin() ) {
						require_once WTBM_PLUGIN_DIR . '/inc/MPWEM_Woo_Installer.php';
					require_once WTBM_PLUGIN_DIR . '/inc/WTBM_PDF_Installer.php';
					}


				if ($this->check_woocommerce() == 1) {
					add_action('activated_plugin', array($this, 'activation_redirect'), 90, 1);
					require_once WTBM_PLUGIN_DIR . '/inc/WTBM_Dependencies.php';

				} else {
					add_action('admin_notices', [$this, 'woocommerce_not_active']);
					add_action('activated_plugin', array($this, 'activation_redirect_setup'), 90, 1);
					add_action('admin_enqueue_scripts', [$this, 'my_plugin_admin_scripts']);
			

				}
                if (!defined('MPCRBM_PLUGIN_DIR_PRO')) {
                    define('MPCRBM_PLUGIN_DIR_PRO', dirname(__FILE__));
                }
                if (!defined('MPCRBM_PLUGIN_URL_PRO')) {
                    define('MPCRBM_PLUGIN_URL_PRO', plugins_url() . '/' . plugin_basename(dirname(__FILE__)));
                }

				// add_action('wp_ajax_wtbm_install_woocommerce', [$this, 'wtbm_install_woocommerce_callback']);

			}
			private static function check_woocommerce(): int {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				$plugin_dir = ABSPATH . 'wp-content/plugins/woocommerce';
				if (is_plugin_active('woocommerce/woocommerce.php')) {
					return 1;
				} elseif (is_dir($plugin_dir)) {
					return 2;
				} else {
					return 0;
				}
			}
			public function activation_redirect($plugin) {
				if ($plugin == plugin_basename(__FILE__)) {
					flush_rewrite_rules();
					//exit(esc_url_raw(wp_safe_redirect(admin_url('edit.php?post_type=mptrs_item&page=mptrs_quick_setup'))));
					// exit(esc_url_raw(wp_safe_redirect(admin_url('admin.php?page=mptrs_main_menu'))));
				}
			}
			public function activation_redirect_setup($plugin) {
				if ($plugin == plugin_basename(__FILE__)) {
					//exit(esc_url_raw(wp_safe_redirect(admin_url('admin.php?post_type=mptrs_item&page=mptrs_quick_setup'))));
					// exit(esc_url_raw(wp_safe_redirect(admin_url('admin.php?page=mptrs_quick_setup'))));
				}
			}

			public function woocommerce_not_active() {
				$nonce = wp_create_nonce('wtbm_installer_nonce');
				?>
				<div id="wtbm-dialog-container" style="display:none;text-align: center;" title="Dependency Required">
					<p><strong><?php esc_html_e('Theaterly Manager', 'wptheaterly'); ?></strong> <p> <?php esc_html_e('requires WooCommerce to be installed and active to function correctly', 'wptheaterly'); ?> .</p>
					<p><?php esc_html_e('Would you like to install and activate it now?', 'wptheaterly'); ?></p>
					<div style="text-align: center;">
						<button class="button button-primary" id="wtbm-install-btn" data-nonce="<?php echo esc_attr( $nonce ); ?>">
							<?php esc_html_e('Install & Activate WooCommerce', 'wptheaterly'); ?>
						</button>
						<span class="spinner" style="float:none;"></span>
					</div>
				</div>
				<?php
			}

			function my_plugin_admin_scripts() {
				// Load jQuery UI Dialog and the base theme
				wp_enqueue_script('jquery-ui-dialog');
				wp_enqueue_style('wp-jquery-ui-dialog');
			}

			public function define_constants() {
				define('WTBM_Plan_FILE', __FILE__);
				define('WTBM_Plan_PATH', __DIR__);
				define('WTBM_Plan_API_LINK', WTBM_Plan_FILE . 'api/');
				define('WTBM_Plan_URL', plugins_url('', WTBM_Plan_FILE));
				define('WTBM_Plan_ASSETS', WTBM_Plan_URL . '/assets/');
				define('WTBM_Plan_PLUGIN_NAME', plugin_basename(__FILE__));
			}
			public function add_body_class($classes) {
				$screen = get_current_screen();
				if ($screen && $screen->id === 'toplevel_page_mptrs_main_menu') {
					$classes .= ' wtbm-backend ';
				}
				return $classes;
			}

			function wtbm_install_woocommerce_callback() {
				check_ajax_referer('wtbm_installer_nonce', 'security');
				if (!current_user_can('install_plugins')) {
					wp_send_json_error('Permission denied.');
				}
				include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
				include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
				$slug = 'woocommerce';
				$api = plugins_api('plugin_information', array('slug' => $slug, 'fields' => array('sections' => false)));
				if (is_wp_error($api)) {
					wp_send_json_error('API Error: ' . $api->get_error_message());
				}
				$upgrader = new Plugin_Upgrader(new Automatic_Upgrader_Skin());
				$install = $upgrader->install($api->download_link);
				if (is_wp_error($install)) {
					wp_send_json_error('Installation failed.');
				}
				// Activate the plugin
				$plugin_path = 'woocommerce/woocommerce.php';
				$activate = activate_plugin($plugin_path);

				if (is_wp_error($activate)) {
					wp_send_json_error('Activation failed.');
				}
				$finish_quick_setup = get_option('mptrs_finish_quick_setup') ? get_option('mptrs_finish_quick_setup') : 'No';
				flush_rewrite_rules();
				if($finish_quick_setup == 'Yes') {
					wp_send_json_success(admin_url('admin.php?page=mptrs_main_menu'));
				} else {
					wp_send_json_success(admin_url('admin.php?page=mptrs_main_menu'));
				}
			}
		}
		new wptheater();
	}