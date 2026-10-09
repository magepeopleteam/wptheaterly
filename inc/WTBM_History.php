<?php
/**
 * Staff history retention and cleanup.
 *
 * @package Theaterly
 */

// phpcs:disable WordPress.Files.FileName -- Preserve the plugin class naming convention.
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
/** History maintenance for authorized staff and scheduled retention. */
class WTBM_History {
	const OPTION = 'wtbm_history_retention';
	const HOOK   = 'wtbm_cleanup_history';
	/**
	 * Register history cleanup hooks.
	 */
	public static function boot() {
		add_action( 'init', array( __CLASS__, 'schedule' ), 25 );
		add_action( self::HOOK, array( __CLASS__, 'automatic' ) );
		add_action( 'wp_ajax_wtbm_history', array( __CLASS__, 'ajax' ) );
	}
	/**
	 * Read retention preferences.
	 */
	public static function settings() {
		return wp_parse_args(
			get_option( self::OPTION, array() ),
			array(
				'enabled' => 0,
				'days'    => 90,
				'time'    => '02:00',
			)
		);
	}
	/**
	 * Calculate the next daily run in the site timezone.
	 *
	 * @param string   $clock Local hour and minute.
	 * @param int|null $now Reference timestamp.
	 */
	public static function next_run( $clock, $now = null ) {
		$now                   = null === $now ? time() : $now;
		$date                  = ( new DateTimeImmutable( '@' . $now ) )->setTimezone( wp_timezone() );
		list( $hour, $minute ) = array_map( 'intval', explode( ':', $clock ) );
		$next                  = $date->setTime( $hour, $minute );
		return ( $next->getTimestamp() <= $now ? $next->modify( '+1 day' ) : $next )->getTimestamp();
	}
	/**
	 * Schedule the next automatic cleanup when enabled.
	 */
	public static function schedule() {
		$settings = self::settings();
		if ( ! $settings['enabled'] ) {
			if ( wp_next_scheduled( self::HOOK ) ) {
				wp_clear_scheduled_hook( self::HOOK );
			} return; }
		if ( ! wp_next_scheduled( self::HOOK ) ) {
			wp_schedule_single_event( self::next_run( $settings['time'] ), self::HOOK ); }
	}
	/**
	 * Remove only records included in a cutoff snapshot.
	 *
	 * @param string $cutoff UTC cutoff or empty for all previewed records.
	 * @param int    $max_id Highest previewed record ID.
	 */
	public static function purge( $cutoff, $max_id ) {
		global $wpdb;
		if ( ! $max_id ) {
			return 0; }
		return $cutoff ? $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wtbm_events WHERE id<=%d AND created_at<%s", $max_id, $cutoff ) ) : $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wtbm_events WHERE id<=%d", $max_id ) );
	}
	/**
	 * Run scheduled retention and arrange the next run.
	 */
	public static function automatic() {
		$settings = self::settings();
		if ( $settings['enabled'] ) {
			global $wpdb;
			$max   = (int) $wpdb->get_var( "SELECT MAX(id) FROM {$wpdb->prefix}wtbm_events" );
			$count = self::purge( gmdate( 'Y-m-d H:i:s', time() - (int) $settings['days'] * DAY_IN_SECONDS ), $max );
			if ( false !== $count ) {
				update_option(
					'wtbm_history_last_cleanup',
					array(
						'at'    => time(),
						'count' => $count,
					),
					false
				); }
		}
		self::schedule();
	}
	/**
	 * Describe the next automatic cleanup.
	 */
	public static function status() {
		$next = wp_next_scheduled( self::HOOK );
		return $next ? sprintf( __( 'Next cleanup: %s', 'wptheaterly' ), wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $next ) ) : __( 'Automatic cleanup is disabled', 'wptheaterly' );
	}
	/**
	 * Handle authorized history previews, clearing and settings.
	 */
	public static function ajax() {
		check_ajax_referer( 'wtbm_operations', 'nonce' );
		if ( ! WTBM_Booking_Service::can( 'manage' ) ) {
			wp_send_json_error( __( 'Permission denied.', 'wptheaterly' ), 403 ); }
		$verb = sanitize_key( $_POST['verb'] ?? '' );
		if ( 'save' === $verb ) {
			$days  = absint( $_POST['days'] ?? 0 );
			$clock = sanitize_text_field( wp_unslash( $_POST['time'] ?? '' ) );
			if ( $days < 1 || $days > 3650 || ! preg_match( '/^(?:[01]\d|2[0-3]):[0-5]\d$/', $clock ) ) {
				wp_send_json_error( __( 'Enter 1–3650 retention days and a valid cleanup time.', 'wptheaterly' ), 400 ); }
			update_option(
				self::OPTION,
				array(
					'enabled' => empty( $_POST['enabled'] ) ? 0 : 1,
					'days'    => $days,
					'time'    => $clock,
				),
				false
			);
			wp_clear_scheduled_hook( self::HOOK );
			self::schedule();
			wp_send_json_success(
				array(
					'message' => __( 'Cleanup settings saved.', 'wptheaterly' ),
					'next'    => self::status(),
				)
			);
		}
		if ( 'preview' === $verb ) {
			$range = sanitize_key( $_POST['range'] ?? '' );
			if ( ! in_array( $range, array( '7', '30', '90', '365', 'all' ), true ) ) {
				wp_send_json_error( __( 'Choose a valid clearing range.', 'wptheaterly' ), 400 ); }
			global $wpdb;
			$cutoff = 'all' === $range ? '' : gmdate( 'Y-m-d H:i:s', time() - (int) $range * DAY_IN_SECONDS );
			$max    = (int) $wpdb->get_var( "SELECT MAX(id) FROM {$wpdb->prefix}wtbm_events" );
			$count  = $cutoff ? $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wtbm_events WHERE id<=%d AND created_at<%s", $max, $cutoff ) ) : $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wtbm_events WHERE id<=%d", $max ) );
			$token  = wp_generate_password( 32, false );
			set_transient(
				'wtbm_history_clear_' . $token,
				array(
					'user'   => get_current_user_id(),
					'cutoff' => $cutoff,
					'max'    => $max,
				),
				5 * MINUTE_IN_SECONDS
			);
			wp_send_json_success(
				array(
					'count' => (int) $count,
					'token' => $token,
				)
			);
		}
		if ( 'clear' === $verb ) {
			$key     = 'wtbm_history_clear_' . sanitize_text_field( wp_unslash( $_POST['token'] ?? '' ) );
			$preview = get_transient( $key );
			if ( ! is_array( $preview ) || get_current_user_id() !== (int) $preview['user'] ) {
				wp_send_json_error( __( 'Clearing preview expired. Please try again.', 'wptheaterly' ), 400 ); }
			delete_transient( $key );
			$count = self::purge( $preview['cutoff'], $preview['max'] );
			if ( false === $count ) {
				wp_send_json_error( __( 'History could not be cleared. Please try again.', 'wptheaterly' ), 500 ); }
			wp_send_json_success(
				array(
					'count'  => $count,
					'max'    => $preview['max'],
					'cutoff' => $preview['cutoff'],
				)
			);
		}
		wp_send_json_error( __( 'Invalid history action.', 'wptheaterly' ), 400 );
	}
}
WTBM_History::boot();
