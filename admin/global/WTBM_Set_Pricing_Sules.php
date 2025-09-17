<?php
/*
* @Author 		rubelcuet10@gmail.com
* Copyright: 	mage-people.com
*/
if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('WTBM_Set_Pricing_Sules')) {

    class WTBM_Set_Pricing_Sules {
        /**
         * Store cached data
         */
        private static $cached_rules = null;

        /**
         * Load pricing rules from database (once per request)
         */
        private static function load_pricing_rules() {

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
                        'value'   => 'true',
                        'compare' => '='
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
            $rules = self::load_pricing_rules();

            $final_price = $price;

            if( is_array( $rules ) && !empty( $rules ) ) {
                usort($rules, function ($a, $b) {
                    return $b['priority'] <=> $a['priority'];
                });

                $theater_type = get_post_meta( $theater_id, 'wtbp_theater_type', true );
                foreach ($rules as $rule) {

                    if ( $seats <= $rule['minSeats'] ) {
                        continue;
                    }

                    $apply = false;

                    switch ($rule['rules_type']) {
                        case 'day':
                            if ( !empty( $rule['days'] ) &&  in_array( $day, $rule['days'] ) ) {
                                $apply = true;
                            }
                            break;

                        case 'theater':
                            if ( !empty( $rule['theaterType'] ) && $theater_type === $rule['theaterType'] ) {
                                error_log( print_r( $theater_type, true ) );
                                $apply = true;
                            }
                            break;

                        case 'time':

                            $booking_float = (float)date('H', strtotime($booking_time)) + ((int)date('i', strtotime($booking_time)) / 60);

                            list( $start, $end ) = explode('-', $rule['time_range']);
                            $start = (int)$start;
                            $end   = (int)$end;
                            if ( $booking_float >= $start && $booking_float <= $end ) {
                                $apply = true;
                            }
                            break;

                        case 'date':
                            $start      = !empty( $rule['startDate'] ) ? strtotime( $rule['startDate'] ) : null;
                            $end        = !empty( $rule['endDate'] ) ? strtotime( $rule['endDate'] ) : null;
                            $booking_ts = strtotime( $booking_data );
                            if ( ( $start === null || $booking_ts >= $start ) && ( $end === null || $booking_ts <= $end ) ) {
                                $apply = true;
                            }
                            break;
                    }

                    if ( $apply ) {
                        $final_price *= $rule['multiplier'];

                        if ( !$rule['combinable'] ) {
                            break;
                        }
                    }

                }
            }

            return round( $final_price, 2 );
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


    new WTBM_Set_Pricing_Sules();
}