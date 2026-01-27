<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if( !class_exists( 'WTBM_New_Ticket_Booking' ) ) {
    class WTBM_New_Ticket_Booking{
        public function __construct(){
            add_action( 'wtbm_new_ticket_booking', array( $this, 'new_ticket_booking_display' ), 10, 1 );


            add_action( 'wp_ajax_wtbm_theater_ticket_booking_admin', [ $this, 'wtbm_theater_ticket_booking_admin' ] );
            add_action( 'wp_ajax_nopriv_wtbm_theater_ticket_booking_admin', [ $this, 'wtbm_theater_ticket_booking_admin' ] );
        }

        function wtbm_theater_ticket_booking_admin() {

            if ( ! isset($_POST['nonce']) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wtbm_nonce' ) ) {
                wp_send_json_error('Invalid request.');
            }

            // Check if user logged in
            if ( ! is_user_logged_in() ) {
                wp_send_json_error('Please login to place an order.');
            }

            $original_post_id = intval($_POST['movie_id'] ?? 0);
            $product_id = intval( get_post_meta($original_post_id, 'link_wc_product', true ) );

            if ( ! $original_post_id || ! $product_id || ! $product = wc_get_product( $product_id ) ) {
                wp_send_json_error('Invalid movie or product.');
            }

            $seat_map = '';

            $quantity = 1;
            $wtbm_movie_id = $original_post_id;
            $theater_id = intval($_POST['theater_id'] ?? 0);
            $booking_date = isset( $_POST['booking_date'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_date'] ) ) : '';
            $booking_time = isset( $_POST['booking_time'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_time'] ) ) : '';
//            $seat_count = intval($_POST['seat_count'] ?? 0);
            $seat_count = isset( $_POST['seat_count'] ) ? intval( wp_unslash( $_POST['seat_count'] ) ) : '';

            $seat_names = isset( $_POST['seat_names'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['seat_names'] ) ) ) : [];
            $booked_seat_ids = isset( $_POST['booked_seat_ids'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['booked_seat_ids'] ) ) ) : [];

            $wtbm_price = floatval($_POST['total_amount'] ?? 0);
            $user_name = isset( $_POST['userName'] ) ? sanitize_text_field( wp_unslash( $_POST['userName'] ) ) : '';
            $user_phone_num = isset( $_POST['userPhoneNum'] ) ? sanitize_text_field( wp_unslash( $_POST['userPhoneNum'] ) ) : '';
            $user_id = get_current_user_id();
            $order = wc_create_order(array('customer_id' => $user_id));
            if ( ! $order ) {
                wp_send_json_error('Failed to create order.');
            }

            $day_of_week = strtolower( gmdate('l', strtotime( $booking_date ) ) );
            $wtbm_price = WTBM_Set_Pricing_Sules::calculate_price_by_rules(  $wtbm_price, $day_of_week, $booking_date, $theater_id, $booking_time, $seat_count  );

            $item = new WC_Order_Item_Product();
            $item->set_product( wc_get_product( $product_id ) );
            $item->set_quantity( $quantity );
            $item->set_total( $wtbm_price );

            // Add meta safely
            $item->add_meta_data('_wtbm_id', $wtbm_movie_id, true);
            $item->add_meta_data('_theater_id', $theater_id, true);
            $item->add_meta_data('_movie_id', $wtbm_movie_id, true);
            $item->add_meta_data('_wtbm_date', $booking_date, true);
            $item->add_meta_data('_wtbm_time', $booking_time, true);
            $item->add_meta_data('_wtbm_tp', $wtbm_price, true);
            $item->add_meta_data('_wtbm_selected_seats', $seat_names, true);
            $item->add_meta_data('_wtbm_selected_seat_ids', $booked_seat_ids, true);
            $item->add_meta_data('_wtbm_number_of_seats', $seat_count, true);
            $item->add_meta_data('_wtbm_user_name', $user_name, true);
            $item->add_meta_data('_wtbm_user_phone', $user_phone_num, true);

            $order->add_item($item);

            $order->calculate_totals();
            $order->update_status('completed');

            $pdf_url_btn= '';
            if( $order->get_id() ){
                $data['wtbm_movie_id']          = $wtbm_movie_id;
                $data['wtbm_theater_id']        = $theater_id;
                $data['wtbm_tp']                = $wtbm_price;
                $data['wtbm_order_date']        = $booking_date;
                $data['wtbm_order_time']        = $booking_time;
                $data['wtbm_order_id']          = $order->get_id();
                $data['wtbm_order_status']      =  $order->get_payment_method();
                $data['wtbm_seats']             = $seat_names;
                $data['wtbm_seat_ids']          = $booked_seat_ids;
                $data['wtbm_number_of_seats']   = $seat_count;
                $data['wtbm_payment_method']    = $order->get_payment_method();
                $data['wtbm_user_id']           = $order->get_user_id() ?? '';
                $data['wtbm_billing_name']      = $user_name;
                $data['wtbm_billing_email']     = '';
                $data['wtbm_billing_phone']     = $user_phone_num;
                $data['wtbm_billing_address']   = '';

                $booking_data = apply_filters( 'wtbm_add_booking_data', $data, $wtbm_movie_id );

                WTBM_Woocommerce::add_cpt_data('wtbm_booking', $booking_data['wtbm_billing_name'], $booking_data );


                if( $theater_id && $wtbm_movie_id &&  $booking_date && $booking_time ){
                    $not_available = WTBM_Manage_Ajax::getAvailableSeats( $theater_id, $wtbm_movie_id, $booking_date, $booking_time );
                }else{
                    $not_available = [];
                }

                if( $theater_id ){
                    $seat_map = WTBM_Details_Layout::display_theater_seat_mapping( $theater_id, $not_available );
                }

                $pdf_url_btn = WTBM_Pro_Pdf::get_pdf_url_button( $order->get_id(),  );

            }

            wp_send_json_success(array(
                'message'   => 'Order placed successfully!',
                'order_id'  => $order->get_id(),
                'seat_map'  => $seat_map,
                'pdf_url_btn'  => $pdf_url_btn,
                'wtbm_total_price' => $wtbm_price,
            ));
        }




        public function new_ticket_booking_display( $args ){

            ?>
            <div id="wtbm_new_ticket_sale_content" class="tab-content">
                <div class="section">
                    <div class="section-header">
                        <h3 class="section-title"><?php esc_attr_e( 'New Ticket Sale', 'wptheaterly' ); ?></h3>
                    </div>

                    <?php $this->display_registration_data( $args );?>
                </div>
            </div>
        <?php }

        public function display_registration_data( $atts ){

            $today_date = gmdate('M d, y');

//            ob_start();
            ?>
            <div class="wtbm_registrationContainer" id="wtbm_registrationContainer">
                <div class="wtbm_registrationMainContent">
                    <?php
                    WTBM_Details_Layout::booking_date_display();
                    ?>

                    <div class="section" id="wtbm_movieSection">
                        <?php WTBM_Details_Layout::display_date_wise_movies() ;?>
                    </div>

                    <div class="section" id="wtbm_hallSection" style="display: none">
                        <h2 class="section-title"><?php esc_attr_e( 'Select Show Time', 'wptheaterly' );?></h2>
                        <div class="halls-list" id="wtbm_displayHallsList">
                        </div>
                    </div>

                    <div class="wtbm_seat_loader" id="wtbm_seat_loader" style="display: none"></div>

                    <div class="section" id="wtbm_seatSection" style="display: none">
                        <h2 class="section-title"><?php esc_attr_e( 'Select Seats', 'wptheaterly' );?></h2>
                        <div class="wtbm_seat_map" id="wtbm_seat_map">
                            <div class="screen"><?php esc_attr_e( 'Theater Screen', 'wptheaterly' );?></div>
                            <div class="wtbm_SeatsGrid" id="wtbm_seatsGrid"></div>
                            <div class="seat-legend">
                                <div class="legend-item">
                                    <div class="legend-color" style="background: #28a745;"></div>
                                    <span><?php esc_attr_e( 'Available', 'wptheaterly' );?></span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: #667eea;"></div>
                                    <span><?php esc_attr_e( 'Selected', 'wptheaterly' );?></span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background: #dc3545;"></div>
                                    <span><?php esc_attr_e( 'Occupied', 'wptheaterly' );?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="wtbm_registrationSidebar" id="wtbm_registrationSidebar">
                    <h2 class="section-title"><?php esc_attr_e( 'Tickets Summary', 'wptheaterly' );?></h2>

                    <div class="wtbm_registrationSummaryCard">
                        <input type="hidden" name="wtbm_summeryMovieId" id="wtbm_summeryMovieId" value="">
                        <input type="hidden" name="wtbm_summeryTheaterId" id="wtbm_summeryTheaterId" value="">
                        <input type="hidden" name="wtbm_summeryDate" id="wtbm_summeryDate" value="<?php echo esc_attr( gmdate("Y-m-d") );?>">
                        <input type="hidden" name="wtbm_summeryTime" id="wtbm_summeryTime" value="">
                        <input type="hidden" name="wtbm_summerySeatType" id="wtbm_summerySeatType" value="">
                        <input type="hidden" name="wtbm_summerySeatNumber" id="wtbm_summerySeatNumber" value="">
                        <input type="hidden" name="wtbm_summerySeatIds" id="wtbm_summerySeatIds" value="">
                        <input type="hidden" name="wtbm_summeryTotalAmount" id="wtbm_summeryTotalAmount" value="">

                        <div id="wtbm_selectedMovieDisplay">
                            <div id="wtbm_movieName"></div>
                            <div id="wtbm_movieDuration"><?php esc_attr_e( 'Select a movie', 'wptheaterly' );?></div>
                        </div>

                        <div class="wtbm_registrationSummaryItem">
                            <span><?php esc_attr_e( 'Location', 'wptheaterly' );?>:</span>
                            <span><?php esc_attr_e( '--', 'wptheaterly' );?></span>
                        </div>
                        <div class="wtbm_registrationSummaryItem">
                            <span>Show Date:</span>
                            <span id="wtbm_summaryDateDisplay"><?php echo esc_attr( $today_date );?></span>
                        </div>
                        <div class="wtbm_registrationSummaryItem">
                            <span><?php esc_attr_e( 'Hall Name:', 'wptheaterly' );?></span>
                            <span id="wtbm_summaryTheaterName">--</span>
                        </div>
                        <div class="wtbm_registrationSummaryItem">
                            <span><?php esc_attr_e( 'Show Time:', 'wptheaterly' );?></span>
                            <span id="wtbm_summaryTimeSlot">--</span>
                        </div>
                        <div class="wtbm_registrationSummaryItem">
                            <span><?php esc_attr_e( 'Seat Type:', 'wptheaterly' );?></span>
                            <span id="wtbm_summarySeatType"><?php esc_attr_e( 'Premium', 'wptheaterly' );?></span>
                        </div>
                        <div class="wtbm_registrationSummaryItem">
                            <span><?php esc_attr_e( 'Ticket Quantity:', 'wptheaterly' );?></span>
                            <span id="wtbm_summaryQuantity">0</span>
                        </div>
                        <div class="wtbm_registrationSummaryItem">
                            <span><?php esc_attr_e( 'Selected Seat:', 'wptheaterly' );?></span>
                            <span id="wtbm_summarySeats">--</span>
                        </div>
                        <div class="wtbm_registrationSummaryItem">
                            <span><?php esc_attr_e( 'Total Amount:', 'wptheaterly' );?></span>
                            <div class="wtbm_totalPriceSymbol">
                                <span id="wtbm_summaryTotal">0 </span>
                                <span class="wtbm_currency"><?php echo esc_attr( get_woocommerce_currency_symbol());?></span>
                            </div>
                        </div>
                    </div>

                    <div class="booking-form">
                        <h3 style="margin-bottom: 15px;"><?php esc_attr_e( 'Ticket For', 'wptheaterly' );?></h3>
                        <div class="form-group">
                            <label class="form-label"><?php esc_attr_e( 'Full Name', 'wptheaterly' );?></label>
                            <input type="text" class="form-input" id="wtbm_getUserName" placeholder="Enter your name">
                        </div>
                        <div class="form-group">
                            <label class="form-label"><?php esc_attr_e( 'Mobile Number', 'wptheaterly' );?></label>
                            <input type="tel" class="form-input" id="wtbm_getUserPhone" placeholder="Enter mobile number">
                        </div>
                        <div class="" id="wtbm_download_ticket"></div>
                        <button class="purchase-btn" id="wtbm_adminTicketPurchaseBtn" ><?php esc_attr_e( 'PURCHASE TICKET', 'wptheaterly' );?></button>
                        <div class="admin-ticket-note">
                            <?php esc_attr_e( 'By clicking the Purchase Tickets you are accepting Terms &amp; Conditions of Star Cineplex', 'wptheaterly' );?>
                        </div>
                    </div>
                </div>
            </div>
            <?php

//            return ob_get_clean();
        }

    }

    new WTBM_New_Ticket_Booking();
}