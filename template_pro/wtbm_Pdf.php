<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}  // if direct access
$wtbm_order_id = $order_id ?? 0;
if ( $wtbm_order_id > 0 ) { ?>
    <html>
<body>
    <?php
    $wtbm_bg_color   = WTBM_Function::get_settings( 'wtbm_pdf_settings', 'pdf_bg_color', '#fbfbfb' );
    $wtbm_text_color = WTBM_Function::get_settings( 'wtbm_pdf_settings', 'pdf_text_color', '#000' );
    $wtbm_bg_image   = WTBM_Function::get_settings( 'wtbm_pdf_settings', 'pdf_bg' );
    ?>
    <style type="text/css">
        .mp_pdf {
            color: <?php echo esc_attr($wtbm_text_color); ?>;
        <?php if (!empty($wtbm_bg_image) && wp_get_attachment_url($wtbm_bg_image)): ?>
            background: <?php echo esc_attr($wtbm_bg_color); ?> url(<?php echo esc_url($wtbm_bg_image); ?>) no-repeat center;
            background-size: cover;
        <?php else: ?>
            background-color: <?php echo esc_attr($wtbm_bg_color); ?>;
        <?php endif; ?>
        }
    </style>

    <?php

    // Get booking posts based on WooCommerce order ID
    $wtbm_booking_posts = get_posts(array(
        'post_type' => 'wtbm_booking',
        'post_status' => 'publish',
        'numberposts' => -1,
        'meta_query' => array(
            array(
                'key' => 'wtbm_order_id',
                'value' => $wtbm_order_id,
                'compare' => '='
            )
        )
    ));

    if (empty($wtbm_booking_posts)) {
        // If no booking posts found, create a single PDF with order data
        $wtbm_ticket_id = 0; // Use 0 to indicate we're using WooCommerce order data directly
        include MPCRBM_PLUGIN_DIR_PRO . '/template_pro/pdf/default.php';
    } else {
        $wtbm_total_guest = count($wtbm_booking_posts);
        $wtbm_i = 0;
        foreach ($wtbm_booking_posts as $booking_post) {
            $wtbm_ticket_id = $booking_post->ID;
//            include MPCRBM_PLUGIN_DIR_PRO . '/template_pro/pdf/default.php';
            include MPCRBM_PLUGIN_DIR_PRO . '/template_pro/pdf/ticket_pdf.php';
            if ($wtbm_i < $wtbm_total_guest - 1) {
                $wtbm_i++;
                ?>
                <div class="page_break"></div>
                <?php
            }
        }
    }
}
