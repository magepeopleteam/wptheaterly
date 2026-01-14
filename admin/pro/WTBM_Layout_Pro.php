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
                $post_id = WTBM_Function::get_post_info($attendee_id, 'wtbm_movie_id');
                $price = WTBM_Function::get_post_info($attendee_id, 'wtbm_tp');
                $all_meta = get_post_meta($attendee_id);
                ?>

                <h5>
                    <?php
                    $transportaion_label = WTBM_Function::get_name();
                    $translated_string = __("%s Information", 'theaterly');
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
                     ?>
                </ul>
                <?php
            }
        }
        public static function service_info($attendee_id){
            if ($attendee_id > 0) {
                $pin = WTBM_Function::get_post_info($attendee_id, 'wtbm_pin');
                $order_date = WTBM_Function::get_post_info($attendee_id, 'wtbm_order_date');
                $order_time = WTBM_Function::get_post_info($attendee_id, 'wtbm_order_time');
                $order_id = WTBM_Function::get_post_info($attendee_id, 'wtbm_order_id');
                $order_status = WTBM_Function::get_post_info($attendee_id, 'wtbm_order_status');
                $payment_method = WTBM_Function::get_post_info($attendee_id, 'wtbm_payment_method');
                $number_of_seats = WTBM_Function::get_post_info($attendee_id, 'wtbm_number_of_seats');
                $seat_ids = WTBM_Function::get_post_info($attendee_id, 'wtbm_seat_ids');
                $seat_numbers = WTBM_Function::get_post_info($attendee_id, 'wtbm_seats');
                $theater_id = WTBM_Function::get_post_info($attendee_id, 'wtbm_theater_id');
                $theater_name = get_the_title($theater_id);
                $movie_id = WTBM_Function::get_post_info($attendee_id, 'wtbm_movie_id');

                $seat_names = '';

                ?>
                <h4><?php esc_html_e('Booking Information', 'theaterly'); ?> </h4>
                <div class="divider"></div>
                <ul class="mp_list">
                    <li>
                        <strong class="min_150"><?php esc_html_e('Order ID:', 'theaterly'); ?> :</strong>&nbsp;#<?php echo esc_html($order_id); ?>
                    </li>
                    <li>
                        <strong class="min_150"><?php esc_html_e('Service No', 'theaterly'); ?> :</strong> <?php echo esc_html($pin); ?>
                    </li>

                    <li>
                        <strong class="min_150"><?php esc_html_e('Theater Name', 'theaterly'); ?> :</strong> <?php echo esc_html($theater_name); ?>
                    </li>
                    <li>
                        <strong class="min_150"><?php esc_html_e('Number Of Seats', 'theaterly'); ?> :</strong> <?php echo esc_html($number_of_seats); ?>
                    </li>
                    <li>
                        <strong class="min_150"><?php esc_html_e('Seat Number', 'theaterly'); ?> :</strong>
                        <?php
                        if( !empty( $seat_numbers ) && is_array( $seat_numbers ) ){
                            $seat_names = implode(", ", $seat_numbers );
                        }
                        echo esc_html($seat_names);
                        ?>
                    </li>

                    <li>
                        <strong class="min_150"><?php esc_html_e('Booking Date', 'theaterly'); ?> :</strong>
                        <?php echo esc_html(WTBM_Function::date_format($order_date, 'full')); ?>
                    </li>
                    <li>
                        <strong class="min_150"><?php esc_html_e('Booking Time', 'theaterly'); ?> :</strong>
                        <?php echo esc_html( $order_time); ?>
                    </li>

                    <li>
                        <strong class="min_150"><?php esc_html_e('Payment Method ', 'theaterly'); ?> :</strong><?php echo $payment_method; ?>
                    </li>

                    <?php do_action('wtbm_after_order_info', $attendee_id); ?>
                </ul>
                <?php
            }
        }
        public static function ex_service_info($attendee_id){
            $ex_service = WTBM_Function::get_post_info($attendee_id, 'mpcrbm_service_info');
            $ex_service_infos = $ex_service ? WTBM_Function::data_sanitize($ex_service) : [];
            if (sizeof($ex_service_infos) > 0) {
                ?>
                <h5><?php esc_html_e('Extra Service', 'theaterly'); ?></h5>
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

            $billing_name = WTBM_Function::get_post_info($attendee_id, 'wtbm_billing_name');
            $email = WTBM_Function::get_post_info($attendee_id, 'wtbm_billing_email');
            $phone = WTBM_Function::get_post_info($attendee_id, 'wtbm_billing_phone');
            $address = WTBM_Function::get_post_info($attendee_id, 'wtbm_billing_address');
            $all_meta = get_post_meta($attendee_id);
            ?>
            <h5><?php esc_html_e('Billing information', 'theaterly'); ?></h5>
            <div class="divider"></div>
            <ul class="mp_list">
                <?php if ($billing_name) { ?>
                    <li>
                        <strong class="min_100"><?php esc_html_e('Name', 'theaterly'); ?> : &nbsp;</strong><?php echo esc_html($billing_name); ?>
                    </li>
                <?php } ?>
                <?php if ($email) { ?>
                    <li>
                        <strong class="min_100"><?php esc_html_e('E-mail', 'theaterly'); ?> : &nbsp;</strong><?php echo esc_html($email); ?>
                    </li>
                <?php } ?>
                <?php if ($phone) { ?>
                    <li>
                        <strong class="min_100"><?php esc_html_e('Phone', 'theaterly'); ?> : &nbsp;</strong><?php echo esc_html($phone); ?>
                    </li>
                <?php } ?>
                <?php if ($address) { ?>
                    <li>
                        <strong class="min_100"><?php esc_html_e('Address', 'theaterly'); ?> : &nbsp;</strong><?php echo esc_html($address); ?>
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