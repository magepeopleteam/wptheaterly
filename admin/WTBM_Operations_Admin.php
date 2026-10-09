<?php
/**
 * Staff operations and policy settings.
 *
 * @package Theaterly
 */

// phpcs:disable WordPress.Files.FileName -- Preserve the plugin's established class/file naming convention.
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
/**
 * Theaterly operational service.
 */
class WTBM_Operations_Admin {
	/**
	 * Boot.
	 */
	public static function boot() {
		add_action( 'init', array( __CLASS__, 'legacy_url' ), 1 );
		add_action( 'init', array( __CLASS__, 'redirect_operations' ), 2 );
		add_action( 'admin_init', array( __CLASS__, 'settings' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_post_wtbm_receipt', array( __CLASS__, 'receipt' ) );
		add_action( 'admin_post_wtbm_collect_payment', array( __CLASS__, 'collect' ) );
		add_action( 'init', array( __CLASS__, 'ticket_page' ), 20 );
	}
	/**
	 * Legacy url.
	 */
	public static function legacy_url() {
		$path = wp_parse_url( sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) ), PHP_URL_PATH );
		if ( wp_parse_url( admin_url( 'wtbm_operations' ), PHP_URL_PATH ) === $path || wp_parse_url( admin_url( 'wtbm_operations/' ), PHP_URL_PATH ) === $path ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mptrs_main_menu&tab_name=wtbm_admission' ) );
			exit; }
	}
	/**
	 * Ticket page.
	 */
	public static function ticket_page() {
		if ( get_option( 'wtbm_tickets_page' ) ) {
			return; }
		$page = get_page_by_path( 'my-theater-tickets' );
		$id   = $page ? $page->ID : wp_insert_post(
			array(
				'post_title'   => __( 'My theater tickets', 'wptheaterly' ),
				'post_name'    => 'my-theater-tickets',
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_content' => '[wtbm_my_tickets]',
			)
		);
		if ( $id ) {
			update_option( 'wtbm_tickets_page', $id, false );
			flush_rewrite_rules( false ); }
	}
	/**
	 * Menu.
	 */
	public static function redirect_operations() {
		if ( 'wtbm_operations' === sanitize_key( $_GET['page'] ?? '' ) ) {
			wp_safe_redirect( admin_url( 'admin.php?page=mptrs_main_menu&tab_name=wtbm_admission' ) );
			exit; }
	}
	/**
	 * Render the operations tabs.
	 *
	 * @param string $current Selected tab.
	 */
	public static function tabs( $current ) {
		foreach ( array( 'admission', 'policies', 'requests', 'history' ) as $section ) {
			?><div id="wtbm_<?php echo esc_attr( $section ); ?>_content" class="tab-content <?php echo 'wtbm_' . $section === $current ? 'active' : ''; ?>"><?php self::page( $section ); ?></div>
			<?php
		}
	}

	/**
	 * Settings.
	 */
	public static function settings() {
		register_setting( 'wtbm_operations', 'wtbm_operations_settings', array( 'sanitize_callback' => array( __CLASS__, 'sanitize' ) ) );
		add_filter(
			'option_page_capability_wtbm_operations',
			function () {
				return 'wtbm_manage';
			}
		);
	}
	/**
	 * Sanitize.
	 *
	 * @param mixed $values Values.
	 */
	public static function sanitize( $values ) {
		$out = array();
		foreach ( array(
			'advance_days'          => array( 1, 365 ),
			'cutoff_minutes'        => array( 0, 1440 ),
			'hold_minutes'          => array( 5, 60 ),
			'cleanup_minutes'       => array( 0, 180 ),
			'change_cutoff_minutes' => array( 0, 10080 ),
		) as $key => $range ) {
			$out[ $key ] = min( $range[1], max( $range[0], absint( $values[ $key ] ?? $range[0] ) ) ); }
		$out['customer_changes'] = empty( $values['customer_changes'] ) ? 0 : 1;
		return $out;
	}
	/**
	 * Assets.
	 *
	 * @param mixed $hook Hook.
	 */
	public static function assets( $hook = '' ) {
		$admin = is_admin();
		if ( $admin && false === strpos( $hook, 'mptrs_main_menu' ) && false === strpos( $hook, 'wtbm_operations' ) ) {
			return; }
		if ( ! $admin && ! ( function_exists( 'is_account_page' ) && is_account_page() ) && ! is_page( (int) get_option( 'wtbm_tickets_page' ) ) && ! is_singular( 'wtbm_movie' ) && ! has_shortcode( get_post_field( 'post_content', get_queried_object_id() ), 'wtbm_ticket_booking' ) && ! has_shortcode( get_post_field( 'post_content', get_queried_object_id() ), 'wtbm_single_movie_booking' ) ) {
			return; }
		if ( ! $admin ) {
			wp_enqueue_style( 'wtbm-tickets', WTBM_PLUGIN_URL . '/assets/frontend/wtbm_tickets.css', array( 'wtbm-operations' ), filemtime( WTBM_PLUGIN_DIR . '/assets/frontend/wtbm_tickets.css' ) ); }
		wp_enqueue_style( 'wtbm-operations', WTBM_PLUGIN_URL . '/assets/admin/wtbm_operations.css', array(), filemtime( WTBM_PLUGIN_DIR . '/assets/admin/wtbm_operations.css' ) );
		wp_enqueue_script( 'wtbm-operations', WTBM_PLUGIN_URL . '/assets/admin/wtbm_operations.js', array( 'jquery' ), filemtime( WTBM_PLUGIN_DIR . '/assets/admin/wtbm_operations.js' ), true );
		wp_localize_script(
			'wtbm-operations',
			'wtbmOperations',
			array(
				'ajax'         => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'wtbm_operations' ),
				'bookingNonce' => wp_create_nonce( 'wtbm_nonce' ),
				'exportUrl'    => wp_nonce_url( admin_url( 'admin-post.php?action=wtbm_report_export' ), 'wtbm_report_export' ),
				'scannerUrl'   => WTBM_PLUGIN_URL . '/assets/vendor/jsQR.js',
				'labels'       => array(
					'cash'      => __( 'Enter the cash received. Change is shown on the receipt.', 'wptheaterly' ),
					'unpaid'    => __( 'Seats are held temporarily. Payment is required before check-in.', 'wptheaterly' ),
					'reference' => __( 'Record the transaction reference for reconciliation.', 'wptheaterly' ),
					'loading'   => __( 'Loading…', 'wptheaterly' ),
					'error'     => __( 'Request failed. Please try again.', 'wptheaterly' ),
					'camera'    => __( 'Allow camera access, or paste the ticket code below.', 'wptheaterly' ),
					'basePrice' => __( 'Base seat total', 'wptheaterly' ),
					'discount' => __( 'Price decrease', 'wptheaterly' ),
					'increase' => __( 'Price increase', 'wptheaterly' ),
					'hold'      => __( 'Seats reserved until', 'wptheaterly' ),
				),
			)
		);
	}
	/**
	 * Report filters.
	 */
	public static function report_filters() {
		?>
		<div class="wtbm-report-filter-heading"><div><span class="wtbm-report-kicker"><?php esc_html_e( 'Explore your sales', 'wptheaterly' ); ?></span><h3><?php esc_html_e( 'Filter report', 'wptheaterly' ); ?></h3><p><?php esc_html_e( 'Choose a period and refine the results by movie, screen or payment status.', 'wptheaterly' ); ?></p></div></div><div class="wtbm-ops-filters"><label><?php esc_html_e( 'From', 'wptheaterly' ); ?><input type="date" id="wtbm-report-from" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>"></label><label><?php esc_html_e( 'To', 'wptheaterly' ); ?><input type="date" id="wtbm-report-to" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>"></label><label><?php esc_html_e( 'Date basis', 'wptheaterly' ); ?><select id="wtbm-report-basis"><option value="sale"><?php esc_html_e( 'Sale/payment date', 'wptheaterly' ); ?></option><option value="show"><?php esc_html_e( 'Show date', 'wptheaterly' ); ?></option></select></label><label><?php esc_html_e( 'Movie', 'wptheaterly' ); ?><select id="wtbm-report-movie"><option value="0"><?php esc_html_e( 'All movies', 'wptheaterly' ); ?></option>
		<?php
		foreach ( get_posts(
			array(
				'post_type'   => 'wtbm_movie',
				'numberposts' => -1,
			)
		) as $post ) {
			?>
	<option value="<?php echo esc_attr( $post->ID ); ?>"><?php echo esc_html( $post->post_title ); ?></option><?php } ?></select></label><label><?php esc_html_e( 'Screen', 'wptheaterly' ); ?><select id="wtbm-report-theater"><option value="0"><?php esc_html_e( 'All screens', 'wptheaterly' ); ?></option>
		<?php
		foreach ( get_posts(
			array(
				'post_type'   => 'wtbm_theater',
				'numberposts' => -1,
			)
		) as $post ) {
			?>
	<option value="<?php echo esc_attr( $post->ID ); ?>"><?php echo esc_html( $post->post_title ); ?></option><?php } ?></select></label><label><?php esc_html_e( 'Status', 'wptheaterly' ); ?><select id="wtbm-report-status"><option value=""><?php esc_html_e( 'All statuses', 'wptheaterly' ); ?></option>
		<?php
		foreach ( wc_get_order_statuses() as $key => $label ) {
			?>
	<option value="<?php echo esc_attr( str_replace( 'wc-', '', $key ) ); ?>"><?php echo esc_html( $label ); ?></option><?php } ?></select></label><button class="button button-primary" id="wtbm-generate-report"><?php esc_html_e( 'Generate report', 'wptheaterly' ); ?></button></div>
		<div class="wtbm-ops-actions">
		<?php
		foreach ( array(
			'pdf'   => __( 'Export PDF', 'wptheaterly' ),
			'excel' => __( 'Export Excel', 'wptheaterly' ),
			'print' => __( 'Print report', 'wptheaterly' ),
		) as $format => $label ) {
			?>
		<button class="button wtbm-report-export" data-format="<?php echo esc_attr( $format ); ?>"><?php echo esc_html( $label ); ?></button><?php } ?></div><div id="wtbm-period-report" aria-live="polite"></div>
		<?php
	}
	/**
	 * Render one operations section.
	 *
	 * @param string $section Section name.
	 */
	public static function page( $section = 'admission' ) {
		if ( ! WTBM_Booking_Service::can( 'manage' ) && ! WTBM_Booking_Service::can( 'sell' ) && ! WTBM_Booking_Service::can( 'admit' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'wptheaterly' ) ); }
		?>
		<?php
		if ( 'requests' === $section ) {
			WTBM_Change_Requests::admin_page();
			return; }
		?>
		<div class="wtbm-operations">
		<?php
		if ( 'admission' === $section && WTBM_Booking_Service::can( 'admit' ) ) {
			?>
			<section class="wtbm-ops-card wtbm-admission-card">
				<header class="wtbm-operation-heading"><div><span class="wtbm-report-kicker"><?php esc_html_e( 'Welcome your audience', 'wptheaterly' ); ?></span><h2><?php esc_html_e( 'Ticket check-in', 'wptheaterly' ); ?></h2><p><?php esc_html_e( 'Scan a ticket or enter its verification code to check guests in securely.', 'wptheaterly' ); ?></p></div><span class="wtbm-operation-badge"><?php esc_html_e( 'Duplicate entry protection', 'wptheaterly' ); ?></span></header>
				<div class="wtbm-admission-layout"><div class="wtbm-admission-camera"><span class="dashicons dashicons-camera" aria-hidden="true"></span><h3><?php esc_html_e( 'Scan entry QR', 'wptheaterly' ); ?></h3><p><?php esc_html_e( 'Use the QR on a PDF ticket or the customer ticket page.', 'wptheaterly' ); ?></p><video id="wtbm-scanner-video" playsinline muted hidden></video><div class="wtbm-camera-actions"><button type="button" class="button button-primary" id="wtbm-start-scanner"><?php esc_html_e( 'Scan with camera', 'wptheaterly' ); ?></button><button type="button" class="button" id="wtbm-stop-scanner" disabled><?php esc_html_e( 'Stop camera', 'wptheaterly' ); ?></button></div></div>
				<div class="wtbm-admission-manual"><h3><?php esc_html_e( 'Verify a ticket', 'wptheaterly' ); ?></h3><div class="wtbm-admission-filters"><label for="wtbm-admit-theater"><?php esc_html_e( 'Screen', 'wptheaterly' ); ?><select id="wtbm-admit-theater"><option value="0"><?php esc_html_e( 'All screens', 'wptheaterly' ); ?></option>
				<?php
				foreach ( get_posts(
					array(
						'post_type'   => 'wtbm_theater',
						'numberposts' => -1,
					)
				) as $screen ) {
					?>
																																									<option value="<?php echo esc_attr( $screen->ID ); ?>"><?php echo esc_html( $screen->post_title ); ?></option><?php } ?></select></label><label for="wtbm-admit-date"><?php esc_html_e( 'Show date', 'wptheaterly' ); ?><input id="wtbm-admit-date" type="date" value="<?php echo esc_attr( current_time( 'Y-m-d' ) ); ?>"></label></div><label for="wtbm-admit-token"><?php esc_html_e( 'QR code or booking number', 'wptheaterly' ); ?><input id="wtbm-admit-token" autocomplete="off" placeholder="<?php esc_attr_e( 'Scan QR or enter BK000067', 'wptheaterly' ); ?>"></label><p class="wtbm-field-help"><?php esc_html_e( 'Accepts the signed ticket QR or a booking number. Select the ticket’s show date and screen.', 'wptheaterly' ); ?></p><button type="button" class="button button-primary" id="wtbm-admit"><?php esc_html_e( 'Validate and check in', 'wptheaterly' ); ?></button></div></div><p id="wtbm-admit-result" role="status" aria-live="polite"></p>
			</section><?php } ?>
		<?php
		if ( 'history' === $section && WTBM_Booking_Service::can( 'manage' ) ) {
			$bid = 0;
			?>
			<section class="wtbm-ops-card wtbm-history-card"><header class="wtbm-operation-heading"><div><span class="wtbm-report-kicker"><?php esc_html_e( 'Activity log', 'wptheaterly' ); ?></span><h2><?php esc_html_e( 'Booking and delivery history', 'wptheaterly' ); ?></h2><p><?php esc_html_e( 'Review the latest 100 booking changes, check-ins and ticket email events.', 'wptheaterly' ); ?></p></div><label class="wtbm-history-search-label"><?php esc_html_e( 'Search activity', 'wptheaterly' ); ?><input type="search" id="wtbm-history-search" placeholder="<?php esc_attr_e( 'Order, event or staff', 'wptheaterly' ); ?>"></label></header>
			<?php
			global $wpdb;
			$events = $bid ? $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}wtbm_events WHERE booking_id=%d OR order_id=%d ORDER BY id DESC LIMIT 100", $bid, absint( get_post_meta( $bid, 'wtbm_order_id', true ) ) ) ) : $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}wtbm_events ORDER BY id DESC LIMIT 100" );
			?>
			<?php $retention = WTBM_History::settings(); ?>
            <div class="wtbm-history-controls">
                <form id="wtbm-history-retention"><h3><?php esc_html_e( 'Automatic cleanup', 'wptheaterly' ); ?></h3><p><?php esc_html_e( 'Keep recent activity and remove older entries once a day.', 'wptheaterly' ); ?></p>
                <label class="wtbm-history-enable"><input type="checkbox" name="enabled" value="1" <?php checked( $retention['enabled'], 1 ); ?>><?php esc_html_e( 'Enable automatic cleanup', 'wptheaterly' ); ?></label>
                <div class="wtbm-history-fields"><label><?php esc_html_e( 'Keep history for (days)', 'wptheaterly' ); ?><input type="number" name="days" min="1" max="3650" value="<?php echo esc_attr( $retention['days'] ); ?>" required></label><label><?php esc_html_e( 'Daily cleanup time', 'wptheaterly' ); ?><input type="time" name="time" value="<?php echo esc_attr( $retention['time'] ); ?>" required></label></div>
                <p class="wtbm-field-help"><?php echo esc_html( wp_timezone_string() ); ?> · <?php esc_html_e( 'Runs through WordPress scheduled tasks when the site receives traffic.', 'wptheaterly' ); ?></p><button class="button button-primary" type="submit"><?php esc_html_e( 'Save cleanup settings', 'wptheaterly' ); ?></button><p id="wtbm-history-next"><?php echo esc_html( WTBM_History::status() ); ?></p></form>
                <div class="wtbm-history-manual"><h3><?php esc_html_e( 'Clear history', 'wptheaterly' ); ?></h3><p><?php esc_html_e( 'Choose which activity to remove. Bookings, payments and check-in records remain intact.', 'wptheaterly' ); ?></p><label for="wtbm-history-range"><?php esc_html_e( 'Entries to clear', 'wptheaterly' ); ?></label><select id="wtbm-history-range"><option value="7"><?php esc_html_e( 'Older than 7 days', 'wptheaterly' ); ?></option><option value="30"><?php esc_html_e( 'Older than 30 days', 'wptheaterly' ); ?></option><option value="90" selected><?php esc_html_e( 'Older than 90 days', 'wptheaterly' ); ?></option><option value="365"><?php esc_html_e( 'Older than 1 year', 'wptheaterly' ); ?></option><option value="all"><?php esc_html_e( 'All history', 'wptheaterly' ); ?></option></select><p class="wtbm-field-help"><?php esc_html_e( 'Cleared history cannot be restored.', 'wptheaterly' ); ?></p><button type="button" class="button" id="wtbm-history-clear"><?php esc_html_e( 'Preview and clear', 'wptheaterly' ); ?></button></div>
            </div><p id="wtbm-history-feedback" role="status" aria-live="polite"></p>
            <div class="wtbm-ops-table"><table class="widefat"><thead><tr><th><?php esc_html_e( 'UTC time', 'wptheaterly' ); ?></th><th><?php esc_html_e( 'Event', 'wptheaterly' ); ?></th><th><?php esc_html_e( 'Staff', 'wptheaterly' ); ?></th><th><?php esc_html_e( 'Details', 'wptheaterly' ); ?></th></tr></thead><tbody>
			<?php
			foreach ( $events as $event ) {
						$user = get_userdata( $event->user_id );
				?>
	<tr data-event-id="<?php echo esc_attr( $event->id ); ?>" data-created="<?php echo esc_attr( $event->created_at ); ?>"><td><?php echo esc_html( $event->created_at ); ?></td><td><?php echo esc_html( ucwords( str_replace( '_', ' ', $event->event ) ) . ( $event->order_id ? ' · #' . $event->order_id : '' ) ); ?></td><td><?php echo esc_html( $user ? $user->display_name : __( 'System/customer', 'wptheaterly' ) ); ?></td><td><details class="wtbm-event-details"><summary><?php esc_html_e( 'View details', 'wptheaterly' ); ?></summary><pre><?php echo esc_html( wp_json_encode( json_decode( $event->details, true ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ); ?></pre></details></td></tr>
				<?php
			} if ( ! $events ) {
				?>
				<tr><td colspan="4" class="wtbm-history-empty"><?php esc_html_e( 'Activity will appear here when tickets are booked, changed or checked in.', 'wptheaterly' ); ?></td></tr><?php } ?></tbody></table></div><p class="wtbm-history-no-match" hidden><?php esc_html_e( 'No activity matches your search.', 'wptheaterly' ); ?></p></section>
			<?php } if ( 'policies' === $section && WTBM_Booking_Service::can( 'manage' ) ) { ?>
			<section class="wtbm-ops-card wtbm-policies-card"><header class="wtbm-operation-heading"><div><span class="wtbm-report-kicker"><?php esc_html_e( 'Booking preferences', 'wptheaterly' ); ?></span><h2><?php esc_html_e( 'Booking policies', 'wptheaterly' ); ?></h2><p class="wtbm-ops-description"><?php esc_html_e( 'Control booking availability, seat holds and customer changes from one place.', 'wptheaterly' ); ?></p></div><span class="wtbm-operation-badge"><?php esc_html_e( 'Applies to all screens', 'wptheaterly' ); ?></span></header><form class="wtbm-policy-form" action="options.php" method="post">
			<?php
			settings_fields( 'wtbm_operations' ); foreach ( array(
				'advance_days'          => array( __( 'Advance booking days', 'wptheaterly' ), 30 ),
				'cutoff_minutes'        => array( __( 'Close booking before show (minutes)', 'wptheaterly' ), 15 ),
				'hold_minutes'          => array( __( 'Seat hold duration (minutes)', 'wptheaterly' ), 15 ),
				'cleanup_minutes'       => array( __( 'Screen cleanup buffer (minutes)', 'wptheaterly' ), 15 ),
				'change_cutoff_minutes' => array( __( 'Customer change deadline before show (minutes)', 'wptheaterly' ), 120 ),
			) as $key => $field ) {
				?>
				<label><?php echo esc_html( $field[0] ); ?><input type="number" min="0" name="wtbm_operations_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( WTBM_Booking_Service::setting( $key, $field[1] ) ); ?>"></label><?php } ?><label><input type="checkbox" name="wtbm_operations_settings[customer_changes]" value="1" <?php checked( WTBM_Booking_Service::setting( 'customer_changes', 1 ), 1 ); ?>><?php esc_html_e( 'Allow customer cancellation and rescheduling requests', 'wptheaterly' ); ?></label><?php submit_button(); ?></form><p><?php esc_html_e( 'Staff roles: Theaterly Manager, Theaterly Cashier and Theaterly Check-in. Assign them from WordPress Users.', 'wptheaterly' ); ?></p><a href="<?php echo esc_url( get_permalink( get_option( 'wtbm_tickets_page' ) ) ); ?>"><?php esc_html_e( 'Open customer ticket page', 'wptheaterly' ); ?></a></section>
		<?php } ?></div>
		<?php
	}
	/**
	 * Payment fields.
	 *
	 * @param mixed $prefix Prefix.
	 */
	public static function payment_fields( $prefix = 'wtbm-pos' ) {
		?>
		<fieldset class="wtbm-payment-fields"><legend><?php esc_html_e( 'Payment details', 'wptheaterly' ); ?></legend><label for="<?php echo esc_attr( $prefix ); ?>-method"><?php esc_html_e( 'Payment method', 'wptheaterly' ); ?><select class="wtbm-payment-method" name="payment_method" id="<?php echo esc_attr( $prefix ); ?>-method">
		<?php
		foreach ( ( 'wtbm-collect' === $prefix ? array( 'wtbm_cash', 'wtbm_card', 'wtbm_manual' ) : array( 'wtbm_cash', 'wtbm_card', 'wtbm_manual', 'wtbm_unpaid' ) ) as $method ) {
			?>
			<option value="<?php echo esc_attr( $method ); ?>"><?php echo esc_html( WTBM_Operations::payment_label( $method ) ); ?></option><?php } ?></select></label><label class="wtbm-tender-field" for="<?php echo esc_attr( $prefix ); ?>-tender"><?php esc_html_e( 'Cash received', 'wptheaterly' ); ?><input type="number" min="0" step="0.01" name="tender" id="<?php echo esc_attr( $prefix ); ?>-tender" inputmode="decimal" placeholder="0.00"></label><label class="wtbm-reference-field" for="<?php echo esc_attr( $prefix ); ?>-reference" hidden><?php esc_html_e( 'Terminal / payment reference', 'wptheaterly' ); ?><input name="payment_reference" id="<?php echo esc_attr( $prefix ); ?>-reference" placeholder="<?php esc_attr_e( 'Transaction or receipt number', 'wptheaterly' ); ?>"></label><p class="wtbm-payment-help"><?php esc_html_e( 'Enter the cash received. Change is shown on the receipt.', 'wptheaterly' ); ?></p></fieldset>
		<?php
	}

	/**
	 * Collect form.
	 */
	public static function collect_form() {
		?>
		<details class="wtbm-ops-card wtbm-collect"><summary><?php esc_html_e( 'Collect payment for an existing unpaid reservation', 'wptheaterly' ); ?></summary><form class="wtbm-collect-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post"><?php wp_nonce_field( 'wtbm_collect_payment' ); ?><input type="hidden" name="action" value="wtbm_collect_payment"><label><?php esc_html_e( 'Order number', 'wptheaterly' ); ?><input type="number" name="order_id" min="1" required></label><?php self::payment_fields( 'wtbm-collect' ); ?><button class="button button-primary"><?php esc_html_e( 'Record payment', 'wptheaterly' ); ?></button></form></details>
		<?php
	}
	/**
	 * Collect.
	 */
	public static function collect() {
		check_admin_referer( 'wtbm_collect_payment' );
		if ( ! WTBM_Booking_Service::can( 'sell' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'wptheaterly' ) ); }
		$order_id  = absint( $_POST['order_id'] ?? 0 );
		$method    = sanitize_key( $_POST['payment_method'] ?? '' );
		$tender    = (float) wc_format_decimal( sanitize_text_field( wp_unslash( $_POST['tender'] ?? '0' ) ) );
		$reference = sanitize_text_field( wp_unslash( $_POST['payment_reference'] ?? '' ) );
		$result    = WTBM_Booking_Service::lock(
			'collect|' . $order_id,
			static function () use ( $order_id, $method, $tender, $reference ) {
				$order = wc_get_order( $order_id );
				if ( ! $order || ! WTBM_Booking_Service::bookings( $order->get_id() ) || $order->is_paid() ) {
					return new WP_Error( 'order', __( 'An unpaid theater order is required.', 'wptheaterly' ) ); }
				if ( ! in_array( $method, array( 'wtbm_cash', 'wtbm_card', 'wtbm_manual' ), true ) || ( 'wtbm_cash' === $method && $tender < $order->get_total() ) || ( 'wtbm_cash' !== $method && ! $reference ) ) {
					return new WP_Error( 'payment', __( 'Payment method, sufficient cash tender or terminal reference is required.', 'wptheaterly' ) ); }
				foreach ( WTBM_Booking_Service::bookings( $order->get_id() ) as $bid ) {
					$quote = WTBM_Booking_Service::quote( get_post_meta( $bid, 'wtbm_movie_id', true ), get_post_meta( $bid, 'wtbm_theater_id', true ), get_post_meta( $bid, 'wtbm_order_date', true ), get_post_meta( $bid, 'wtbm_order_time', true ), (array) get_post_meta( $bid, 'wtbm_seat_ids', true ) );
					if ( is_wp_error( $quote ) ) {
						return $quote;
					} $claim = WTBM_Booking_Service::claim( $quote, $order->get_meta( '_wtbm_claim_owner' ) ? $order->get_meta( '_wtbm_claim_owner' ) : hash( 'sha256', 'order|' . $order->get_id() ), $order->get_id(), $bid, true );
					if ( is_wp_error( $claim ) ) {
						return $claim; }
				}
				$order->set_payment_method( $method );
				$order->set_payment_method_title( WTBM_Operations::payment_label( $method ) );
				$order->update_meta_data( '_wtbm_tender', $tender );
				$order->update_meta_data( '_wtbm_cashier_id', get_current_user_id() );
				$order->update_meta_data( '_wtbm_payment_reference', $reference );
				$order->update_meta_data( '_wtbm_initial_paid_total', $order->get_total() );
				$order->save();
				$order->payment_complete( $reference );
				WTBM_Booking_Service::sync_order( $order );
				WTBM_Booking_Service::log(
					'payment_collected',
					0,
					$order->get_id(),
					array(
						'method' => $method,
						'tender' => $tender,
					)
				);

				return $order->get_id();
			}
		);
		if ( is_wp_error( $result ) ) {
			wp_die( esc_html( $result->get_error_message() ) ); }
		wp_safe_redirect( WTBM_Operations::receipt_url( $result ) );
		exit;
	}

	/**
	 * Receipt.
	 */
	public static function receipt() {
		// Read-only receipts use staff authentication and capability rather than expiring navigation nonces.
		if ( ! WTBM_Booking_Service::can( 'sell' ) ) {
			wp_die( esc_html__( 'Permission denied.', 'wptheaterly' ) ); }
		$order = wc_get_order( absint( $_GET['order_id'] ?? $_GET['amp;order_id'] ?? 0 ) );
		if ( ! $order || ! WTBM_Booking_Service::bookings( $order->get_id() ) ) {
			wp_die( esc_html__( 'Receipt not found.', 'wptheaterly' ) ); }
		nocache_headers();
		echo self::receipt_html( $order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The receipt template escapes dynamic fields.

		exit;
	}
    /** Render the printable receipt without changing the order. */
    public static function receipt_html( $order ) {
        ob_start();
        include WTBM_PLUGIN_DIR . '/templates/box-office-receipt.php';
        return ob_get_clean();
    }

}
WTBM_Operations_Admin::boot();
