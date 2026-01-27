<?php
/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.
if ( ! class_exists('WTBM_Admin_Pro') ) {
    class WTBM_Admin_Pro
    {
        public function __construct() {
            $this->load_file();
            add_action( 'wtbm_status_notice_sec', array( $this, 'status_notice_sec' ) );
            add_action( 'mpcrbm_status_table_item_sec', array( $this, 'status_table_item_sec' ) );
            add_action( 'admin_notices', array( $this, 'pdf_admin_notice' ) );
        }

        private function load_file(): void {
            require_once WTBM_PLUGIN_DIR . '/admin/pro/WTBM_Pro_Pdf.php';
            require_once WTBM_PLUGIN_DIR . '/admin/pro/WTBM_Layout_Pro.php';
            require_once WTBM_PLUGIN_DIR . '/admin/pro/WTBM_Settings_Global_Pro.php';
            require_once WTBM_PLUGIN_DIR . '/admin/pro/WTBM_Pro_Mail.php';

        }
        public function status_notice_sec() {

            if ( isset( $_GET['wtbm_nonce'] ) ) {
                if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['wtbm_nonce'] ) ), 'wtbm_mpdf_plugin_action') ) {

                    if (isset($_REQUEST['active_mep_pdf_support_plugin']) && $_REQUEST['active_mep_pdf_support_plugin'] == 'yes') {
                        activate_plugin('magepeople-pdf-support-master/mage-pdf.php');
                    }

                    if (isset($_REQUEST['install_mep_pdf_support_plugin']) && $_REQUEST['install_mep_pdf_support_plugin'] == 'yes') {
                        include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
                        include_once(ABSPATH . 'wp-admin/includes/file.php');
                        include_once(ABSPATH . 'wp-admin/includes/misc.php');
                        include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
                        $title = 'title';
                        $url = 'url';
                        $nonce = 'nonce';
                        $plugin = 'plugin';
                        $api = 'api';
                        $upgrades = new Plugin_Upgrader(new Plugin_Installer_Skin(compact('title', 'url', 'nonce', 'plugin', 'api')));
                        $upgrades->install('https://github.com/magepeopleteam/magepeople-pdf-support/archive/master.zip');
                    }
                }
            }else{
                // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
                wp_die( __( 'Security check failed', 'wptheaterly' ) );
            }

        }
        public function status_table_item_sec() {
            ?>
            <tr>
                <td data-export-label="WC Version">PDF Tickets Installed:</td>
                <td><span class="textSuccess"> <span class="far fa-check-circle mR_xs"></span>Yes</span></td>
            </tr>
            <tr>
                <td data-export-label="WC Version">MagePeople PDF Support Installed:</td>
                <td><?php $this->pdf_support_install_check(); ?></td>
            </tr>
            <tr>
                <td data-export-label="WC Version">PHP GD library Installed:</td>
                <td>
                    <?php if ( extension_loaded( 'gd' ) ) { ?>
                        <span class="textSuccess"> <span class="far fa-check-circle mR_xs"></span>Yes</span>
                    <?php } else { ?>
                        <span class="textWarning"> <span class="fas fa-exclamation-triangle mR_xs"></span>No</span>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td data-export-label="WC Version">PHP mbstring library Installed:</td>
                <td>
                    <?php if ( extension_loaded( 'mbstring' ) ) { ?>
                        <span class="textSuccess"> <span class="far fa-check-circle mR_xs"></span>Yes</span>
                    <?php } else { ?>
                        <span class="textWarning"> <span class="fas fa-exclamation-triangle mR_xs"></span>No</span>
                    <?php } ?>
                </td>
            </tr>
            <?php
        }
        public function pdf_support_install_check() {
            $admin_url = get_admin_url();
            $active_mpdf_plugin_url = '<a href="' . $admin_url . 'admin.php?page=wtbm_status_page&active_mep_pdf_support_plugin=yes" class="page-title-action">Active Now</a>';
            $install_mpdf_plugin_url = '<a href="' . $admin_url . 'admin.php?page=wtbm_status_page&install_mep_pdf_support_plugin=yes" class="page-title-action">Install Now</a>';
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            $plugin_dir = ABSPATH . 'wp-content/plugins/magepeople-pdf-support-master';
            if ( is_plugin_active( 'magepeople-pdf-support-master/mage-pdf.php' ) ) {
                echo '<span class="textSuccess"> <span class="far fa-check-circle mR_xs"></span>Yes</span>';
            } elseif ( is_dir( $plugin_dir ) ) {
                echo '<span class="textWarning"> <span class="fas fa-exclamation-triangle mR_xs"></span>Installed But Not Active ' .  esc_attr( $active_mpdf_plugin_url ) . '</span>';
            } else {
                echo '<span class="textWarning"> <span class="fas fa-exclamation-triangle mR_xs"></span>Not Installed  ' .  esc_attr( $install_mpdf_plugin_url ) . '</span>';
            }
        }
        public function pdf_admin_notice() {
            $admin_url = get_admin_url();

            $active_mpdf_plugin_url = wp_nonce_url(
                $admin_url . 'admin.php?page=wtbm_status_page&active_mep_pdf_support_plugin=yes',
                'wtbm_mpdf_plugin_action',
                'wtbm_nonce'
            );

            $install_mpdf_plugin_url = wp_nonce_url(
                $admin_url . 'admin.php?page=wtbm_status_page&install_mep_pdf_support_plugin=yes',
                'wtbm_mpdf_plugin_action',
                'wtbm_nonce'
            );

            $active_mpdf_plugin_url = '<a href="' . esc_url( $active_mpdf_plugin_url ) . '" class="page-title-action">Activate Now</a>';
            $install_mpdf_plugin_url = '<a href="' . esc_url( $install_mpdf_plugin_url ) . '" class="page-title-action">Install Now</a>';

           /* $active_mpdf_plugin_url = '<a href="' . $admin_url . 'admin.php?page=wtbm_status_page&active_mep_pdf_support_plugin=yes" class="page-title-action">Active Now</a>';
            $install_mpdf_plugin_url = '<a href="' . $admin_url . 'admin.php?page=wtbm_status_page&install_mep_pdf_support_plugin=yes" class="page-title-action">Install Now</a>';
            */
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
            $plugin_dir = ABSPATH . 'wp-content/plugins/magepeople-pdf-support-master';
            if ( is_plugin_active( 'magepeople-pdf-support-master/mage-pdf.php' ) ) {
                $message = null;
            } elseif ( is_dir( $plugin_dir ) ) {
                $message = '<span class="textWarning"> <span class="fas fa-exclamation-triangle mR_xs"></span>Mage PDF Support Plugin should be Activated But its only Installed But Not Actived ' . $active_mpdf_plugin_url . '</span>';
            } else {
                $message = '<span class="textWarning"> <span class="fas fa-exclamation-triangle mR_xs"></span>Mage PDF Support Plugin should be Installed & Activated But its not installed in your website  ' . $install_mpdf_plugin_url . '</span>';
            }
            if ( ! empty( $message ) ) {
                $class = 'notice notice-error';
                printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), wp_kses_post( $message ) );
            }
        }

    }
    new WTBM_Admin_Pro();
}