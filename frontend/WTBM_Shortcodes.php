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
                $movie_id = 471;

                if( $movie_id ){

                    $date = '2026-02-02';

                    $theater_show_times = WTBM_Details_Layout::display_theater_show_time_single_movie( $movie_id, $date );

                    ?>

                    <style>
                        :root{
                            --wtbm-gold:#d4af37;
                        }
                        .wtbm_single_movie_body{
                            margin:0;
                            font-family:Georgia, serif;
                            background:radial-gradient(circle at top,#4a141c,#140609);
                            color:#f5e6c8;
                        }
                        .wtbm_single_movie_wrapper{
                            max-width:900px;
                            margin:30px auto;
                            padding:20px;
                        }
                        .wtbm_single_movie_title{
                            text-align:center;
                            color:var(--wtbm-gold);
                            font-size:42px;
                        }
                        .wtbm_single_movie_subtitle{
                            text-align:center;
                            letter-spacing:3px;
                            font-size:12px;
                            color:#c9a74a;
                            margin-bottom:30px;
                        }
                        .wtbm_single_movie_card{
                            background:linear-gradient(135deg,#3a1218,#1b070b);
                            border:1px solid #6b4a1f;
                            border-radius:14px;
                            padding:20px;
                            margin-bottom:25px;
                        }
                        .wtbm_single_movie_movie_title{
                            color:var(--wtbm-gold);
                            font-size:22px;
                        }
                        .wtbm_single_movie_meta{
                            font-size:13px;
                            opacity:.85;
                            margin-top:5px;
                        }
                        .wtbm_single_movie_section_title{
                            color:var(--wtbm-gold);
                            margin-bottom:10px;
                        }
                        .wtbm_single_movie_options{
                            display:flex;
                            gap:12px;
                            flex-wrap:wrap;
                        }
                        .wtbm_single_movie_option{
                            border:1px solid #6b4a1f;
                            padding:14px 18px;
                            border-radius:10px;
                            text-align:center;
                            cursor:pointer;
                            min-width:90px;
                            background:rgba(0,0,0,.2);
                        }
                        .wtbm_single_movie_option small{
                            display:block;
                            font-size:11px;
                            opacity:.7;
                        }
                        .wtbm_single_movie_option.wtbm_active{
                            background:var(--wtbm-gold);
                            color:#000;
                            font-weight:bold;
                        }
                        .wtbm_single_movie_screen{
                            text-align:center;
                            color:var(--wtbm-gold);
                            letter-spacing:3px;
                        }
                        .wtbm_single_movie_screen_bar{
                            height:4px;
                            width:60%;
                            margin:10px auto 25px;
                            background:linear-gradient(to right,transparent,var(--wtbm-gold),transparent);
                        }
                        .wtbm_single_movie_seats{
                            display:grid;
                            /*grid-template-columns:repeat(10,1fr);*/
                            gap:8px;
                            max-width:500px;
                            margin:0 auto;
                        }
                        .wtbm_single_movie_seat{
                            border:1px solid #6b4a1f;
                            padding:8px 0;
                            text-align:center;
                            font-size:11px;
                            border-radius:6px;
                            cursor:pointer;
                            background:rgba(0,0,0,.25);
                        }
                        .wtbm_single_movie_seat.wtbm_selected{
                            background:#b91c1c;
                            color:#fff;
                        }
                        .wtbm_single_movie_seat.wtbm_occupied{
                            background:#333;
                            cursor:not-allowed;
                            opacity:.5;
                        }
                        .wtbm_single_movie_legend{
                            display:flex;
                            justify-content:center;
                            gap:20px;
                            margin-top:20px;
                            font-size:12px;
                        }
                        .wtbm_single_movie_summary{
                            display:grid;
                            grid-template-columns:1fr 1fr;
                            gap:10px;
                        }
                        .wtbm_single_movie_total{
                            grid-column:span 2;
                            font-size:20px;
                            color:var(--wtbm-gold);
                            margin-top:10px;
                        }
                        .wtbm_single_movie_button{
                            margin-top:15px;
                            width:100%;
                            padding:14px;
                            border-radius:10px;
                            border:1px solid var(--wtbm-gold);
                            background:linear-gradient(#a61d2a,#7a121c);
                            color:#fff;
                            font-size:16px;
                            cursor:pointer;
                        }
                    </style>

                    <body class="wtbm_single_movie_body">

                    <div class="wtbm_single_movie_wrapper">

                        <div class="wtbm_single_movie_title">Cinema Paradiso</div>
                        <div class="wtbm_single_movie_subtitle">RESERVE YOUR EXPERIENCE</div>

                        <div class="wtbm_single_movie_card">
                            <div class="wtbm_single_movie_movie_title">The Midnight Chronicles</div>
                            <div class="wtbm_single_movie_meta">
                                Runtime: 2h 15m | Genre: Mystery Thriller | Rating: PG-13<br>
                                Director: Sofia Martinez | Starring: James Chen, Elena Rodriguez
                            </div>
                        </div>

                        <div class="wtbm_single_movie_card">
                            <div class="wtbm_single_movie_section_title">Select Date</div>
                            <div class="wtbm_single_movie_options wtbm_date">
                                <div class="wtbm_single_movie_option"><small>TUE</small>03</div>
                                <div class="wtbm_single_movie_option"><small>WED</small>04</div>
                                <div class="wtbm_single_movie_option"><small>THU</small>05</div>
                                <div class="wtbm_single_movie_option"><small>FRI</small>06</div>
                                <div class="wtbm_single_movie_option"><small>SAT</small>07</div>
                            </div>

                            <div class="wtbm_single_movie_section_title" style="margin-top:20px">Select Time</div>
                            <div class="wtbm_single_movie_options wtbm_time">
                                <?php
                                echo wp_kses_post( $theater_show_times );
                                ?>
                            </div>
                        </div>

                        <div class="wtbm_single_movie_card">
                            <div class="wtbm_single_movie_section_title">Select Your Seats</div>
                            <div class="wtbm_single_movie_screen">SCREEN</div>
                            <div class="wtbm_single_movie_screen_bar"></div>
                            <div class="wtbm_single_movie_seats" id="wtbm_single_movie_seats"></div>
                        </div>

                        <div class="wtbm_single_movie_card">
                            <div class="wtbm_single_movie_summary">
                                <div>Date:</div><div id="wtbm_date">Not selected</div>
                                <div>Time:</div><div id="wtbm_time">Not selected</div>
                                <div>Seats:</div><div id="wtbm_seats">None</div>
                                <div class="wtbm_single_movie_total">
                                    Total: $<span id="wtbm_total">0.00</span>
                                </div>
                            </div>
                            <button class="wtbm_single_movie_button">COMPLETE BOOKING</button>
                        </div>

                    </div>

                    <script>
                        jQuery(function($){
                            let price=12;

                            $(".wtbm_single_movie_option").on("click",function(){
                                $(this).siblings().removeClass("wtbm_active");
                                $(this).addClass("wtbm_active");
                                if($(this).parent().hasClass("wtbm_date"))
                                    $("#wtbm_date").text($(this).text());
                                if($(this).parent().hasClass("wtbm_time"))
                                    $("#wtbm_time").text($(this).text());
                            });

                            $(".wtbm_single_movie_seats").on("click",".wtbm_single_movie_seat",function(){
                                if($(this).hasClass("wtbm_occupied")) return;
                                $(this).toggleClass("wtbm_selected");
                                let seats=$(".wtbm_selected").map(function(){return $(this).text()}).get();
                                $("#wtbm_seats").text(seats.join(", ") || "None");
                                $("#wtbm_total").text((seats.length*price).toFixed(2));
                            });
                        });
                    </script>

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