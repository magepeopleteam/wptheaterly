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
                    // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment
                    $translated_string = __("%s Information", 'wptheaterly');
                    $formatted_string = sprintf($translated_string, $transportaion_label);
                    echo esc_html($formatted_string);
                    ?>
                </h5>
                <div class="divider"></div>
                <ul class="mp_list">
                    <li class="justifyBetween">
                        <p class="min_150"> <?php echo esc_attr( get_the_title( $post_id ) ); ?> </p>
                        <span>x 1 &nbsp;|&nbsp;<?php echo wp_kses_post( wc_price( $price ) ); ?> = <?php echo wp_kses_post( wc_price( $price ) ); ?></span>
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
                <h4><?php esc_html_e('Booking Information', 'wptheaterly'); ?> </h4>
                <div class="divider"></div>
                <ul class="mp_list">
                    <li>
                        <strong class="min_150"><?php esc_html_e('Order ID:', 'wptheaterly'); ?> :</strong>&nbsp;#<?php echo esc_html($order_id); ?>
                    </li>
                    <li>
                        <strong class="min_150"><?php esc_html_e('Service No', 'wptheaterly'); ?> :</strong> <?php echo esc_html($pin); ?>
                    </li>

                    <li>
                        <strong class="min_150"><?php esc_html_e('Theater Name', 'wptheaterly'); ?> :</strong> <?php echo esc_html($theater_name); ?>
                    </li>
                    <li>
                        <strong class="min_150"><?php esc_html_e('Number Of Seats', 'wptheaterly'); ?> :</strong> <?php echo esc_html($number_of_seats); ?>
                    </li>
                    <li>
                        <strong class="min_150"><?php esc_html_e('Seat Number', 'wptheaterly'); ?> :</strong>
                        <?php
                        if( !empty( $seat_numbers ) && is_array( $seat_numbers ) ){
                            $seat_names = implode(", ", $seat_numbers );
                        }
                        echo esc_html($seat_names);
                        ?>
                    </li>

                    <li>
                        <strong class="min_150"><?php esc_html_e('Booking Date', 'wptheaterly'); ?> :</strong>
                        <?php echo esc_html(WTBM_Function::date_format($order_date, 'full')); ?>
                    </li>
                    <li>
                        <strong class="min_150"><?php esc_html_e('Booking Time', 'wptheaterly'); ?> :</strong>
                        <?php echo esc_html( $order_time); ?>
                    </li>

                    <li>
                        <strong class="min_150"><?php esc_html_e('Payment Method ', 'wptheaterly'); ?> :</strong><?php echo esc_html( $payment_method ); ?>
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
                <h5><?php esc_html_e('Extra Service', 'wptheaterly'); ?></h5>
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
                            <span>x<?php echo esc_html($qty); ?>&nbsp;|&nbsp;<?php echo wp_kses_post( wc_price( $price ) ); ?> = <?php echo wp_kses_post( wc_price($price * $qty ) ); ?></span>
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
            <h5><?php esc_html_e('Billing information', 'wptheaterly'); ?></h5>
            <div class="divider"></div>
            <ul class="mp_list">
                <?php if ($billing_name) { ?>
                    <li>
                        <strong class="min_100"><?php esc_html_e('Name', 'wptheaterly'); ?> : &nbsp;</strong><?php echo esc_html($billing_name); ?>
                    </li>
                <?php } ?>
                <?php if ($email) { ?>
                    <li>
                        <strong class="min_100"><?php esc_html_e('E-mail', 'wptheaterly'); ?> : &nbsp;</strong><?php echo esc_html($email); ?>
                    </li>
                <?php } ?>
                <?php if ($phone) { ?>
                    <li>
                        <strong class="min_100"><?php esc_html_e('Phone', 'wptheaterly'); ?> : &nbsp;</strong><?php echo esc_html($phone); ?>
                    </li>
                <?php } ?>
                <?php if ($address) { ?>
                    <li>
                        <strong class="min_100"><?php esc_html_e('Address', 'wptheaterly'); ?> : &nbsp;</strong><?php echo esc_html($address); ?>
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

        public static function get_booking_data_by_ids( $booking_ids ) {

            if ( empty( $booking_ids ) || ! is_array( $booking_ids ) ) {
                return [];
            }

            $args = array(
                'post_type'      => 'wtbm_booking',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'post__in'       => $booking_ids,
                'orderby'        => 'post__in',
            );

            $query = new WP_Query( $args );
            $report_data = array();

            if ( $query->have_posts() ) {

                foreach ( $query->posts as $booking_post ) {

                    $booking_id = $booking_post->ID;

                    $movie_id   = get_post_meta( $booking_id, 'wtbm_movie_id', true );
                    $movie_name = get_the_title( $movie_id );

                    $theater_id   = get_post_meta( $booking_id, 'wtbm_theater_id', true );
                    $theater_name = get_the_title( $theater_id );

                    $movie_time = get_post_meta( $booking_id, 'wtbm_order_time', true );

                    $attendees_name = get_post_meta( $booking_id, 'wtbm_billing_name', true );
                    $attendees_phone = get_post_meta( $booking_id, 'wtbm_billing_phone', true );

                    $booking_seats_str = '';
                    $booking_seats = get_post_meta( $booking_id, 'wtbm_seats', true );
                    if( is_array( $booking_seats ) && !empty( $booking_seats ) ) {
                        $booking_seats_str = implode( ', ', $booking_seats );
                    }

                    $report_data[] = array(
                        'id'            => $booking_id,
                        'name'          => $attendees_name,
                        'phone'         => $attendees_phone,
                        'movie_name'    => $movie_name,
                        'theater_name'  => $theater_name,
                        'time'          => $movie_time,
                        'seat_number'   => $booking_seats_str,
                    );

                }
            }
            wp_reset_postdata();

            return $report_data;
        }

        public static function generate_booking_data_pdf( $booking_ids ) {

            if (empty($booking_ids) || !is_array($booking_ids)) {
                return '';
            }
            $bookings_data = self::get_booking_data_by_ids( $booking_ids );

            $html = '<style>
                        body { font-family: sans-serif; font-size: 11px; color: #333; }
                        h1 { text-align: center; margin-bottom: 10px; font-size: 18px; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
                        th { background: #2c3e50; color: #fff; }
                        tr:nth-child(even) { background: #f9f9f9; }
                        .footer { font-size: 10px; text-align: center; margin-top: 10px; color: #555; }
                    </style>';
            $html .= '<div class="footer">Generated on '.gmdate('d M Y H:i').'</div>';
            $html .= '<table>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Theater Name</th>
                                <th>Movie Name</th>
                                <th>Time</th>
                                <th>Seat Number</th>
                            </tr>
                        ';
            $i = 1;
            foreach ($bookings_data as $booking_data) {
                $name       = esc_html($booking_data['name'] ?? '');
                $phone      = esc_html($booking_data['phone'] ?? '');
                $theater    = esc_html($booking_data['theater_name'] ?? '');
                $movie      = esc_html($booking_data['movie_name'] ?? '');
                $time       = esc_html($booking_data['time'] ?? '');
                $seat       = esc_html($booking_data['seat_number'] ?? '');

                $html .= '
                    <tr>
                        <td>'.$i++.'</td>
                        <td>'.$name.'</td>
                        <td>'.$phone.'</td>
                        <td>'.$theater.'</td>
                        <td>'.$movie.'</td>
                        <td>'.$time.'</td>
                        <td>'.$seat.'</td>
                    </tr>
                    ';
            }

            $html .= '</table>';
            $html .= '<pagebreak />';

            return $html;
        }

    }
}