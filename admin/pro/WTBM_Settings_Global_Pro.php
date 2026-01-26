<?php
/*
	* @Author 		engr.sumonazma@gmail.com
	* Copyright: 	mage-people.com
	*/

if (!defined('ABSPATH')) {
    die;
} // Cannot access pages directly.
if (!class_exists('WTBM_Settings_Global_Pro')) {
    class WTBM_Settings_Global_Pro
    {

        public function __construct()
        {
            add_filter('mpcrbm_settings_sec_reg', array($this, 'settings_sec_reg'), 80);
            add_filter('mpcrbm_settings_sec_fields', array($this, 'settings_sec_fields'), 80);
        }

        public function settings_sec_reg($default_sec): array
        {
            $sections = array(
                array(
                    'id' => 'wtbm_pdf_settings',
                    'icon' => 'far fa-file-pdf',
                    'title' => esc_html__('PDF Settings', 'wptheaterly')
                ),
                array(
                    'id' => 'mpcrbm_email_settings',
                    'icon' => 'far fa-envelope',
                    'title' => esc_html__('Email Settings', 'wptheaterly')
                ),
            );
            return array_merge($default_sec, $sections);
        }

        public function settings_sec_fields($default_fields = []): array
        {
            $settings_fields = array(
                'wtbm_pdf_settings' => array(
                    array(
                        'name' => 'pdf_logo',
                        'label' => esc_html__('Logo', 'wptheaterly'),
                        'desc' => esc_html__('Add your custom logo what will appear on the PDF Ticket', 'wptheaterly'),
                        'type' => 'file',
                        'default' => ''
                    ),
                    array(
                        'name' => 'pdf_bg',
                        'label' => esc_html__('Background Image', 'wptheaterly'),
                        'desc' => esc_html__('You can add a custom Background Image for Pdf. The image width should be 680px', 'wptheaterly'),
                        'type' => 'file',
                        'default' => ''
                    ),
                    array(
                        'name' => 'pdf_bg_color',
                        'label' => esc_html__('Background Color', 'wptheaterly'),
                        'desc' => esc_html__('PDF Ticket Background Color', 'wptheaterly'),
                        'type' => 'color',
                        'default' => ''
                    ),
                    array(
                        'name' => 'pdf_text_color',
                        'label' => esc_html__('Text Color', 'wptheaterly'),
                        'desc' => esc_html__('PDF Ticket Text Color', 'wptheaterly'),
                        'type' => 'color',
                        'default' => ''
                    ),
                    array(
                        'name' => 'pdf_address',
                        'label' => esc_html__('Company address', 'wptheaterly'),
                        'desc' => esc_html__('Add your company address', 'wptheaterly'),
                        'type' => 'textarea'
                    ),
                    array(
                        'name' => 'pdf_phone',
                        'label' => esc_html__('Phone Number', 'wptheaterly'),
                        'desc' => esc_html__('Add company phone number here', 'wptheaterly'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'pdf_tc_title',
                        'label' => esc_html__('Terms & Condition Title', 'wptheaterly'),
                        'desc' => esc_html__('This T & C Text will display in the ticket footer', 'wptheaterly'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'pdf_tc_text',
                        'label' => esc_html__('Terms & Condition Text', 'wptheaterly'),
                        'desc' => esc_html__('This T & C Text will display in the ticket footer', 'wptheaterly'),
                        'type' => 'wysiwyg',
                        'default' => ''
                    ),
                ),
                'mpcrbm_email_settings' => array(
                    array(
                        'name' => 'pdf_send_status',
                        'label' => __('Send Pdf?', 'wptheaterly'),
                        'desc' => __('Please select which order status data you want to export', 'wptheaterly'),
                        'type' => 'select',
                        'default' => 'yes',
                        'options' => array(
                            'yes' => esc_html__('Yes', 'wptheaterly'),
                            'no' => esc_html__('No', 'wptheaterly')
                        )
                    ),
                    array(
                        'name' => 'pdf_email_status',
                        'label' => esc_html__('Send Email on', 'wptheaterly'),
                        'desc' => esc_html__('Send email with the ticket as attachment when these order status comes', 'wptheaterly'),
                        'type' => 'multicheck',
                        'options' => array(
                            'on-hold' => esc_html__('On Hold', 'wptheaterly'),
                            'pending' => esc_html__('Pending', 'wptheaterly'),
                            'processing' => esc_html__('Processing', 'wptheaterly'),
                            'completed' => esc_html__('Completed', 'wptheaterly'),
                        )
                    ),
                    array(
                        'name' => 'pdf_email_subject',
                        'label' => esc_html__('Email Subject', 'wptheaterly'),
                        'desc' => esc_html__('Set email subject here', 'wptheaterly'),
                        'type' => 'text',
                        'default' => MPCRBM_Function::get_name(),
                    ),
                    array(
                        'name' => 'pdf_email_content',
                        'label' => esc_html__('Email Content', 'wptheaterly'),
                        'desc' => '<span style="color: red">' . esc_html__('Please use this shortcode for get real data.', 'wptheaterly') . '</span> <br><br>' . esc_html__('Customer Name:', 'wptheaterly') . '{customer_name} <br>' . MPCRBM_Function::get_name() . '{service_name} <br>' . esc_html__('Date:', 'wptheaterly') . '{service_date} <br>' . esc_html__(' Order ID: ', 'wptheaterly') . '{order_id} <br>',
                        'type' => 'wysiwyg',
                        'default' => 'Hello {customer_name}, <br><br> Thank you for registering. <br><br> Please download pdf  in this attachment. <br><br> Please carry out printing copy. <br><br> Here is details of Booking: <br><br> Service Name: {service_name} <br><br> Date: {service_date}'
                    ),
                    array(
                        'name' => 'pdf_admin_notification_email',
                        'label' => esc_html__('Admin Notification Email', 'wptheaterly'),
                        'desc' => esc_html__('Please enter an email address if admin want to get a pdf ticket after an order placed.', 'wptheaterly'),
                        'type' => 'text',
                        'default' => ''
                    )
                ),
            );
            return array_merge($default_fields, $settings_fields);
        }

    }

//    new WTBM_Settings_Global_Pro();
}