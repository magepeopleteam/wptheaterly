<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('WTBM_Settings_Global')) {
		class WTBM_Settings_Global {
			protected $settings_api;
			public function __construct() {
				$this->settings_api = new WTBM_Setting_API;
				add_action('admin_menu', array($this, 'global_settings_menu'), 20);
				add_action('admin_init', array($this, 'admin_init'));
				add_filter('mptrs_settings_sec_reg', array($this, 'settings_sec_reg'), 10);
				add_filter('mptrs_settings_sec_fields', array($this, 'settings_sec_fields'), 10);
                /*******************************/
				add_action('wsa_form_bottom_mptrs_license_settings', [$this, 'license_settings'], 5);
				add_action('mptrs_license', [$this, 'licence_area']);
			}
			public function global_settings_menu() {
				$label = WTBM_Function::get_name();
				$cpt = WTBM_Function::get_cpt();
				add_submenu_page(
                        'mptrs_main_menu',
                        $label . esc_html__(' Settings', 'theaterly'),
                        $label . esc_html__(' Settings', 'theaterly'),
                        'manage_options',
                        'mptrs_settings_page',
                        array($this, 'settings_page')
                );
			}
			public function settings_page() {
				$label = WTBM_Function::get_name();
				?>
                <div class="mptrs_area mptrs_global_settings">
                    <div class="mpPanel">
                        <div class="mpPanelHeader"><?php echo esc_html($label . esc_html__(' Global Settings', 'theaterly')); ?></div>
                        <div class="mpPanelBody mp_zero">
                            <div class="mptrs_tab leftTabs">
								<?php $this->settings_api->show_navigation(); ?>
                                <div class="tabsContent">
									<?php $this->settings_api->show_forms(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function admin_init() {
				$this->settings_api->set_sections($this->get_settings_sections());
				$this->settings_api->set_fields($this->get_settings_fields());
				$this->settings_api->admin_init();
			}
			public function get_settings_sections() {
				$sections = array();
				return apply_filters('mptrs_settings_sec_reg', $sections);
			}
			public function get_settings_fields() {
				$settings_fields = array();
				return apply_filters('mptrs_settings_sec_fields', $settings_fields);
			}
			public function settings_sec_reg($default_sec): array {
				$sections = array(
					array(
						'id' => 'mptrs_general_settings',
						'icon' => 'mi mi-settings',
						'title' => esc_html__('General Settings', 'theaterly')
					),
					array(
						'id' => 'mptrs_global_settings',
						'icon' => 'mi mi-settings-sliders',
						'title' => esc_html__('Global Settings', 'theaterly')
					),
					array(
						'id' => 'mptrs_slider_settings',
						'icon' => 'mi mi-images',
						'title' => esc_html__('Slider Settings', 'theaterly')
					),
					array(
						'id' => 'mptrs_style_settings',
						'icon' => 'mi mi-palette',
						'title' => esc_html__('Style Settings', 'theaterly')
					),
					array(
						'id' => 'mptrs_add_custom_css',
						'icon' => 'mi mi-file-code',
						'title' => esc_html__('Custom CSS', 'theaterly')
					),
					array(
						'id' => 'mptrs_license_settings',
						'icon' => 'mi mi-badget-check-alt',
						'title' => esc_html__('Mage-People License', 'theaterly')
					),
                    array(
                        'id' => 'wtbm_pdf_settings',
                        'icon' => 'far fa-file-pdf',
                        'title' => esc_html__('PDF Settings', 'car-rental-manager-pro')
                    ),
                    array(
                        'id' => 'wtbm_email_settings',
                        'icon' => 'far fa-envelope',
                        'title' => esc_html__('Email Settings', 'car-rental-manager-pro')
                    ),
				);
				return array_merge($default_sec, $sections);
			}
			public function settings_sec_fields($default_fields): array {
				$label = WTBM_Function::get_name();
				$current_date = current_time('Y-m-d');
				$settings_fields = array(
					'mptrs_general_settings' => apply_filters('filter_mptrs_general_settings', array(
						array(
							'name' => 'label',
							'label' => $label . ' ' . esc_html__('Label', 'theaterly'),
							'desc' => esc_html__('If you like to change the label in the dashboard menu, you can change it here.', 'theaterly'),
							'type' => 'text',
							'default' => 'theaterly'
						),
						array(
							'name' => 'slug',
							'label' => $label . ' ' . esc_html__('Slug', 'theaterly'),
							'desc' => esc_html__('Please enter the slug name you want. Remember, after changing this slug; you need to flush permalink; go to', 'theaterly') . '<strong>' . esc_html__('Settings-> Permalinks', 'theaterly') . '</strong> ' . esc_html__('hit the Save Settings button.', 'theaterly'),
							'type' => 'text',
							'default' => 'theaterly'
						),
						array(
							'name' => 'icon',
							'label' => $label . ' ' . esc_html__('Icon', 'theaterly'),
							'desc' => esc_html__('If you want to change the  icon in the dashboard menu, you can change it from here, and the Dashboard icon only supports the Dashicons, So please go to ', 'theaterly') . '<a href=https://developer.wordpress.org/resource/dashicons/#calendar-alt target=_blank>' . esc_html__('Dashicons Library.', 'theaterly') . '</a>' . esc_html__('and copy your icon code and paste it here.', 'theaterly'),
							'type' => 'text',
							'default' => 'dashicons-list-view'
						),
						array(
							'name' => 'category_label',
							'label' => $label . ' ' . esc_html__('Category Label', 'theaterly'),
							'desc' => esc_html__('If you want to change the  category label in the dashboard menu, you can change it here.', 'theaterly'),
							'type' => 'text',
							'default' => esc_html__('Category', 'theaterly')
						),
						array(
							'name' => 'category_slug',
							'label' => $label . ' ' . esc_html__('Category Slug', 'theaterly'),
							'desc' => esc_html__('Please enter the slug name you want for  category. Remember after change this slug you need to flush permalink, Just go to  ', 'theaterly') . '<strong>' . esc_html__('Settings-> Permalinks', 'theaterly') . '</strong> ' . esc_html__('hit the Save Settings button.', 'theaterly'),
							'type' => 'text',
							'default' => 'service-category'
						),
						array(
							'name' => 'organizer_label',
							'label' => $label . ' ' . esc_html__('Organizer Label', 'theaterly'),
							'desc' => esc_html__('If you want to change the   category label in the dashboard menu you can change here', 'theaterly'),
							'type' => 'text',
							'default' => 'Organizer'
						),
						array(
							'name' => 'organizer_slug',
							'label' => $label . ' ' . esc_html__('Organizer Slug', 'theaterly'),
							'desc' => esc_html__('Please enter the slug name you want for the   organizer. Remember, after changing this slug, you need to flush the permalinks. Just go to ', 'theaterly') . '<strong>' . esc_html__('Settings-> Permalinks', 'theaterly') . '</strong> ' . esc_html__('hit the Save Settings button.', 'theaterly'),
							'type' => 'text',
							'default' => 'service-organizer'
						),
						array(
							'name' => 'category_text',
							'label' => $label . ' ' . esc_html__('Product Category Text', 'theaterly'),
							'desc' => esc_html__('If you want to change the  Product Category Text, you can change it here.', 'theaterly'),
							'type' => 'text',
							'default' => esc_html__('Category', 'theaterly')
						),
						array(
							'name' => 'service_text',
							'label' => $label . ' ' . esc_html__('Product ServiceText', 'theaterly'),
							'desc' => esc_html__('If you want to change the  Product Service Text, you can change it here.', 'theaterly'),
							'type' => 'text',
							'default' => esc_html__('Service', 'theaterly')
						),
						array(
							'name' => 'buffer_time',
							'label' => esc_html__('Buffer Time', 'theaterly'),
							'desc' => esc_html__('Please enter here  buffer time in minute. By default is 0', 'theaterly'),
							'type' => 'number',
							'default' => 0,
							'placeholder' => esc_html__('Ex:50', 'theaterly'),
						),
					)),
					'mptrs_global_settings' => apply_filters('filter_mptrs_global_settings', array(
						array(
							'name' => 'disable_block_editor',
							'label' => esc_html__('Disable Block/Gutenberg Editor', 'theaterly'),
							'desc' => esc_html__('If you want to disable WordPress\'s new Block/Gutenberg editor, please select Yes.', 'theaterly'),
							'type' => 'select',
							'default' => 'yes',
							'options' => array(
								'yes' => esc_html__('Yes', 'theaterly'),
								'no' => esc_html__('No', 'theaterly')
							)
						),
						array(
							'name' => 'set_book_status',
							'label' => esc_html__('Seat Booked Status', 'theaterly'),
							'desc' => esc_html__('Please Select when and which order status Seat Will be Booked/Reduced.', 'theaterly'),
							'type' => 'multicheck',
							'default' => array(
								'processing' => 'processing',
								'completed' => 'completed'
							),
							'options' => array(
								'on-hold' => esc_html__('On Hold', 'theaterly'),
								'pending' => esc_html__('Pending', 'theaterly'),
								'processing' => esc_html__('Processing', 'theaterly'),
								'completed' => esc_html__('Completed', 'theaterly'),
							)
						),
						array(
							'name' => 'date_format',
							'label' => esc_html__('Date Picker Format', 'theaterly'),
							'desc' => esc_html__('If you want to change Date Picker Format, please select format. Default  is D d M , yy.', 'theaterly'),
							'type' => 'select',
							'default' => 'D d M , yy',
							'options' => array(
								'yy-mm-dd' => $current_date,
								'yy/mm/dd' => date_i18n('Y/m/d', strtotime($current_date)),
								'yy-dd-mm' => date_i18n('Y-d-m', strtotime($current_date)),
								'yy/dd/mm' => date_i18n('Y/d/m', strtotime($current_date)),
								'dd-mm-yy' => date_i18n('d-m-Y', strtotime($current_date)),
								'dd/mm/yy' => date_i18n('d/m/Y', strtotime($current_date)),
								'mm-dd-yy' => date_i18n('m-d-Y', strtotime($current_date)),
								'mm/dd/yy' => date_i18n('m/d/Y', strtotime($current_date)),
								'd M , yy' => date_i18n('j M , Y', strtotime($current_date)),
								'D d M , yy' => date_i18n('D j M , Y', strtotime($current_date)),
								'M d , yy' => date_i18n('M  j, Y', strtotime($current_date)),
								'D M d , yy' => date_i18n('D M  j, Y', strtotime($current_date)),
							)
						),
						array(
							'name' => 'date_format_short',
							'label' => esc_html__('Short Date  Format', 'theaterly'),
							'desc' => esc_html__('If you want to change Short Date  Format, please select format. Default  is M , Y.', 'theaterly'),
							'type' => 'select',
							'default' => 'M , Y',
							'options' => array(
								'D , M d' => date_i18n('D , M d', strtotime($current_date)),
								'M , Y' => date_i18n('M , Y', strtotime($current_date)),
								'M , y' => date_i18n('M , y', strtotime($current_date)),
								'M - Y' => date_i18n('M - Y', strtotime($current_date)),
								'M - y' => date_i18n('M - y', strtotime($current_date)),
								'F , Y' => date_i18n('F , Y', strtotime($current_date)),
								'F , y' => date_i18n('F , y', strtotime($current_date)),
								'F - Y' => date_i18n('F - y', strtotime($current_date)),
								'F - y' => date_i18n('F - y', strtotime($current_date)),
								'm - Y' => date_i18n('m - Y', strtotime($current_date)),
								'm - y' => date_i18n('m - y', strtotime($current_date)),
								'm , Y' => date_i18n('m , Y', strtotime($current_date)),
								'm , y' => date_i18n('m , y', strtotime($current_date)),
								'F' => date_i18n('F', strtotime($current_date)),
								'm' => date_i18n('m', strtotime($current_date)),
								'M' => date_i18n('M', strtotime($current_date)),
							)
						),
					)),
					'mptrs_slider_settings' => array(
						array(
							'name' => 'slider_type',
							'label' => esc_html__('Slider Type', 'theaterly'),
							'desc' => esc_html__('Please Select Slider Type Default Slider', 'theaterly'),
							'type' => 'select',
							'default' => 'slider',
							'options' => array(
								'slider' => esc_html__('Slider', 'theaterly'),
								'single_image' => esc_html__('Post Thumbnail', 'theaterly')
							)
						),
						array(
							'name' => 'slider_style',
							'label' => esc_html__('Slider Style', 'theaterly'),
							'desc' => esc_html__('Please Select Slider Style Default Style One', 'theaterly'),
							'type' => 'select',
							'default' => 'style_1',
							'options' => array(
								'style_1' => esc_html__('Style One', 'theaterly'),
								'style_2' => esc_html__('Style Two', 'theaterly'),
							)
						),
						array(
							'name' => 'indicator_visible',
							'label' => esc_html__('Slider Indicator Visible?', 'theaterly'),
							'desc' => esc_html__('Please Select Slider Indicator Visible or Not? Default ON', 'theaterly'),
							'type' => 'select',
							'default' => 'on',
							'options' => array(
								'on' => esc_html__('ON', 'theaterly'),
								'off' => esc_html__('Off', 'theaterly')
							)
						),
						array(
							'name' => 'indicator_type',
							'label' => esc_html__('Slider Indicator Type', 'theaterly'),
							'desc' => esc_html__('Please Select Slider Indicator Type Default Icon', 'theaterly'),
							'type' => 'select',
							'default' => 'icon',
							'options' => array(
								'icon' => esc_html__('Icon Indicator', 'theaterly'),
								'image' => esc_html__('image Indicator', 'theaterly')
							)
						),
						array(
							'name' => 'showcase_visible',
							'label' => esc_html__('Slider Showcase Visible?', 'theaterly'),
							'desc' => esc_html__('Please Select Slider Showcase Visible or Not? Default ON', 'theaterly'),
							'type' => 'select',
							'default' => 'on',
							'options' => array(
								'on' => esc_html__('ON', 'theaterly'),
								'off' => esc_html__('Off', 'theaterly')
							)
						),
						array(
							'name' => 'showcase_position',
							'label' => esc_html__('Slider Showcase Position', 'theaterly'),
							'desc' => esc_html__('Please Select Slider Showcase Position Default Right', 'theaterly'),
							'type' => 'select',
							'default' => 'right',
							'options' => array(
								'top' => esc_html__('At Top Position', 'theaterly'),
								'right' => esc_html__('At Right Position', 'theaterly'),
								'bottom' => esc_html__('At Bottom Position', 'theaterly'),
								'left' => esc_html__('At Left Position', 'theaterly')
							)
						),
						array(
							'name' => 'popup_image_indicator',
							'label' => esc_html__('Slider Popup Image Indicator', 'theaterly'),
							'desc' => esc_html__('Please Select Slider Popup Indicator Image ON or Off? Default ON', 'theaterly'),
							'type' => 'select',
							'default' => 'on',
							'options' => array(
								'on' => esc_html__('ON', 'theaterly'),
								'off' => esc_html__('Off', 'theaterly')
							)
						),
						array(
							'name' => 'popup_icon_indicator',
							'label' => esc_html__('Slider Popup Icon Indicator', 'theaterly'),
							'desc' => esc_html__('Please Select Slider Popup Indicator Icon ON or Off? Default ON', 'theaterly'),
							'type' => 'select',
							'default' => 'on',
							'options' => array(
								'on' => esc_html__('ON', 'theaterly'),
								'off' => esc_html__('Off', 'theaterly')
							)
						)
					),
					'mptrs_style_settings' => apply_filters('filter_mptrs_style_settings', array(
						array(
							'name' => 'theme_color',
							'label' => esc_html__('Theme Color', 'theaterly'),
							'desc' => esc_html__('Select Default Theme Color', 'theaterly'),
							'type' => 'color',
							'default' => '#a855f7'
						),
						array(
							'name' => 'theme_alternate_color',
							'label' => esc_html__('Theme Alternate Color', 'theaterly'),
							'desc' => esc_html__('Select Default Theme Alternate  Color that means, if background theme color then it will be text color.', 'theaterly'),
							'type' => 'color',
							'default' => '#fff'
						),
						array(
							'name' => 'default_text_color',
							'label' => esc_html__('Default Text Color', 'theaterly'),
							'desc' => esc_html__('Select Default Text  Color.', 'theaterly'),
							'type' => 'color',
							'default' => '#333'
						),

					)),
					'mptrs_add_custom_css' => apply_filters('filter_mptrs_add_custom_css', array(
						array(
							'name' => 'custom_css',
							'label' => esc_html__('Custom CSS', 'theaterly'),
							'desc' => esc_html__('Write Your Custom CSS Code Here', 'theaterly'),
							'class' => 'mptrs_custom_css',
							'type' => 'textarea',
						)
					)),
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
                    'wtbm_email_settings' => array(
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
                            'default' => WTBM_Function::get_name(),
                        ),
                        array(
                            'name' => 'pdf_email_content',
                            'label' => esc_html__('Email Content', 'car-rental-manager-pro'),
                            'desc' => '<span style="color: red">' . esc_html__('Please use this shortcode for get real data.', 'car-rental-manager-pro') . '</span> <br><br>' . esc_html__('Customer Name:', 'car-rental-manager-pro') . '{customer_name} <br>' . WTBM_Function::get_name() . '{service_name} <br>' . esc_html__('Date:', 'car-rental-manager-pro') . '{service_date} <br>' . esc_html__(' Order ID: ', 'car-rental-manager-pro') . '{order_id} <br>',
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
			public function license_settings() {
				?>
                <div class="mptrs_license_settings">
                    <h4><?php esc_html_e('Mage-People License', 'theaterly'); ?></h4>
                    <div class="_dFlex">
                        <i><?php esc_html_e('Thanking you for using our Mage-People plugin. Our some plugin  free and no license is required. We have some Additional addon to enhance feature of this plugin functionality. If you have any addon you need to enter a valid license for that plugin below.', 'theaterly'); ?></i>
                    </div>
                    <div class="divider"></div>
                    <div class="dLayout mp_basic_license_area">
						<?php $this->licence_area(); ?>
                    </div>
                </div>
				<?php
			}
			public function licence_area(){
				?>
                <table>
                    <thead>
                    <tr>
                        <th colspan="4"><?php esc_html_e('Plugin Name', 'theaterly'); ?></th>
                        <th><?php esc_html_e('Type', 'theaterly'); ?></th>
                        <th><?php esc_html_e('Order No', 'theaterly'); ?></th>
                        <th colspan="2"><?php esc_html_e('Expire on', 'theaterly'); ?></th>
                        <th colspan="3"><?php esc_html_e('License Key', 'theaterly'); ?></th>
                        <th><?php esc_html_e('Status', 'theaterly'); ?></th>
                        <th colspan="2"><?php esc_html_e('Action', 'theaterly'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
					<?php do_action('mp_license_page_plugin_list'); ?>
                    </tbody>
                </table>
				<?php
			}
		}
		new  WTBM_Settings_Global();
	}