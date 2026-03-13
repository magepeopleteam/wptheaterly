<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$wtbm_attendee_id       = $wtbm_ticket_id ?? 0;
$wtbm_pin               = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_pin');
$wtbm_order_date        = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_order_date');
$wtbm_order_time        = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_order_time');
$wtbm_order_id          = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_order_id');
$wtbm_order_status      = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_order_status');
$wtbm_payment_method    = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_payment_method');
$wtbm_number_of_seats   = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_number_of_seats');
$wtbm_seat_ids          = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_seat_ids');
$wtbm_seat_numbers      = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_seats');
$wtbm_seat_names        = '';
if( !empty( $wtbm_seat_numbers ) && is_array( $wtbm_seat_numbers ) ){
    $wtbm_seat_names = implode(", ", $wtbm_seat_numbers );
}
$wtbm_theater_id        = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_theater_id');
$wtbm_theater_name      = get_the_title($wtbm_theater_id);
$wtbm_movie_id          = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_movie_id');
$wtbm_movie_name        = get_the_title($wtbm_movie_id);
$wtbm_poster_id              = get_post_meta( $wtbm_movie_id, 'wtbp_movie_poster_id', true );
$wtbm_poster_url             = wp_get_attachment_url($wtbm_poster_id);
$wtbm_logo_img_id       = WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_logo');
if( $wtbm_logo_img_id ){
    $wtbm_logo_url      = wp_get_attachment_url($wtbm_logo_img_id);
}else{
    $wtbm_logo_url = 'https://cdn-icons-png.flaticon.com/512/705/705062.png';
}
$wtbm_pdf_phone         = WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_phone');
$wtbm_pdf_address            = WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_address');
$wtbm_pdf_term_title         = WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_tc_title');
$wtbm_pdf_term_text          = WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_tc_text');

?>
<?php
// Your existing PHP variables remain the same
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            padding: 30px;
        }

        .ticket-wrapper {
            width: 800px;
            margin: 0 auto;
            background: #fff;
            /* border-radius: 12px; */
            overflow: hidden;
            /* box-shadow: 0 15px 35px rgba(0,0,0,0.1); */
            border: 1px solid #e0e0e0;
        }

        /* Top Accent - Navy and Gold Theme */
        .top-accent {
            height: 5px;
            background: linear-gradient(90deg, #001f3f, #FFD700);
        }

        .header {
            display: flex;
            justify-content: space-between;
            padding: 25px 40px;
            background: #fafafa;
            border-bottom: 1px solid #eee;
        }

        .company-logo img {
            max-height: 60px;
        }

        .company-info {
            text-align: right;
        }

        .company-name {
            font-size: 14px;
            font-weight: bold;
            color: #001f3f;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .company-address {
            font-size: 10px;
            color: #777;
            line-height: 1.3;
        }

        /* 2-Column Body */
        .ticket-content {
            display: flex;
            padding: 30px 40px;
            gap: 40px;
        }

        /* Left Column: Ticket Details */
        .details-col {
            flex: 1;
        }

        .movie-label {
            font-size: 10px;
            color: #FFD700;
            background: #001f3f;
            padding: 3px 10px;
            border-radius: 4px;
            text-transform: uppercase;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }

        .movie-title {
            font-size: 32px;
            font-weight: 800;
            margin: 0 0 20px 0;
            color: #111;
            border-left: 4px solid #001f3f;
            padding-left: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .info-box .label {
            display: block;
            font-size: 9px;
            text-transform: uppercase;
            color: #999;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .info-box .value {
            font-size: 14px;
            font-weight: bold;
            color: #222;
        }

        /* Right Column: Movie Poster */
        .poster-col {
            flex: 0 0 200px;
            text-align: right;
        }

        .poster-col img {
            width: 20px;
            height: 280px;
            object-fit: cover;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            border: 1px solid #ddd;
        }

        /* QR & Verification Footer */
        .scan-strip {
            background: #edededff;
            color: #fff;
            padding: 20px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .qr-wrapper {
            background: #fff;
            padding: 8px;
            border-radius: 5px;
        }

        .booking-summary {
            text-align: right;
        }

        .order-no {
            font-size: 11px;
            opacity: 0.8;
            margin-bottom: 5px;
        }

        .pin-display {
            font-size: 28px;
            font-weight: 900;
            color: #FFD700; /* Gold PIN */
            letter-spacing: 3px;
        }

        /* Terms and Conditions */
        .terms-section {
            padding: 20px 40px;
            font-size: 10px;
            color: #292929ff;
            background: #fbfbfbff;
            border-top: 1px solid #eee;
        }

        .terms-section strong {
            color: #444;
            display: block;
            margin-bottom: 5px;
            text-transform: uppercase;
        }


    </style>
</head>
<body>

    <div class="ticket-wrapper">
        <div class="top-accent"></div>
        <div class="header">
            <table style="width: 100%; border-collapse: collapse; padding: 30px 40px;">
                <tr>
                    <td style="width: 70%; vertical-align: top; padding-right: 20px;">            
                        <div class="company-logo">
                            <img src="<?php echo esc_url( $wtbm_logo_url );?>" alt="Logo">
                        </div>        
                    </td>
                    <td style="width: 30%; vertical-align: top; text-align: right;padding-top: 15px;">           
                    <div class="company-info">
                            <p class="company-name"><?php bloginfo('name'); ?></p>
                            <div class="company-address">
                                <?php echo wp_kses_post($wtbm_pdf_address); ?><br>
                                <strong><?php echo esc_html($wtbm_pdf_phone); ?></strong>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>           
        </div>
        <div class="ticket-content">
        <h1 style="font-size: 32px; font-weight: 800; margin: 10px 0 10px 0; border-bottom: 1px dashed #b7b7b7ff; padding-bottom: 5px;display:block;"><?php echo esc_html($wtbm_movie_name); ?></h1>           
        <table style="width: 100%; border-collapse: collapse; padding: 30px 40px;">
            <tr>
                <td style="width: 70%; vertical-align: top; padding-right: 20px;">
                    
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr>
                            <td style="padding-bottom: 15px;">
                                <span style="display: block; font-size: 13px; color: #101010ff; text-transform: uppercase;font-weight: bold;">Date</span>
                                <span style="font-size: 14px; "><?php echo esc_html($wtbm_order_date); ?></span>
                            </td>
                            <td style="padding-bottom: 15px;">
                                <span style="display: block; font-size: 13px; color: #101010ff; text-transform: uppercase;font-weight: bold;">Show Time</span>
                                <span style="font-size: 14px; "><?php echo esc_html($wtbm_order_time); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-bottom: 15px;">
                                <span style="display: block; font-size: 13px; color: #101010ff; text-transform: uppercase;font-weight: bold;">Seats</span>
                                <span style="font-size: 14px;  color: #001f3f;"><?php echo esc_html($wtbm_seat_names); ?></span>
                            </td>
                            <td style="padding-bottom: 15px;">
                                <span style="display: block; font-size: 13px; color: #101010ff; text-transform: uppercase;font-weight: bold;">Quantity</span>
                                <span style="font-size: 14px; "><?php echo esc_html($wtbm_number_of_seats); ?> Person(s)</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-bottom: 15px;">
                                <span style="display: block; font-size: 13px; color: #101010ff; text-transform: uppercase;font-weight: bold;"><?php esc_html_e('THEATER:', 'wptheaterly'); ?></span>
                                <span style="font-size: 14px;  color: #001f3f;"><?php echo esc_html($wtbm_theater_name); ?></span>
                            </td>
                            <td style="padding-bottom: 15px;">
                                <span style="display: block; font-size: 13px; color: #101010ff; text-transform: uppercase;font-weight: bold;"><?php esc_html_e('Price:', 'wptheaterly'); ?></span>
                                <span style="font-size: 14px; "><?php echo wp_kses_post( wc_price(WTBM_Function::get_post_info( $wtbm_attendee_id, 'wtbm_tp' ) ) ); ?></span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-bottom: 15px;">
                                <span style="display: block; font-size: 13px; color: #101010ff; text-transform: uppercase;font-weight: bold;"><?php esc_html_e('ORDER ID:', 'wptheaterly'); ?></span>
                                <span style="font-size: 14px;  color: #001f3f;"><?php echo esc_html($wtbm_order_id); ?></span>
                            </td>
                            <td style="padding-bottom: 15px;">
                                <span style="display: block; font-size: 13px; color: #101010ff; text-transform: uppercase;font-weight: bold;"><?php esc_html_e('Payment Method:', 'wptheaterly'); ?></span>
                                <span style="font-size: 14px; "><?php echo esc_html($wtbm_payment_method); ?></span>
                                
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-bottom: 15px;" colspan='2'>                                            
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=70x70&data=PIN-<?php echo esc_html($wtbm_pin); ?>" alt="QR Code"><br/>
                                <span style="display: block; font-size: 10px; color: #101010ff; text-transform: uppercase;font-weight: bold;"><?php esc_html_e('PIN:', 'wptheaterly'); ?></span>
                                <span style="font-size: 10px;  color: #001f3f;"><?php echo esc_html($wtbm_pin); ?></span>                         
                            </td>
                        </tr>                
                    </table>
                </td>
                <td style="width: 30%; vertical-align: top; text-align: right;">
                    <?php if($wtbm_poster_url): ?>
                        <img src="<?php echo esc_url($wtbm_poster_url); ?>" style="width: 160px; height: 230px; border-radius: 8px; border: 1px solid #ddd;" alt="Poster">
                    <?php else: ?>
                        <div style="width: 160px; height: 230px; background: #f0f0f0; border: 1px dashed #bbb; text-align: center; padding-top: 100px; color: #ccc;">No Poster</div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        </div>
        <div class="terms-section">
            <strong style='font-size:15px'><?php echo esc_html($wtbm_pdf_term_title); ?></strong>
            <div style="line-height: 1.4;">
                <?php echo wp_kses_post($wtbm_pdf_term_text); ?>
            </div>
        </div>
    </div>
</body>
</html>