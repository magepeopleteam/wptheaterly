<?php
/** Self-contained pricing regression checks: wp eval-file this file. */
if ( ! defined( 'ABSPATH' ) || ! defined( 'WP_CLI' ) || ! WP_CLI ) { exit; }
$cache = new ReflectionProperty( WTBM_Set_Pricing_Rules::class, 'cached_rules' );
$before = $cache->getValue(); $checks = 0;
$rule = array( 'ID' => 1, 'title' => 'Weekday offer', 'rules_type' => 'day', 'multiplier' => .8, 'priority' => 1, 'minSeats' => 1, 'combinable' => 'false', 'days' => array( 'Friday' ), 'time_range' => '', 'startDate' => '', 'endDate' => '', 'theaterType' => '' );
$assert = static function ( $condition, $message ) use ( &$checks ) { if ( ! $condition ) { throw new RuntimeException( $message ); } ++$checks; WP_CLI::log( 'PASS ' . $message ); };
$price = static function ( $rules, $date = '2026-10-02', $time = '21:00', $seats = 3, $theater = 0 ) use ( $cache ) { $cache->setValue( null, $rules ); return WTBM_Set_Pricing_Rules::price_details( 210, strtolower( ( new DateTimeImmutable( $date ) )->format( 'l' ) ), $date, $theater, $time, $seats ); };
try {
    $offer = $price( array( $rule ) );
    $assert( 168.0 === $offer['price'] && -20.0 === $offer['percent'] && 210.0 === $offer['base'], 'Three 70 seats receive the Friday 20 percent reduction with a base/adjustment breakdown' );
    $second = array_replace( $rule, array( 'ID' => 2, 'multiplier' => 2, 'priority' => 0 ) );
    $assert( 168.0 === $price( array( $rule, $second ) )['price'], 'String false prevents unintended rule stacking' );
    $assert( 336.0 === $price( array( array_replace( $rule, array( 'combinable' => 'true' ) ), $second ) )['price'], 'Explicit combination multiplies matching rules in priority order' );
    $assert( 210.0 === $price( array( $rule ), '2026-10-03' )['price'], 'Unmatched weekdays retain the base price' );
    $assert( 210.0 === $price( array( array_replace( $rule, array( 'minSeats' => 4 ) ) ) )['price'], 'Minimum seat thresholds apply to previews and quotes' );
    $time_rule = array_replace( $rule, array( 'rules_type' => 'time', 'time_range' => '09:30-14:15' ) );
    $assert( 210.0 === $price( array( $time_rule ), '2026-10-02', '09:29' )['price'] && 168.0 === $price( array( $time_rule ), '2026-10-02', '02:15 PM' )['price'], 'Time rules preserve minutes and accept 12 hour booking times' );
    $assert( 168.0 === $price( array( array_replace( $time_rule, array( 'time_range' => '22:30-02:15' ) ) ), '2026-10-02', '01:00' )['price'], 'Overnight time ranges apply on both sides of midnight' );
    $assert( 210.0 === $price( array( array_replace( $time_rule, array( 'time_range' => 'broken' ) ) ) )['price'], 'Malformed time ranges cannot change a price' );
    $date_rule = array_replace( $rule, array( 'rules_type' => 'date', 'startDate' => '2026-10-01', 'endDate' => '2026-10-02', 'multiplier' => 1.25 ) );
    $assert( 262.5 === $price( array( $date_rule ) )['price'] && 210.0 === $price( array( $date_rule ), '2026-10-03' )['price'], 'Date increases include their end date and exclude dates outside the range' );
    $assert( 210.0 === $price( array( array_replace( $date_rule, array( 'startDate' => '', 'endDate' => '' ) ) ) )['price'], 'An empty date rule cannot accidentally apply to every show' );
    $assert( 168.0 === $price( array( array_replace( $rule, array( 'rules_type' => 'theater' ) ) ), '2026-10-02', '21:00', 3, 123 )['price'], 'All theaters rules apply when a theater is selected' );
    $assert( 210.0 === $price( array( array_replace( $rule, array( 'multiplier' => '-1' ) ) ) )['price'], 'Invalid negative multipliers are ignored' );
    $assert( false !== strpos( WTBM_Set_Pricing_Rules::indicator( -20, '<Offer>' ), '↓' ) && false !== strpos( WTBM_Set_Pricing_Rules::indicator( 25 ), '↑' ) && false !== strpos( WTBM_Set_Pricing_Rules::indicator( -20, '<Offer>' ), '&lt;Offer&gt;' ), 'Price indicators show direction and escape rule names' );
    $cache->setValue( null, array( array_replace( $rule, array( 'minSeats' => 2 ) ) ) );
    $assert( false !== strpos( WTBM_Set_Pricing_Rules::date_indicator( '2026-10-02' ), '2+ seats' ) && '' === WTBM_Set_Pricing_Rules::date_indicator( '2026-10-02', 0, '', 1 ), 'Calendar previews label minimum seats and hide ineligible offers after a quantity is selected' );
    $item = new WC_Order_Item_Product(); WTBM_Set_Pricing_Rules::order_details( $item, $offer );
    $assert( 'Weekday offer' === $item->get_meta( 'Pricing rules' ) && false !== strpos( $item->get_meta( 'Price adjustment' ), '20%' ), 'Customer and staff order metadata explains the immutable applied price' );
    WTBM_Set_Pricing_Rules::order_details( $item, $price( array() ) );
    $assert( '' === $item->get_meta( 'Pricing rules' ), 'Editing to a show without rules removes outdated visible pricing metadata' );
    WP_CLI::success( $checks . ' pricing checks passed; no live rules or orders modified.' );
} finally { $cache->setValue( null, $before ); }
