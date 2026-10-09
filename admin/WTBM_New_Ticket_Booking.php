<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
if( !class_exists( 'WTBM_New_Ticket_Booking' ) ) {
    class WTBM_New_Ticket_Booking{
        public function __construct(){
            add_action( 'wtbm_new_ticket_booking', array( $this, 'new_ticket_booking_display' ), 10, 2 );


            add_action( 'wp_ajax_wtbm_theater_ticket_booking_admin', [ $this, 'wtbm_theater_ticket_booking_admin' ] );
        }

        function wtbm_theater_ticket_booking_admin() {
            check_ajax_referer( 'wtbm_nonce', 'nonce' );
            WTBM_Operations::response( WTBM_Operations::purchase( wp_unslash( $_POST ), true ) );
        }

        public function new_ticket_booking_display( $args, $current_tab ){

            ?>
            <div id="wtbm_new_ticket_sale_content" class="tab-content <?php echo ($current_tab == 'wtbm_new_ticket_sale') ? 'active' : ''; ?>">
                <div class="section">
                    <div class="section-header">
                        <h3 class="section-title"><?php esc_attr_e( 'New Ticket Sale', 'wptheaterly' ); ?></h3>
                    </div>

                    <?php $this->display_registration_data( $args ); WTBM_Operations_Admin::collect_form(); ?>
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

                    <div class="section wtbm_movieSection" id="wtbm_movieSection">
                        <?php
                        $screening_status = 'showing';
                        WTBM_Details_Layout::display_date_wise_movies( $screening_status ) ;?>
                    </div>

                    <div class="section wtbm_hallSection" id="wtbm_hallSection" style="display: none">
                        <h2 class="section-title"><?php esc_attr_e( 'Select Show Time', 'wptheaterly' );?></h2>
                        <div class="halls-list" id="wtbm_displayHallsList">
                        </div>
                    </div>
                    <div class="seat-generating-area">
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
                </div>
                <div class="wtbm_registrationSidebar" id="wtbm_registrationSidebar">
                    <h2 class="booking-summary-title"><?php esc_attr_e( 'Booking Summary', 'wptheaterly' );?></h2>
                    <?php
                    WTBM_Layout_Functions::display_pricing_rules();
                    ?>

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
                        <div class="form-group"><label class="form-label" for="wtbm-pos-email"><?php esc_html_e( 'Ticket email', 'wptheaterly' ); ?></label><input type="email" id="wtbm-pos-email" class="form-input"></div>
                        <?php WTBM_Operations_Admin::payment_fields(); ?>
                        <div class="wtbm-pos-result" aria-live="polite"></div>
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