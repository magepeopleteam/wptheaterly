<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('WTBM_Woocommerce')) {
		class WTBM_Woocommerce {
			public function __construct() {
				add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 90, 3);
				add_action('woocommerce_before_calculate_totals', array($this, 'before_calculate_totals'), 90, 1);
				add_filter('woocommerce_cart_item_thumbnail', array($this, 'cart_item_thumbnail'), 90, 3);
				add_filter('woocommerce_get_item_data', array($this, 'get_item_data'), 90, 2);
				//************//
				add_action('woocommerce_after_checkout_validation', array($this, 'after_checkout_validation'));
				add_action('woocommerce_checkout_create_order_line_item', array($this, 'checkout_create_order_line_item'), 90, 4);
				add_action('woocommerce_checkout_order_processed', array($this, 'checkout_order_processed'), 90, 3);
				add_action('woocommerce_store_api_checkout_order_processed', array($this, 'checkout_order_processed'), 90, 3);
				add_filter('woocommerce_order_status_changed', array($this, 'order_status_changed'), 10, 4);

//                add_action( 'woocommerce_before_thankyou', [ $this, 'pa_show_welcome_message' ], 10, 1 );
                add_action( 'woocommerce_thankyou', [ $this, 'pa_show_welcome_message' ], 10, 1 );
			}
			public function add_cart_item_data( $cart_item_data, $product_id ) {

				$linked_id = WTBM_Function::get_post_info($product_id, 'link_mptrs_id', $product_id);
				$product_id = is_string(get_post_status($linked_id)) ? $linked_id : $product_id;
                if ( isset($_POST['nonce']) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'wtbm_nonce') ) {
					if ( get_post_type($product_id) == WTBM_Function::get_movie_cpt()) {
						$total_price = sanitize_text_field( wp_unslash( $cart_item_data['wtbm_price'] ) );
						$cart_item_data['wtbm_tp'] = $total_price;
						$cart_item_data['line_total'] = $total_price;
						$cart_item_data['line_subtotal'] = $total_price;
						$cart_item_data = apply_filters('mptrs_add_cart_item', $cart_item_data, $product_id);
					}
					$cart_item_data['wtbm_movie_id'] = $product_id;
				}
                //echo '<pre>'; print_r( $cart_item_data ); echo '</pre>'; die();
				return $cart_item_data;
			}
			public function before_calculate_totals($cart_object): void {
				foreach ($cart_object->cart_contents as $value) {
					$post_id = array_key_exists('wtbm_movie_id', $value) ? $value['wtbm_movie_id'] : 0;
					if ( get_post_type($post_id) == WTBM_Function::get_movie_cpt() ) {
						$total_price = $value['wtbm_price'];
						$value['data']->set_price($total_price);
						$value['data']->set_regular_price($total_price);
						$value['data']->set_sale_price($total_price);
						$value['data']->set_sold_individually('yes');
						$value['data']->get_price();
					}
				}
            }
			public function cart_item_thumbnail($thumbnail, $cart_item) {
				$post_id = array_key_exists('wtbm_movie_id', $cart_item) ? $cart_item['wtbm_movie_id'] : 0;
				if (get_post_type($post_id) == WTBM_Function::get_movie_cpt()) {
					$thumbnail = '<div class="bg_image_area" data-href="' . get_the_permalink($post_id) . '"><div data-bg-image="' . WTBM_Function::get_image_url($post_id) . '"></div></div>';
				}
				return $thumbnail;
			}
			public function get_item_data($item_data, $cart_item) {
				ob_start();
				$post_id = array_key_exists('wtbm_movie_id', $cart_item) ? $cart_item['wtbm_movie_id'] : 0;
				if (get_post_type($post_id) == WTBM_Function::get_movie_cpt()) {
					$this->show_cart_item( $cart_item, $post_id );
					do_action('wtbm_show_cart_item', $cart_item, $post_id );
				}
				$item_data[] = array('key' => esc_html__('Booking Details ', 'wptheaterly'), 'value' => ob_get_clean());
				return $item_data;
			}
			//**************//
			public function after_checkout_validation() {
				global $woocommerce;
				$items = $woocommerce->cart->get_cart();
				foreach ($items as $values) {
					$post_id = array_key_exists('wtbm_movie_id', $values) ? $values['wtbm_movie_id'] : 0;
					if (get_post_type($post_id) == WTBM_Function::get_movie_cpt()) {
						//wc_add_notice( __( "custom_notice", 'fake_error' ), 'error');
						do_action('wtbm_validate_cart_item', $values, $post_id );
					}
				}
			}
			public function checkout_create_order_line_item( $item, $cart_item_key, $values ) {
				$post_id = array_key_exists('wtbm_movie_id', $values) ? $values['wtbm_movie_id'] : 0;
				if (get_post_type($post_id) == WTBM_Function::get_movie_cpt() ) {
					$date = $values['booking_date'] ?: '';
					$time = $values['booking_time'] ?: '';
					$total_price = $values['wtbm_price'] ?? '';
                    $seat_names = $values['seat_names'] ?? [];
                    $seat_ids = $values['booked_seat_ids'] ?? [];
                    $seats_str = is_array( $seat_names ) ? implode(', ', $seat_names ) : '';
                    $theater = get_the_title( $values['theater_id'] );
                    $movie = get_the_title( $values['wtbm_movie_id'] );

					$item->add_meta_data(esc_html__('Date ', 'wptheaterly'), esc_html(WTBM_Function::date_format( $date, 'date' ) ) );
					$item->add_meta_data(esc_html__('Time ', 'wptheaterly'), esc_html(WTBM_Function::date_format( $time, 'time' ) ) );
					$item->add_meta_data(esc_html__('Movie ', 'wptheaterly'), esc_html( $movie ) );
					$item->add_meta_data(esc_html__('Theater Name ', 'wptheaterly'), esc_html( $theater ) );
					$item->add_meta_data(esc_html__('Selected Seats ', 'wptheaterly'), esc_html( $seats_str ) );

					$item->add_meta_data('_wtbm_id', $post_id );
					$item->add_meta_data('_theater_id', $values['theater_id'] );
					$item->add_meta_data('_movie_id', $values['wtbm_movie_id'] );
					$item->add_meta_data('_wtbm_date', $date);
					$item->add_meta_data('_wtbm_time', $time);
					$item->add_meta_data('_wtbm_tp', $total_price);
					$item->add_meta_data('_wtbm_selected_seats', $seat_names );
					$item->add_meta_data('_wtbm_selected_seat_ids', $seat_ids );

					do_action('mpwpb_checkout_create_order_line_item', $item, $values);
				}
			}
			public function checkout_order_processed( $order ) {
				if ($order) {
					$order_id = $order->get_id();
					$order_status = $order->get_status();
					if ($order_status != 'failed') {
						//$item_id = current( array_keys( $order->get_items() ) );
						foreach ( $order->get_items() as $item_id => $item ) {
							$post_id = wc_get_order_item_meta( $item_id, '_wtbm_id');
							if (get_post_type( $post_id ) == WTBM_Function::get_movie_cpt() ) {
								$date = wc_get_order_item_meta($item_id, '_wtbm_date');
                                $time = wc_get_order_item_meta($item_id, '_wtbm_time');
                                $selected_seats = wc_get_order_item_meta($item_id, '_wtbm_selected_seats');
                                $selected_seat_ids = wc_get_order_item_meta($item_id, '_wtbm_selected_seat_ids');
								$date = $date ? WTBM_Function::data_sanitize($date) : '';
                                $time = $time ? WTBM_Function::data_sanitize($time) : '';
								$total_price = wc_get_order_item_meta($item_id, '_wtbm_tp');
								$total_price = $total_price ? WTBM_Function::data_sanitize($total_price) : '';
								$data['wtbm_theater_id'] = wc_get_order_item_meta($item_id, '_theater_id');
								$data['wtbm_movie_id'] = $post_id;

								$data['wtbm_tp'] = $total_price;
								$data['wtbm_order_date'] = $date;
								$data['wtbm_order_time'] = $time;
								$data['wtbm_order_id'] = $order_id;
								$data['wtbm_order_status'] = $order_status;
								$data['wtbm_seats'] = $selected_seats;
								$data['wtbm_seat_ids'] = $selected_seat_ids;
								$data['wtbm_number_of_seats'] = count( $selected_seat_ids );
								$data['wtbm_payment_method'] = $order->get_payment_method();
								$data['wtbm_user_id'] = $order->get_user_id() ?? '';
								$data['wtbm_billing_name'] = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
								$data['wtbm_billing_email'] = $order->get_billing_email();
								$data['wtbm_billing_phone'] = $order->get_billing_phone();
								$data['wtbm_billing_address'] = $order->get_billing_address_1() . ' ' . $order->get_billing_address_2();

								$booking_data = apply_filters( 'add_wtbm_booking_data', $data, $post_id );
								self::add_cpt_data('wtbm_booking', $booking_data['wtbm_billing_name'], $booking_data);
							}
						}
					}
				}
			}
			public function order_status_changed($order_id) {
				$order = wc_get_order($order_id);
				$order_status = $order->get_status();
				foreach ($order->get_items() as $item_id => $item_values) {
					$post_id = wc_get_order_item_meta($item_id, '_mpwpb_id');
					if (get_post_type($post_id) == WTBM_Function::get_movie_cpt()) {
						$this->wc_order_status_change($order_status, $post_id, $order_id);
					}
				}
			}
			//**************************//
			public function show_cart_item( $cart_item, $post_id ) {

                $seat_string = '';
                if( is_array( $cart_item['seat_names'] ) && !empty( $cart_item['seat_names'] ) ){
                    $seat_string = implode(', ', $cart_item['seat_names']);
                }

                $theater = get_the_title( $cart_item['theater_id'] );
                $movie = get_the_title( $cart_item['wtbm_movie_id'] );
				?>
                <div class="mptrs_area">
					<?php do_action('mpwpb_before_cart_item_display', $cart_item, $post_id ); ?>
                    <div class="dLayout_xs">
                        <ul class="cart_list">
                            <li>
                                <span class="far fa-clock"></span>
                                <h6><?php esc_html_e('Booking Date', 'wptheaterly'); ?>&nbsp;:&nbsp;</h6>
                                <span><?php echo esc_html( WTBM_Function::date_format( $cart_item['booking_date'] ) ); ?></span>
                            </li>
                            <li>
                                <span class="far fa-clock"></span>
                                <h6><?php esc_html_e('Booking Time', 'wptheaterly'); ?>&nbsp;:&nbsp;</h6>
                                <span><?php echo esc_html( WTBM_Function::date_format( $cart_item['booking_time'], 'time')); ?></span>
                            </li>
                            <li>
                                <span class="far fa-clock"></span>
                                <h6><?php esc_html_e('Theater Name', 'wptheaterly'); ?>&nbsp;:&nbsp;</h6>
                                <span><?php echo esc_html( $theater ); ?></span>
                            </li>
                            <li>
                                <span class="far fa-clock"></span>
                                <h6><?php esc_html_e('Movie', 'wptheaterly'); ?>&nbsp;:&nbsp;</h6>
                                <span><?php echo esc_html( $movie ); ?></span>
                            </li>
                            <li>
                                <span class="far fa-clock"></span>
                                <h6><?php esc_html_e('Booking Seats', 'wptheaterly'); ?>&nbsp;:&nbsp;</h6>
                                <span><?php echo esc_html( $seat_string ); ?></span>
                            </li>
                        </ul>
                    </div>
					<?php do_action('wtbm_after_cart_item_display', $cart_item, $post_id); ?>
                </div>
				<?php
			}
			public function wc_order_status_change($order_status, $post_id, $order_id) {
				$args = array(
					'post_type' => 'mpwpb_booking',
					'posts_per_page' => -1,
					'meta_query' => array(
						'relation' => 'AND',
						array(
							array(
								'key' => 'mpwpb_id',
								'value' => $post_id,
								'compare' => '='
							),
							array(
								'key' => 'mpwpb_order_id',
								'value' => $order_id,
								'compare' => '='
							)
						)
					)
				);
				$loop = new WP_Query($args);
				foreach ($loop->posts as $user) {
					$user_id = $user->ID;
					//echo '<pre>';print_r($user_id);echo '</pre>';
					update_post_meta($user_id, 'mpwpb_order_status', $order_status);
				}
				$args = array(
					'post_type' => 'mpwpb_extra_service_booking',
					'posts_per_page' => -1,
					'meta_query' => array(
						'relation' => 'AND',
						array(
							array(
								'key' => 'mpwpb_id',
								'value' => $post_id,
								'compare' => '='
							),
							array(
								'key' => 'mpwpb_order_id',
								'value' => $order_id,
								'compare' => '='
							)
						)
					)
				);
				$loop = new WP_Query($args);
				foreach ($loop->posts as $user) {
					$user_id = $user->ID;
					update_post_meta($user_id, 'mpwpb_order_status', $order_status);
				}
			}
			//**********************//
			public static function add_cpt_data( $cpt_name, $title, $meta_data = array(), $status = 'publish', $cat = array() ) {
				$new_post = array(
					'post_title' => $title,
					'post_content' => '',
					'post_category' => $cat,
					'tags_input' => array(),
					'post_status' => $status,
					'post_type' => $cpt_name
				);
				wp_reset_postdata();
				$post_id = wp_insert_post($new_post);
				if (sizeof($meta_data) > 0) {
					foreach ($meta_data as $key => $value) {
						update_post_meta($post_id, $key, $value);
					}
				}
				if ($cpt_name == 'wtbm_booking') {
					$pin = $meta_data['wtbm_user_id'] . $meta_data['wtbm_order_id'] . $meta_data['wtbm_theater_id'] . $post_id;
					update_post_meta($post_id, 'wtbm_pin', $pin);
				}
				wp_reset_postdata();
			}

            /**
             * Get booking data (post + meta) by WooCommerce order ID
             *
             * @param int $order_id WooCommerce order ID
             * @return array|false Array of booking data with meta values or false if none
             */
            public static function wtbm_get_booking_data_by_order( $order_id ) {
                if ( empty( $order_id ) ) {
                    return false;
                }

                $args = array(
                    'post_type'      => 'wtbm_booking',
                    'posts_per_page' => -1,
                    'post_status'    => 'any',
                    'meta_query'     => array(
                        array(
                            'key'   => 'wtbm_order_id',
                            'value' => $order_id,
                        ),
                    ),
                );

                $query = new WP_Query( $args );
                $results = array();

                if ( $query->have_posts() ) {
                    foreach ( $query->posts as $post ) {

                        $theater_id = get_post_meta( $post->ID, 'wtbm_theater_id', true );
                        $movie_id = get_post_meta( $post->ID, 'wtbm_movie_id', true );

                        $results = array(
                            'id'                => $post->ID,
                            'title'             => $post->post_title,
                            'theater_name'      => get_the_title( $theater_id ),
                            'movie_name'        => get_the_title( $movie_id ),
                            'total_price'       => get_post_meta( $post->ID, 'wtbm_tp', true ),
                            'order_date'        => get_post_meta( $post->ID, 'wtbm_order_date', true ),
                            'order_time'        => get_post_meta( $post->ID, 'wtbm_order_time', true ),
                            'selected_seats'    => get_post_meta( $post->ID, 'wtbm_seats', true ),
                            'billing_name'      => get_post_meta( $post->ID, 'wtbm_billing_name', true ),
                        );
                    }
                    return $results;
                }

                return false;
            }


            function pa_show_welcome_message( $order_id ) {
                if ( ! $order_id ) return;

                $order = wc_get_order( $order_id );

                if ( $order ) {

                    $booking = self::wtbm_get_booking_data_by_order($order_id);
                    $order_datetime = DateTime::createFromFormat('Y-m-d H:i', $booking['order_date'].' '.$booking['order_time']);
                    $formatted_datetime = $order_datetime->format('j M, Y | h:i A');
                    ?>
                    <div id="wtbm_pa-welcome-popup" class="wtbm_welcome_popup">
                        <div class="wtbm_welcome_popup_content">
                            <div class="wtbm_pa_close">&times;</div>
                            <h2>🎉 <?php esc_attr_e( 'Welcome to our family!', 'wptheaterly' )?></h2>
                            <div class="wtbm_welcome_card">
                                <h3 class="wtbm_welcome_header">🎬 <?php esc_attr_e( 'Booking Summary', 'wptheaterly' )?></h3>
                                <div class="wtbm_welcome_content">
                                    <h3 class="wtbm_welcome_movie"><strong><?php esc_attr_e( 'Movie:', 'wptheaterly' )?></strong><?php echo esc_html($booking['movie_name']); ?></h3>
                                    <p class="wtbm_welcome_theater"><strong><?php esc_attr_e( 'Theater:', 'wptheaterly' )?></strong> <?php echo esc_html($booking['theater_name']); ?></p>
                                    <p class="wtbm_welcome_datetime"><strong><?php esc_attr_e( 'Date & Time:', 'wptheaterly' )?></strong> <?php echo esc_html($formatted_datetime); ?></p>
                                    <p class="wtbm_welcome_seats"><strong><?php esc_attr_e( 'Seats:', 'wptheaterly' )?></strong>
                                        <?php foreach ($booking['selected_seats'] as $seat): ?>
                                            <span class="wtbm_welcome_seat"><?php echo esc_html($seat); ?></span>
                                        <?php endforeach; ?>
                                    </p>
                                    <p class="wtbm_welcome_total"><strong><?php esc_attr_e( 'Total Price:', 'wptheaterly' )?></strong> <?php echo get_woocommerce_currency();?> <?php echo esc_html($booking['total_price']); ?></p>
                                </div>
                            </div>

                            <p style="font-size: 14px"><?php esc_attr_e( 'Thank you for your order,', 'wptheaterly' );?> <strong><?php echo esc_html( $booking['billing_name'] ); ?></strong>.
                                <?php esc_attr_e( 'We’re excited to have you with us!', 'wptheaterly' )?></p>

                        </div>

                    </div>
                    <?php
                }
            }

		}

		new WTBM_Woocommerce();
	}