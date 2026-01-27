<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}  // if direct access
$order_id = $order_id ?? 0;
if ( $order_id > 0 ) {
    $css_url=MPCRBM_PLUGIN_URL_PRO . '/assets/admin/mpcrbm_pdf.css' ;
    printf(
        '<link rel="stylesheet" href="%s" />',
        esc_url( $css_url )
    );
    ?>
    <html>
<body>
    <?php
    $bg_color   = WTBM_Function::get_settings( 'wtbm_pdf_settings', 'pdf_bg_color', '#fbfbfb' );
    $text_color = WTBM_Function::get_settings( 'wtbm_pdf_settings', 'pdf_text_color', '#000' );
    $bg_image   = WTBM_Function::get_settings( 'wtbm_pdf_settings', 'pdf_bg' );
    ?>
    <style type="text/css">
        .mp_pdf {
            color: <?php echo esc_attr($text_color); ?>;
        <?php if (!empty($bg_image) && wp_get_attachment_url($bg_image)): ?>
            background: <?php echo esc_attr($bg_color); ?> url(<?php echo esc_url($bg_image); ?>) no-repeat center;
            background-size: cover;
        <?php else: ?>
            background-color: <?php echo esc_attr($bg_color); ?>;
        <?php endif; ?>
        }
    </style>

    <?php

    // Get booking posts based on WooCommerce order ID
    $booking_posts = get_posts(array(
        'post_type' => 'wtbm_booking',
        'post_status' => 'publish',
        'numberposts' => -1,
        'meta_query' => array(
            array(
                'key' => 'wtbm_order_id',
                'value' => $order_id,
                'compare' => '='
            )
        )
    ));

    if (empty($booking_posts)) {
        // If no booking posts found, create a single PDF with order data
        $ticket_id = 0; // Use 0 to indicate we're using WooCommerce order data directly
        include MPCRBM_PLUGIN_DIR_PRO . '/template_pro/pdf/default.php';
    } else {
        $total_guest = count($booking_posts);
        $i = 0;
        foreach ($booking_posts as $booking_post) {
            $ticket_id = $booking_post->ID;
//            include MPCRBM_PLUGIN_DIR_PRO . '/template_pro/pdf/default.php';
            include MPCRBM_PLUGIN_DIR_PRO . '/template_pro/pdf/ticket_pdf.php';
            if ($i < $total_guest - 1) {
                $i++;
                ?>
                <div class="page_break"></div>
                <?php
            }
        }
    }
}
