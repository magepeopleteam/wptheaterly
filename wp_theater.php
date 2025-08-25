<?php
	/**
	 * Plugin Name: WordPress WpTheaterly
	 * Plugin URI: http://mage-people.com
	 * Description: A complete solution for Any kind of theater booking.
	 * Version: 1.0.0
	 * Author: MagePeople Team
	 * Author URI: http://www.mage-people.com/
	 * Text Domain: wptheaterly
	 * Domain Path: /languages/
	 */
	if (!defined('ABSPATH'))
		die;
	if (!class_exists('wptheater')) {
		class wptheater {
			public function __construct() {
				$this->load_plugin();
			}
			private function load_plugin() {
				include_once(ABSPATH . 'wp-admin/includes/plugin.php');
				if (!defined('MPTRS_PLUGIN_DIR')) {
					define('MPTRS_PLUGIN_DIR', dirname(__FILE__));
				}
				if (!defined('MPTRS_PLUGIN_URL')) {
					define('MPTRS_PLUGIN_URL', plugins_url() . '/' . plugin_basename(dirname(__FILE__)));
				}
				require_once MPTRS_PLUGIN_DIR . '/inc/MPTRS_Dependencies.php';
				if (MPTRS_Function::check_woocommerce() == 1) {
					add_action('activated_plugin', array($this, 'activation_redirect'), 90, 1);
				} else {
					require_once MPTRS_PLUGIN_DIR . '/Admin/MPTRS_Quick_Setup.php';
					add_action('admin_notices', [$this, 'woocommerce_not_active']);
					add_action('activated_plugin', array($this, 'activation_redirect_setup'), 90, 1);
				}
			}
			public function activation_redirect($plugin) {
				if ($plugin == plugin_basename(__FILE__)) {
					flush_rewrite_rules();
					exit(esc_url_raw(wp_redirect(admin_url('edit.php?post_type=mptrs_item&page=mptrs_quick_setup'))));
				}
			}
			public function activation_redirect_setup($plugin) {
				if ($plugin == plugin_basename(__FILE__)) {
					exit(esc_url_raw(wp_redirect(admin_url('admin.php?post_type=mptrs_item&page=mptrs_quick_setup'))));
				}
			}
			public function woocommerce_not_active() {
				$wc_install_url = get_admin_url() . 'plugin-install.php?s=woocommerce&tab=search&type=term';
				$text = esc_html__('You Must Install WooCommerce Plugin before activating Tablely Manager, Because It is dependent on Woocommerce Plugin.', 'theaterly') . '<a class="btn button" href="' . esc_html($wc_install_url) . '">' . esc_html__('Click Here to Install', 'theaterly') . '</a>';
				printf('<div class="error" style="background:red; color:#fff;"><p>%s</p></div>', wp_kses_post($text));
			}
		}
		new wptheater();
	}