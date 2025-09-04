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
                $lay_outs = new WTBM_Details_Layout();
                ob_start();
                ?>
                <div class="container">
                    <div class="main-content">
                        <!-- Date Selection -->
                        <?php
                        WTBM_Details_Layout::booking_date_display();
                        ?>

                        <!-- Movie Selection -->
                        <div class="section" id="wtbm_movieSection">
                            <?php echo wp_kses_post( WTBM_Details_Layout::display_date_wise_movies() );?>
                        </div>

                        <!-- Hall & Time Selection -->
                        <div class="section" id="wtbm_hallSection" style="display: none">
                            <h2 class="section-title">Select Show Time</h2>
                            <div class="halls-list" id="wtbm_displayHallsList">
                            </div>
                        </div>

                        <!-- Seat Selection -->
                        <div class="section" id="wtbm_seatSection" style="display: none">
                            <h2 class="section-title">Select Seats</h2>
                            <div class="seat-map">
                                <div class="screen">THEATER SCREEN</div>
                                <div class="seats-grid" id="wtbm_seatsGrid">
                                    <!-- Seats will be populated by JavaScript -->
                                </div>
                                <div class="seat-legend">
                                    <div class="legend-item">
                                        <div class="legend-color" style="background: #28a745;"></div>
                                        <span>Available</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color" style="background: #667eea;"></div>
                                        <span>Selected</span>
                                    </div>
                                    <div class="legend-item">
                                        <div class="legend-color" style="background: #dc3545;"></div>
                                        <span>Occupied</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sidebar">
                        <h2 class="summary-title">Tickets Summary</h2>

                        <div class="summary-card">
                            <div id="selectedMovieDisplay">
                                <div style="width: 60px; height: 80px; background: #ddd; border-radius: 8px; margin-bottom: 15px;"></div>
                                <div style="color: #666;">Select a movie</div>
                            </div>

                            <div class="summary-item">
                                <span>Location:</span>
                                <span>BSC</span>
                            </div>
                            <div class="summary-item">
                                <span>Show Date:</span>
                                <span id="summaryDate">Aug 18, 25</span>
                            </div>
                            <div class="summary-item">
                                <span>Hall Name:</span>
                                <span id="summaryHall">--</span>
                            </div>
                            <div class="summary-item">
                                <span>Show Time:</span>
                                <span id="summaryTime">--</span>
                            </div>
                            <div class="summary-item">
                                <span>Seat Type:</span>
                                <span id="summarySeatType">Premium</span>
                            </div>
                            <div class="summary-item">
                                <span>Ticket Quantity:</span>
                                <span id="summaryQuantity">0</span>
                            </div>
                            <div class="summary-item">
                                <span>Selected Seat:</span>
                                <span id="summarySeats">--</span>
                            </div>
                            <div class="summary-item">
                                <span>Total Amount:</span>
                                <span id="summaryTotal">0 BDT</span>
                            </div>
                        </div>

                        <div class="booking-form">
                            <h3 style="margin-bottom: 15px;">Ticket For</h3>
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-input" placeholder="Enter your name">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Mobile Number</label>
                                <input type="tel" class="form-input" placeholder="Enter mobile number">
                            </div>
                            <button class="purchase-btn" id="purchaseBtn" disabled="">PURCHASE TICKET</button>
                            <div style="margin-top: 15px; font-size: 12px; color: #666; text-align: center;">
                                By clicking the Purchase Tickets you are accepting Terms &amp; Conditions of Star Cineplex
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