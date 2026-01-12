<?php
// Template Name: Default Theme
?>
<?php
$attendee_id = $ticket_id ?? 0;

// If no attendee_id, try to get data from WooCommerce order directly
if ($attendee_id == 0 && isset($order_id)) {
    $wc_order = wc_get_order($order_id);
    if ($wc_order) {
        // We'll handle WooCommerce order data display
        $use_wc_data = true;
    }
}
?>
<div class='mp_pdf'>
    <div class="mp_pdf_header">
        <?php WTBM_Pro_Pdf::pdf_logo(); ?>
        <h5><?php echo WTBM_Function::get_settings('mpcrbm_pdf_settings', 'pdf_address'); ?> </h5>
        <h6><?php echo WTBM_Function::get_settings('mpcrbm_pdf_settings', 'pdf_phone'); ?> </h6>
    </div>

    <?php if (isset($use_wc_data) && $use_wc_data && $wc_order): ?>
        <!-- WooCommerce Order Data Display -->
        <div class="mp_pdf_body">
            <h4><?php esc_html_e('Booking Information', 'car-rental-manager-pro'); ?></h4>
            <div class="divider"></div>
            <ul class="mp_list">
                <li>
                    <strong class="min_150"><?php esc_html_e('Order ID:', 'car-rental-manager-pro'); ?></strong>&nbsp;#<?php echo esc_html($wc_order->get_id()); ?>
                </li>
                <li>
                    <strong class="min_150"><?php esc_html_e('Order Date:', 'car-rental-manager-pro'); ?></strong> <?php echo esc_html($wc_order->get_date_created()->date('Y-m-d H:i:s')); ?>
                </li>
                <li>
                    <strong class="min_150"><?php esc_html_e('Order Status:', 'car-rental-manager-pro'); ?></strong> <?php echo esc_html($wc_order->get_status()); ?>
                </li>
                <li>
                    <strong class="min_150"><?php esc_html_e('Payment Method:', 'car-rental-manager-pro'); ?></strong> <?php echo esc_html($wc_order->get_payment_method_title()); ?>
                </li>
            </ul>
        </div>

        <div class="mp_pdf_body">
            <h5><?php esc_html_e('Billing Information', 'car-rental-manager-pro'); ?></h5>
            <div class="divider"></div>
            <ul class="mp_list">
                <li>
                    <strong class="min_100"><?php esc_html_e('Name', 'car-rental-manager-pro'); ?>:</strong> <?php echo esc_html($wc_order->get_billing_first_name() . ' ' . $wc_order->get_billing_last_name()); ?>
                </li>
                <li>
                    <strong class="min_100"><?php esc_html_e('E-mail', 'car-rental-manager-pro'); ?>:</strong> <?php echo esc_html($wc_order->get_billing_email()); ?>
                </li>
                <li>
                    <strong class="min_100"><?php esc_html_e('Phone', 'car-rental-manager-pro'); ?>:</strong> <?php echo esc_html($wc_order->get_billing_phone()); ?>
                </li>
                <li>
                    <strong class="min_100"><?php esc_html_e('Address', 'car-rental-manager-pro'); ?>:</strong> <?php echo wp_kses_post($wc_order->get_formatted_billing_address()); ?>
                </li>
            </ul>
        </div>

        <div class="mp_pdf_body">
            <h5><?php esc_html_e('Order Items', 'car-rental-manager-pro'); ?></h5>
            <div class="divider"></div>
            <ul class="mp_list">
                <?php foreach ($wc_order->get_items() as $item): ?>
                    <li class="justifyBetween">
                        <p class="min_150"><?php echo esc_html($item->get_name()); ?></p>
                        <span>x<?php echo esc_html($item->get_quantity()); ?> | <?php echo wc_price($item->get_total()); ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="divider"></div>
            <h4>
                <span style="float: left;"><?php esc_html_e('Total Bill:', 'car-rental-manager-pro'); ?></span>
                <span style="text-align: right;float: right;"><?php echo wc_price($wc_order->get_total()); ?></span>
            </h4>
        </div>
    <?php else: ?>
        <!-- Original booking post data display -->
        <div class="mp_pdf_body">
            <?php WTBM_Layout_Pro::service_info($attendee_id); ?>
        </div>
        <div class="mp_pdf_body">
            <?php WTBM_Layout_Pro::billing_info($attendee_id); ?>
        </div>
        <div class="mp_pdf_body">
            <?php WTBM_Layout_Pro::order_info($attendee_id); ?>
            <div class="divider"></div>
            <h4>
                <span style="float: left;"><?php esc_html_e('Total Bill:', 'car-rental-manager-pro'); ?></span>
                <span style="text-align: right;float: right;"><?php echo wc_price(WTBM_Function::get_post_info($attendee_id, 'mpcrbm_tp')); ?></span>
            </h4>
        </div>
    <?php endif; ?>

    <div class="mp_pdf_footer">
        <?php
        $term_title = WTBM_Function::get_settings('mpcrbm_pdf_settings', 'pdf_tc_title');
        $term_text = WTBM_Function::get_settings('mpcrbm_pdf_settings', 'pdf_tc_text');
        if ($term_title) {
            ?>
            <h4><?php echo esc_html($term_title); ?></h4>
            <?php
        }
        if ($term_text) { ?>
            <span><?php echo html_entity_decode($term_text); ?></span>
        <?php } ?>
    </div>
</div>