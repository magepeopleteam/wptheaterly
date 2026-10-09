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
                'post_status'    => array( 'publish', 'draft' ),
                'posts_per_page' => -1,
                'post__in'       => $booking_ids,
                'orderby'        => 'post__in',
            );

            $query = new WP_Query( $args );
            $report_data = array();
            $checkins = WTBM_Tickets::checkin_data( $booking_ids );

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
                    $order = wc_get_order( get_post_meta( $booking_id, 'wtbm_order_id', true ) );
                    $item = $order ? $order->get_item( absint( get_post_meta( $booking_id, 'wtbm_item_id', true ) ) ) : false;
                    // Older bookings saved attendee contacts on the order item rather than billing fields.
                    $item_name = $item ? (string) $item->get_meta( '_wtbm_user_name' ) : '';
                    $item_phone = $item ? (string) $item->get_meta( '_wtbm_user_phone' ) : '';
                    if ( '' === trim( (string) $attendees_name ) || ( is_email( $attendees_name ) && '' !== trim( $item_name ) ) ) {
                        $attendees_name = '' !== trim( $item_name ) ? $item_name : ( $order ? $order->get_formatted_billing_full_name() : '' );
                    }
                    if ( '' === trim( (string) $attendees_phone ) ) {
                        $attendees_phone = '' !== trim( $item_phone ) ? $item_phone : ( $order ? $order->get_billing_phone() : '' );
                    }
                    if ( '' === trim( (string) $attendees_name ) ) { $attendees_name = __( 'Not provided', 'wptheaterly' ); }
                    if ( '' === trim( (string) $attendees_phone ) ) { $attendees_phone = __( 'Not provided', 'wptheaterly' ); }


                    $booking_seats_str = '';
                    $booking_seats = get_post_meta( $booking_id, 'wtbm_seats', true );
                    if( is_array( $booking_seats ) && !empty( $booking_seats ) ) {
                        $booking_seats_str = implode( ', ', $booking_seats );
                    }

                    $checkin = $checkins[ $booking_id ];
                    $label = $checkin['count'] ? ( $checkin['count'] === $checkin['total'] ? __( 'Checked in', 'wptheaterly' ) : __( 'Partially checked in', 'wptheaterly' ) ) : __( 'Not checked in', 'wptheaterly' );
                    $details = array();
                    foreach ( $checkin['groups'] as $group ) { $details[] = implode( ', ', $group['seats'] ) . ' · ' . $group['time'] . ' · ' . $group['staff']; }
                    $report_data[] = array(
                        'booking_number' => 'BK' . str_pad( (string) get_post_meta( $booking_id, 'wtbm_order_id', true ), 6, '0', STR_PAD_LEFT ),
                        'date' => get_post_meta( $booking_id, 'wtbm_order_date', true ),
                        'amount' => get_post_meta( $booking_id, 'wtbm_tp', true ),
                        'currency' => get_woocommerce_currency(),
                        'status' => wc_get_order_status_name( get_post_meta( $booking_id, 'wtbm_order_status', true ) ),
                        'checkin' => $label,
                        'checkin_details' => implode( '; ', $details ),
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
            $rows = self::get_booking_data_by_ids( $booking_ids );
            $columns = array( 'booking_number' => 'Booking ID', 'name' => 'Customer', 'phone' => 'Phone', 'movie_name' => 'Movie', 'theater_name' => 'Theater', 'date' => 'Show date', 'time' => 'Time', 'seat_number' => 'Seats', 'amount' => 'Amount', 'status' => 'Status', 'checkin' => 'Check-in' );
            $html = '<style>body{font-family:dejavusans;font-size:9px;color:#463452}h1{font-size:22px;color:#8054a8}p{color:#8d789d}table{width:100%;border-collapse:collapse}th{background:#8a61b0;color:white;padding:9px;text-align:left}td{padding:9px;border-bottom:1px solid #e9dff1}tr:nth-child(even){background:#faf7fd}</style>';
            $html .= '<h1>' . esc_html__( 'Theaterly · Booking report', 'wptheaterly' ) . '</h1><p>' . esc_html( count( $rows ) . ' bookings · ' . get_woocommerce_currency() . ' · ' . wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ) . '</p><table><thead><tr>';
            foreach ( $columns as $label ) { $html .= '<th>' . esc_html( $label ) . '</th>'; }
            $html .= '</tr></thead><tbody>';
            foreach ( $rows as $row ) {
                $html .= '<tr>';
                foreach ( $columns as $key => $label ) { $html .= '<td>' . esc_html( $row[ $key ] ) . '</td>'; }
                $html .= '</tr>';
            }
            return $html . '</tbody></table>';
        }

    }
}