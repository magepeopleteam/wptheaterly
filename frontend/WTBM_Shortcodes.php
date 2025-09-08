<?php
	/*
   * @Author 		rubelcuet10@gmail.com
   * Copyright: 	mage-people.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'WTBM_Shortcodes' ) ) {
		class WTBM_Shortcodes {
			public function __construct() {
                add_shortcode( 'wtbm_ticket_booking', array( $this, 'wtbm_ticket_booking_shortcode' ) );
			}

            public function wtbm_ticket_booking_shortcode( $atts = array(), $content = null ) {
                // Default attributes
                $atts = shortcode_atts( array(
                    'name' => 'Guest',
                ), $atts, 'wtbm_hello' );

                $template = $this->display_registration_data( $atts );

                return $template;

            }

            public function display_registration_data( $atts ){

                $today_date = date('M d, y');

                ob_start();
                ?>
                <div class="wtbm_registrationContainer">
                    <div class="wtbm_registrationMainContent">
                        <?php
                            echo WTBM_Details_Layout::booking_date_display();
                        ?>

                        <div class="section" id="wtbm_movieSection">
                            <?php echo WTBM_Details_Layout::display_date_wise_movies() ;?>
                        </div>

                        <div class="section" id="wtbm_hallSection" style="display: none">
                            <h2 class="section-title"><?php esc_attr_e( 'Select Show Time', 'wptheaterly' );?></h2>
                            <div class="halls-list" id="wtbm_displayHallsList">
                            </div>
                        </div>

                        <div class="section" id="wtbm_seatSection" style="display: none">
                            <h2 class="section-title"><?php esc_attr_e( 'Select Seats', 'wptheaterly' );?></h2>
                            <div class=" v seat-map">
                                <div class="screen"><?php esc_attr_e( 'THEATER SCREEN', 'wptheaterly' );?></div>
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
                        <h2 class="summary-title"><?php esc_attr_e( 'Tickets Summary', 'wptheaterly' );?></h2>

                        <div class="wtbm_registrationSummaryCard">
                            <input type="hidden" name="wtbm_summeryMovieId" id="wtbm_summeryMovieId" value="">
                            <input type="hidden" name="wtbm_summeryTheaterId" id="wtbm_summeryTheaterId" value="">
                            <input type="hidden" name="wtbm_summeryDate" id="wtbm_summeryDate" value="<?php echo esc_attr( date("Y-m-d") );?>">
                            <input type="hidden" name="wtbm_summeryTime" id="wtbm_summeryTime" value="">
                            <input type="hidden" name="wtbm_summerySeatType" id="wtbm_summerySeatType" value="">
                            <input type="hidden" name="wtbm_summerySeatNumber" id="wtbm_summerySeatNumber" value="">
                            <input type="hidden" name="wtbm_summeryTotalAmount" id="wtbm_summeryTotalAmount" value="">

                            <div id="wtbm_selectedMovieDisplay">
                                <div id="wtbm_movieName" style="width: 60px; height: 80px; background: #ddd; border-radius: 8px; margin-bottom: 15px;"></div>
                                <div id="wtbm_movieDuration" style="color: #666;"><?php esc_attr_e( 'Select a movie', 'wptheaterly' );?></div>
                            </div>

                            <div class="wtbm_registrationSummaryItem">
                                <span><?php esc_attr_e( 'Location', 'wptheaterly' );?>:</span>
                                <span><?php esc_attr_e( 'BSC', 'wptheaterly' );?></span>
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
                                <span>Seat Type:</span>
                                <span id="wtbm_summarySeatType"><?php esc_attr_e( 'Premium', 'wptheaterly' );?></span>
                            </div>
                            <div class="swtbm_registrationSummaryItem">
                                <span><?php esc_attr_e( 'Ticket Quantity:', 'wptheaterly' );?></span>
                                <span id="wtbm_summaryQuantity">0</span>
                            </div>
                            <div class="wtbm_registrationSummaryItem">
                                <span><?php esc_attr_e( 'Selected Seat:', 'wptheaterly' );?></span>
                                <span id="wtbm_summarySeats">--</span>
                            </div>
                            <div class="wtbm_registrationSummaryItem">
                                <span><?php esc_attr_e( 'Total Amount:', 'wptheaterly' );?></span>
                                <span id="wtbm_summaryTotal">0 BDT</span>
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
                            <button class="purchase-btn" id="wtbm_ticketPurchaseBtn" ><?php esc_attr_e( 'PURCHASE TICKET', 'wptheaterly' );?></button>
                            <div style="margin-top: 15px; font-size: 12px; color: #666; text-align: center;">
                                <?php esc_attr_e( 'By clicking the Purchase Tickets you are accepting Terms &amp; Conditions of Star Cineplex', 'wptheaterly' );?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php

                return ob_get_clean();
            }

		}
		new WTBM_Shortcodes();
	}