<?php
/** Run with: wp eval-file wp-content/plugins/wptheaterly/tests/operations-integration.php */
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) { exit; }
global $fixture_posts, $fixture_orders, $fixture_users, $checks;
$fixture_posts = array(); $fixture_orders = array(); $fixture_users = array(); $checks = 0;
function wtbm_test_assert( $condition, $message ) { global $checks; if ( ! $condition ) { throw new RuntimeException( $message ); } $checks++; WP_CLI::log( 'PASS ' . $message ); }
function wtbm_test_post( $type, $title, $meta = array() ) { global $fixture_posts; $id = wp_insert_post( array( 'post_type' => $type, 'post_title' => $title, 'post_status' => 'publish' ) ); $fixture_posts[] = $id; foreach ( $meta as $key => $value ) { update_post_meta( $id, $key, $value ); } return $id; }
$admin = get_users( array( 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ) ); wp_set_current_user( $admin[0] );
// All test email goes through an intercepted transport and an invalid recipient domain.
$mail_calls = 0; $mail_filter = function () use ( &$mail_calls ) { $mail_calls++; return true; }; add_filter( 'pre_wp_mail', $mail_filter );
try {
    $date = wp_date( 'Y-m-d', time() + 3 * DAY_IN_SECONDS, wp_timezone() );
    $theater = wtbm_test_post( 'wtbm_theater', 'Theaterly integration fixture screen' );
    $seats = array(); for ( $i = 1; $i <= 5; $i++ ) { $seats[] = array( 'id' => (string) $i, 'seat_number' => 'T' . $i, 'price' => 50, 'row' => 0, 'col' => $i, 'left' => $i * 40, 'top' => 40, 'width' => 25, 'height' => 30, 'color' => '#44aa44' ); }
    update_post_meta( $theater, 'wtbp_theater_seat_map', array( 'seat_data' => $seats, 'seat_text_data' => array(), 'dynamic_shapes' => array() ) );
    $movie = wtbm_test_post( 'wtbm_movie', 'Theaterly integration fixture movie', array( 'wtbp_movie_duration' => '1h 0m' ) );
    $product = absint( get_post_meta( $movie, 'link_wc_product', true ) ); $fixture_posts[] = $product;
    $show = wtbm_test_post( 'wtbm_show_time', 'Theaterly fixture show', array( 'wtbp_show_time_movieId' => $movie, 'wtbp_show_time_theaterId' => $theater, 'wtbp_showtime_start_date' => $date, 'wtbp_showtime_end_date' => $date, 'wtbp_show_starting_time' => '11:00 AM', 'wtbp_show_ending_time' => '12:00 PM', 'wtbp_showtime_off_days' => array() ) );
    $quote = WTBM_Booking_Service::quote( $movie, $theater, $date, '11:00 AM', array( 'seat_1', 'seat_2' ) );
    wtbm_test_assert( ! is_wp_error( $quote ) && 2 === $quote['count'] && 100.0 === $quote['base'], 'Authoritative seat IDs, quantities and base price' );
    wtbm_test_assert( is_wp_error( WTBM_Booking_Service::quote( $movie, $theater, $date, '11:00 AM', array( 'seat_99' ) ) ), 'Unknown seats rejected' );
    wtbm_test_assert( is_wp_error( WTBM_Booking_Service::quote( $movie, $theater, $date, '11:00 AM', array( 'seat_1', 'seat_1' ) ) ), 'Duplicate seat IDs rejected' );
    wtbm_test_assert( WTBM_Booking_Service::show_key( $theater, $date, '11:00 AM' ) === WTBM_Booking_Service::show_key( $theater, $date, '11:00' ), 'Legacy and current time formats share a seat allocation key' );
    wtbm_test_assert( is_wp_error( WTBM_Booking_Service::quote( $movie, $theater, $date, '09:00 AM', array( 'seat_1' ) ) ), 'Unscheduled show rejected' );
    wtbm_test_assert( ! is_wp_error( WTBM_Booking_Service::claim( $quote, 'fixture-owner-a' ) ), 'Seat hold created' );
    wtbm_test_assert( is_wp_error( WTBM_Booking_Service::claim( $quote, 'fixture-owner-b' ) ), 'Competing owner cannot take held seats' );
    global $wpdb; $wpdb->update( $wpdb->prefix . 'wtbm_claims', array( 'expires_at' => time() - 1 ), array( 'owner' => 'fixture-owner-a' ) );
    wtbm_test_assert( ! is_wp_error( WTBM_Booking_Service::claim( $quote, 'fixture-owner-b' ) ), 'Expired holds become available' );
    $wpdb->delete( $wpdb->prefix . 'wtbm_claims', array( 'owner' => 'fixture-owner-b' ) );
    $request = array( 'movie_id' => $movie, 'theater_id' => $theater, 'booking_date' => $date, 'booking_time' => '11:00 AM', 'booked_seat_ids' => wp_json_encode( $quote['ids'] ), 'total_amount' => '0.01', 'seat_count' => 999, 'userName' => 'Fixture customer', 'userPhoneNum' => '0000000000', 'request_id' => wp_generate_uuid4(), 'payment_method' => 'wtbm_cash', 'tender' => 1000 );
    $sale = WTBM_Operations::purchase( $request, true );
    if ( is_wp_error( $sale ) ) { throw new RuntimeException( $sale->get_error_message() ); }
    $fixture_orders[] = $sale['order_id']; $order = wc_get_order( $sale['order_id'] );
    parse_str( (string) wp_parse_url( $sale['receipt_url'], PHP_URL_QUERY ), $receipt_query );
    wtbm_test_assert( false === strpos( $sale['receipt_url'], '&amp;' ) && false === strpos( $sale['receipt_url'], '&#' ) && (int) $receipt_query['order_id'] === $sale['order_id'] && ! empty( $receipt_query['_wpnonce'] ), 'Receipt AJAX URL has raw query separators and usable order/nonce parameters' );
    wtbm_test_assert( abs( (float) $order->get_total() - $quote['price'] ) < 0.01, 'Forged client total and quantity cannot reduce charge' );
    $pricing_item = array_values( $order->get_items() )[0];
    wtbm_test_assert( $pricing_item->get_meta( '_wtbm_pricing_details' ) === $quote['pricing'], 'Counter orders preserve the authoritative pricing snapshot independently of later rule edits' );
    $receipt = WTBM_Operations_Admin::receipt_html( $order );
    wtbm_test_assert( false !== strpos( $receipt, 'Fixture customer' ) && false !== strpos( $receipt, '0000000000' ) && false !== strpos( $receipt, 'T1, T2' ) && false !== strpos( $receipt, 'Cash received' ) && false !== strpos( $receipt, 'Change returned' ), 'Professional receipt includes customer contacts, named seats and cash reconciliation' );
    wtbm_test_assert( false !== strpos( $receipt, 'data-print="a4"' ) && false !== strpos( $receipt, 'data-print="thermal"' ) && false !== strpos( $receipt, 'size:A4' ), 'Receipt has dedicated A4 and 80 mm printing controls' );
    global $wpdb;
    $history_prefix = $wpdb->prefix; $history_table = $history_prefix . 'wtbm_fixture_history_wtbm_events';
    $retention_before = get_option( WTBM_History::OPTION, false );
    $history_next_before = wp_next_scheduled( WTBM_History::HOOK );
    $last_cleanup_before = get_option( 'wtbm_history_last_cleanup', false );
    $wpdb->query( "CREATE TEMPORARY TABLE $history_table LIKE {$history_prefix}wtbm_events" );
    try {
        $wpdb->prefix = $history_prefix . 'wtbm_fixture_history_';
        foreach ( array( 1 => '2000-01-01 00:00:00', 2 => '2026-10-02 00:00:00', 3 => '2000-01-01 00:00:00' ) as $event_id => $created ) { $wpdb->insert( $history_table, array( 'id' => $event_id, 'event' => 'fixture', 'booking_id' => 0, 'order_id' => 0, 'user_id' => 0, 'created_at' => $created, 'details' => '{}' ) ); }
        wtbm_test_assert( 1 === WTBM_History::purge( '2020-01-01 00:00:00', 2 ) && 2 === (int) $wpdb->get_var( "SELECT COUNT(*) FROM $history_table" ), 'History cleanup removes only old previewed records and preserves newer or subsequently added records' );
        wtbm_test_assert( 0 === WTBM_History::purge( '', 0 ), 'An empty clearing preview cannot delete newly arriving history' );
        update_option( WTBM_History::OPTION, array( 'enabled' => 0, 'days' => 90, 'time' => '02:00' ) );
        WTBM_History::automatic();
        wtbm_test_assert( 2 === (int) $wpdb->get_var( "SELECT COUNT(*) FROM $history_table" ) && ! wp_next_scheduled( WTBM_History::HOOK ), 'Disabled automatic history cleanup preserves entries and clears its schedule' );
        update_option( WTBM_History::OPTION, array( 'enabled' => 1, 'days' => 90, 'time' => '02:00' ) );
        WTBM_History::schedule();
        wtbm_test_assert( wp_next_scheduled( WTBM_History::HOOK ) > time() && '02:00' === wp_date( 'H:i', wp_next_scheduled( WTBM_History::HOOK ) ), 'Automatic cleanup schedules the selected time in the site timezone' );
        WTBM_History::automatic();
        wtbm_test_assert( 1 === (int) $wpdb->get_var( "SELECT COUNT(*) FROM $history_table" ), 'Scheduled retention removes expired history while preserving recent events' );
    } finally {
        $wpdb->prefix = $history_prefix;
        $wpdb->query( "DROP TEMPORARY TABLE $history_table" );
        wp_clear_scheduled_hook( WTBM_History::HOOK );
        if ( false === $retention_before ) { delete_option( WTBM_History::OPTION ); } else { update_option( WTBM_History::OPTION, $retention_before ); }
        if ( $history_next_before ) { wp_schedule_single_event( $history_next_before, WTBM_History::HOOK ); }
        if ( false === $last_cleanup_before ) { delete_option( 'wtbm_history_last_cleanup' ); } else { update_option( 'wtbm_history_last_cleanup', $last_cleanup_before ); }
    }

    $retry = WTBM_Operations::purchase( $request, true );
    wtbm_test_assert( ! is_wp_error( $retry ) && $retry['order_id'] === $sale['order_id'], 'Purchase retry returns the same order' );
    $ids = WTBM_Booking_Service::bookings( $sale['order_id'] ); $bid = $ids[0];
    $filters = WTBM_Booking_Content::booking_filters( array( 'movie_id' => $movie, 'theater_id' => $theater, 'show_date' => $date, 'show_time' => '11:00 AM', 'booking_status' => $order->get_status() ) );
    $filtered = WTBM_Layout_Functions::wtbm_get_filtered_booking_data( $filters, array(), 10 );
    wtbm_test_assert( '11:00' === $filters['wtbm_order_time'] && isset( $filtered['booking_data'][$bid] ), 'Combined booking filters normalize show time and match real WooCommerce status' );
    wtbm_test_assert( is_wp_error( WTBM_Booking_Content::booking_filters( array( 'show_date' => '2026-02-30' ) ) ) && is_wp_error( WTBM_Booking_Content::booking_filters( array( 'booking_status' => 'complete' ) ) ), 'Invalid dates and noncanonical status filters are rejected' );
    $export_ids = WTBM_Pro_Pdf::export_ids( array( 'filtered' => 1, 'movie_id' => $movie ) );
    wtbm_test_assert( in_array( $bid, $export_ids, true ), 'Filtered exports use the shared booking query' );
    $page_fixtures = array();
    for ( $page = 0; $page < 11; $page++ ) { $page_fixtures[] = wtbm_test_post( 'wtbm_booking', 'Filter pagination fixture', array( 'wtbm_movie_id' => $movie, 'wtbm_order_status' => 'pending' ) ); }
    $first_page = WTBM_Layout_Functions::wtbm_get_filtered_booking_data( array( 'wtbm_movie_id' => $movie ), array(), 10 );
    $second_page = WTBM_Layout_Functions::wtbm_get_filtered_booking_data( array( 'wtbm_movie_id' => $movie ), array_keys( $first_page['booking_data'] ), 10 );
    $all_exports = WTBM_Pro_Pdf::export_ids( array( 'filtered' => 1, 'movie_id' => $movie ) );
    wtbm_test_assert( 10 === count( $first_page['booking_data'] ) && ! array_intersect( array_keys( $first_page['booking_data'] ), array_keys( $second_page['booking_data'] ) ) && count( $all_exports ) === $first_page['total_booking'] && count( $all_exports ) > 10, 'Pagination has no duplicate rows and exports include matches beyond the loaded page' );
    wp_update_post( array( 'ID' => $page_fixtures[0], 'post_status' => 'draft' ) );
    $draft_export = WTBM_Layout_Pro::get_booking_data_by_ids( array( $page_fixtures[0] ) );
    wtbm_test_assert( 1 === count( $draft_export ) && $draft_export[0]['id'] === $page_fixtures[0], 'Exports retain draft and cancelled bookings present in filtered results' );
    foreach ( $page_fixtures as $fixture ) { wp_delete_post( $fixture, true ); }
    $contact_item = $order->get_item( get_post_meta( $bid, 'wtbm_item_id', true ) );
    $contact_item->update_meta_data( '_wtbm_user_name', 'Legacy attendee' );
    $contact_item->update_meta_data( '_wtbm_user_phone', '01700123456' ); $contact_item->save();
    $saved_name = get_post_meta( $bid, 'wtbm_billing_name', true ); $saved_phone = get_post_meta( $bid, 'wtbm_billing_phone', true );
    update_post_meta( $bid, 'wtbm_billing_name', 'legacy@example.invalid' ); update_post_meta( $bid, 'wtbm_billing_phone', '' );
    $contact_export = WTBM_Layout_Pro::get_booking_data_by_ids( array( $bid ) );
    wtbm_test_assert( 'Legacy attendee' === $contact_export[0]['name'] && '01700123456' === $contact_export[0]['phone'], 'Exports recover legacy attendee name and phone from the order item' );
    wtbm_test_assert( false !== strpos( WTBM_Pro_Pdf::booking_csv( array( $bid ) ), '01700123456' ) && false !== strpos( WTBM_Layout_Pro::generate_booking_data_pdf( array( $bid ) ), 'Legacy attendee' ), 'CSV and PDF both include recovered customer contacts' );
    update_post_meta( $bid, 'wtbm_billing_name', $saved_name ); update_post_meta( $bid, 'wtbm_billing_phone', $saved_phone );
    $preferred_contact = WTBM_Layout_Pro::get_booking_data_by_ids( array( $bid ) );
    wtbm_test_assert( $preferred_contact[0]['name'] === $saved_name && $preferred_contact[0]['phone'] === $saved_phone, 'Current booking contacts take precedence over legacy item contacts' );
    $csv = WTBM_Pro_Pdf::booking_csv( array( $bid ) );
    wtbm_test_assert( str_starts_with( $csv, "\xEF\xBB\xBF" ) && false !== strpos( $csv, 'BK' . str_pad( (string) $order->get_id(), 6, '0', STR_PAD_LEFT ) ) && false !== strpos( $csv, 'Check-in' ), 'CSV includes booking number, Unicode BOM and check-in fields' );
    wtbm_test_assert( "'=SUM(1,2)" === WTBM_Pro_Pdf::csv_cell( '=SUM(1,2)' ) && "'+123" === WTBM_Pro_Pdf::csv_cell( '+123' ), 'CSV prevents customer formula injection and preserves telephone values as text' );
    $booking_pdf_html = WTBM_Layout_Pro::generate_booking_data_pdf( array( $bid ) );
    $booking_pdf = WTBM_Booking_Service::pdf( array( 'format' => 'A4-L' ) ); $booking_pdf->WriteHTML( $booking_pdf_html );
    wtbm_test_assert( str_starts_with( $booking_pdf->Output( '', 'S' ), '%PDF-' ) && false !== strpos( $booking_pdf_html, 'Booking report' ), 'Filtered booking PDF renders successfully' );
    do_action( 'woocommerce_checkout_order_processed', $order->get_id(), array(), $order );
    do_action( 'woocommerce_store_api_checkout_order_processed', $order );
    wtbm_test_assert( 1 === count( WTBM_Booking_Service::bookings( $order->get_id() ) ), 'Classic/block callbacks are compatible and idempotent' );
    $plan = get_post_meta( $theater, 'wtbp_theater_seat_map', true ); $bad_plan = $plan; array_shift( $bad_plan['seat_data'] );
    wtbm_test_assert( is_wp_error( WTBM_Booking_Service::protect_plan( $theater, $bad_plan ) ), 'Future sold seats cannot be removed from theater plan' );
    $overlap = WTBM_Operations::validate_schedule( array( 'movieId' => $movie, 'theaterId' => $theater, 'start_date' => $date, 'end_date' => $date, 'startTime' => '12:05 PM', 'endTime' => '01:05 PM', 'showtime_off_days' => '' ) );
    wtbm_test_assert( is_wp_error( $overlap ), 'Cleanup buffer prevents adjacent overlap' );
    $clear = WTBM_Operations::validate_schedule( array( 'movieId' => $movie, 'theaterId' => $theater, 'start_date' => $date, 'end_date' => $date, 'startTime' => '01:00 PM', 'endTime' => '02:00 PM', 'showtime_off_days' => '' ) );
    wtbm_test_assert( true === $clear, 'Non-overlapping schedule accepted' );
    $not_checked = WTBM_Tickets::checkin_data( array( $bid ) );
    wtbm_test_assert( 0 === $not_checked[ $bid ]['count'] && 2 === $not_checked[ $bid ]['total'], 'Booking check-in summary reports unchecked seats' );
    $wpdb->update( $wpdb->prefix . 'wtbm_claims', array( 'admitted_at' => current_time( 'mysql', true ), 'admitted_by' => $admin[0] ), array( 'booking_id' => $bid, 'seat_id' => 'seat_1' ) );
    $partial_checkin = WTBM_Tickets::checkin_data( array( $bid ) ); $partial_group = array_values( $partial_checkin[ $bid ]['groups'] )[0];
    wtbm_test_assert( 1 === $partial_checkin[ $bid ]['count'] && 2 === $partial_checkin[ $bid ]['total'] && array( 'T1' ) === $partial_group['seats'] && ! empty( $partial_group['time'] ) && ! empty( $partial_group['staff'] ), 'Partial check-in includes saved seat name, local time and staff' );
    $wpdb->update( $wpdb->prefix . 'wtbm_claims', array( 'admitted_at' => null, 'admitted_by' => 0 ), array( 'booking_id' => $bid, 'seat_id' => 'seat_1' ) );
    $edit = WTBM_Operations::edit( $bid, array( 'ids' => array( 'seat_2', 'seat_3' ), 'name' => 'Updated fixture', 'email' => 'test@theaterly.invalid' ) );
    wtbm_test_assert( ! is_wp_error( $edit ) && 2 === (int) get_post_meta( $bid, 'wtbm_number_of_seats', true ), 'Booking edit synchronizes count and order' );
    $order = wc_get_order( $order->get_id() ); $item = current( $order->get_items() );
    wtbm_test_assert( array( 'seat_2', 'seat_3' ) === $item->get_meta( '_wtbm_selected_seat_ids' ), 'Edited seat IDs persist in WooCommerce item' );
    $before_change = WTBM_Tickets::token( $bid );
    $topup = WTBM_Operations::edit( $bid, array( 'ids' => array( 'seat_2', 'seat_3', 'seat_4' ) ), false );
    $topup_id = absint( get_post_meta( $bid, 'wtbm_change_payment', true ) ); $fixture_orders[] = $topup_id;
    wtbm_test_assert( ! is_wp_error( $topup ) && ! empty( $topup['payment_url'] ) && 2 === (int) get_post_meta( $bid, 'wtbm_number_of_seats', true ), 'Price increase waits for payment before changing seats' );
    $topup_order = wc_get_order( $topup_id ); $topup_order->payment_complete();
    wtbm_test_assert( 3 === (int) get_post_meta( $bid, 'wtbm_number_of_seats', true ) && is_wp_error( WTBM_Tickets::parse( $before_change ) ), 'Paid price difference applies change and revokes old ticket' );
    $paid_version = get_post_meta( $bid, 'wtbm_ticket_version', true ); WTBM_Operations::complete_change_payment( $topup_id );
    wtbm_test_assert( $paid_version === get_post_meta( $bid, 'wtbm_ticket_version', true ), 'Price-difference callback retry is idempotent' );
    $failure = function () { throw new Exception( 'Fixture refund transport failure' ); }; add_action( 'woocommerce_create_refund', $failure );
    $failed_edit = WTBM_Operations::edit( $bid, array( 'ids' => array( 'seat_2', 'seat_3' ) ) ); remove_action( 'woocommerce_create_refund', $failure );
    $still_reserved = $wpdb->get_col( $wpdb->prepare( "SELECT seat_id FROM {$wpdb->prefix}wtbm_claims WHERE booking_id=%d", $bid ) );
    wtbm_test_assert( is_wp_error( $failed_edit ) && in_array( 'seat_4', $still_reserved, true ) && 3 === (int) get_post_meta( $bid, 'wtbm_number_of_seats', true ), 'Failed refund preserves original seats and reservation' );
    $cheaper_quote = WTBM_Booking_Service::quote( $movie, $theater, $date, '11:00 AM', array( 'seat_2', 'seat_3' ) );
    $cheaper = WTBM_Operations::edit( $bid, array( 'ids' => array( 'seat_2', 'seat_3' ) ) );
    wtbm_test_assert( ! is_wp_error( $cheaper ) && 2 === (int) get_post_meta( $bid, 'wtbm_number_of_seats', true ) && ! is_wp_error( $cheaper_quote ) && abs( $cheaper_quote['price'] - (float) get_post_meta( $bid, 'wtbm_tp', true ) ) < 0.01, 'Cheaper change refunds difference and stores net ticket price' );
    wtbm_test_assert( is_wp_error( WTBM_Operations_Report::data( array( 'from' => '2026-02-30', 'to' => '2026-03-05' ) ) ), 'Impossible report dates rejected' );
    $before_request = get_post_meta( $bid, 'wtbm_ticket_version', true );
    $pending = WTBM_Change_Requests::submit( $bid, 'cancel', array( 'ids' => array( 'seat_2' ), 'reason' => 'Fixture request' ) );
    wtbm_test_assert( ! is_wp_error( $pending ) && 2 === (int) get_post_meta( $bid, 'wtbm_number_of_seats', true ) && $before_request === get_post_meta( $bid, 'wtbm_ticket_version', true ), 'Cancellation request leaves seats, order and ticket unchanged until approval' );
    ob_start(); WTBM_Change_Requests::customer_status( $bid ); $request_status_html = ob_get_clean();
    wtbm_test_assert( false !== strpos( $request_status_html, 'Awaiting admin approval' ), 'Customer ticket displays its pending approval status' );
    $duplicate_request = WTBM_Change_Requests::submit( $bid, 'cancel', array( 'ids' => array( 'seat_2' ) ) );
    wtbm_test_assert( $pending['request_id'] === $duplicate_request['request_id'], 'Repeated customer requests do not duplicate the approval queue' );
    $rejection = WTBM_Change_Requests::review( $pending['request_id'], 'reject', 'Fixture rejected' );
    wtbm_test_assert( ! is_wp_error( $rejection ) && 2 === (int) get_post_meta( $bid, 'wtbm_number_of_seats', true ) && 'rejected' === get_post_meta( $pending['request_id'], '_wtbm_status', true ), 'Admin rejection leaves the original booking valid' );
    $move_request = WTBM_Change_Requests::submit( $bid, 'reschedule', array( 'movie' => $movie, 'theater' => $theater, 'date' => $date, 'time' => '11:00 AM', 'ids' => array( 'seat_2', 'seat_4' ) ) );
    wtbm_test_assert( ! is_wp_error( $move_request ) && array( 'seat_2', 'seat_3' ) === get_post_meta( $bid, 'wtbm_seat_ids', true ), 'Rescheduling request does not change the live booking' );
    $move_approval = WTBM_Change_Requests::review( $move_request['request_id'], 'approve' );
    wtbm_test_assert( ! is_wp_error( $move_approval ) && array( 'seat_2', 'seat_4' ) === get_post_meta( $bid, 'wtbm_seat_ids', true ), 'Admin approval applies the requested reschedule' );
    wtbm_test_assert( is_wp_error( WTBM_Change_Requests::review( $move_request['request_id'], 'approve' ) ), 'Repeated admin approval does not reapply a change' );
    $restored = WTBM_Operations::edit( $bid, array( 'ids' => array( 'seat_2', 'seat_3' ) ) );
    $stale = WTBM_Change_Requests::submit( $bid, 'cancel', array( 'ids' => array( 'seat_2' ) ) );
    WTBM_Booking_Service::revoke( $bid );
    wtbm_test_assert( is_wp_error( WTBM_Change_Requests::review( $stale['request_id'], 'approve' ) ), 'Approval refuses a request for a changed ticket version' );
    WTBM_Change_Requests::review( $stale['request_id'], 'reject' );
    $price_request = WTBM_Change_Requests::submit( $bid, 'reschedule', array( 'movie' => $movie, 'theater' => $theater, 'date' => $date, 'time' => '11:00 AM', 'ids' => array( 'seat_2', 'seat_3', 'seat_4' ) ) );
    $price_approval = WTBM_Change_Requests::review( $price_request['request_id'], 'approve' );
    $approved_payment = (int) get_post_meta( $price_request['request_id'], '_wtbm_payment_order', true ); $fixture_orders[] = $approved_payment;
    wtbm_test_assert( ! is_wp_error( $price_approval ) && ! empty( $price_approval['payment_url'] ) && $approved_payment > 0 && 2 === (int) get_post_meta( $bid, 'wtbm_number_of_seats', true ), 'Higher-priced approval still waits for customer payment before changing seats' );
    wc_get_order( $approved_payment )->update_status( 'cancelled' );
    $unpaid_request = $request; $unpaid_request['request_id'] = wp_generate_uuid4(); $unpaid_request['booked_seat_ids'] = '["seat_5"]'; $unpaid_request['payment_method'] = 'wtbm_unpaid';
    $unpaid = WTBM_Operations::purchase( $unpaid_request, true ); $fixture_orders[] = $unpaid['order_id']; $unpaid_bid = WTBM_Booking_Service::bookings( $unpaid['order_id'] )[0];
    wtbm_test_assert( ! wc_get_order( $unpaid['order_id'] )->is_paid() && is_wp_error( WTBM_Tickets::admit( WTBM_Tickets::token( $unpaid_bid ), $theater, $date ) ), 'Unpaid reservations cannot enter the theater' );
    wtbm_test_assert( ! is_wp_error( WTBM_Tickets::cancel( $unpaid_bid, array(), true ) ), 'Unpaid cancellation releases reserved seats' );
    wtbm_test_assert( ! in_array( 'seat_5', WTBM_Booking_Service::occupied( $theater, $movie, $date, '11:00 AM' ), true ), 'Cancelled seats become available again' );
    $token = WTBM_Tickets::token( $bid );
    wtbm_test_assert( ! is_wp_error( WTBM_Tickets::parse( $token ) ), 'Signed ticket token verifies' );
    wtbm_test_assert( is_wp_error( WTBM_Tickets::parse( $token . 'x' ) ), 'Forged ticket token rejected' );
    wtbm_test_assert( ! WTBM_Tickets::access( $order, 'wrong-order-key' ) || WTBM_Booking_Service::can( 'manage' ), 'Order access helper respects staff capability' );
    wp_set_current_user( 0 );
    wtbm_test_assert( ! WTBM_Tickets::access( $order, 'wrong-order-key' ) && WTBM_Tickets::access( $order, $order->get_order_key() ), 'Guest ticket access is bound to its order key' );
    wtbm_test_assert( is_wp_error( WTBM_Change_Requests::submit( $bid, 'cancel', array( 'ids' => array( 'seat_2' ) ), 'wrong-order-key' ) ), 'Guest request submission requires the matching private order key' );
    wp_set_current_user( $admin[0] );
    $cancel_request = WTBM_Change_Requests::submit( $bid, 'cancel', array( 'ids' => array( 'seat_2' ), 'reason' => 'Fixture partial refund' ) );
    $refund = WTBM_Change_Requests::review( $cancel_request['request_id'], 'approve' );
    wtbm_test_assert( ! is_wp_error( $refund ) && 1 === (int) get_post_meta( $bid, 'wtbm_number_of_seats', true ), 'Partial cancellation refunds and retains remaining seat' );
    wtbm_test_assert( is_wp_error( WTBM_Tickets::parse( $token ) ), 'Partial cancellation revokes the previous ticket' );
    $report = WTBM_Operations_Report::data( array( 'from' => $date, 'to' => $date, 'basis' => 'show', 'theater' => $theater ) );
    wtbm_test_assert( ! is_wp_error( $report ) && 1 === $report['tickets'] && $report['refunds'] > 0 && abs( $report['net'] + $report['refunds'] - $report['gross'] ) < 0.01, 'Report reconciles gross, partial refunds, net and tickets' );
    $sale_report = WTBM_Operations_Report::data( array( 'from' => $date, 'to' => $date, 'basis' => 'sale', 'theater' => $theater ) );
    wtbm_test_assert( 0 === count( $sale_report['rows'] ), 'Sale-date report differs from future show-date report' );
    $calendar = WTBM_Tickets::calendar_content( $bid );
    wtbm_test_assert( false !== strpos( $calendar, 'BEGIN:VCALENDAR' ) && false !== strpos( $calendar, 'DTSTART:' . gmdate( 'Ymd\THis\Z', WTBM_Booking_Service::timestamp( $date, '11:00 AM' ) ) ), 'Calendar event contains the correct UTC show time' );
    ob_start(); WTBM_Tickets::card( $bid, wc_get_order( $order->get_id() ) ); $card_html = ob_get_clean();
    wtbm_test_assert( false !== strpos( $card_html, 'Show entry QR' ) && false !== strpos( $card_html, 'Add to calendar' ) && false !== strpos( $card_html, 'wtbm-ticket-form' ), 'Customer ticket card connects QR, calendar and booking actions' );
    $current = WTBM_Tickets::token( $bid );
    wtbm_test_assert( is_wp_error( WTBM_Tickets::admit( 'BK' . str_pad( $order->get_id(), 6, '0', STR_PAD_LEFT ), $theater, current_time( 'Y-m-d' ) ) ), 'Booking-number admission validates the selected show date' );
    wtbm_test_assert( is_wp_error( WTBM_Tickets::admit( 'BK999999999', $theater, $date ) ), 'Unknown booking number is rejected' );
    $admit = WTBM_Tickets::admit( 'BK' . str_pad( $order->get_id(), 6, '0', STR_PAD_LEFT ), $theater, $date );
    wtbm_test_assert( ! is_wp_error( $admit ), 'Paid ticket admission succeeds' );
    $checked = WTBM_Tickets::checkin_data( array( $bid ) );
    wtbm_test_assert( 1 === $checked[ $bid ]['count'] && 1 === $checked[ $bid ]['total'], 'Checked-in booking summary reports every active seat checked in' );
    $booking_meta = array(); foreach ( get_post_meta( $bid ) as $meta_name => $unused ) { $booking_meta[ $meta_name ] = get_post_meta( $bid, $meta_name, true ); }
    $booking_row = WTBM_Booking_Content::movie_booking_data( array( $bid => $booking_meta ), 1 );
    wtbm_test_assert( false !== strpos( $booking_row, 'Check-in details' ) && false !== strpos( $booking_row, 'Checked in' ) && false !== strpos( $booking_row, 'T3' ), 'Booking table renders saved check-in status and seat details' );

    wtbm_test_assert( array( 'T3' ) === $admit['seat_names'] && array( 'seat_3' ) === $admit['seats'], 'Admission displays saved seat names while preserving allocation IDs' );
    wtbm_test_assert( is_wp_error( WTBM_Tickets::admit( $current, $theater, $date ) ), 'Repeated admission is rejected after order synchronization' );
    wtbm_test_assert( is_wp_error( WTBM_Tickets::cancel( $bid, array( 'seat_3' ), true ) ), 'Admitted seats cannot be refunded' );
    $order = wc_get_order( $order->get_id() ); $key = WTBM_Notifications::fingerprint( $order );
    $delivered = WTBM_Notifications::deliver( $order->get_id(), 0, $key ); if ( ! $delivered ) { WP_CLI::log( (string) $wpdb->get_var( $wpdb->prepare( "SELECT details FROM {$wpdb->prefix}wtbm_events WHERE order_id=%d AND event='email_failed' ORDER BY id DESC LIMIT 1", $order->get_id() ) ) ); }
    $sent_calls = $mail_calls; WTBM_Notifications::deliver( $order->get_id(), 0, $key );
    wtbm_test_assert( $sent_calls === $mail_calls, 'Repeated email delivery does not send a duplicate attachment' );
    wtbm_test_assert( $delivered, 'PDF attachment and email delivery succeed through intercepted transport' );
    wtbm_test_assert( false !== strpos( WTBM_Operations_Report::html( $report ), 'Cashier reconciliation' ), 'Report output includes payment and cashier sections' );
    // A cashier can sell but cannot manage, admit or view all revenue.
    $uid = wp_insert_user( array( 'user_login' => 'wtbm-fixture-' . wp_generate_password( 8, false ), 'user_pass' => wp_generate_password( 24 ), 'role' => 'wtbm_cashier' ) ); $fixture_users[] = $uid; wp_set_current_user( $uid );
    wtbm_test_assert( is_wp_error( WTBM_Change_Requests::review( $pending['request_id'], 'approve' ) ), 'Cashiers cannot approve customer change requests' );
    wtbm_test_assert( WTBM_Booking_Service::can( 'sell' ) && ! WTBM_Booking_Service::can( 'manage' ) && ! WTBM_Booking_Service::can( 'admit' ) && ! WTBM_Booking_Service::can( 'reports' ), 'Cashier capabilities follow least privilege' );
    $fixture_user = new WP_User( $uid ); $fixture_user->set_role( 'wtbm_admission' ); wp_set_current_user( 0 ); wp_set_current_user( $uid );
    wtbm_test_assert( WTBM_Booking_Service::can( 'admit' ) && ! WTBM_Booking_Service::can( 'sell' ) && ! WTBM_Booking_Service::can( 'reports' ), 'Admission role has no sales or report access' );
    $fixture_user->set_role( 'wtbm_manager' ); wp_set_current_user( 0 ); wp_set_current_user( $uid );
    wtbm_test_assert( WTBM_Booking_Service::can( 'manage' ) && WTBM_Booking_Service::can( 'reports' ), 'Manager can manage bookings and reports' );
    WP_CLI::success( $checks . ' integration checks passed.' );
} catch ( Throwable $error ) { WP_CLI::warning( $error->getMessage() ); $failed = true; }
finally {
    wp_set_current_user( $admin[0] ); remove_filter( 'pre_wp_mail', $mail_filter ); global $wpdb;
    foreach ( $fixture_orders as $oid ) {
        foreach ( WTBM_Booking_Service::bookings( $oid ) as $bid ) { foreach ( WTBM_Change_Requests::requests( $bid ) as $request_post ) { wp_delete_post( $request_post, true ); } $wpdb->delete( $wpdb->prefix . 'wtbm_claims', array( 'booking_id' => $bid ) ); wp_delete_post( $bid, true ); }
        $wpdb->delete( $wpdb->prefix . 'wtbm_events', array( 'order_id' => $oid ) );
        $order = wc_get_order( $oid ); if ( $order ) { foreach ( $order->get_refunds() as $refund ) { $refund->delete( true ); } $order->delete( true ); }
        foreach ( _get_cron_array() as $stamp => $hooks ) { foreach ( $hooks['wtbm_deliver_ticket'] ?? array() as $event ) { if ( (int) $event['args'][0] === $oid ) { wp_unschedule_event( $stamp, 'wtbm_deliver_ticket', $event['args'] ); } } }
    }
    foreach ( array_unique( array_filter( $fixture_posts ) ) as $id ) { wp_delete_post( $id, true ); $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}wtbm_events WHERE order_id=0 AND event='plan_saved' AND details LIKE %s", '%"theater_id":' . $id . ',%' ) ); }
    foreach ( $fixture_users as $id ) { require_once ABSPATH . 'wp-admin/includes/user.php'; wp_delete_user( $id ); }
    $wpdb->query( "DELETE FROM {$wpdb->prefix}wtbm_claims WHERE owner IN ('fixture-owner-a','fixture-owner-b')" );
}
if ( ! empty( $failed ) ) { WP_CLI::halt( 1 ); }
