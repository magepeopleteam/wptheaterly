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


    //        require_once MPCRBM_PLUGIN_DIR_PRO . '/admin/MPCRBM_Settings_Global_Pro.php';
    //        require_once MPCRBM_PLUGIN_DIR_PRO . '/admin/MPCRBM_Order_List.php';
            require_once WTBM_PLUGIN_DIR . '/admin/pro/WTBM_Pro_Pdf.php';
            require_once WTBM_PLUGIN_DIR . '/admin/pro/WTBM_Layout_Pro.php';
            require_once WTBM_PLUGIN_DIR . '/admin/pro/WTBM_Settings_Global_Pro.php';
            require_once WTBM_PLUGIN_DIR . '/admin/pro/WTBM_Pro_Mail.php';

        }
        public function status_notice_sec() {
            if ( isset( $_REQUEST['active_mep_pdf_support_plugin'] ) && $_REQUEST['active_mep_pdf_support_plugin'] == 'yes' ) {
                activate_plugin( 'magepeople-pdf-support-master/mage-pdf.php' );
            }

            error_log( print_r( [ '$_REQUEST' => $_REQUEST ], true ) );
            if ( isset( $_REQUEST['install_mep_pdf_support_plugin'] ) && $_REQUEST['install_mep_pdf_support_plugin'] == 'yes' ) {
                include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
                include_once( ABSPATH . 'wp-admin/includes/file.php' );
                include_once( ABSPATH . 'wp-admin/includes/misc.php' );
                include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
                $title='title';
                $url='url';
                $nonce='nonce';
                $plugin='plugin';
                $api='api';
                $upgrades = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
                $upgrades->install( 'https://github.com/magepeopleteam/magepeople-pdf-support/archive/master.zip' );
            }
        }
        public function status_table_item_sec() {
            ?>
            <tr>
                <th data-export-label="WC Version">PDF Tickets Installed:</th>
                <th><span class="textSuccess"> <span class="far fa-check-circle mR_xs"></span>Yes</span></th>
            </tr>
            <tr>
                <th data-export-label="WC Version">MagePeople PDF Support Installed:</th>
                <th><?php $this->pdf_support_install_check(); ?></th>
            </tr>
            <tr>
                <th data-export-label="WC Version">PHP GD library Installed:</th>
                <th>
                    <?php if ( extension_loaded( 'gd' ) ) { ?>
                        <span class="textSuccess"> <span class="far fa-check-circle mR_xs"></span>Yes</span>
                    <?php } else { ?>
                        <span class="textWarning"> <span class="fas fa-exclamation-triangle mR_xs"></span>No</span>
                    <?php } ?>
                </th>
            </tr>
            <tr>
                <th data-export-label="WC Version">PHP mbstring library Installed:</th>
                <th>
                    <?php if ( extension_loaded( 'mbstring' ) ) { ?>
                        <span class="textSuccess"> <span class="far fa-check-circle mR_xs"></span>Yes</span>
                    <?php } else { ?>
                        <span class="textWarning"> <span class="fas fa-exclamation-triangle mR_xs"></span>No</span>
                    <?php } ?>
                </th>
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
                echo '<span class="textWarning"> <span class="fas fa-exclamation-triangle mR_xs"></span>Installed But Not Active ' . $active_mpdf_plugin_url . '</span>';
            } else {
                echo '<span class="textWarning"> <span class="fas fa-exclamation-triangle mR_xs"></span>Not Installed  ' . $install_mpdf_plugin_url . '</span>';
            }
        }
        public function pdf_admin_notice() {
            $admin_url = get_admin_url();
            $active_mpdf_plugin_url = '<a href="' . $admin_url . 'admin.php?page=wtbm_status_page&active_mep_pdf_support_plugin=yes" class="page-title-action">Active Now</a>';
            $install_mpdf_plugin_url = '<a href="' . $admin_url . 'admin.php?page=wtbm_status_page&install_mep_pdf_support_plugin=yes" class="page-title-action">Install Now</a>';
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
                printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), $message );
            }
        }

    }
    new WTBM_Admin_Pro();
}