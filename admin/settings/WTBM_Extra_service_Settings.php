<?php
	/*
* @Author 		engr.sumonazma@gmail.com
* Copyright: 	mage-people.com
*/
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPTRS_Extra_service_Settings')) {
		class MPTRS_Extra_service_Settings {
			public function __construct() {
				add_action('wtbm_add_settings_tab_content', [$this, 'extra_service_settings'], 10, 1);
				// save extra service
				add_action('wp_ajax_mptrs_save_ex_service', [$this, 'save_ex_service']);
				add_action('wp_ajax_nopriv_mptrs_save_ex_service', [$this, 'save_ex_service']);
				// mptrs update extra service
				add_action('wp_ajax_mptrs_ext_service_update', [$this, 'ext_service_update_item']);
				add_action('wp_ajax_nopriv_mptrs_ext_service_update', [$this, 'ext_service_update_item']);
				// mptrs delete extra service
				add_action('wp_ajax_mptrs_ext_service_delete_item', [$this, 'extra_service_delete_item']);
				add_action('wp_ajax_nopriv_mptrs_ext_service_delete_item', [$this, 'extra_service_delete_item']);
			}
			public function ext_service_update_item() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'mptrs_admin_nonce')) {
					wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
				}
				$post_id = isset($_POST['service_postID']) ? sanitize_text_field(wp_unslash($_POST['service_postID'])) : '';
				$ext_services = $this->get_extra_services($post_id);
				$iconClass = '';
				$imageID = '';
				if (isset($_POST['service_image_icon'])) {
					if (is_numeric($_POST['service_image_icon'])) {
						$imageID = sanitize_text_field(wp_unslash($_POST['service_image_icon']));
						$iconClass = '';
					} else {
						$iconClass = sanitize_text_field(wp_unslash($_POST['service_image_icon']));
						$imageID = '';
					}
				}
				$new_data = [
					'name' => isset($_POST['service_name']) ?sanitize_text_field(wp_unslash($_POST['service_name'])):'',
					'price' => isset($_POST['service_price']) ?sanitize_text_field(wp_unslash($_POST['service_price'])):'',
					'qty' => isset($_POST['service_qty']) ?sanitize_text_field(wp_unslash($_POST['service_qty'])):'',
					'details' => isset($_POST['service_description']) ?sanitize_text_field(wp_unslash($_POST['service_description'])):'',
					'icon' => $iconClass,
					'image' => $imageID,
				];
				if (!empty($ext_services)) {
					if (isset($_POST['service_itemId'])) {
						$ext_services[sanitize_text_field(wp_unslash($_POST['service_itemId']))] = $new_data;
					}
				}
				update_post_meta($post_id, 'mptrs_extra_service', $ext_services);
				ob_start();
				$resultMessage = esc_html__('Data Updated Successfully', 'wptheaterly');
				$this->show_extra_service($post_id);
				$html_output = ob_get_clean();
				wp_send_json_success([
					'message' => $resultMessage,
					'html' => $html_output,
				]);
				die;
			}
			public function save_ex_service() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'mptrs_admin_nonce')) {
					wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
				}
				$post_id = isset($_POST['service_postID']) ? sanitize_text_field(wp_unslash($_POST['service_postID'])) : '';
				update_post_meta($post_id, 'mptrs_extra_service_active', 'on');
				$extra_services = $this->get_extra_services($post_id);
				$iconClass = '';
				$imageID = '';
				if (isset($_POST['service_image_icon'])) {
					if (is_numeric($_POST['service_image_icon'])) {
						$imageID = sanitize_text_field(wp_unslash($_POST['service_image_icon']));
						$iconClass = '';
					} else {
						$iconClass = sanitize_text_field(wp_unslash($_POST['service_image_icon']));
						$imageID = '';
					}
				}
				$new_data = [
					'name' => isset($_POST['service_name']) ?sanitize_text_field(wp_unslash($_POST['service_name'])):'',
					'price' => isset($_POST['service_price']) ?sanitize_text_field(wp_unslash($_POST['service_price'])):'',
					'qty' => isset($_POST['service_qty']) ?sanitize_text_field(wp_unslash($_POST['service_qty'])):'',
					'details' => isset($_POST['service_description']) ?sanitize_text_field(wp_unslash($_POST['service_description'])):'',
					'icon' => $iconClass,
					'image' => $imageID,
				];
				array_push($extra_services, $new_data);
				update_post_meta($post_id, 'mptrs_extra_service', $extra_services);
				ob_start();
				$resultMessage = esc_html__('Data Updated Successfully', 'wptheaterly');
				$this->show_extra_service($post_id);
				$html_output = ob_get_clean();
				wp_send_json_success([
					'message' => $resultMessage,
					'html' => $html_output,
				]);
				die;
			}
			public function get_extra_services($post_id) {
				$extra_services = WTBM_Function::get_post_info($post_id, 'mptrs_extra_service', []);
				$services = [];
				foreach ($extra_services as $value) {
					if (isset($value['group_service_info'])) {
						$services = array_merge($services, $value['group_service_info']);
					}
				}
				if (!empty($services)) {
					update_post_meta($post_id, 'mptrs_extra_service', $services);
					return $services;
				} else {
					return $extra_services;
				}
			}
			public function extra_service_settings($post_id) {
				$extra_service_active = WTBM_Function::get_post_info($post_id, 'mptrs_extra_service_active', 'off');
				$active_class = $extra_service_active == 'on' ? 'mActive' : '';
				$extra_service_checked = $extra_service_active == 'on' ? 'checked' : '';
				?>
                <div class="tabsItem mptrs_extra_service_settings" data-tabs="#mptrs_extra_service_settings">
                    <header>
                        <h2><?php esc_html_e('Extra Service Configuration', 'wptheaterly'); ?></h2>
                        <span><?php esc_html_e('Here you can configure Extra Service.', 'wptheaterly'); ?></span>
                    </header>
                    <section class="section">
                        <h2><?php esc_html_e('Extra Service Settings', 'wptheaterly'); ?></h2>
                        <span><?php esc_html_e('Extra Service Settings', 'wptheaterly'); ?></span>
                    </section>
                    <section>
                        <div class="label">
                            <div>
                                <p><?php esc_html_e('Enable Extra Service', 'wptheaterly'); ?></p>
                                <span><?php esc_html_e('Enable Extra Service.', 'wptheaterly'); ?></span>
                            </div>
                            <div>
								<?php WTBM_Layout::switch_button('mptrs_extra_service_active', $extra_service_checked); ?>
                            </div>
                        </div>
                    </section>
                    <section class="mptrs-extra-section <?php echo esc_attr($active_class); ?>" data-collapse="#mptrs_extra_service_active">
                        <table class="table extra-service-table mB">
                            <thead>
                            <tr>
                                <th style="width:66px"><?php esc_html_e('Image', 'wptheaterly'); ?></th>
                                <th style="width:150px"><?php esc_html_e('Service Title', 'wptheaterly'); ?></th>
                                <th><?php esc_html_e('Description', 'wptheaterly'); ?></th>
                                <th style="width:90px"><?php esc_html_e('Quantity', 'wptheaterly'); ?></th>
                                <th style="width:90px"><?php esc_html_e('Price', 'wptheaterly'); ?></th>
                                <th style="width:92px"><?php esc_html_e('Action', 'wptheaterly'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
							<?php $this->show_extra_service($post_id); ?>
                            </tbody>
                        </table>
                        <button class="button mptrs-extra-service-new" data-modal="mptrs-extra-service-new" type="button"><?php esc_html_e('Add Extra Service', 'wptheaterly'); ?></button>
                    </section>
                    <!-- sidebar collapse open -->
                    <div class="mptrs-modal-container" data-modal-target="mptrs-extra-service-new">
                        <div class="mptrs-modal-content">
                            <span class="mptrs-modal-close"><i class="fas fa-times"></i></span>
                            <div class="title">
                                <h3><?php esc_html_e('Add Extra Service', 'wptheaterly'); ?></h3>
                                <div id="mptrs-service-msg"></div>
                            </div>
                            <div class="content">
                                <div id="mptrs-ex-service-msg"></div>
                                <input type="hidden" name="mptrs_ext_post_id" value="<?php echo esc_attr($post_id); ?>">
                                <input type="hidden" name="mptrs_ext_service_item_id" value="">
                                <label>
									<?php esc_html_e('Service Name', 'wptheaterly'); ?>
                                    <input type="text" name="mptrs_ext_service_name">
                                </label>
                                <label>
									<?php esc_html_e('Price', 'wptheaterly'); ?>
                                    <input type="number" name="mptrs_ext_service_price">
                                </label>
                                <label>
									<?php esc_html_e('Quantity', 'wptheaterly'); ?>
                                    <input type="number" name="mptrs_ext_service_qty">
                                </label>
                                <label>
									<?php esc_html_e('Description', 'wptheaterly'); ?>
                                    <textarea name="mptrs_ext_service_description" rows="5"></textarea>
                                </label>
                                <label>
									<?php esc_html_e('Image/Icon', 'wptheaterly'); ?>
                                </label>
                                <div class="add_icon_image_area">
                                    <input type="hidden" name="mptrs_ext_service_image_icon" value="">
                                    <div class="icon_item dNone">
                                        <span class="" data-add-icon=""></span>
                                        <span class="fas fa-times mp_remove_icon icon_remove"></span>
                                    </div>
                                    <div class="image_item dNone">
	                                    <?php echo wp_get_attachment_image(0, 'medium'); ?>
                                        <span class="fas fa-times mp_remove_icon image_remove"></span>
                                    </div>
                                    <div class="add_icon_image_button_area ">
                                        <button class="mp_image_add" type="button">
                                            <span class="fas fa-images"></span>Image
                                        </button>
                                        <button class="icon_add" type="button" data-target-popup="#add_icon_popup">
                                            <span class="fas fa-plus"></span>Icon
                                        </button>
                                    </div>
                                </div>
                                <div class="mptrs_ex_service_save_button">
                                    <p>
                                        <button id="mptrs_ex_service_save" class="button button-primary button-large"><?php esc_html_e('Save', 'wptheaterly'); ?></button>
                                        <button id="mptrs_ex_service_save_close" class="button button-primary button-large">save close</button>
                                    <p>
                                </div>
                                <div class="mptrs_ex_service_update_button" style="display: none;">
                                    <p>
                                        <button id="mptrs_ex_service_update" class="button button-primary button-large"><?php esc_html_e('Update and Close', 'wptheaterly'); ?></button>
                                    <p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function show_extra_service($post_id) {
				$extra_services = $this->get_extra_services($post_id);
				if (!empty($extra_services)):
					foreach ($extra_services as $key => $value) :
						?>
                        <tr data-id='<?php echo esc_attr($key); ?>'>
                            <td>
								<?php if (!empty($value['image'])): ?>
									<?php echo wp_get_attachment_image($value['image'], 'medium'); ?>
								<?php endif; ?>
								<?php if (!empty($value['icon'])): ?>
                                    <i class="<?php echo esc_attr($value['icon']); ?>"></i>
								<?php endif; ?>
                            </td>
                            <td><?php echo esc_html($value['name']); ?></td>
                            <td><?php echo esc_html($value['details']); ?></td>
                            <td><?php echo esc_html($value['qty']); ?></td>
                            <td><?php echo esc_html($value['price']); ?></td>
                            <td>
                                <span class="mptrs-ext-service-edit" data-modal="mptrs-extra-service-new"><i class="fas fa-edit"></i></span>
                                <span class="mptrs-ext-service-delete"><i class="fas fa-trash"></i></span>
                            </td>
                        </tr>
					<?php
					endforeach;
				endif;
			}
			public function extra_service_delete_item() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'mptrs_admin_nonce')) {
					wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
				}
				$post_id = isset($_POST['service_postID']) ? sanitize_text_field(wp_unslash($_POST['service_postID'])) : '';
				$extra_services = $this->get_extra_services($post_id);
				if (!empty($extra_services)) {
					if (isset($_POST['itemId'])) {
						unset($extra_services[sanitize_text_field(wp_unslash($_POST['itemId']))]);
						$extra_services = array_values($extra_services);
					}
				}
				$result = update_post_meta($post_id, 'mptrs_extra_service', $extra_services);
				if ($result) {
					ob_start();
					$resultMessage = esc_html__('Data Deleted Successfully', 'wptheaterly');
					$this->show_extra_service($post_id);
					$html_output = ob_get_clean();
					wp_send_json_success([
						'message' => $resultMessage,
						'html' => $html_output,
					]);
				} else {
					wp_send_json_success([
						'message' => 'Data not deleted',
						'html' => '',
					]);
				}
				die;
			}
		}
		new MPTRS_Extra_service_Settings();
	}