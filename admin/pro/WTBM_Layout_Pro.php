<?php

/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('WTBM_Layout_Pro')) {
    class WTBM_Layout_Pro{
        public static function order_info($attendee_id){
            if ($attendee_id > 0) {
                $post_id = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_id');
                $price = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_base_price');
                $all_meta = get_post_meta($attendee_id);
                if ($all_meta['mpcrbm_selected_driver'][0]) {
                    $driver_id = $all_meta['mpcrbm_selected_driver'][0];
                    $driver_info = get_userdata($driver_id);
                    $phone_number = get_user_meta($driver_id, 'user_phone', true);
                }
                ?>

                <h5>
                    <?php
                    $transportaion_label = WTBM_Function::get_name();
                    $translated_string = __("%s Information", 'ecab-taxi-booking-manager');
                    $formatted_string = sprintf($translated_string, $transportaion_label);
                    echo esc_html($formatted_string);
                    ?>
                </h5>
                <div class="divider"></div>
                <ul class="mp_list">
                    <li class="justifyBetween">
                        <p class="min_150"> <?php echo get_the_title($post_id); ?> </p>
                        <span>x 1 &nbsp;|&nbsp;<?php echo wc_price($price); ?> = <?php echo wc_price($price); ?></span>
                    </li>
                    <?php
                    // Iterate through the $all_meta array
                    foreach ($all_meta as $meta_key => $meta_value) {
                        // Check if the key starts with 'order'
                        if (strpos($meta_key, 'order') === 0) {
                            // Remove 'order' from the key
                            $formatted_key = str_replace('order', '', $meta_key);

                            // Replace underscores and hyphens with spaces
                            $formatted_key = str_replace(['_', '-'], ' ', $formatted_key);

                            // Format the key to be human-readable
                            $formatted_key = ucwords($formatted_key);

                            // Get the value (assuming it's the first element in the array)
                            $value = isset($meta_value[0]) ? $meta_value[0] : '';

                            // Display the formatted key and its value
                            if (!empty($value)) { ?>
                                <li>
                                    <strong class="min_100"><?php echo esc_html($formatted_key); ?> : &nbsp;</strong><?php echo esc_html($value); ?>
                                </li>
                            <?php }
                        }
                    }


                    // Display driver information if available
                    if (isset($driver_info->display_name) && isset($driver_info->user_email)) {

                        ?>

                        <li>
                            <strong class="min_100">Driver Name : &nbsp;</strong><?php echo esc_html($driver_info->display_name); ?>
                        </li>
                        <li>
                            <strong class="min_100">Driver Email : &nbsp;</strong><?php echo esc_html($driver_info->user_email); ?>
                        </li>
                        <li>
                            <strong class="min_100">Driver Phone : &nbsp;</strong><?php echo esc_html($phone_number); ?>
                        </li>
                    <?php } ?>
                </ul>
                <?php
            }
        }
        public static function service_info($attendee_id){
            if ($attendee_id > 0) {
                $pin = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_pin');
                $start_place = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_start_place');
                $end_place = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_end_place');
                $date = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_date');
                $return_date_time = WTBM_Function::get_post_info($attendee_id, 'return_date_time');
                $order_id = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_order_id');
                $return = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_taxi_return');
                $waiting_time = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_waiting_time');
                $attendee_info = get_post($attendee_id);
                $order_status = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_order_status');
                $order_status_text = $order_status === 'partially-paid' ? 'Partially Paid' : $order_status;
                $payment_method = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_payment_method');

                $return_target_date = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_return_target_date');
                $return_target_time = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_return_target_time');


                ?>
                <h4><?php esc_html_e('Booking Information', 'car-rental-manager-pro'); ?> </h4>
                <div class="divider"></div>
                <ul class="mp_list">
                    <li>
                        <strong class="min_150"><?php esc_html_e('Order ID:', 'car-rental-manager-pro'); ?> :</strong>&nbsp;#<?php echo esc_html($order_id); ?>
                    </li>
                    <li>
                        <strong class="min_150"><?php esc_html_e('Service No', 'car-rental-manager-pro'); ?> :</strong> <?php echo esc_html($pin); ?>
                    </li>
                    <li>
                        <strong class="min_150"><?php esc_html_e('Pick-Up Location', 'car-rental-manager-pro'); ?> :</strong> <?php echo esc_html($start_place); ?>
                    </li>
                    <li>
                        <strong class="min_150"><?php esc_html_e('Drop-Off Location', 'car-rental-manager-pro'); ?> :</strong> <?php echo esc_html($end_place); ?>
                    </li>

                    <li>
                        <strong class="min_150"><?php esc_html_e('Transfer Type', 'car-rental-manager-pro'); ?> :</strong> <?php esc_html_e('Return ', 'mpcrbm_plugin'); ?>
                    </li>

                    <li>
                        <strong class="min_150"><?php esc_attr_e('Booking Date', 'car-rental-manager-pro'); ?> :</strong>
                        <?php echo esc_html(WTBM_Function::date_format($attendee_info->post_date, 'full')); ?>
                    </li>

                    <li>
                        <strong class="min_150"><?php esc_html_e('Service Date', 'car-rental-manager-pro'); ?> :</strong>
                        <?php echo esc_html(WTBM_Function::date_format($date, 'full')); ?>
                    </li>

                    <?php if ($return_date_time) { ?>
                        <li>
                            <strong class="min_150"><?php esc_html_e('Return Date', 'car-rental-manager-pro'); ?> :</strong>
                            <?php echo esc_html(WTBM_Function::date_format($return_date_time, 'full')); ?>
                        </li>
                    <?php } ?>


                    <?php if ($waiting_time > 0) { ?>
                        <li>
                            <strong class="min_150"><?php esc_html_e('Extra Waiting Hours', 'car-rental-manager-pro'); ?> :</strong> <?php echo esc_html($waiting_time); ?>
                        </li>
                    <?php } ?>

                    <li>
                        <strong class="min_150"><?php esc_html_e('Order status', 'car-rental-manager-pro'); ?> :</strong><?php echo esc_html($order_status_text); ?>
                    </li>
                    <li>
                        <strong class="min_150"><?php esc_html_e('Payment Method ', 'car-rental-manager-pro'); ?> :</strong><?php echo $payment_method; ?>
                    </li>

                    <?php do_action('mpcrbm_after_order_info', $attendee_id); ?>
                </ul>
                <?php
            }
        }
        public static function ex_service_info($attendee_id){
            $ex_service = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_service_info');
            $ex_service_infos = $ex_service ? WTBM_Function::data_sanitize($ex_service) : [];
            if (sizeof($ex_service_infos) > 0) {
                ?>
                <h5><?php esc_html_e('Extra Service', 'car-rental-manager-pro'); ?></h5>
                <div class="divider"></div>
                <ul class="mp_list">
                    <?php
                    foreach ($ex_service_infos as $ex_service_info) {
                        $name = $ex_service_info['service_name'];
                        $price = $ex_service_info['service_price'];
                        $qty = $ex_service_info['service_quantity'];
                        ?>
                        <li class="justifyBetween">
                            <strong class="min_100"> <?php echo esc_html($name); ?> </strong>
                            <span>x<?php echo esc_html($qty); ?>&nbsp;|&nbsp;<?php echo wc_price($price); ?> = <?php echo wc_price($price * $qty); ?></span>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
                <?php
            }
        }
        public static function billing_info($attendee_id){

            $billing_name = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_billing_name');
            $email = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_billing_email');
            $phone = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_billing_phone');
            $address = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_billing_address');
            $all_meta = get_post_meta($attendee_id);
            ?>
            <h5><?php esc_html_e('Billing information', 'car-rental-manager-pro'); ?></h5>
            <div class="divider"></div>
            <ul class="mp_list">
                <?php if ($billing_name) { ?>
                    <li>
                        <strong class="min_100"><?php esc_html_e('Name', 'car-rental-manager-pro'); ?> : &nbsp;</strong><?php echo esc_html($billing_name); ?>
                    </li>
                <?php } ?>
                <?php if ($email) { ?>
                    <li>
                        <strong class="min_100"><?php esc_html_e('E-mail', 'car-rental-manager-pro'); ?> : &nbsp;</strong><?php echo esc_html($email); ?>
                    </li>
                <?php } ?>
                <?php if ($phone) { ?>
                    <li>
                        <strong class="min_100"><?php esc_html_e('Phone', 'car-rental-manager-pro'); ?> : &nbsp;</strong><?php echo esc_html($phone); ?>
                    </li>
                <?php } ?>
                <?php if ($address) { ?>
                    <li>
                        <strong class="min_100"><?php esc_html_e('Address', 'car-rental-manager-pro'); ?> : &nbsp;</strong><?php echo esc_html($address); ?>
                    </li>
                <?php } ?>
                <?php
                // Iterate through the $all_meta array
                foreach ($all_meta as $meta_key => $meta_value) {
                    // Check if the key starts with '_billing'
                    if (strpos($meta_key, '_billing') === 0) {
                        // Remove '_billing_' from the key
                        $formatted_key = str_replace('_billing_', '', $meta_key);

                        // Replace underscores and hyphens with spaces
                        $formatted_key = str_replace(['_', '-'], ' ', $formatted_key);

                        // Format the key to be human-readable
                        $formatted_key = ucwords($formatted_key);

                        // Get the value (assuming it's the first element in the array)
                        $value = isset($meta_value[0]) ? $meta_value[0] : '';

                        // Display the formatted key and its value
                        if (!empty($value)) { ?>
                            <li>
                                <strong class="min_100"><?php echo esc_html($formatted_key); ?> : &nbsp;</strong><?php echo esc_html($value); ?>
                            </li>
                        <?php }
                    }
                }
                ?>
            </ul>
            <?php
        }
    }
}