<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WTBM_Pricing_Rules' ) ) {
    class WTBM_Pricing_Rules
    {
        public function __construct(){
            add_action('wp_ajax_wtbm_insert_pricing_rules_post', [ $this, 'wtbm_insert_pricing_rules_post' ]);
            add_action('wp_ajax_nopriv_wtbm_insert_pricing_rules_post', [ $this, 'wtbm_insert_pricing_rules_post' ]);

            /*add_action('wp_ajax_wtbm_add_edit_pricing_rules_form', [ $this, 'wtbm_add_edit_pricing_rules_form' ]);
            add_action('wp_ajax_nopriv_wtbm_add_edit_pricing_rules_form', [ $this, 'wtbm_add_edit_pricing_rules_form' ]);*/

            add_action('wp_ajax_wtbm_add_edit_pricing_form', [ $this, 'wtbm_add_edit_pricing_form' ]);
            add_action('wp_ajax_nopriv_wtbm_add_edit_pricing_form', [ $this, 'wtbm_add_edit_pricing_form' ]);
        }

        public function wtbm_add_edit_pricing_form(){
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');

            $post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
            if( $post_id == '' ){
                $type = 'add';
            }else{
                $type = 'edit';
            }
            $add_form = WTBM_Pricing_Rules::add_edit_pricing_rules( $type, $post_id );

            wp_send_json_success(  $add_form );

        }


        public function wtbm_insert_pricing_rules_post(){
            check_ajax_referer('mptrs_admin_nonce', '_ajax_nonce');
            $cpt = WTBM_Function::get_pricing_cpt();

            $name           =  isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
            $description    =  isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';

            $type           = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
            $multiplier     = isset( $_POST['multiplier'] ) ? sanitize_text_field( wp_unslash( $_POST['multiplier'] ) ) : 1;
            $active         = isset( $_POST['active'] ) ? sanitize_text_field( wp_unslash( $_POST['active'] ) )  : false;
            $priority       = isset( $_POST['priority'] ) ? sanitize_text_field( wp_unslash( $_POST['priority'] ) )  : '';
            $min_seats      = isset( $_POST['minSeats'] ) ? sanitize_text_field( wp_unslash( $_POST['minSeats'] ) ) : 1;
            $combinable     = isset( $_POST['combinable'] ) ? sanitize_text_field( wp_unslash( $_POST['combinable'] ) ) : '';
            $timeRange      = isset( $_POST['timeRange'] ) ? sanitize_text_field( wp_unslash( $_POST['timeRange'] ) ) : '';
            $days           = isset( $_POST['days'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['days'] ) ) ) : [];
            $startDate      = isset( $_POST['startDate'] ) ? sanitize_text_field( wp_unslash( $_POST['startDate'] ) ) : '';
            $endDate        = isset( $_POST['endDate'] ) ? sanitize_text_field( wp_unslash( $_POST['endDate'] ) ) : '';
            $dateRange      = isset( $_POST['dateRange'] ) ? sanitize_text_field( wp_unslash( $_POST['dateRange'] ) ) : '';
            $theaterType    = isset( $_POST['theaterType'] ) ? sanitize_text_field( wp_unslash( $_POST['theaterType'] ) ) : '';

            $action_type = isset( $_POST['action_type'] ) ? sanitize_text_field( wp_unslash( $_POST['action_type'] ) ) : '';

            if( $action_type === 'edit' ){
                $pricing_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
                $post_data = [
                    'post_title'   => $name,
                    'post_type'    => $cpt,
                    'post_status'  => 'publish',
                    'post_content' => $description,
                ];
                $post_data['ID'] = $pricing_id;
                $post_id = wp_update_post( $post_data );
            }else{
                $post_id = wp_insert_post([
                    'post_title'   => $name,
                    'post_type'    => $cpt,
                    'post_status'  => 'publish',
                    'post_content' => $description,
                ]);

            }

            if ( $post_id ) {

                update_post_meta( $post_id, 'wtbp_pricing_rules_type', $type );
                update_post_meta( $post_id, 'wtbp_pricing_rules_multiplier', $multiplier );
                update_post_meta( $post_id, 'wtbp_pricing_rules_active', $active );
                update_post_meta( $post_id, 'wtbp_pricing_rules_priority', $priority );
                update_post_meta( $post_id, 'wtbp_pricing_rules_minSeats', $min_seats );
                update_post_meta( $post_id, 'wtbp_pricing_rules_combinable', $combinable );
                update_post_meta( $post_id, 'wtbp_pricing_rules_timeRange', $timeRange );
                update_post_meta( $post_id, 'wtbp_pricing_rules_days', $days );
                update_post_meta( $post_id, 'wtbp_pricing_rules_startDate', $startDate );
                update_post_meta( $post_id, 'wtbp_pricing_rules_endDate', $endDate );
                update_post_meta( $post_id, 'wtbp_pricing_rules_dateRange', $dateRange );
                update_post_meta( $post_id, 'wtbp_pricing_rules_theaterType', $theaterType );


                $new_pricing_rules = array(
                    0 => WTBM_Layout_Functions::get_pricing_rules_data_by_id( $post_id ),
                );
                $pricing_rule_html = self::pricing_rules_data_display( $new_pricing_rules );
                wp_send_json_success($pricing_rule_html );


            } else {
                wp_send_json_error("Failed to insert post" );
            }
        }

        public static function add_edit_pricing_rules( $action_type, $post_id ) {

            if ( ! current_user_can( 'manage_options' ) ) {
                return '<p>' . esc_html__( 'You do not have permission to access this page.', 'wptheaterly' ) . '</p>';
            }

            $add_action = 'wtbp_add_new_pricing_rule';
            $rule_data  = [];

            if ( $action_type === 'edit' && $post_id ) {
                $add_action = 'wtbm_edit_pricing_rule';
                $rule_data  = WTBM_Layout_Functions::get_pricing_rules_data_by_id( absint( $post_id ) );
            }

            $id               = isset( $rule_data['id'] ) ? esc_attr( $rule_data['id'] ) : '';
            $name             = isset( $rule_data['name'] ) ? esc_attr( $rule_data['name'] ) : '';
            $description      = isset( $rule_data['description'] ) ? esc_textarea( $rule_data['description'] ) : '';
            $rules_type       = isset( $rule_data['rules_type'] ) ? esc_attr( $rule_data['rules_type'] ) : 'day';
            $rules_days       = isset( $rule_data['rules_days'] ) ? (array) $rule_data['rules_days'] : [];
            $rules_start_date = isset( $rule_data['rules_start_date'] ) ? esc_attr( $rule_data['rules_start_date'] ) : '';
            $rules_end_date   = isset( $rule_data['rules_end_date'] ) ? esc_attr( $rule_data['rules_end_date'] ) : '';
            $rules_time_range = isset( $rule_data['rules_time_range'] ) ? esc_attr( $rule_data['rules_time_range'] ) : '';
            $rules_min_seats  = isset( $rule_data['rules_min_seats'] ) ? intval( $rule_data['rules_min_seats'] ) : '';
            $rules_priority   = isset( $rule_data['rules_priority'] ) ? intval( $rule_data['rules_priority'] ) : '';
            $rules_multiplier = isset( $rule_data['rules_multiplier'] ) ? esc_attr( $rule_data['rules_multiplier'] ) : '';
            $rules_active     = ! empty( $rule_data['rules_active'] ) ? 'checked' : '';
            $rules_combinable = ! empty( $rule_data['rules_combinable'] ) ? 'checked' : '';
            $rules_theater_type = isset( $rule_data['rules_theater_type'] ) ? esc_attr( $rule_data['rules_theater_type'] ) : '';

            $title = ( $action_type === 'edit' ) ? __( 'Edit Pricing Rule', 'wptheaterly' ) : __( 'Add New Pricing Rule', 'wptheaterly' );

            ob_start();
            ?>
            <h4 class="mb-4 font-semibold"><?php echo esc_html( $title ); ?></h4>

            <div class="grid grid-cols-2 mb-4">
                <div class="form-group">
                    <label class="form-label"><?php esc_html_e( 'Rule Name', 'wptheaterly' ); ?></label>
                    <input type="text" id="pricing-name" class="form-input" value="<?php echo esc_attr( $name ) ;?>" placeholder="e.g., Matinee, Weekend" required>
                </div>

                <div class="form-group">
                    <label class="form-label"><?php esc_html_e( 'Rule Type', 'wptheaterly' ); ?></label>
                    <select id="pricing-type" class="form-input">
                        <option value="day" <?php selected( $rules_type, 'day' ); ?>><?php esc_html_e( 'Day-based', 'wptheaterly' ); ?></option>
                        <option value="date" <?php selected( $rules_type, 'date_range' ); ?>><?php esc_html_e( 'Date-based', 'wptheaterly' ); ?></option>
                        <option value="time" <?php selected( $rules_type, 'time_range' ); ?>><?php esc_html_e( 'Time-based', 'wptheaterly' ); ?></option>
                        <option value="theater" <?php selected( $rules_type, 'theater' ); ?>><?php esc_html_e( 'Theater-based', 'wptheaterly' ); ?></option>
                    </select>
                </div>

                <!-- Time Range -->
                <div class="form-group" id="time-range-group" style="<?php echo ( $rules_type === 'time' ) ? '' : 'display:none;'; ?>">
                    <label class="form-label"><?php esc_html_e( 'Time Range', 'wptheaterly' ); ?></label>
                    <input type="text" id="pricing-time-range" class="form-input" value="<?php echo esc_attr( $rules_time_range );?>" placeholder="e.g., 09:00-14:00">
                </div>

                <!-- Days -->
                <div class="form-group" id="days-group" style="<?php echo ( $rules_type === 'day' ) ? '' : 'display:none;'; ?>">
                    <label class="form-label"><?php esc_html_e( 'Days of Week', 'wptheaterly' ); ?></label>
                    <div class="flex gap-2 flex-wrap">
                        <?php
                        $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                        foreach ( $days as $day ) { ?>
                            <label class="flex items-center gap-1">
                                <input type="checkbox" name="pricing-days[]" value="<?php echo esc_attr( $day ); ?>" <?php checked( in_array( $day, $rules_days ) ); ?>>
                                <?php echo esc_attr( ucfirst( $day ) ) ; ?>
                            </label>
                        <?php } ?>
                    </div>
                </div>

                <!-- Date Range -->
                <div class="form-group" id="date-range-group" style="<?php echo ( $rules_type === 'date' ) ? '' : 'display:none;'; ?>">
                    <label class="form-label"><?php esc_html_e( 'Date Range', 'wptheaterly' ); ?></label>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="date" id="pricing-start-date" class="form-input" value="<?php echo esc_attr( $rules_start_date ) ;?>">
                        <input type="date" id="pricing-end-date" class="form-input" value="<?php echo esc_attr( $rules_end_date ); ?>">
                    </div>
                </div>

                <!-- Theater Type -->
                <div class="form-group" id="theater-group" style="<?php echo ( $rules_type === 'theater' ) ? '' : 'display:none;'; ?>">
                    <label class="form-label"><?php esc_html_e( 'Theater Type', 'wptheaterly' ); ?></label>
                    <select id="pricing-theater-type" class="form-input">
                        <option value="" <?php selected( $rules_theater_type, '' ); ?>><?php esc_html_e( 'All Theaters', 'wptheaterly' ); ?></option>
                        <option value="Standard" <?php selected( $rules_theater_type, 'Standard' ); ?>><?php esc_html_e( 'Standard', 'wptheaterly' ); ?></option>
                        <option value="Premium" <?php selected( $rules_theater_type, 'Premium' ); ?>><?php esc_html_e( 'Premium', 'wptheaterly' ); ?></option>
                        <option value="IMAX" <?php selected( $rules_theater_type, 'IMAX' ); ?>><?php esc_html_e( 'IMAX', 'wptheaterly' ); ?></option>
                        <option value="VIP" <?php selected( $rules_theater_type, 'VIP' ); ?>><?php esc_html_e( 'VIP', 'wptheaterly' ); ?></option>
                    </select>
                </div>

                <!-- Multiplier -->
                <div class="form-group">
                    <label class="form-label"><?php esc_html_e( 'Price Multiplier', 'wptheaterly' ); ?></label>
                    <input type="number" id="pricing-multiplier" class="form-input" value="<?php echo esc_attr( $rules_multiplier ); ?>" step="0.1" min="0.1" max="5.0">
                    <div class="text-sm text-gray-500 mt-1">1.0 = base price, 0.8 = 20% discount, 1.5 = 50% markup</div>
                </div>

                <!-- Priority -->
                <div class="form-group">
                    <label class="form-label"><?php esc_html_e( 'Priority', 'wptheaterly' ); ?></label>
                    <input type="number" id="pricing-priority" class="form-input" value="<?php echo esc_attr( $rules_priority); ?>" min="1" max="100">
                </div>

                <!-- Min Seats -->
                <div class="form-group">
                    <label class="form-label"><?php esc_html_e( 'Minimum Seats', 'wptheaterly' ); ?></label>
                    <input type="number" id="pricing-min-seats" class="form-input" value="<?php echo esc_attr( $rules_min_seats); ?>" min="1">
                </div>

                <!-- Active -->
                <div class="form-group">
                    <label class="flex items-center">
                        <input type="checkbox" id="pricing-active" class="mr-2" <?php echo esc_attr( $rules_active); ?>>
                        <span><?php esc_html_e( 'Active', 'wptheaterly' ); ?></span>
                    </label>
                </div>

                <!-- Combinable -->
                <div class="form-group">
                    <label class="flex items-center">
                        <input type="checkbox" id="pricing-combinable" class="mr-2" <?php echo esc_attr( $rules_combinable ); ?>>
                        <span><?php esc_html_e( 'Can be combined with other rules', 'wptheaterly' ); ?></span>
                    </label>
                </div>
            </div>

            <!-- Description -->
            <div class="form-group mb-4">
                <label class="form-label"><?php esc_html_e( 'Description', 'wptheaterly' ); ?></label>
                <textarea id="pricing-description" class="form-input" rows="2"><?php echo esc_attr( $description ); ?></textarea>
            </div>

            <div class="flex gap-2">
                <button class="btn btn-success" data-edit-pricing="<?php echo esc_attr( $id );?>" id="<?php echo esc_attr( $add_action );?>"><?php echo ( $action_type === 'edit' ) ? esc_html__( 'Update Rule', 'wptheaterly' ) : esc_html__( 'Add Rule', 'wptheaterly' ); ?></button>
                <button class="btn btn-secondary" type="button" id="wtbm_clear_pricing_form"><?php esc_html_e( 'Cancel', 'wptheaterly' ); ?></button>
                <button class="btn btn-secondary" style="display: none" id="wtbp_previewPricing" type="button"><?php esc_html_e( 'Preview Pricing', 'wptheaterly' ); ?></button>
            </div>

            <div id="pricing-preview" class="mt-4 p-4 bg-gray-50 rounded-lg" style="display: none;">
                <h5 class="font-semibold mb-2"><?php esc_html_e( 'Pricing Preview', 'wptheaterly' ); ?></h5>
                <div id="preview-content" class="text-sm"></div>
            </div>

            <?php
            return ob_get_clean();
        }

        public static function pricing_rules_data_display( $pricing_rules_date ) {

            ob_start();

            if ( empty( $pricing_rules_date ) ) {
                echo wp_kses_post( '<tr><td colspan="5" class="text-center text-gray-500">No pricing rules found.</td></tr>' );
                return ob_get_clean();
            }

            foreach ( $pricing_rules_date as $rule ) {
                $id           = esc_html( $rule['id'] );
                $name        = esc_html( $rule['name'] );
                $desc        = esc_html( $rule['description'] );
                $type        = esc_html( $rule['rules_type'] );
                $priority    = intval( $rule['rules_priority'] );
                $multiplier  = floatval( $rule['rules_multiplier'] );
                $active      = filter_var( $rule['rules_active'], FILTER_VALIDATE_BOOLEAN );
                $statusClass = $active ? 'status-active' : 'status-inactive';
                $statusText  = $active ? 'Active' : 'Inactive';

                // Rule type specific display
                $details = '';
                switch ( $type ) {
                    case 'date':
                        $details = esc_html( $rule['rules_start_date'] . ' to ' . $rule['rules_end_date'] );
                        $subtitle = 'date-based rule';
                        break;

                    case 'day':
                        $days = ! empty( $rule['rules_days'] ) ? implode( ', ', (array) $rule['rules_days'] ) : 'N/A';
                        $details = esc_html( ucfirst( $days ) );
                        $subtitle = 'day-based rule';
                        break;

                    case 'theater':
                        $details = esc_html( $rule['rules_theater_type'] ?: 'All Theaters' );
                        $subtitle = 'theater-based rule';
                        break;

                    case 'time':
                        $details = esc_html( $rule['rules_time_range'] ?: 'N/A' );
                        $subtitle = 'time-based rule';
                        break;

                    default:
                        $details = esc_html( $rule['rules_start_date'] . ' to ' . $rule['rules_end_date'] );
                        $subtitle = 'date-based rule';
                        break;
                }
                ?>
                <tr id="pricing_rules_content_<?php echo esc_attr( $id );?>">
                    <td>
                        <div class="text-sm font-medium text-gray-900"><?php echo esc_attr( $name ); ?></div>
                        <div class="text-sm text-gray-500"><?php echo esc_attr( $subtitle ); ?></div>
                    </td>
                    <td class="text-sm text-gray-900"><?php echo esc_attr( $details ); ?></td>
                    <td class="text-sm text-gray-900"><?php echo esc_attr( $multiplier ); ?>x</td>
                    <td>
                        <span class="status-badge <?php echo esc_attr( $statusClass ); ?>">
                            <?php echo esc_attr( $statusText ); ?>
                        </span>
                        <div class="text-xs text-gray-500 mt-1">Priority: <?php echo esc_attr( $priority ); ?></div>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <button class="btn-icon edit wtbm_edit_pricing_rules" data-pricing-id="<?php echo intval( $rule['id'] ); ?>" title="Edit Rule"><i class="mi mi-pencil"></i></button>
                            <button class="btn-icon delete wtbm_delete_pricing_rules" data-pricing-rules-id="<?php echo intval( $rule['id'] ); ?>" title="Delete Rule"><i class="mi mi-trash"></i></button>
                        </div>
                    </td>
                </tr>
                <?php
            }

            return ob_get_clean();
        }



    }

    new WTBM_Pricing_Rules();

}