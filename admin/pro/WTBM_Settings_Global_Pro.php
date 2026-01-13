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
                    'title' => esc_html__('PDF Settings', 'car-rental-manager-pro')
                ),
                array(
                    'id' => 'mpcrbm_email_settings',
                    'icon' => 'far fa-envelope',
                    'title' => esc_html__('Email Settings', 'car-rental-manager-pro')
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
                        'label' => esc_html__('Logo', 'car-rental-manager-pro'),
                        'desc' => esc_html__('Add your custom logo what will appear on the PDF Ticket', 'car-rental-manager-pro'),
                        'type' => 'file',
                        'default' => ''
                    ),
                    array(
                        'name' => 'pdf_bg',
                        'label' => esc_html__('Background Image', 'car-rental-manager-pro'),
                        'desc' => esc_html__('You can add a custom Background Image for Pdf. The image width should be 680px', 'car-rental-manager-pro'),
                        'type' => 'file',
                        'default' => ''
                    ),
                    array(
                        'name' => 'pdf_bg_color',
                        'label' => esc_html__('Background Color', 'car-rental-manager-pro'),
                        'desc' => esc_html__('PDF Ticket Background Color', 'car-rental-manager-pro'),
                        'type' => 'color',
                        'default' => ''
                    ),
                    array(
                        'name' => 'pdf_text_color',
                        'label' => esc_html__('Text Color', 'car-rental-manager-pro'),
                        'desc' => esc_html__('PDF Ticket Text Color', 'car-rental-manager-pro'),
                        'type' => 'color',
                        'default' => ''
                    ),
                    array(
                        'name' => 'pdf_address',
                        'label' => esc_html__('Company address', 'car-rental-manager-pro'),
                        'desc' => esc_html__('Add your company address', 'car-rental-manager-pro'),
                        'type' => 'textarea'
                    ),
                    array(
                        'name' => 'pdf_phone',
                        'label' => esc_html__('Phone Number', 'car-rental-manager-pro'),
                        'desc' => esc_html__('Add company phone number here', 'car-rental-manager-pro'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'pdf_tc_title',
                        'label' => esc_html__('Terms & Condition Title', 'car-rental-manager-pro'),
                        'desc' => esc_html__('This T & C Text will display in the ticket footer', 'car-rental-manager-pro'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'name' => 'pdf_tc_text',
                        'label' => esc_html__('Terms & Condition Text', 'car-rental-manager-pro'),
                        'desc' => esc_html__('This T & C Text will display in the ticket footer', 'car-rental-manager-pro'),
                        'type' => 'wysiwyg',
                        'default' => ''
                    ),
                ),
                'mpcrbm_email_settings' => array(
                    array(
                        'name' => 'pdf_send_status',
                        'label' => __('Send Pdf?', 'car-rental-manager-pro'),
                        'desc' => __('Please select which order status data you want to export', 'car-rental-manager-pro'),
                        'type' => 'select',
                        'default' => 'yes',
                        'options' => array(
                            'yes' => esc_html__('Yes', 'car-rental-manager-pro'),
                            'no' => esc_html__('No', 'car-rental-manager-pro')
                        )
                    ),
                    array(
                        'name' => 'pdf_email_status',
                        'label' => esc_html__('Send Email on', 'car-rental-manager-pro'),
                        'desc' => esc_html__('Send email with the ticket as attachment when these order status comes', 'car-rental-manager-pro'),
                        'type' => 'multicheck',
                        'options' => array(
                            'on-hold' => esc_html__('On Hold', 'car-rental-manager-pro'),
                            'pending' => esc_html__('Pending', 'car-rental-manager-pro'),
                            'processing' => esc_html__('Processing', 'car-rental-manager-pro'),
                            'completed' => esc_html__('Completed', 'car-rental-manager-pro'),
                        )
                    ),
                    array(
                        'name' => 'pdf_email_subject',
                        'label' => esc_html__('Email Subject', 'car-rental-manager-pro'),
                        'desc' => esc_html__('Set email subject here', 'car-rental-manager-pro'),
                        'type' => 'text',
                        'default' => MPCRBM_Function::get_name(),
                    ),
                    array(
                        'name' => 'pdf_email_content',
                        'label' => esc_html__('Email Content', 'car-rental-manager-pro'),
                        'desc' => '<span style="color: red">' . esc_html__('Please use this shortcode for get real data.', 'car-rental-manager-pro') . '</span> <br><br>' . esc_html__('Customer Name:', 'car-rental-manager-pro') . '{customer_name} <br>' . MPCRBM_Function::get_name() . '{service_name} <br>' . esc_html__('Date:', 'car-rental-manager-pro') . '{service_date} <br>' . esc_html__(' Order ID: ', 'car-rental-manager-pro') . '{order_id} <br>',
                        'type' => 'wysiwyg',
                        'default' => 'Hello {customer_name}, <br><br> Thank you for registering. <br><br> Please download pdf  in this attachment. <br><br> Please carry out printing copy. <br><br> Here is details of Booking: <br><br> Service Name: {service_name} <br><br> Date: {service_date}'
                    ),
                    array(
                        'name' => 'pdf_admin_notification_email',
                        'label' => esc_html__('Admin Notification Email', 'car-rental-manager-pro'),
                        'desc' => esc_html__('Please enter an email address if admin want to get a pdf ticket after an order placed.', 'car-rental-manager-pro'),
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