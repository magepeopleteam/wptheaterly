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
            add_action('wtbm_send_mail', array($this, 'send_mail'));
        }

        public function send_mail($order_id = '')
        {
            return WTBM_Notifications::queue( $order_id, true );
        }

        public function mail_content($content, $order)
        {
            $get_content = $content;
            $get_content = str_replace('{customer_name}', $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(), $get_content);
            $get_content = str_replace('{order_id}', $order->get_order_number(), $get_content);
            $item_id = current(array_keys($order->get_items()));
            $post_id = WTBM_Function::get_order_item_meta($item_id, 'wtbm_movie_id');
            $post_title = get_the_title($post_id);
            $date = WTBM_Function::get_order_item_meta($item_id, 'wtbm_order_date');
            $date = WTBM_Function::date_format($date, 'full');
            $get_content = str_replace('{service_name}', $post_title, $get_content);

            return str_replace('{service_date}', $date, $get_content);
        }
    }

    new WTBM_Pro_Mail();
}