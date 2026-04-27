<?php
/**
 * WTBM PDF Support Installer
 * Handles MagePeople PDF Support dependency check, beautiful popup display,
 * and AJAX-based installation & activation from GitHub.
 * The popup shows on the plugin's admin page when PDF Support is not active.
 *
 * @package WPTheaterly
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'WTBM_PDF_Installer' ) ) {

	class WTBM_PDF_Installer {

		/**
		 * GitHub ZIP download URL for MagePeople PDF Support.
		 */
		private $github_zip_url = 'https://github.com/magepeopleteam/magepeople-pdf-support/archive/master.zip';

		/**
		 * Plugin slug (folder name after extraction).
		 */
		private $plugin_slug = 'magepeople-pdf-support-master';

		/**
		 * Expected plugin file path relative to plugins directory.
		 */
		private $plugin_file = 'magepeople-pdf-support-master/mage-pdf.php';

		/**
		 * Constructor – hooks into WordPress.
		 */
		public function __construct() {
			// Enqueue popup assets on admin pages (only outputs if PDF Support is missing)
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
			// Render the popup markup in admin footer
			add_action( 'admin_footer', array( $this, 'render_popup' ) );
			// AJAX handlers for install & activate
			add_action( 'wp_ajax_wtbm_install_pdf_support', array( $this, 'ajax_install_pdf_support' ) );
			add_action( 'wp_ajax_wtbm_activate_pdf_support', array( $this, 'ajax_activate_pdf_support' ) );
		}

		/**
		 * Check if MagePeople PDF Support plugin file exists (installed but maybe not active).
		 *
		 * @return bool
		 */
		private function is_pdf_installed() {
			$plugin_file = WP_PLUGIN_DIR . '/' . $this->plugin_file;
			return file_exists( $plugin_file );
		}

		/**
		 * Check if MagePeople PDF Support is active.
		 *
		 * @return bool
		 */
		private function is_pdf_active() {
			return function_exists( 'Mage_PDF_Support_init' );
		}

		/**
		 * Should we show the popup on this page load?
		 * Only show on the plugin's own admin page when PDF Support is not active.
		 *
		 * @return bool
		 */
		private function should_show_popup() {
			if ( $this->is_pdf_active() ) {
				return false;
			}

			$screen = get_current_screen();
			if ( $screen && $screen->id === 'toplevel_page_mptrs_main_menu' ) {
				return true;
			}

			return false;
		}

		/**
		 * Enqueue CSS & JS for the popup only when needed.
		 */
		public function enqueue_assets() {
			if ( ! $this->should_show_popup() ) {
				return;
			}

			wp_enqueue_style(
				'wtbm-pdf-installer',
				WTBM_PLUGIN_URL . '/assets/admin/wtbm_pdf_installer.css',
				array(),
				filemtime( WTBM_PLUGIN_DIR . '/assets/admin/wtbm_pdf_installer.css' )
			);

			wp_enqueue_script(
				'wtbm-pdf-installer',
				WTBM_PLUGIN_URL . '/assets/admin/wtbm_pdf_installer.js',
				array( 'jquery' ),
				filemtime( WTBM_PLUGIN_DIR . '/assets/admin/wtbm_pdf_installer.js' ),
				true
			);

			wp_localize_script( 'wtbm-pdf-installer', 'wtbm_pdf_installer', array(
				'ajax_url'         => admin_url( 'admin-ajax.php' ),
				'install_nonce'    => wp_create_nonce( 'wtbm_install_pdf' ),
				'activate_nonce'   => wp_create_nonce( 'wtbm_activate_pdf' ),
				'redirect_url'     => admin_url( 'admin.php?page=mptrs_main_menu' ),
				'pdf_installed'    => $this->is_pdf_installed() ? 'yes' : 'no',
				'i18n'             => array(
					'installing'     => __( 'Installing MagePeople PDF Support...', 'wptheaterly' ),
					'activating'     => __( 'Activating MagePeople PDF Support...', 'wptheaterly' ),
					'success'        => __( 'PDF Support activated successfully!', 'wptheaterly' ),
					'redirecting'    => __( 'Redirecting...', 'wptheaterly' ),
					'error'          => __( 'Something went wrong. Please try again.', 'wptheaterly' ),
					'install_error'  => __( 'Installation failed. Please install MagePeople PDF Support manually.', 'wptheaterly' ),
					'activate_error' => __( 'Activation failed. Please activate MagePeople PDF Support manually.', 'wptheaterly' ),
				),
			) );
		}

		/**
		 * Render the popup HTML in admin footer.
		 */
		public function render_popup() {
			if ( ! $this->should_show_popup() ) {
				return;
			}

			$is_installed = $this->is_pdf_installed();
			$btn_text     = $is_installed
				? __( 'Activate PDF Support', 'wptheaterly' )
				: __( 'Install & Activate PDF Support', 'wptheaterly' );
			?>
			<!-- WTBM PDF Support Installer Popup Overlay -->
			<div id="wtbm-pdf-overlay" class="wtbm-pdf-overlay">
				<div class="wtbm-pdf-popup">

					<!-- Header strip -->
					<div class="wtbm-pdf-header">
						<div class="wtbm-pdf-header-icon">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
								<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</div>
						<span class="wtbm-pdf-header-text"><?php esc_html_e( 'WP Theaterly', 'wptheaterly' ); ?></span>
					</div>

					<!-- Icon -->
					<div class="wtbm-pdf-icon-wrapper">
						<div class="wtbm-pdf-icon">
							<svg width="40" height="40" viewBox="0 0 24 24" fill="none">
								<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.5"/>
								<path d="M12 8v4M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
						</div>
					</div>

					<!-- Content -->
					<div class="wtbm-pdf-content">
						<h2 class="wtbm-pdf-title"><?php esc_html_e( 'PDF Support Required', 'wptheaterly' ); ?></h2>
						<p class="wtbm-pdf-desc">
							<?php esc_html_e( 'WP Theaterly requires MagePeople PDF Support to generate and download PDF tickets. Please install and activate MagePeople PDF Support to continue using this feature.', 'wptheaterly' ); ?>
						</p>
					</div>

					<!-- Feature highlights -->
					<div class="wtbm-pdf-features">
						<div class="wtbm-pdf-feature">
							<span class="wtbm-pdf-feature-icon">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4.3L6 11.6 2.7 8.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<span><?php esc_html_e( 'PDF ticket generation', 'wptheaterly' ); ?></span>
						</div>
						<div class="wtbm-pdf-feature">
							<span class="wtbm-pdf-feature-icon">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4.3L6 11.6 2.7 8.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<span><?php esc_html_e( 'Downloadable tickets', 'wptheaterly' ); ?></span>
						</div>
						<div class="wtbm-pdf-feature">
							<span class="wtbm-pdf-feature-icon">
								<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13.3 4.3L6 11.6 2.7 8.3" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
							</span>
							<span><?php esc_html_e( 'Custom PDF templates', 'wptheaterly' ); ?></span>
						</div>
					</div>

					<!-- Progress area (hidden by default) -->
					<div id="wtbm-pdf-progress" class="wtbm-pdf-progress" style="display:none;">
						<div class="wtbm-pdf-progress-bar">
							<div id="wtbm-pdf-progress-fill" class="wtbm-pdf-progress-fill"></div>
						</div>
						<p id="wtbm-pdf-status-text" class="wtbm-pdf-status-text"></p>
					</div>

					<!-- Action buttons -->
					<div class="wtbm-pdf-actions">
						<button type="button" id="wtbm-pdf-install-btn" class="wtbm-pdf-btn wtbm-pdf-btn-primary">
							<span class="wtbm-pdf-btn-icon">
								<svg width="18" height="18" viewBox="0 0 20 20" fill="none">
									<path d="M10 3v10m0 0l-4-4m4 4l4-4M3 17h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</span>
							<span class="wtbm-pdf-btn-text"><?php echo esc_html( $btn_text ); ?></span>
						</button>
						<a href="https://github.com/magepeopleteam/magepeople-pdf-support" target="_blank" class="wtbm-pdf-btn wtbm-pdf-btn-secondary">
							<?php esc_html_e( 'View on GitHub', 'wptheaterly' ); ?>
						</a>
					</div>

					<!-- Footer note -->
					<p class="wtbm-pdf-footer-note">
						<svg width="14" height="14" viewBox="0 0 14 14" fill="none" style="vertical-align: -2px; flex-shrink: 0;">
							<path d="M7 1a6 6 0 100 12A6 6 0 007 1zm0 8.5a.75.75 0 110-1.5.75.75 0 010 1.5zM7.75 6.25a.75.75 0 01-1.5 0V4a.75.75 0 011.5 0v2.25z" fill="currentColor"/>
						</svg>
						<?php esc_html_e( 'MagePeople PDF Support is free and open-source, hosted on GitHub.', 'wptheaterly' ); ?>
					</p>
				</div>
			</div>
			<?php
		}

		/**
		 * AJAX: Install MagePeople PDF Support from GitHub ZIP.
		 */
		public function ajax_install_pdf_support() {
			check_ajax_referer( 'wtbm_install_pdf', 'nonce' );

			if ( ! current_user_can( 'install_plugins' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to install plugins.', 'wptheaterly' ) ) );
			}

			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/misc.php';
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			$upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
			$result   = $upgrader->install( $this->github_zip_url );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			}

			if ( $result === false ) {
				wp_send_json_error( array( 'message' => __( 'Installation failed.', 'wptheaterly' ) ) );
			}

			wp_send_json_success( array( 'message' => __( 'MagePeople PDF Support installed successfully.', 'wptheaterly' ) ) );
		}

		/**
		 * AJAX: Activate MagePeople PDF Support plugin.
		 */
		public function ajax_activate_pdf_support() {
			check_ajax_referer( 'wtbm_activate_pdf', 'nonce' );

			if ( ! current_user_can( 'activate_plugins' ) ) {
				wp_send_json_error( array( 'message' => __( 'You do not have permission to activate plugins.', 'wptheaterly' ) ) );
			}

			$result = activate_plugin( $this->plugin_file );

			if ( is_wp_error( $result ) ) {
				wp_send_json_error( array( 'message' => $result->get_error_message() ) );
			}

			wp_send_json_success( array( 'message' => __( 'MagePeople PDF Support activated successfully!', 'wptheaterly' ) ) );
		}
	}

	new WTBM_PDF_Installer();
}