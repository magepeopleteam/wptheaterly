<?php
/**
 * Paid/net reporting and exports, using WooCommerce financial records.
 *
 * @package Theaterly
 */

// phpcs:disable WordPress.Files.FileName -- Preserve the plugin's established class/file naming convention.
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
/**
 * Theaterly operational service.
 */
class WTBM_Operations_Report {
	/**
	 * Boot.
	 */
	public static function boot() {
		add_action( 'wp_ajax_wtbm_operations_report', array( __CLASS__, 'ajax' ) );
		add_action( 'admin_post_wtbm_report_export', array( __CLASS__, 'export' ) );
	}
	/**
	 * Data.
	 *
	 * @param mixed $filters Filters.
	 */
	public static function data( $filters ) {
		$from = sanitize_text_field( $filters['from'] ?? current_time( 'Y-m-d' ) );
		$to   = sanitize_text_field( $filters['to'] ?? $from );
		if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $from ) || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $to ) || wp_date( 'Y-m-d', strtotime( $from ), new DateTimeZone( 'UTC' ) ) !== $from || wp_date( 'Y-m-d', strtotime( $to ), new DateTimeZone( 'UTC' ) ) !== $to || strtotime( $from ) > strtotime( $to ) || strtotime( $to ) - strtotime( $from ) > 366 * DAY_IN_SECONDS ) {
			return new WP_Error( 'range', __( 'Select a valid date range of up to one year.', 'wptheaterly' ) ); }
		$basis  = 'show' === ( $filters['basis'] ?? 'sale' ) ? 'show' : 'sale';
		$result = array(
			'from'      => $from,
			'to'        => $to,
			'basis'     => $basis,
			'gross'     => 0,
			'net'       => 0,
			'refunds'   => 0,
			'tax'       => 0,
			'discounts' => 0,
			'tickets'   => 0,
			'orders'    => 0,
			'payments'  => array(),
			'rows'      => array(),
			'cashiers'  => array(),
		);
		$ids    = get_posts(
			array(
				'post_type'   => 'wtbm_booking',
				'post_status' => array( 'publish', 'private', 'draft' ),
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);
		$orders = array();
		foreach ( $ids as $bid ) {
			$order_id = absint( get_post_meta( $bid, 'wtbm_order_id', true ) );
			if ( ! isset( $orders[ $order_id ] ) ) {
				$orders[ $order_id ] = wc_get_order( $order_id ); }
			$order = $orders[ $order_id ];
			if ( ! $order ) {
				continue; }
			$sale_date = wp_date( 'Y-m-d', ( $order->get_date_paid() ? $order->get_date_paid() : $order->get_date_created() )->getTimestamp(), wp_timezone() );
			$date      = 'show' === $basis ? get_post_meta( $bid, 'wtbm_order_date', true ) : ( $order->get_date_paid() ? $order->get_date_paid() : $order->get_date_created() );
			if ( $date instanceof WC_DateTime ) {
				$date = wp_date( 'Y-m-d', $date->getTimestamp(), wp_timezone() ); }
			if ( $date < $from || $date > $to ) {
				continue; }
			if ( ! empty( $filters['movie'] ) && (int) get_post_meta( $bid, 'wtbm_movie_id', true ) !== absint( $filters['movie'] ) ) {
				continue; }
			if ( ! empty( $filters['theater'] ) && (int) get_post_meta( $bid, 'wtbm_theater_id', true ) !== absint( $filters['theater'] ) ) {
				continue; }
			if ( ! empty( $filters['status'] ) && $order->get_status() !== $filters['status'] ) {
				continue; }
			$item = $order->get_item( absint( get_post_meta( $bid, 'wtbm_item_id', true ) ) );
			if ( ! $item ) {
				foreach ( $order->get_items() as $candidate ) {
					if ( (int) $candidate->get_meta( '_wtbm_id' ) === (int) get_post_meta( $bid, 'wtbm_movie_id', true ) ) {
						$item = $candidate;
						break; }
				}
			}
			if ( ! $item ) {
				continue; }
			$paid       = $order->is_paid() || (bool) $order->get_date_paid();
			$subtotal   = $paid ? (float) $item->get_subtotal() : 0;
			$gross      = $paid ? (float) $item->get_total() : 0;
			$tax        = $paid ? (float) $item->get_total_tax() : 0;
			$refund     = $paid ? (float) $order->get_total_refunded_for_item( $item->get_id() ) : 0;
			$refund_tax = 0;
			foreach ( $item->get_taxes()['total'] ?? array() as $rate => $amount ) {
				$refund_tax += (float) $order->get_tax_refunded_for_item( $item->get_id(), $rate ); }
			$net                  = max( 0, $gross - $refund );
			$count                = $paid && ! in_array( $order->get_status(), array( 'cancelled', 'refunded', 'failed' ), true ) ? (int) get_post_meta( $bid, 'wtbm_number_of_seats', true ) : 0;
			$result['gross']     += $gross;
			$result['net']       += $net;
			$result['refunds']   += $refund;
			$result['tax']       += max( 0, $tax - $refund_tax );
			$result['discounts'] += max( 0, $subtotal - $gross );
			$result['tickets']   += $count;
			$method               = $order->get_payment_method_title() ? $order->get_payment_method_title() : __( 'Unspecified', 'wptheaterly' );
			if ( ! isset( $result['payments'][ $method ] ) ) {
				$result['payments'][ $method ] = array(
					'gross'   => 0,
					'refunds' => 0,
					'net'     => 0,
				); }
			foreach ( array(
				'gross'   => $gross,
				'refunds' => $refund,
				'net'     => $net,
			) as $field => $value ) {
				$result['payments'][ $method ][ $field ] += $value; }
			$cashier = absint( $order->get_meta( '_wtbm_cashier_id' ) );
			if ( $cashier ) {
				$result['cashiers'][ $cashier ] = ( $result['cashiers'][ $cashier ] ?? 0 ) + $net; }
			$result['rows'][] = array(
				'booking'   => $bid,
				'order'     => $order->get_order_number(),
				'movie'     => get_the_title( get_post_meta( $bid, 'wtbm_movie_id', true ) ),
				'theater'   => get_the_title( get_post_meta( $bid, 'wtbm_theater_id', true ) ),
				'show_date' => get_post_meta( $bid, 'wtbm_order_date', true ),
				'sale_date' => $sale_date,
				'tickets'   => $count,
				'status'    => $order->get_status(),
				'payment'   => $method,
				'gross'     => $gross,
				'refunds'   => $refund,
				'net'       => $net,
				'tax'       => max( 0, $tax - $refund_tax ),
			);
		}
		$result['orders'] = count( array_unique( array_column( $result['rows'], 'order' ) ) );
		return $result;
	}
	/**
	 * Html.
	 *
	 * @param mixed $data Data.
	 */
	public static function html( $data ) {
		ob_start(); ?>
		<div class="wtbm-operations wtbm-report-result">
		<header class="wtbm-period-heading"><div><span class="wtbm-report-kicker"><?php esc_html_e( 'Report results', 'wptheaterly' ); ?></span><h2><?php esc_html_e( 'Sales and payment report', 'wptheaterly' ); ?></h2><p><?php echo esc_html( $data['from'] . ' — ' . $data['to'] ); ?></p></div><span class="wtbm-report-basis"><?php echo esc_html( 'show' === $data['basis'] ? __( 'By show date', 'wptheaterly' ) : __( 'By sale/payment date', 'wptheaterly' ) ); ?></span></header>
		<div class="wtbm-ops-metrics">
		<?php
		foreach ( array(
			'gross'     => __( 'Gross sales', 'wptheaterly' ),
			'refunds'   => __( 'Refunds', 'wptheaterly' ),
			'net'       => __( 'Net sales', 'wptheaterly' ),
			'tax'       => __( 'Net tax', 'wptheaterly' ),
			'discounts' => __( 'Discounts', 'wptheaterly' ),
			'tickets'   => __( 'Active paid tickets', 'wptheaterly' ),
		) as $key => $label ) {
			?>
			<?php // translators: %s is the number of matching orders. ?>
			<article class="wtbm-ops-card wtbm-report-metric wtbm-report-metric-<?php echo esc_attr( $key ); ?>"><span><?php echo esc_html( $label ); ?></span><strong><?php echo 'tickets' === $key ? esc_html( number_format_i18n( $data[ $key ] ) ) : wp_kses_post( wc_price( $data[ $key ] ) ); ?></strong><small><?php echo esc_html( in_array( $key, array( 'gross', 'net' ), true ) ? __( 'Excluding tax', 'wptheaterly' ) : ( 'tickets' === $key ? sprintf( __( '%s matching orders', 'wptheaterly' ), number_format_i18n( $data['orders'] ) ) : __( 'For matching orders', 'wptheaterly' ) ) ); ?></small></article>
		<?php } ?></div>
		<div class="wtbm-report-breakdowns"><section class="wtbm-report-panel"><header><h3><?php esc_html_e( 'Payment methods', 'wptheaterly' ); ?></h3><span><?php esc_html_e( 'Sales less refunds', 'wptheaterly' ); ?></span></header><div class="wtbm-ops-table"><table><thead><tr><th scope="col"><?php esc_html_e( 'Method', 'wptheaterly' ); ?></th><th scope="col"><?php esc_html_e( 'Gross', 'wptheaterly' ); ?></th><th scope="col"><?php esc_html_e( 'Refunds', 'wptheaterly' ); ?></th><th scope="col"><?php esc_html_e( 'Net', 'wptheaterly' ); ?></th></tr></thead><tbody>
		<?php
		foreach ( $data['payments'] as $method => $totals ) {
			?>
			<tr><th scope="row"><?php echo esc_html( $method ); ?></th>
			<?php
			foreach ( $totals as $value ) {
				?>
			<td class="wtbm-numeric"><?php echo wp_kses_post( wc_price( $value ) ); ?></td><?php } ?></tr>
			<?php
		} if ( ! $data['payments'] ) {
			?>
			<tr><td colspan="4" class="wtbm-report-empty"><?php esc_html_e( 'No payments for these filters.', 'wptheaterly' ); ?></td></tr><?php } ?></tbody></table></div></section>
		<section class="wtbm-report-panel"><header><h3><?php esc_html_e( 'Cashier reconciliation', 'wptheaterly' ); ?></h3><span><?php esc_html_e( 'Net recorded sales', 'wptheaterly' ); ?></span></header><div class="wtbm-cashier-list">
		<?php
		foreach ( $data['cashiers'] as $id => $amount ) {
			$user = get_userdata( $id );
			?>
			<div><span><?php echo esc_html( $user ? $user->display_name : $id ); ?></span><strong><?php echo wp_kses_post( wc_price( $amount ) ); ?></strong></div>
			<?php
		} if ( ! $data['cashiers'] ) {
			?>
			<p class="wtbm-report-empty"><?php esc_html_e( 'No box-office sales recorded for these filters.', 'wptheaterly' ); ?></p><?php } ?></div><p class="wtbm-report-note"><?php esc_html_e( 'Manual refunds require actual reimbursement. Totals reflect recorded sales less refunds.', 'wptheaterly' ); ?></p></section></div>
		<section class="wtbm-report-panel"><header><h3><?php esc_html_e( 'Bookings', 'wptheaterly' ); ?></h3><span>
		<?php
			// translators: %s is the number of matching orders.
			printf( esc_html__( '%s matching orders', 'wptheaterly' ), esc_html( number_format_i18n( $data['orders'] ) ) );
		?>
			</span></header><div class="wtbm-ops-table"><table class="wtbm-result-bookings"><thead><tr>
		<?php
		foreach ( array( __( 'Order', 'wptheaterly' ), __( 'Movie', 'wptheaterly' ), __( 'Screen', 'wptheaterly' ), __( 'Sale date', 'wptheaterly' ), __( 'Show date', 'wptheaterly' ), __( 'Tickets', 'wptheaterly' ), __( 'Status', 'wptheaterly' ), __( 'Net', 'wptheaterly' ) ) as $label ) {
			?>
			<th scope="col"><?php echo esc_html( $label ); ?></th><?php } ?></tr></thead><tbody>
		<?php
		foreach ( $data['rows'] as $row ) {
			?>
			<tr><th scope="row">#<?php echo esc_html( $row['order'] ); ?></th>
			<?php
			foreach ( array( 'movie', 'theater', 'sale_date', 'show_date', 'tickets' ) as $field ) {
				?>
			<td><?php echo esc_html( $row[ $field ] ); ?></td><?php } ?><td><span class="wtbm-status-badge wtbm-status-<?php echo esc_attr( $row['status'] ); ?>"><?php echo esc_html( wc_get_order_status_name( $row['status'] ) ); ?></span></td><td class="wtbm-numeric"><?php echo wp_kses_post( wc_price( $row['net'] ) ); ?></td></tr>
			<?php
		} if ( ! $data['rows'] ) {
			?>
			<tr><td colspan="8" class="wtbm-report-empty"><?php esc_html_e( 'No bookings found. Adjust the date range or filters to see results.', 'wptheaterly' ); ?></td></tr><?php } ?></tbody></table></div></section></div>
		<?php
		return ob_get_clean();
	}
	/**
	 * Ajax.
	 */
	public static function ajax() {
		check_ajax_referer( 'wtbm_operations', 'nonce' );
		if ( ! WTBM_Booking_Service::can( 'reports' ) ) {
			wp_send_json_error( 'Permission denied.', 403 ); }
		$data = self::data( wp_unslash( $_POST ) );
		if ( is_wp_error( $data ) ) {
			WTBM_Operations::response( $data ); }
		wp_send_json_success( array( 'html' => self::html( $data ) ) );
	}
	/**
	 * Export.
	 */
	public static function export() {
		check_admin_referer( 'wtbm_report_export' );
		if ( ! WTBM_Booking_Service::can( 'reports' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'wptheaterly' ), '', array( 'response' => 403 ) ); }
		$data = self::data( wp_unslash( $_GET ) );
		if ( is_wp_error( $data ) ) {
			wp_die( esc_html( $data->get_error_message() ) ); }
		$format = sanitize_key( $_GET['format'] ?? 'print' );
		nocache_headers();
		if ( 'excel' === $format ) {
			header( 'Content-Type: application/vnd.ms-excel; charset=UTF-8' );
			header( 'Content-Disposition: attachment; filename="theaterly-sales.xls"' );
			echo '<?xml version="1.0" encoding="UTF-8"?><Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Sales"><Table>';
			$headers = array( 'order', 'movie', 'theater', 'show_date', 'sale_date', 'tickets', 'status', 'payment', 'gross', 'refunds', 'net', 'tax' );
			echo '<Row>';
			foreach ( $headers as $field ) {
				echo '<Cell><Data ss:Type="String">' . esc_html( $field ) . '</Data></Cell>';
			} echo '</Row>';
			foreach ( $data['rows'] as $row ) {
				echo '<Row>';
				foreach ( $headers as $field ) {
					$type = in_array( $field, array( 'tickets', 'gross', 'refunds', 'net', 'tax' ), true ) ? 'Number' : 'String';
					echo '<Cell><Data ss:Type="' . esc_attr( $type ) . '">' . esc_html( (string) $row[ $field ] ) . '</Data></Cell>';
				} echo '</Row>'; }
			echo '</Table></Worksheet></Workbook>';
			exit;
		}
		$html = '<html><head><meta charset="UTF-8"><title>Theaterly sales</title><style>body{font-family:sans-serif;color:#222}table{width:100%;border-collapse:collapse}td,th{padding:8px;border:1px solid #ddd;text-align:left}.wtbm-ops-metrics{display:flex;gap:12px}.wtbm-ops-card{padding:12px}strong{display:block} @media print{button{display:none}}</style></head><body>' . self::html( $data ) . '</body></html>';
		if ( 'pdf' === $format ) {
			if ( ! class_exists( '\Mpdf\Mpdf' ) ) {
				wp_die( esc_html__( 'Activate MagePeople PDF Support for PDF exports.', 'wptheaterly' ) );
			} $pdf = WTBM_Booking_Service::pdf( array( 'orientation' => 'L' ) );
			$pdf->WriteHTML( $html );
			$pdf->Output( 'theaterly-sales.pdf', 'D' );
			exit; }
		header( 'Content-Type: text/html; charset=UTF-8' );
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- html() escapes its fields; surrounding document is static.
		echo $html;
		echo '<script>window.print();</script>';
		exit;
	}
}
WTBM_Operations_Report::boot();
