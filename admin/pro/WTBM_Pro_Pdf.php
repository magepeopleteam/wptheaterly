<?php

/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
if ( ! defined( 'ABSPATH' ) ) {
    die;
} // Cannot access pages directly.
if ( ! class_exists('WTBM_Pro_Pdf') ) {
    class WTBM_Pro_Pdf {
        public function __construct() {
            add_action('woocommerce_thankyou', array($this, 'pdf_button'));
            add_action('wtbm_pdf_button', array($this, 'pdf_button' ), 10, 2 );
            add_action('wtbm_generate_pdf', array($this, 'generate_pdf'), 10, 3);
            /***************/
            add_action('wp_ajax_wtbm_generate_pdf', array($this, 'wtbm_generate_pdf'));
            add_action('wp_ajax_nopriv_wtbm_generate_pdf', array($this, 'wtbm_generate_pdf'));

            add_action('wp_ajax_wtbm_prepare_booking_pdf', array( $this, 'wtbm_prepare_booking_pdf' ) );

            add_action('wp_ajax_wtbm_download_booking_data_pdf',  array( $this, 'wtbm_download_booking_data_pdf' ) );

            add_action('wp_ajax_wtbm_prepare_booking_csv',  array( $this, 'wtbm_prepare_booking_csv' ) );
            add_action('wp_ajax_wtbm_download_booking_data_csv', array( $this, 'wtbm_download_booking_data_csv' ) );

        }
        public function pdf_button( $order_id, $action ='order_done' ) {
            $order = wc_get_order($order_id);
            $order_status = $order ? $order->get_status() : '';
            if (class_exists('\Mpdf\Mpdf') && in_array($order_status, ['processing', 'completed'])) {
                $item_id = current(array_keys($order->get_items()));
                $post_id = wc_get_order_item_meta($item_id, '_wtbm_id');
                if (get_post_type($post_id) == WTBM_Function::get_movie_cpt() ) {
                    $download_url = WTBM_Tickets::pdf_url( $order );
                    if ($download_url) {
                        ?>
                        <div class="wtbm">
                            <button type="button" class="_themeButton_mB" data-href="<?php echo esc_attr($download_url); ?>">
                                <span class="fas fa-file-pdf mR_xs"></span>
                                <?php esc_html_e('Ticket', 'wptheaterly'); ?>
                            </button>
                        </div>
                        <?php
                    }

                }
            }
        }

        public static function get_pdf_url_button( $order_id ) {
            ob_start();
            $order = wc_get_order($order_id);
            $order_status = $order ? $order->get_status() : '';
            if (class_exists('\Mpdf\Mpdf') && in_array($order_status, ['processing', 'completed', 'completed'])) {
                $item_id = current(array_keys($order->get_items()));
                $post_id = wc_get_order_item_meta($item_id, '_wtbm_id');
                if (get_post_type($post_id) == WTBM_Function::get_movie_cpt() ) {
                    $download_url = WTBM_Tickets::pdf_url( $order );
                    if ($download_url) {
                        ?>
                        <div class="wtbm">
                            <button type="button" class="_themeButton_mB" data-href="<?php echo esc_attr($download_url); ?>">
                                <span class="fas fa-file-pdf mR_xs"></span>
                                <?php esc_html_e('Download Ticket', 'wptheaterly'); ?>
                            </button>
                        </div>
                        <?php
                    }
                }
            }

            return ob_get_clean();
        }
        public function wtbm_generate_pdf() {
            // Read-only download is authorized below by customer ownership or the order key.
            $order_id = isset($_GET['order_id']) ? sanitize_text_field(wp_unslash( $_GET['order_id'] ) ) : '';
            if (empty($order_id)) {
                esc_attr_e('Order ID is required.', 'wptheaterly');
                wp_die();
            }
            $order = wc_get_order( absint( $order_id ) );
            $key = sanitize_text_field( wp_unslash( $_GET['order_key'] ?? '' ) );
            if ( ! WTBM_Tickets::access( $order, $key ) || ! $order->is_paid() ) { wp_die( esc_html__( 'Ticket access denied.', 'wptheaterly' ), '', array( 'response' => 403 ) ); }
            nocache_headers();
            header("Content-Type: application/pdf; charset=UTF-8");
            $file_name = esc_html__('Order_', 'wptheaterly') . $order_id . '.pdf';
            $this->generate_pdf($order_id, $file_name);
            exit;
        }
        public function generate_pdf($order_id, $file_name, $mail = '') {

            if (class_exists('\Mpdf\Mpdf')) {
                $html = $this->create_pdf_file($order_id);
                $mpdf = WTBM_Booking_Service::pdf();
                $mpdf->allow_charset_conversion = true;  // Set by default to TRUE
                $mpdf->autoScriptToLang = true;
                $mpdf->baseScript = 1;
                $mpdf->autoVietnamese = true;
                $mpdf->autoArabic = true;
                $mpdf->autoLangToFont = true;
                $mpdf->WriteHTML($html);
                if ($mail) {
                    $mpdf->Output($file_name, 'F');
                } else {
                    $mpdf->Output($file_name, 'I');
                }
            }
        }
        public function create_pdf_file($order_id) {
            $file_slug = 'WTBM_Pdf';
            $template_dir = WTBM_PLUGIN_DIR . '/template_pro/wtbm_Pdf.php';
            $template_dir = file_exists($template_dir) ? $template_dir : '';
            ob_start();
            include $template_dir;
            return ob_get_clean();
        }
        //**********************//
        public static function get_pdf_url($args = array()) {
            $default_args = array(
                'action' => 'wtbm_generate_pdf',
                'order_id' => '',
            );
            $args = wp_parse_args($args, $default_args);
            $build_url = http_build_query($args);
            $nonce_url = wp_nonce_url(admin_url("admin-ajax.php?" . $build_url), $args['action']);
            return apply_filters('wtbm_filter_pdf_url', $nonce_url);
        }
        public static function pdf_bg() {
            $ticket_bg_url = WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_bg');
            if (!empty($ticket_bg_url) && wp_get_attachment_url($ticket_bg_url)) { // Ensure the URL exists
                ?>
                style="background: url(<?php echo esc_url($ticket_bg_url); ?>); background-repeat: no-repeat; background-size: 100% 100%;"
                <?php
            } else {
                ?>
                style="background-color: <?php echo esc_attr(WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_bg_color', '#fbfbfb')); ?>;"
                <?php
            }
        }
        public static function pdf_logo() {
            $logo_url = WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_logo');
            if (!empty($logo_url)) {
                ?>
                <img src="<?php echo esc_attr(wp_get_attachment_url($logo_url)) ?>" alt="logo" width="150"/>
                <?php
            }
        }



		/**
		 * Resolve all matching bookings, independently of the visible page size.
		 *
		 * @param array $request Export filters or booking IDs.
		 * @return array|WP_Error
		 */
	public static function export_ids( $request ) {
		if ( ! empty( $request['filtered'] ) ) {
			$filters = WTBM_Booking_Content::booking_filters( $request );
			if ( is_wp_error( $filters ) ) {
				return $filters; }
			$data = WTBM_Layout_Functions::wtbm_get_filtered_booking_data( $filters, array(), -1 );
			return array_keys( $data['booking_data'] );
		}
		return array_values(
			array_filter(
				array_unique( array_map( 'absint', (array) ( $request['ids'] ?? array() ) ) ),
				static function ( $bid ) {
					return 'wtbm_booking' === get_post_type( $bid ) && in_array( get_post_status( $bid ), array( 'publish', 'draft' ), true );
				}
			)
		);
	}

		/**
		 * Prepare a short-lived staff export.
		 *
		 * @param string $format Export format.
		 */
	private function prepare_booking_export( $format ) {
		check_ajax_referer( 'mptrs_admin_nonce', 'nonce' );
		if ( ! WTBM_Booking_Service::can( 'manage' ) ) {
			wp_send_json_error( 'Permission denied.', 403 ); }
		if ( 'pdf' === $format && ! class_exists( '\Mpdf\Mpdf' ) ) {
			wp_send_json_error( __( 'PDF support is unavailable.', 'wptheaterly' ), 503 ); }
		$ids = self::export_ids( $_POST );
		if ( is_wp_error( $ids ) ) {
			wp_send_json_error( $ids->get_error_message(), 400 ); }
		if ( ! $ids ) {
			wp_send_json_error( __( 'No bookings match these filters.', 'wptheaterly' ), 400 ); }
		$token = wp_generate_password( 32, false );
		set_transient(
			'wtbm_booking_data_' . $format . '_' . $token,
			array(
				'user' => get_current_user_id(),
				'ids'  => $ids,
			),
			5 * MINUTE_IN_SECONDS
		);
		$action = 'wtbm_download_booking_data_' . $format;
		wp_send_json_success(
			array(
				'count'        => count( $ids ),
				'download_url' => add_query_arg(
					array(
						'action'   => $action,
						'token'    => $token,
						'_wpnonce' => wp_create_nonce( $action ),
					),
					admin_url( 'admin-ajax.php' )
				),
			)
		);
	}

		/**
		 * Prepare the booking PDF.
		 */
	public function wtbm_prepare_booking_pdf() {
		$this->prepare_booking_export( 'pdf' ); }

		/**
		 * Authorize a download and resolve its saved booking IDs.
		 *
		 * @param string $format Export format.
		 * @return array
		 */
	private function booking_export_ids( $format ) {
		if ( ! WTBM_Booking_Service::can( 'manage' ) ) {
			wp_die( 'Permission denied.', '', array( 'response' => 403 ) ); }
		check_admin_referer( 'wtbm_download_booking_data_' . $format );
		$token  = sanitize_text_field( wp_unslash( $_GET['token'] ?? '' ) );
		$key    = 'wtbm_booking_data_' . $format . '_' . $token;
		$stored = get_transient( $key );
		if ( ! is_array( $stored ) || (int) ( $stored['user'] ?? 0 ) !== get_current_user_id() || empty( $stored['ids'] ) ) {
			wp_die( 'Download expired. Please export again.', '', array( 'response' => 410 ) ); }
		delete_transient( $key );
		nocache_headers();
		return $stored['ids'];
	}

		/**
		 * Download the authorized booking PDF.
		 */
	public function wtbm_download_booking_data_pdf() {
		$ids = $this->booking_export_ids( 'pdf' );
		if ( ! class_exists( '\Mpdf\Mpdf' ) ) {
			wp_die( 'PDF support unavailable.', '', array( 'response' => 503 ) ); }
		try {
			$mpdf                   = WTBM_Booking_Service::pdf(
				array(
					'format'        => 'A4-L',
					'margin_top'    => 12,
					'margin_bottom' => 12,
				)
			);
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- mPDF API property.
			$mpdf->autoScriptToLang = true;
			$mpdf->autoLangToFont   = true;
			$mpdf->WriteHTML( WTBM_Layout_Pro::generate_booking_data_pdf( $ids ) );
			if ( ob_get_length() ) {
				ob_end_clean(); }
			$mpdf->Output( 'theaterly-bookings.pdf', 'D' );
		} catch ( Throwable $error ) {
			wp_die( esc_html__( 'PDF generation failed. Please try again.', 'wptheaterly' ), '', array( 'response' => 500 ) ); }
		exit;
	}

		/**
		 * Prepare the booking CSV.
		 */
	public function wtbm_prepare_booking_csv() {
		$this->prepare_booking_export( 'csv' ); }

		/**
		 * Protect spreadsheet users against formula injection from customer fields.
		 *
		 * @param mixed $value Cell value.
		 * @return string
		 */
	public static function csv_cell( $value ) {
		$value = (string) $value;
		return preg_match( '/^[\s]*[=+@-]|^[\t\r\n]/u', $value ) ? "'" . $value : $value;
	}

		/**
		 * Build a Unicode CSV containing booking and check-in details.
		 *
		 * @param array $ids Booking IDs.
		 * @return string
		 */
	public static function booking_csv( $ids ) {
		$out = fopen( 'php://temp', 'w+' );
		fwrite( $out, "\xEF\xBB\xBF" );
		fputcsv( $out, array( 'Booking ID', 'Name', 'Phone', 'Theater', 'Movie', 'Show date', 'Time', 'Seats', 'Amount', 'Currency', 'Status', 'Check-in', 'Check-in details' ), ',', '"', '' );
		foreach ( WTBM_Layout_Pro::get_booking_data_by_ids( $ids ) as $row ) {
			fputcsv( $out, array_map( array( __CLASS__, 'csv_cell' ), array( $row['booking_number'], $row['name'], $row['phone'], $row['theater_name'], $row['movie_name'], $row['date'], $row['time'], $row['seat_number'], $row['amount'], $row['currency'], $row['status'], $row['checkin'], $row['checkin_details'] ) ), ',', '"', '' );
		}
		rewind( $out );
		$csv = stream_get_contents( $out );
		fclose( $out );
		return $csv;
	}

		/**
		 * Download the authorized booking CSV.
		 */
	public function wtbm_download_booking_data_csv() {
		$ids = $this->booking_export_ids( 'csv' );
		$csv = self::booking_csv( $ids );
		if ( ob_get_length() ) {
			ob_end_clean(); }
		header( 'Content-Type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename="theaterly-bookings.csv"' );
		echo $csv; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- CSV cells are encoded with fputcsv, not rendered as HTML.
		exit;
	}

    }
    new WTBM_Pro_Pdf();
}
