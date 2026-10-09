<?php
/**
 * Idempotent payment-driven email delivery with a bounded retry queue.
 *
 * @package Theaterly
 */

// phpcs:disable WordPress.Files.FileName -- Preserve the plugin's established class/file naming convention.
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
/**
 * Theaterly operational service.
 */
class WTBM_Notifications {
	/**
	 * Boot.
	 */
	public static function boot() {
		add_action( 'woocommerce_payment_complete', array( __CLASS__, 'queue' ), 40 );
		add_action(
			'woocommerce_order_status_changed',
			function ( $id ) {
				WTBM_Notifications::queue( $id );
			},
			40
		);
		add_action(
			'wtbm_ticket_changed',
			function ( $id ) {
				WTBM_Notifications::queue( $id, true );
			}
		);
		add_action( 'wtbm_deliver_ticket', array( __CLASS__, 'deliver' ), 10, 3 );
	}
	/**
	 * Fingerprint.
	 *
	 * @param mixed $order Order.
	 */
	public static function fingerprint( $order ) {
		$versions = array();
		foreach ( WTBM_Booking_Service::bookings( $order->get_id() ) as $bid ) {
			$versions[ $bid ] = get_post_meta( $bid, 'wtbm_ticket_version', true ); }
		return hash( 'sha256', wp_json_encode( array( $order->get_status(), $order->get_billing_email(), $versions ) ) );
	}
	/**
	 * Queue.
	 *
	 * @param mixed $id Id.
	 * @param mixed $force Force.
	 */
	public static function queue( $id, $force = false ) {
		$order = wc_get_order( $id );
		if ( ! $order || ! $order->is_paid() || ! WTBM_Booking_Service::bookings( $id ) ) {
			return new WP_Error( 'unpaid', __( 'Paid theater booking required for ticket email.', 'wptheaterly' ) ); }
		$settings = get_option( 'wtbm_email_settings', array() );
		if ( 'no' === ( $settings['pdf_send_status'] ?? 'yes' ) ) {
			return new WP_Error( 'disabled', __( 'Ticket emails are disabled.', 'wptheaterly' ) ); }
		$statuses = (array) ( $settings['pdf_email_status'] ?? array( 'processing', 'completed' ) );
		if ( ! $force && ! in_array( $order->get_status(), $statuses, true ) ) {
			return true; }
		if ( ! is_email( $order->get_billing_email() ) ) {
			return new WP_Error( 'email', __( 'Set a valid billing email before sending tickets.', 'wptheaterly' ) ); }
		return WTBM_Booking_Service::lock(
			'email|' . $id,
			function () use ( $order, $id, $force ) {
				$key = self::fingerprint( $order );
				if ( ! $force && $order->get_meta( '_wtbm_mail_sent' ) === $key ) {
					return true; }
				if ( $force && (int) $order->get_meta( '_wtbm_mail_queued_at' ) > time() - 60 ) {
					return array( 'message' => __( 'A ticket email is already queued. Please wait one minute.', 'wptheaterly' ) ); }
				$order->delete_meta_data( '_wtbm_mail_sent' );
				$args = array( $id, 0, $key );
				if ( ! wp_next_scheduled( 'wtbm_deliver_ticket', $args ) ) {
					wp_schedule_single_event( time() + 5, 'wtbm_deliver_ticket', $args ); }
				$order->update_meta_data( '_wtbm_mail_queued_at', time() );
				$order->save();
				WTBM_Booking_Service::log( 'email_queued', 0, $id );
				return array( 'message' => __( 'Ticket email queued for delivery.', 'wptheaterly' ) );
			}
		);
	}
	/**
	 * Deliver.
	 *
	 * @param mixed $id Id.
	 * @param mixed $attempt Attempt.
	 * @param mixed $key Key.
	 */
	public static function deliver( $id, $attempt = 0, $key = '' ) {
		return WTBM_Booking_Service::lock(
			'email|' . $id,
			function () use ( $id, $attempt, $key ) {
				$order = wc_get_order( $id );
				if ( ! $order || ! $order->is_paid() || ! is_email( $order->get_billing_email() ) ) {
					return false; }
				if ( self::fingerprint( $order ) !== $key ) {
					self::queue( $id );
					return false; }
				if ( $order->get_meta( '_wtbm_mail_sent' ) === $key ) {
					return true; }
				$file    = '';
				$success = false;
				$error   = '';
				try {
					if ( ! class_exists( '\Mpdf\Mpdf' ) ) {
						throw new RuntimeException( 'PDF support is unavailable.' ); }
					$file = wp_tempnam( 'wtbm-ticket-' . $id . '.pdf' );
					( new WTBM_Pro_Pdf() )->generate_pdf( $id, $file, 'mail' );
					if ( ! $file || ! is_file( $file ) || ! filesize( $file ) ) {
						throw new RuntimeException( 'Ticket attachment could not be generated.' ); }
					$settings = get_option( 'wtbm_email_settings', array() );
					$subject  = sanitize_text_field( $settings['pdf_email_subject'] ?? __( 'Your theater tickets', 'wptheaterly' ) );
					$body     = wp_kses_post( $settings['pdf_email_content'] ?? __( 'Hello {customer_name}, your theater tickets are attached.', 'wptheaterly' ) );
					$movies   = array();
					$dates    = array();
					foreach ( WTBM_Booking_Service::bookings( $id ) as $bid ) {
								$movies[] = get_the_title( get_post_meta( $bid, 'wtbm_movie_id', true ) );
								$dates[]  = get_post_meta( $bid, 'wtbm_order_date', true ) . ' ' . get_post_meta( $bid, 'wtbm_order_time', true ); }
					$body    = strtr(
						$body,
						array(
							'{customer_name}' => esc_html( $order->get_formatted_billing_full_name() ),
							'{order_id}'      => esc_html( $order->get_order_number() ),
							'{service_name}'  => esc_html( implode( ', ', array_unique( $movies ) ) ),
							'{service_date}'  => esc_html( implode( ', ', $dates ) ),
						)
					);
					$body   .= '<p><a href="' . esc_url( WTBM_Tickets::pdf_url( $order ) ) . '">' . esc_html__( 'Download current ticket', 'wptheaterly' ) . '</a></p>';
					$success = wp_mail( $order->get_billing_email(), $subject, $body, array( 'Content-Type: text/html; charset=UTF-8' ), array( $file ) );
					$admin   = sanitize_email( $settings['pdf_admin_notification_email'] ?? '' );
					if ( $success && $admin ) {
						wp_mail( $admin, $subject, $body, array( 'Content-Type: text/html; charset=UTF-8' ), array( $file ) ); }
					if ( ! $success ) {
						$error = 'Mail transport did not accept the message.'; }
				} catch ( Throwable $exception ) {
					$error = $exception->getMessage(); } finally {
					if ( $file && is_file( $file ) ) {
						wp_delete_file( $file ); }
					}
					if ( $success ) {
						$order->update_meta_data( '_wtbm_mail_sent', $key );
						$order->update_meta_data( '_wtbm_mail_status', 'sent' );
						$order->save();
						WTBM_Booking_Service::log( 'email_sent', 0, $id, array( 'attempt' => $attempt + 1 ) ); } else {
								$order->update_meta_data( '_wtbm_mail_status', 'failed' );
								$order->save();
								WTBM_Booking_Service::log(
									'email_failed',
									0,
									$id,
									array(
										'attempt' => $attempt + 1,
										'error'   => sanitize_text_field( $error ),
									)
								);
						if ( $attempt < 2 ) {
							wp_schedule_single_event( time() + ( 5 * MINUTE_IN_SECONDS * ( $attempt + 1 ) ), 'wtbm_deliver_ticket', array( $id, $attempt + 1, $key ) ); }
						}
						return $success;
			}
		);
	}
}
WTBM_Notifications::boot();
