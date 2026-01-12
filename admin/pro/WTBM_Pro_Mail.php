<?php

/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.
if ( ! class_exists('WTBM_Pro_Mail') ) {
    class WTBM_Pro_Mail
    {
        public function __construct()
        {
            add_action('mpcrbm_send_mail', array($this, 'send_mail'));
        }

        public function send_mail($order_id = '')
        {
            $order = wc_get_order($order_id);
            if (empty($order_id) || !$order) {
                return new WP_Error('invalid_data', esc_html__('Invalid order id provided', 'car-rental-manager-pro'));
            }
            $subject = MPCRBM_Global_Function::get_settings('mpcrbm_email_settings', 'pdf_email_subject', 'PDF Booking Confirmation');
            $content = MPCRBM_Global_Function::get_settings('mpcrbm_email_settings', 'pdf_email_content', 'Here is PDF Booking Confirmation Attachment');
            $form_email = get_option('woocommerce_email_from_address');
            $form_name = get_option('woocommerce_email_from_name');
            $admin_notify_email = MPCRBM_Global_Function::get_settings('mpcrbm_email_settings', 'pdf_admin_notification_email', '');
            $email_status = MPCRBM_Global_Function::get_settings('mpcrbm_email_settings', 'pdf_send_status', 'yes');
            $attachments = array();
            $headers = array(
                sprintf("From: %s <%s>", $form_name, $form_email),
            );
            if ($email_status == 'yes') {
                $upload_dir = wp_upload_dir();
                $pdf_url = $upload_dir['basedir'] . '/' . $order_id . '.pdf';
                do_action('mpcrbm_generate_pdf', $order_id, $pdf_url, 'mail');
                if (!is_wp_error($pdf_url)) {
                    $attachments[] = $pdf_url;
                }
                $email_address_arr = array(
                    $order->get_billing_email(),
                    $admin_notify_email
                );
                $email_address = implode(",", $email_address_arr);
                // Mail content dynamic
                $content = $this->mail_content($content, $order);
                $pdf_email_content = apply_filters('mpcrbm_pdf_email_text', $content, $order_id);
                wp_mail($email_address, $subject, $pdf_email_content, $headers, $attachments);
            }
        }

        public function mail_content($content, $order)
        {
            $get_content = $content;
            $get_content = str_replace('{customer_name}', $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(), $get_content);
            $get_content = str_replace('{order_id}', $order->get_order_number(), $get_content);
            $item_id = current(array_keys($order->get_items()));
            $post_id = MPCRBM_Global_Function::get_order_item_meta($item_id, '_mpcrbm_id');
            $post_title = get_the_title($post_id);
            $date = MPCRBM_Global_Function::get_order_item_meta($item_id, '_mpcrbm_date');
            $date = MPCRBM_Global_Function::date_format($date, 'full');
            $get_content = str_replace('{service_name}', $post_title, $get_content);
            return str_replace('{service_date}', $date, $get_content);
        }
    }

    new WTBM_Pro_Mail();
}