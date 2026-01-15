<?php

/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.
if ( ! class_exists('WTBM_Pro_Pdf') ) {
    class WTBM_Pro_Pdf {
        public function __construct() {
            add_action('woocommerce_thankyou', array($this, 'pdf_button'));
            add_action('wtbm_pdf_button', array($this, 'pdf_button' ), 10, 2 );
            add_action('wtbm_generate_pdf', array($this, 'generate_pdf'), 10, 3);
            /***************/
            add_action('wp_ajax_wtbm_generate_pdf', array($this, 'wtbm_generate_pdf'));
            add_action('wp_ajax_nopriv_wtbm_generate_pdf', array($this, 'wtbm_generate_pdf'));
        }
        public function pdf_button( $order_id, $action ) {
            $order = wc_get_order($order_id);
            $order_status = $order->get_status();
            if (class_exists('\Mpdf\Mpdf') && in_array($order_status, ['processing', 'completed'])) {
                $item_id = current(array_keys($order->get_items()));
                $post_id = wc_get_order_item_meta($item_id, '_wtbm_id');
                if (get_post_type($post_id) == WTBM_Function::get_cpt() || 1 ) {
                    $download_url = self::get_pdf_url(array('order_id' => $order_id));
                    if ($download_url) {
                        ?>
                        <div class="wtbm">
                            <button type="button" class="_themeButton_mB" data-href="<?php echo esc_attr($download_url); ?>">
                                <span class="fas fa-file-pdf mR_xs"></span>
                                <?php esc_html_e('Download Ticket', 'car-rental-manager-pro'); ?>
                            </button>
                        </div>
                        <?php
                    }
                    $email_status = WTBM_Function::get_settings('mpcrbm_email_settings', 'pdf_email_status', array('processing', 'completed'));
                    if ( $action === 'order_done' && get_post_type($post_id) == 'wtbm_movie' && sizeof($email_status) > 0) {
                        $order_status = $order->get_status();
                        if (in_array($order_status, $email_status)) {
                            do_action('wtbm_send_mail', $order_id);
                        }
                    }
                }
            }
        }

        public static function get_pdf_url_button( $order_id ) {
            ob_start();
            $order = wc_get_order($order_id);
            $order_status = $order->get_status();
            if (class_exists('\Mpdf\Mpdf') && in_array($order_status, ['processing', 'completed', 'completed'])) {
                $item_id = current(array_keys($order->get_items()));
                $post_id = wc_get_order_item_meta($item_id, '_wtbm_id');
                if (get_post_type($post_id) == WTBM_Function::get_cpt() || 1 ) {
                    $download_url = self::get_pdf_url(array('order_id' => $order_id));
                    if ($download_url) {
                        ?>
                        <div class="wtbm">
                            <button type="button" class="_themeButton_mB" data-href="<?php echo esc_attr($download_url); ?>">
                                <span class="fas fa-file-pdf mR_xs"></span>
                                <?php esc_html_e('Download Ticket', 'car-rental-manager-pro'); ?>
                            </button>
                        </div>
                        <?php
                    }
                }
            }

            return ob_get_clean();
        }
        public function wtbm_generate_pdf() {
            if (empty($_GET['action']) || !check_admin_referer($_GET['action'])) {
                wp_die(__('You do not have sufficient permissions to access this page.', 'car-rental-manager-pro'));
            }
            $order_id = isset($_GET['order_id']) ? sanitize_text_field($_GET['order_id']) : '';
            if (empty($order_id)) {
                wp_die(__('Order ID is required.', 'car-rental-manager-pro'));
            }
            header("Content-Type: application/pdf; charset=UTF-8");
            $file_name = esc_html__('Order_', 'car-rental-manager-pro') . $order_id . '.pdf';
            $this->generate_pdf($order_id, $file_name);
            exit;
        }
        public function generate_pdf($order_id, $file_name, $mail = '') {

            if (class_exists('\Mpdf\Mpdf')) {
                $html = $this->create_pdf_file($order_id);
                $mpdf = new \Mpdf\Mpdf();
                $mpdf->allow_charset_conversion = true;  // Set by default to TRUE
                $mpdf->autoScriptToLang = true;
                $mpdf->baseScript = 1;
                $mpdf->autoVietnamese = true;
                $mpdf->autoArabic = true;
                $mpdf->autoLangToFont = true;
                $mpdf->WriteHTML($html);
                if ($mail) {
                    $mpdf->Output($file_name, 'F');
                } else {
                    $mpdf->Output($file_name, 'D');
                }
            }
        }
        public function create_pdf_file($order_id) {
            $file_slug = 'WTBM_Pdf';
            $template_dir = MPCRBM_PLUGIN_DIR_PRO . '/template_pro/wtbm_Pdf.php';
            $template_dir = file_exists($template_dir) ? $template_dir : '';
            ob_start();
            include $template_dir;
            return ob_get_clean();
        }
        //**********************//
        public static function get_pdf_url($args = array()) {
            $default_args = array(
                'action' => 'wtbm_generate_pdf',
                'order_id' => '',
            );
            $args = wp_parse_args($args, $default_args);
            $build_url = http_build_query($args);
            $nonce_url = wp_nonce_url(admin_url("admin-ajax.php?" . $build_url), $args['action']);
            return apply_filters('wtbm_filter_pdf_url', $nonce_url);
        }
        public static function pdf_bg() {
            $ticket_bg_url = WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_bg');
            if (!empty($ticket_bg_url) && wp_get_attachment_url($ticket_bg_url)) { // Ensure the URL exists
                ?>
                style="background: url(<?php echo esc_url($ticket_bg_url); ?>); background-repeat: no-repeat; background-size: 100% 100%;"
                <?php
            } else {
                ?>
                style="background-color: <?php echo esc_attr(WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_bg_color', '#fbfbfb')); ?>;"
                <?php
            }
        }
        public static function pdf_logo() {
            $logo_url = WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_logo');
            if (!empty($logo_url)) {
                ?>
                <img src="<?php echo esc_attr(wp_get_attachment_url($logo_url)) ?>" alt="logo" width="150"/>
                <?php
            }
        }
    }

    new WTBM_Pro_Pdf();
}