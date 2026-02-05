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
                add_shortcode('wtbm_single_movie_booking', array( $this, 'single_movie_booking' ) );
			}

            function single_movie_booking( $attr ) {
                ob_start();

                $movie_id = isset( $attr['movie_id'] ) ? $attr['movie_id'] : '';

                if( $movie_id ){

                    $movie_description = get_the_excerpt( $movie_id );
                    $movie_poster_id = WTBM_Function::get_post_info( $movie_id, 'wtbp_movie_poster_id', '');
                    $poster_url = wp_get_attachment_url( $movie_poster_id );
                    $movie_active = WTBM_Function::get_post_info( $movie_id, 'wtbp_movie_active', '');
                    $movie_release_date = WTBM_Function::get_post_info( $movie_id, 'wtbp_movie_release_date', '');
                    $movie_rating = WTBM_Function::get_post_info( $movie_id, 'wtbp_movie_rating', '');
                    $movie_duration = WTBM_Function::get_post_info( $movie_id, 'wtbp_movie_duration', '');
                    $movie_genre = WTBM_Function::get_post_info( $movie_id, 'wtbp_movie_genre', '');
//                    $date = '2026-02-02';
                    $date = gmdate("Y-m-d");
                    $theater_show_times = WTBM_Details_Layout::display_theater_show_time_single_movie( $movie_id, $date );

                    ?>
                    <div class="wtbm_single_movie_wrapper">

                        <div class="wtbm_booking_movie_card wtbm_movieActive" style="visibility: hidden"
                             data-movie-id="<?php echo esc_attr( $movie_id ); ?>">
                        </div>

                       <!-- <div class="wtbm_single_movie_title"><?php /*echo esc_attr( get_the_title( $movie_id ) );*/?></div>
                        <div class="wtbm_single_movie_subtitle"><?php /*echo esc_attr( get_the_title( $movie_id ) );*/?></div>
-->
                        <div class="wtbm_single_movie_card_description">
                            <div class="wtbm_single_movie_poster">
                                <img src="<?php echo esc_url( $poster_url );?>" alt="Avatar Poster" />
                            </div>
                            <div class="wtbm_single_movie_container">
                                <h1 class="wtbm_single_movie_movie_title"><?php echo esc_attr( get_the_title( $movie_id ) );?></h1>
                                <div class="" style="display: flex; gap: 10px">
                                    <div class="wtbm_single_movie_meta">
                                        <?php esc_attr_e( 'Runtime', 'wptheaterly' );?>: <?php echo esc_attr( $movie_duration );?> | <?php esc_attr_e( 'Genre', 'wptheaterly' );?>: <?php echo esc_attr( $movie_genre );?> | <?php esc_attr_e( 'Rating', 'wptheaterly' );?>: <?php echo esc_attr( $movie_rating );?><br>
                                        <?php esc_attr_e( 'Director', 'wptheaterly' );?>: Sofia Martinez | <?php esc_attr_e( 'Starring', 'wptheaterly' );?>: James Chen, Elena Rodriguez<br>
                                        <?php esc_attr_e( 'Description', 'wptheaterly' );?>: <?php echo esc_html( $movie_description );?>
                                    </div>
                                    <div class="wtbm_single_movie_meta" style="display: flex; flex-direction: column">
                                        <span class="wtbm_single_movie_badge">Thriller / Mystery</span>
                                        <span class="wtbm_single_movie_badge">Action</span>
                                        <span class="wtbm_single_movie_badge">Adventure</span>
                                        <span class="wtbm_single_movie_badge">Fantasy</span>
                                        <span>PG‑13</span>
                                        <span>3h 12m</span>
                                    </div>
                                </div>

                            </div>

                        </div>

                        <div class="wtbm_single_movie_card">
                            <div class="wtbm_single_movie_section_title"><?php esc_attr_e( 'Select Date', 'wptheaterly' );?></div>
                            <div class="wtbm_single_movie_options wtbm_date">
                                <?php
                                 echo wp_kses_post( WTBM_Details_Layout::booking_date_display_single_movie( $movie_id ) );
                                ?>
                            </div>

                            <div class="wtbm_single_movie_section_title" style="margin-top:20px"><?php esc_attr_e( 'Select Time', 'wptheaterly' );?></div>
                            <div class="wtbm_single_movie_options wtbm_time">
                                <?php
                                    $today_date = gmdate('M d, y');
                                    echo wp_kses_post( $theater_show_times );
                                ?>
                            </div>
                        </div>

                        <div class="" style="display: flex; gap: 20px">
                            <div class="wtbm_single_movie_card" style="width: calc( 100% - 170px)">
                                <div class="wtbm_single_movie_section_title"><?php esc_attr_e( 'Select Your Seats', 'wptheaterly' );?></div>
                                <div class="wtbm_single_movie_screen"><?php esc_attr_e( 'SCREEN', 'wptheaterly' );?></div>
                                <div class="wtbm_single_movie_screen_bar"></div>
                                <div class="wtbm_single_movie_seats" id="wtbm_single_movie_seats">

                                </div>
                            </div>
                            <div class="wtbm_single_movie_card" id="wtbm_single_movie_booking_card" style="width: 170px; display: none">
                                <div class="wtbm_single_movie_summary">
                                    <div class="wtbm_singleRegistrationSidebar" id="wtbm_registrationSidebar">
                                        <h2 class="section-title"><?php esc_attr_e( 'Tickets Summary', 'wptheaterly' );?></h2>

                                        <div class="wtbm_registrationSummaryCard">
                                            <input type="hidden" name="wtbm_summeryMovieId" id="wtbm_summeryMovieId" value="<?php echo esc_attr( $movie_id );?>">
                                            <input type="hidden" name="wtbm_summeryTheaterId" id="wtbm_summeryTheaterId" value="">
                                            <input type="hidden" name="wtbm_summeryDate" id="wtbm_summeryDate" value="<?php echo esc_attr( gmdate("Y-m-d") );?>">
                                            <input type="hidden" name="wtbm_summeryTime" id="wtbm_summeryTime" value="">
                                            <input type="hidden" name="wtbm_summerySeatType" id="wtbm_summerySeatType" value="">
                                            <input type="hidden" name="wtbm_summerySeatNumber" id="wtbm_summerySeatNumber" value="">
                                            <input type="hidden" name="wtbm_summerySeatIds" id="wtbm_summerySeatIds" value="">
                                            <input type="hidden" name="wtbm_summeryTotalAmount" id="wtbm_summeryTotalAmount" value="">

<!--                                            <div id="wtbm_selectedMovieDisplay"></div>-->

                                            <div class="wtbm_registrationSummaryItem" style="display: none">
                                                <span><i class="mi mi-stage-theatre"></i> <?php esc_attr_e( 'Hall Name:', 'wptheaterly' );?></span>
                                                <span id="wtbm_summaryTheaterName">--</span>
                                            </div>
                                            <div class="wtbm_registrationSummaryItem" style="display: none; flex-direction: column">
                                                <span><i class="mi mi-calendar"></i> <?php esc_attr_e( 'Show Date:', 'wptheaterly' );?></span>
                                                <span id="wtbm_summaryDateDisplay"><?php echo esc_attr( $today_date );?></span>
                                            </div>
                                            <div class="wtbm_registrationSummaryItem" style="display: none; flex-direction: column">
                                                <span><i class="mi mi-clock-three"></i> <?php esc_attr_e( 'Show Time:', 'wptheaterly' );?></span>
                                                <span id="wtbm_summaryTimeSlot">--</span>
                                            </div>

                                            <div class="wtbm_registrationSummaryItem" style="display: flex; flex-direction: column">
                                                <span><i class="mi mi-loveseat"></i> <?php esc_attr_e( 'Selected Seat:', 'wptheaterly' );?></span>
                                                <span id="wtbm_summarySeats">--</span>
                                            </div>

                                            <div class="wtbm_registrationSummaryItem" style="display: flex; flex-direction: column">
                                                <span><i class="mi mi-ticket"></i> <?php esc_attr_e( 'Ticket Quantity:', 'wptheaterly' );?></span>
                                                <span id="wtbm_summaryQuantity">0</span>
                                            </div>

                                            <div class="wtbm_registrationSummaryItem" style="display: flex; flex-direction: column">
                                                <span><i class="mi mi-coins"></i> <?php esc_attr_e( 'Total Amount:', 'wptheaterly' );?></span>
                                                <div class="wtbm_totalPriceSymbol">
                                                    <span id="wtbm_summaryTotal">0 </span>
                                                    <span class="wtbm_currency"><?php echo esc_attr( get_woocommerce_currency_symbol());?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="booking-form">
                                            <h2 class="section-title"><?php esc_attr_e( 'Ticket For', 'wptheaterly' );?></h2>
                                            <div class="form-group">
                                                <label class="form-label"><?php esc_attr_e( 'Full Name', 'wptheaterly' );?></label>
                                                <input type="text" class="form-input" id="wtbm_getUserName" placeholder="Enter your name">
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label"><?php esc_attr_e( 'Mobile Number', 'wptheaterly' );?></label>
                                                <input type="tel" class="form-input" id="wtbm_getUserPhone" placeholder="Enter mobile number">
                                            </div>

                                        </div>
                                    </div>
                                </div>
                                <!--                            <button class="wtbm_single_movie_button">COMPLETE BOOKING</button>-->
                                <button class="wtbm_single_movie_button" id="wtbm_ticketPurchaseBtn" ><?php esc_attr_e( 'PURCHASE TICKET', 'wptheaterly' );?></button>
                                <div class="purchase-info">
                                    <?php esc_attr_e( 'By clicking the Purchase Tickets you are accepting Terms &amp; Conditions of Star Cineplex', 'wptheaterly' );?>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php
                }
                return ob_get_clean();
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

                $today_date = gmdate('M d, y');

                ob_start();
                ?>
                <div class="wtbm_registrationContainer">
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
                            </div>

                            <!-- <div class="wtbm_registrationSummaryItem">
                                <span><?php esc_attr_e( 'Location', 'wptheaterly' );?>:</span>
                                <span><?php esc_attr_e( '--', 'wptheaterly' );?></span>
                            </div> -->
                            <div class="wtbm_registrationSummaryItem">
                                <span><i class="mi mi-stage-theatre"></i> <?php esc_attr_e( 'Hall Name:', 'wptheaterly' );?></span>
                                <span id="wtbm_summaryTheaterName">--</span>
                            </div>
                            <div class="wtbm_registrationSummaryItem">
                                <span><i class="mi mi-calendar"></i> <?php esc_attr_e( 'Show Date:', 'wptheaterly' );?></span>
                                <span id="wtbm_summaryDateDisplay"><?php echo esc_attr( $today_date );?></span>
                            </div>
                            <div class="wtbm_registrationSummaryItem">
                                <span><i class="mi mi-clock-three"></i> <?php esc_attr_e( 'Show Time:', 'wptheaterly' );?></span>
                                <span id="wtbm_summaryTimeSlot">--</span>
                            </div>
                            <div class="wtbm_registrationSummaryItem">
                                <span><i class="mi mi-loveseat"></i> <?php esc_attr_e( 'Seat Type:', 'wptheaterly' );?></span>
                                <span id="wtbm_summarySeatType"><?php esc_attr_e( '--', 'wptheaterly' );?></span>
                            </div>
                            <div class="wtbm_registrationSummaryItem">
                                <span><i class="mi mi-ticket"></i> <?php esc_attr_e( 'Ticket Quantity:', 'wptheaterly' );?></span>
                                <span id="wtbm_summaryQuantity">0</span>
                            </div>
                            <div class="wtbm_registrationSummaryItem">
                                <span><i class="mi mi-loveseat"></i> <?php esc_attr_e( 'Selected Seat:', 'wptheaterly' );?></span>
                                <span id="wtbm_summarySeats">--</span>
                            </div>
                            <div class="wtbm_registrationSummaryItem">
                                <span><i class="mi mi-coins"></i> <?php esc_attr_e( 'Total Amount:', 'wptheaterly' );?></span>
                                <div class="wtbm_totalPriceSymbol">
                                    <span id="wtbm_summaryTotal">0 </span>
                                    <span class="wtbm_currency"><?php echo esc_attr( get_woocommerce_currency_symbol());?></span>
                                </div>
                            </div>
                        </div>

                        <div class="booking-form">
                            <h2 class="section-title"><?php esc_attr_e( 'Ticket For', 'wptheaterly' );?></h2>
                            <div class="form-group">
                                <label class="form-label"><?php esc_attr_e( 'Full Name', 'wptheaterly' );?></label>
                                <input type="text" class="form-input" id="wtbm_getUserName" placeholder="Enter your name">
                            </div>
                            <div class="form-group">
                                <label class="form-label"><?php esc_attr_e( 'Mobile Number', 'wptheaterly' );?></label>
                                <input type="tel" class="form-input" id="wtbm_getUserPhone" placeholder="Enter mobile number">
                            </div>
                            <button class="purchase-btn" id="wtbm_ticketPurchaseBtn" ><?php esc_attr_e( 'PURCHASE TICKET', 'wptheaterly' );?></button>
                            <div class="purchase-info">
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