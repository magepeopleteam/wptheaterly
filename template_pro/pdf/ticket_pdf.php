<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$wtbm_attendee_id = $wtbm_ticket_id ?? 0;

$wtbm_pin = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_pin');
$wtbm_order_date = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_order_date');
$wtbm_order_time = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_order_time');
$wtbm_order_id = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_order_id');
$wtbm_order_status = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_order_status');
$wtbm_payment_method = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_payment_method');
$wtbm_number_of_seats = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_number_of_seats');
$wtbm_seat_ids = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_seat_ids');
$wtbm_seat_numbers = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_seats');
$wtbm_seat_names = '';
if( !empty( $wtbm_seat_numbers ) && is_array( $wtbm_seat_numbers ) ){
    $wtbm_seat_names = implode(", ", $wtbm_seat_numbers );
}
$wtbm_theater_id = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_theater_id');
$wtbm_theater_name = get_the_title($wtbm_theater_id);
$wtbm_movie_id = WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_movie_id');
$wtbm_movie_name = get_the_title($wtbm_movie_id);

$wtbm_logo_img_id = WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_logo');
if( $wtbm_logo_img_id ){
    $wtbm_logo_url = wp_get_attachment_url($wtbm_logo_img_id);
}else{
    $wtbm_logo_url = 'https://cdn-icons-png.flaticon.com/512/705/705062.png';
}
$wtbm_pdf_phone = WTBM_Function::get_settings('wtbm_pdf_settings', 'pdf_phone');

?>
<!DOCTYPE html>
<html>
<head>
    <style>
        .mp_pdf {
            color: <?php echo esc_attr( $wtbm_text_color ); ?>;
        <?php if (!empty( $wtbm_bg_image ) && wp_get_attachment_url( $wtbm_bg_image ) ): ?>
            background: <?php echo esc_attr( $wtbm_bg_color ); ?> url(<?php echo esc_url( $wtbm_bg_image ); ?>) no-repeat center;
            background-size: cover;
        <?php else: ?>
            background-color: <?php echo esc_attr( $wtbm_bg_color ); ?>;
        <?php endif; ?>
        }
        .wtbm_pdf_container {
            width: 100%;
            font-family: 'Helvetica', sans-serif;
            color: #ffffff;
        }

        .wtbm_pdf_ticket_wrapper {
            width: 700px;
            margin: 0 auto;
            background-color: #333;
            padding: 10px;
        }

        .wtbm_pdf_ticket {
            width: 100%;
            border-collapse: collapse;
            border-radius: 15px;
            overflow: hidden;
        }

        .wtbm_pdf_left_section {
            background-color: #9e3d34;
            width: 60%;
            padding: 30px;
            position: relative;
            vertical-align: top;
        }

        .wtbm_pdf_right_section {
            background-color: #f5eed7;
            width: 40%;
            padding: 30px;
            color: #9e3d34;
            vertical-align: top;
            text-align: center;
            border-left: 2px dashed #9e3d34;
        }

        .wtbm_pdf_title {
            font-size: 20px;
            font-weight: bold;
            line-height: 1;
            margin: 10px 0;
            text-transform: uppercase;
        }

        .wtbm_pdf_divider {
            border-top: 2px solid #ffffff;
            margin: 15px 0;
        }

        .wtbm_pdf_info_table {
            width: 100%;
            color: #ffffff;
            font-size: 14px;
            margin-top: 15px;
        }

        .wtbm_pdf_label {
            font-weight: bold;
            text-transform: uppercase;
            text-align: left;
        }

        .wtbm_pdf_qr_code {
            width: 120px;
            margin-bottom: 20px;
        }

        .wtbm_pdf_details_right {
            text-align: left;
            font-size: 13px;
            line-height: 1.6;
            margin-left: 10px;
        }

        .wtbm_pdf_website_btn {
            display: block;
            background-color: #9e3d34;
            color: #ffffff;
            text-decoration: none;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            margin-top: 15px;
            text-align: center;
        }

        /* Scalloped edge simulation for mPDF */
        .wtbm_pdf_ticket_container {
            background: radial-gradient(circle at 0 50%, transparent 10px, #9e3d34 11px),
            radial-gradient(circle at 100% 50%, transparent 10px, #f5eed7 11px);
        }
    </style>
</head>
<body>
<div class="mp_pdf">
    <div class="wtbm_pdf_container">
        <div class="wtbm_pdf_ticket_wrapper">
            <table class="wtbm_pdf_ticket" cellpadding="0" cellspacing="0">
                <tr>
                    <td class="wtbm_pdf_left_section">
                        <table width="100%">
                            <tr>
                                <td>
                                    <img src="<?php echo esc_url( $wtbm_logo_url );?>" width="50" alt="icon">
                                </td>
                                <td style="text-align: right; color: #f5eed7; font-size: 24px;"><?php echo esc_attr( $wtbm_pin );?></td>
                            </tr>
                        </table>

                        <div class="wtbm_pdf_divider"></div>
                        <div class="wtbm_pdf_title"><?php echo esc_attr( $wtbm_movie_name );?></div>
                        <div class="wtbm_pdf_divider"></div>

                        <table class="wtbm_pdf_info_table">
                            <tr>
                                <td class="wtbm_pdf_label" width="40%"><?php esc_attr_e( 'DATE', 'wptheaterly' );?>: <?php echo esc_attr( $wtbm_order_date );?></td>
                                <td class="wtbm_pdf_label"><?php esc_attr_e( 'TIME', 'wptheaterly' );?>: <?php echo esc_html( $wtbm_order_time );?></td>

                            </tr>
                            <tr>
                                <td class="wtbm_pdf_label"><?php esc_attr_e( 'THEATER', 'wptheaterly' );?>: <?php echo esc_attr( $wtbm_theater_name );?></td>
                                <td class="wtbm_pdf_label"><?php esc_attr_e( 'SEAT', 'wptheaterly' );?>: <?php echo esc_html($wtbm_seat_names);?></td>
                            </tr>
                            <tr>
                                <td class="wtbm_pdf_label"><?php esc_attr_e( 'Total Bill', 'wptheaterly' );?>: <?php echo wp_kses_post( wc_price( WTBM_Function::get_post_info($wtbm_attendee_id, 'wtbm_tp' ) ) ); ?></td>
                            </tr>
                        </table>
                    </td>

                    <td class="wtbm_pdf_right_section">
                        <table width="100%">
                            <tr>
                                <td>
                                    <img src="<?php echo esc_url( $wtbm_logo_url );?>" width="50" alt="icon">
                                </td>
                                <td style="text-align: right; color: #333333; font-size: 24px;"><?php echo esc_attr( $wtbm_pin );?></td>
                            </tr>
                        </table>
                        <!--                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=Ticket123&color=9e3d34&bgcolor=f5eed7" class="wtbm_pdf_qr_code" alt="QR">-->

                        <div class="wtbm_pdf_details_right">
                            <div class="wtbm_pdf_label"><?php esc_attr_e( 'DATE', 'wptheaterly' );?>: <?php echo esc_attr( $wtbm_order_date );?></div>
                            <div class="wtbm_pdf_label"><?php esc_attr_e( 'TIME', 'wptheaterly' );?> : <?php echo esc_html( $wtbm_order_time );?></div>
                            <div class="wtbm_pdf_label"><?php esc_attr_e( 'THEATER', 'wptheaterly' );?>: <?php echo esc_attr( $wtbm_theater_name );?></div>
                            <div class="wtbm_pdf_label"><?php esc_attr_e( 'SEAT', 'wptheaterly' );?>: <?php echo esc_html($wtbm_seat_names);?></div>
                            <div class="wtbm_pdf_label"><?php esc_attr_e( 'Total Bill', 'wptheaterly' );?>: <?php echo wp_kses_post( wc_price(WTBM_Function::get_post_info( $wtbm_attendee_id, 'wtbm_tp' ) ) ); ?></div>
                        </div>

                        <div class="wtbm_pdf_website_btn"><?php echo esc_attr( get_site_url() );?></div>
                        <div class="wtbm_pdf_website_btn"><?php echo esc_attr( $wtbm_pdf_phone );?></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

</body>
</html>
