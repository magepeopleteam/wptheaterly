<?php
if ( ! defined( 'ABSPATH' ) ) exit
// Template Name: Default Theme
?>
<?php
$wtbm_attendee_id = $wtbm_ticket_id ?? 0;
// If no attendee_id, try to get data from WooCommerce order directly
if ($wtbm_attendee_id == 0 && isset($wtbm_order_id)) {
    $wtbm_wc_order = wc_get_order($wtbm_order_id);
    if ($wtbm_wc_order) {
        // We'll handle WooCommerce order data display
        $wtbm_use_wc_data = true;
    }
}
?>
<div class='mp_pdf'>
    <div class="mp_pdf_header">
        <?php WTBM_Pro_Pdf::pdf_logo(); ?>
        <h5><?php echo esc_html( WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_address') ); ?> </h5>
        <h6><?php echo esc_html( WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_phone') ) ; ?> </h6>
    </div>

    <?php if (isset($wtbm_use_wc_data) && $wtbm_use_wc_data && $wtbm_wc_order): ?>
        <!-- WooCommerce Order Data Display -->
        <div class="mp_pdf_body">
            <h4><?php esc_html_e('Booking Information', 'wptheaterly'); ?></h4>
            <div class="divider"></div>
            <ul class="mp_list">
                <li>
                    <strong class="min_150"><?php esc_html_e('Order ID:', 'wptheaterly'); ?></strong>&nbsp;#<?php echo esc_html($wtbm_wc_order->get_id()); ?>
                </li>
                <li>
                    <strong class="min_150"><?php esc_html_e('Order Date:', 'wptheaterly'); ?></strong> <?php echo esc_html($wtbm_wc_order->get_date_created()->date('Y-m-d H:i:s')); ?>
                </li>
                <li>
                    <strong class="min_150"><?php esc_html_e('Order Status:', 'wptheaterly'); ?></strong> <?php echo esc_html($wtbm_wc_order->get_status()); ?>
                </li>
                <li>
                    <strong class="min_150"><?php esc_html_e('Payment Method:', 'wptheaterly'); ?></strong> <?php echo esc_html($wtbm_wc_order->get_payment_method_title()); ?>
                </li>
            </ul>
        </div>

        <div class="mp_pdf_body">
            <h5><?php esc_html_e('Billing Information', 'wptheaterly'); ?></h5>
            <div class="divider"></div>
            <ul class="mp_list">
                <li>
                    <strong class="min_100"><?php esc_html_e('Name', 'wptheaterly'); ?>:</strong> <?php echo esc_html($wtbm_wc_order->get_billing_first_name() . ' ' . $wtbm_wc_order->get_billing_last_name()); ?>
                </li>
                <li>
                    <strong class="min_100"><?php esc_html_e('E-mail', 'wptheaterly'); ?>:</strong> <?php echo esc_html($wtbm_wc_order->get_billing_email()); ?>
                </li>
                <li>
                    <strong class="min_100"><?php esc_html_e('Phone', 'wptheaterly'); ?>:</strong> <?php echo esc_html($wtbm_wc_order->get_billing_phone()); ?>
                </li>
                <li>
                    <strong class="min_100"><?php esc_html_e('Address', 'wptheaterly'); ?>:</strong> <?php echo wp_kses_post($wtbm_wc_order->get_formatted_billing_address()); ?>
                </li>
            </ul>
        </div>

        <div class="mp_pdf_body">
            <h5><?php esc_html_e('Order Items', 'wptheaterly'); ?></h5>
            <div class="divider"></div>
            <ul class="mp_list">
                <?php foreach ($wtbm_wc_order->get_items() as $wtbm_item): ?>
                    <li class="justifyBetween">
                        <p class="min_150"><?php echo esc_html($wtbm_item->get_name()); ?></p>
                        <span>x<?php echo esc_html($wtbm_item->get_quantity()); ?> | <?php echo wp_kses_post(  wc_price($wtbm_item->get_total() ) ); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="divider"></div>
            <h4>
                <span style="float: left;"><?php esc_html_e('Total Bill:', 'wptheaterly'); ?></span>
                <span style="text-align: right;float: right;"><?php echo wp_kses_post( wc_price($wtbm_wc_order->get_total() ) ); ?></span>
            </h4>
        </div>
    <?php else: ?>
        <!-- Original booking post data display -->
        <div class="" style="display:block;width:100%; float:left;">
            <div class="mp_pdf_body" style="display:block;width:50%; float:left;">
                <?php WTBM_Layout_Pro::service_info($wtbm_attendee_id); ?>
            </div>
            <div class="mp_pdf_body" style="display:block;width:50%; float:left;">
                <?php WTBM_Layout_Pro::billing_info($wtbm_attendee_id); ?>
            </div>
        </div>
        <div class="mp_pdf_body" style="width: 100%; float: left">
            <?php WTBM_Layout_Pro::order_info($wtbm_attendee_id); ?>
            <div class="divider"></div>
            <h4>
                <span style="float: left;"><?php esc_html_e('Total Bill:', 'wptheaterly'); ?></span>
                <span style="text-align: right;float: right;"><?php echo wp_kses_post( wc_price(WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_tp' ) ) ); ?></span>
            </h4>
        </div>
    <?php endif; ?>

    <div class="mp_pdf_footer" style="float: left; width: 100%">
        <?php
        $wtbm_term_title = WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_tc_title');
        $wtbm_term_text = WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_tc_text');
        if ($wtbm_term_title) {
            ?>
            <h4><?php echo esc_html($wtbm_term_title); ?></h4>
            <?php
        }
        if ($wtbm_term_text) { ?>
            <span><?php echo esc_html($wtbm_term_text); ?></span>
        <?php } ?>
    </div>
</div>