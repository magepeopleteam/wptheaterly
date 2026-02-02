<?php
	/**
	 * Plugin Name: WordPress WpTheaterly
	 * Plugin URI: http://mage-people.com
	 * Description: A complete solution for Any kind of theater booking.
     * Requires at least: 5.6
     * Requires PHP: 7.2
	 * Version: 1.0.0
	 * Author: MagePeople Team
	 * Author URI: http://www.mage-people.com/
     * License: GPL v2 or later
	 * Text Domain: wptheaterly
	 * Domain Path: /languages/
	 */
	if (!defined('ABSPATH'))
		die;
if ( ! class_exists( 'wptheater' ) ) {

	class wptheater {

		public function __construct() {
			$this->load_plugin();
			$this->define_constants();
			add_filter( 'admin_body_class', [ $this, 'add_body_class' ] );
		}

		private function load_plugin() {

			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			if ( ! defined( 'WTBM_PLUGIN_DIR' ) ) {
				define( 'WTBM_PLUGIN_DIR', dirname( __FILE__ ) );
			}

			if ( ! defined( 'WTBM_PLUGIN_URL' ) ) {
				define( 'WTBM_PLUGIN_URL', plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) ) );
			}

			if ( class_exists( 'WooCommerce', false ) ) {

				require_once WTBM_PLUGIN_DIR . '/inc/WTBM_Dependencies.php';
				add_action( 'activated_plugin', [ $this, 'activation_redirect' ], 90, 1 );

			} else {

				add_action( 'admin_notices', [ $this, 'woocommerce_not_active' ] );
				add_action( 'activated_plugin', [ $this, 'activation_redirect_setup' ], 90, 1 );
			}

			if ( ! defined( 'MPCRBM_PLUGIN_DIR_PRO' ) ) {
				define( 'MPCRBM_PLUGIN_DIR_PRO', dirname( __FILE__ ) );
			}

			if ( ! defined( 'MPCRBM_PLUGIN_URL_PRO' ) ) {
				define( 'MPCRBM_PLUGIN_URL_PRO', plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) ) );
			}
		}
		
		public function activation_redirect( $plugin ) {
			if ( $plugin === plugin_basename( __FILE__ ) ) {
				flush_rewrite_rules();
				wp_safe_redirect( admin_url( 'admin.php?page=mptrs_main_menu' ) );
				exit;
			}
		}

		public function activation_redirect_setup( $plugin ) {
			if ( $plugin === plugin_basename( __FILE__ ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=mptrs_quick_setup' ) );
				exit;
			}
		}

		/**
		 * WooCommerce missing notice with Install / Activate button
		 */
		public function woocommerce_not_active() {

			if ( ! current_user_can( 'install_plugins' ) ) {
				return;
			}

			$plugin_slug = 'woocommerce';
			$plugin_file = 'woocommerce/woocommerce.php';

			$is_installed = file_exists( WP_PLUGIN_DIR . '/' . $plugin_file );

			if ( $is_installed ) {
				$action_url  = wp_nonce_url(
					admin_url( 'plugins.php?action=activate&plugin=' . $plugin_file ),
					'activate-plugin_' . $plugin_file
				);
				$button_text = __( 'Activate WooCommerce', 'wptheaterly' );
			} else {
				$action_url  = wp_nonce_url(
					admin_url( 'update.php?action=install-plugin&plugin=' . $plugin_slug ),
					'install-plugin_' . $plugin_slug
				);
				$button_text = __( 'Install WooCommerce', 'wptheaterly' );
			}
			?>
			<div class="notice notice-error is-dismissible">
				<p>
					<strong><?php esc_html_e( 'WooCommerce is required.', 'wptheaterly' ); ?></strong><br>
					<?php esc_html_e( 'This plugin depends on WooCommerce. Please install and activate WooCommerce to continue.', 'wptheaterly' ); ?>
				</p>
				<p>
					<a href="<?php echo esc_url( $action_url ); ?>" class="button button-primary">
						<?php echo esc_html( $button_text ); ?>
					</a>
				</p>
			</div>
			<?php
		}

		public function define_constants() {
			define( 'WTBM_Plan_FILE', __FILE__ );
			define( 'WTBM_Plan_PATH', __DIR__ );
			define( 'WTBM_Plan_API_LINK', WTBM_Plan_FILE . 'api/' );
			define( 'WTBM_Plan_URL', plugins_url( '', WTBM_Plan_FILE ) );
			define( 'WTBM_Plan_ASSETS', WTBM_Plan_URL . '/assets/' );
			define( 'WTBM_Plan_PLUGIN_NAME', plugin_basename( __FILE__ ) );
		}

		public function add_body_class( $classes ) {
			$screen = get_current_screen();
			if ( $screen && $screen->id === 'toplevel_page_mptrs_main_menu' ) {
				$classes .= ' wtbm-backend ';
			}
			return $classes;
		}
	}

	new wptheater();
}