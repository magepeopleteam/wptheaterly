<?php
/*
* @Author 		rubelcuet10@gmail.com
* Copyright: 	mage-people.com
*/
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('WTBM_Set_Pricing_Rules')) {

    class WTBM_Set_Pricing_Rules {
        /**
         * Store cached data
         */
        private static $cached_rules = null;

        /**
         * Load pricing rules from database (once per request)
         */
        public static function load_pricing_rules() {

            if (self::$cached_rules !== null) {
                return self::$cached_rules;
            }

            $args = [
                'post_type'      => 'wtbm_pricing',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'meta_query'     => [
                    [
                        'key'     => 'wtbp_pricing_rules_active',
                        'value'   => array( 'true', '1', 'yes', 'on' ),
                        'compare' => 'IN'
                    ]
                ]
            ];

            $query = new WP_Query($args);
            $results = [];

            if ($query->have_posts()) {
                foreach ($query->posts as $post) {
                    $meta = get_post_meta( $post->ID );
                    $results[] = [
                        'ID'    => $post->ID,
                        'title' => get_the_title($post),
                        'rules_type' => $meta['wtbp_pricing_rules_type'][0] ?? '',
                        'multiplier' => $meta['wtbp_pricing_rules_multiplier'][0] ?? 1,
                        'priority'   => $meta['wtbp_pricing_rules_priority'][0] ?? 0,
                        'minSeats'   => $meta['wtbp_pricing_rules_minSeats'][0] ?? 0,
                        'combinable' => $meta['wtbp_pricing_rules_combinable'][0] ?? false,
                        'days'       => isset( $meta['wtbp_pricing_rules_days'][0]) ? maybe_unserialize($meta['wtbp_pricing_rules_days'][0]) : [],
                        'time_range' => $meta['wtbp_pricing_rules_timeRange'][0] ?? '',
                        'startDate'  => $meta['wtbp_pricing_rules_startDate'][0] ?? '',
                        'endDate'    => $meta['wtbp_pricing_rules_endDate'][0] ?? '',
                        'theaterType'=> $meta['wtbp_pricing_rules_theaterType'][0] ?? '',
                    ];
                }
            }

            wp_reset_postdata();

            // Cache array
            self::$cached_rules = $results;

            return self::$cached_rules;
        }


        /**
         * Calculate price based on rules
         *
         * @param float $price Original price
         * @param string $day Current day (e.g. 'tuesday')
         * @param int $seats Number of seats
         * @return float Adjusted price
         */
        public static function calculate_price_by_rules( $price, $day, $booking_data, $theater_id, $booking_time, $seats  = 1 ) {
            return self::price_details( $price, $day, $booking_data, $theater_id, $booking_time, $seats )['price'];
        }

        /** One evaluator for calendar previews, booking summaries and authoritative orders. */
        public static function price_details( $price, $day, $date, $theater_id, $time, $seats = 1 ) {
            $rules = self::load_pricing_rules();
            usort( $rules, static function ( $a, $b ) {
                return ( (int) $b['priority'] <=> (int) $a['priority'] ) ?: ( (int) $a['ID'] <=> (int) $b['ID'] );
            } );
            $base = max( 0, (float) $price );
            $final = $base;
            $applied = array();
            $type = strtolower( (string) get_post_meta( $theater_id, 'wtbp_theater_type', true ) );
            foreach ( $rules as $rule ) {
                if ( $seats < (int) $rule['minSeats'] || ! is_numeric( $rule['multiplier'] ) || ! is_finite( (float) $rule['multiplier'] ) || (float) $rule['multiplier'] < 0 ) { continue; }
                $match = false;
                switch ( $rule['rules_type'] ) {
                    case 'day':
                        $match = in_array( strtolower( $day ), array_map( 'strtolower', (array) $rule['days'] ), true );
                        break;
                    case 'theater':
                        $match = $theater_id && ( '' === $rule['theaterType'] || strtolower( $rule['theaterType'] ) === $type );
                        break;
                    case 'time':
                        $range = explode( '-', $rule['time_range'] );
                        if ( count( $range ) !== 2 || ! $time ) { break; }
                        $start = self::time_minutes( $range[0] );
                        $end = self::time_minutes( $range[1] );
                        $current = self::time_minutes( $time );
                        if ( null !== $start && null !== $end && null !== $current ) {
                            $match = $start <= $end ? ( $current >= $start && $current <= $end ) : ( $current >= $start || $current <= $end );
                        }
                        break;
                    case 'date':
                        $start = $rule['startDate'];
                        $end = $rule['endDate'];
                        $valid = static function ( $value ) { $parsed = DateTimeImmutable::createFromFormat( '!Y-m-d', $value, wp_timezone() ); return $parsed && $parsed->format( 'Y-m-d' ) === $value; };
                        $match = $valid( $date ) && ( $start || $end ) && ( ! $start || ( $valid( $start ) && $date >= $start ) ) && ( ! $end || ( $valid( $end ) && $date <= $end ) );
                        break;
                }
                if ( ! $match ) { continue; }
                $final *= (float) $rule['multiplier'];
                $applied[] = array( 'id' => (int) $rule['ID'], 'name' => $rule['title'], 'multiplier' => (float) $rule['multiplier'], 'min_seats' => max( 1, (int) $rule['minSeats'] ) );
                if ( ! filter_var( $rule['combinable'], FILTER_VALIDATE_BOOLEAN ) ) { break; }
            }
            $decimals = function_exists( 'wc_get_price_decimals' ) ? wc_get_price_decimals() : 2;
            return array( 'base' => $base, 'price' => round( $final, $decimals ), 'percent' => $base > 0 ? round( ( $final / $base - 1 ) * 100, 2 ) : 0, 'rules' => $applied );
        }

        /** Before seats are selected, label conditional offers with their minimum quantity. */
        public static function date_indicator( $date, $theater = 0, $time = '', $seats = 0 ) {
            $parsed = DateTimeImmutable::createFromFormat( '!Y-m-d', $date, wp_timezone() );
            if ( ! $parsed || $parsed->format( 'Y-m-d' ) !== $date ) { return ''; }
            $pricing = self::price_details( 100, strtolower( $parsed->format( 'l' ) ), $date, $theater, $time, $seats ?: 100 );
            $badge = self::indicator( $pricing['percent'], implode( ', ', array_column( $pricing['rules'], 'name' ) ) );
            $minimum = $pricing['rules'] ? max( array_column( $pricing['rules'], 'min_seats' ) ) : 1;
            if ( $badge && ! $seats && $minimum > 1 ) {
                $badge .= '<small class="wtbm-price-minimum">' . esc_html( sprintf( __( '%s+ seats', 'wptheaterly' ), $minimum ) ) . '</small>';
            }
            return $badge;
        }

        /** Visible order metadata explains the saved price without recalculating historical orders. */
        public static function order_details( $item, $pricing ) {
            $keys = array( __( 'Pricing rules', 'wptheaterly' ), __( 'Base seat total', 'wptheaterly' ), __( 'Price adjustment', 'wptheaterly' ) );
            foreach ( $keys as $key ) { $item->delete_meta_data( $key ); }
            if ( empty( $pricing['rules'] ) ) { return; }
            $item->update_meta_data( $keys[0], implode( ', ', array_column( $pricing['rules'], 'name' ) ) );
            $item->update_meta_data( $keys[1], wc_format_localized_price( $pricing['base'] ) );
            $item->update_meta_data( $keys[2], ( $pricing['percent'] < 0 ? '−' : '+' ) . abs( $pricing['percent'] ) . '% (' . wc_format_localized_price( $pricing['price'] - $pricing['base'] ) . ')' );
        }

        private static function time_minutes( $time ) {
            $time = trim( $time );
            if ( preg_match( '/^([01]?\d|2[0-3])(?:\.(\d+))?$/', $time, $parts ) ) { return (float) $time * 60; }
            if ( ! preg_match( '/^(\d{1,2}):(\d{2})(?:\s*(AM|PM))?$/i', $time, $parts ) ) { return null; }
            $hour = (int) $parts[1]; $minute = (int) $parts[2];
            if ( $minute > 59 || ( isset( $parts[3] ) ? ( $hour < 1 || $hour > 12 ) : $hour > 23 ) ) { return null; }
            if ( isset( $parts[3] ) ) { $hour = $hour % 12 + ( strtoupper( $parts[3] ) === 'PM' ? 12 : 0 ); }
            return $hour * 60 + $minute;
        }

        /** Accessible up/down indicator, shared by rule rows and calendar dates. */
        public static function indicator( $percent, $names = '' ) {
            if ( abs( $percent ) < 0.005 ) { return ''; }
            $label = sprintf( $percent < 0 ? __( '%s%% lower price', 'wptheaterly' ) : __( '%s%% higher price', 'wptheaterly' ), number_format_i18n( abs( $percent ), abs( $percent - round( $percent ) ) < 0.005 ? 0 : 2 ) );
            return '<span class="wtbm-price-indicator ' . ( $percent < 0 ? 'is-discount' : 'is-increase' ) . '" title="' . esc_attr( $label . ( $names ? ' · ' . $names : '' ) ) . '" aria-label="' . esc_attr( $label ) . '"><span aria-hidden="true">' . ( $percent < 0 ? '↓' : '↑' ) . '</span> ' . esc_html( number_format_i18n( abs( $percent ), abs( $percent - round( $percent ) ) < 0.005 ? 0 : 2 ) ) . '%</span>';
        }

        /**
         * Get a specific rule by ID
         */
        public static function get_rule_by_id($id) {
            $data = self::load_pricing_rules();

            foreach ($data as $rule) {
                if ((int)$rule['ID'] === (int)$id) {
                    return $rule;
                }
            }

            return []; // return empty array if not found
        }

        /**
         * Get all meta values for a specific key
         */
        public static function get_meta_by_key($meta_key) {
            $data = self::load_pricing_rules();
            $result = [];

            foreach ($data as $rule) {
                if (isset($rule['meta'][$meta_key])) {
                    $result[] = $rule['meta'][$meta_key];
                }
            }

            return $result;
        }
    }


    new WTBM_Set_Pricing_Rules();
}