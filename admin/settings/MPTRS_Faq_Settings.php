<?php
	/**
	 * @author Sahahdat Hossain <raselsha@gmail.com>
	 * @license mage-people.com
	 * @var 1.0.0
	 */
	if (!defined('ABSPATH'))
		die;
	if (!class_exists('MPTRS_Faq_Settings')) {
		class MPTRS_Faq_Settings {
			public function __construct() {
				add_action('add_mptrs_settings_tab_content', [$this, 'faq_settings']);
				add_action('admin_enqueue_scripts', [$this, 'my_custom_editor_enqueue']);
				// save faq data
				add_action('wp_ajax_mptrs_faq_data_save', [$this, 'save_faq_data_settings']);
				add_action('wp_ajax_nopriv_mptrs_faq_data_save', [$this, 'save_faq_data_settings']);
				// update faq data
				add_action('wp_ajax_mptrs_faq_data_update', [$this, 'faq_data_update']);
				add_action('wp_ajax_nopriv_mptrs_faq_data_update', [$this, 'faq_data_update']);
				// mptrs_delete_faq_data
				add_action('wp_ajax_mptrs_faq_delete_item', [$this, 'faq_delete_item']);
				add_action('wp_ajax_nopriv_mptrs_faq_delete_item', [$this, 'faq_delete_item']);
			}
			public function my_custom_editor_enqueue() {
				// Enqueue necessary scripts
				wp_enqueue_script('jquery');
				wp_enqueue_script('editor');
				wp_enqueue_script('media-upload');
				wp_enqueue_script('thickbox');
				wp_enqueue_style('thickbox');
			}
			public function faq_settings($post_id) {
				$mptrs_faq_active = MPTRS_Function::get_post_info($post_id, 'mptrs_faq_active', 'off');
				$active_class = $mptrs_faq_active == 'on' ? 'mActive' : '';
				$mptrs_faq_active_checked = $mptrs_faq_active == 'on' ? 'checked' : '';
				?>
                <div class="tabsItem" data-tabs="#mptrs_faq_settings">
                    <header>
                        <h2><?php esc_html_e('FAQ Settings', 'theaterly'); ?></h2>
                        <span><?php esc_html_e('FAQ Settings will be here.', 'theaterly'); ?></span>
                    </header>
                    <section class="section">
                        <h2><?php esc_html_e('FAQ Settings', 'theaterly'); ?></h2>
                        <span><?php esc_html_e('FAQ Settings', 'theaterly'); ?></span>
                    </section>
                    <section>
                        <div class="label">
                            <div>
                                <p><?php esc_html_e('Enable FAQ Section', 'theaterly'); ?></p>
                                <span><?php esc_html_e('Enable FAQ Section', 'theaterly'); ?></span>
                            </div>
                            <div>
								<?php MPTRS_Layout::switch_button('mptrs_faq_active', $mptrs_faq_active_checked); ?>
                            </div>
                        </div>
                    </section>
                    <section class="mptrs-faq-section <?php echo esc_attr($active_class); ?>" data-collapse="#mptrs_faq_active">
                        <div class="mptrs-faq-items mB">
							<?php $this->show_faq_data($post_id); ?>
                        </div>
                        <button class="button mptrs-faq-item-new" data-modal="mptrs-faq-item-new" type="button"><?php esc_html_e('Add FAQ', 'theaterly'); ?></button>
                    </section>
                    <!-- sidebar collapse open -->
                    <div class="mptrs-modal-container" data-modal-target="mptrs-faq-item-new">
                        <div class="mptrs-modal-content">
                            <span class="mptrs-modal-close"><i class="fas fa-times"></i></span>
                            <div class="title">
                                <h3><?php esc_html_e('Add F.A.Q.', 'theaterly'); ?></h3>
                                <div id="mptrs-service-msg"></div>
                            </div>
                            <div class="content">
                                <label>
									<?php esc_html_e('Add Title', 'theaterly'); ?>
                                    <input type="hidden" name="mptrs_post_id" value="<?php echo esc_attr($post_id); ?>">
                                    <input type="text" name="mptrs_faq_title">
                                    <input type="hidden" name="mptrs_faq_item_id">
                                </label>
                                <label>
									<?php esc_html_e('Add Content', 'theaterly'); ?>
                                </label>
								<?php
									$content = '';
									$editor_id = 'mptrs_faq_content';
									$settings = array(
										'textarea_name' => 'mptrs_faq_content',
										'media_buttons' => true,
										'textarea_rows' => 10,
									);
									wp_editor($content, $editor_id, $settings);
								?>
                                <div class="mT"></div>
                                <div class="mptrs_faq_save_buttons">
                                    <p>
                                        <button id="mptrs_faq_save" class="button button-primary button-large"><?php esc_html_e('Save', 'theaterly'); ?></button>
                                        <button id="mptrs_faq_save_close" class="button button-primary button-large">save close</button>
                                    <p>
                                </div>
                                <div class="mptrs_faq_update_buttons" style="display: none;">
                                    <p>
                                        <button id="mptrs_faq_update" class="button button-primary button-large"><?php esc_html_e('Update and Close', 'theaterly'); ?></button>
                                    <p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function show_faq_data($post_id) {
				$mptrs_faq = get_post_meta($post_id, 'mptrs_faq', true);
				if (!empty($mptrs_faq)):
					foreach ($mptrs_faq as $key => $value) :
						?>
                        <div class="mptrs-faq-item mptrs_area" data-id="<?php echo esc_attr($key); ?>">
                            <section class="faq-header" data-mptrs-collapse="#faq-content-<?php echo esc_attr($key); ?>">
                                <label class="label">
                                    <p><?php echo esc_html($value['title']); ?></p>
                                    <div class="faq-action">
                                        <span class=""><i class="fas fa-eye"></i></span>
                                        <span class="mptrs-faq-item-edit" data-modal="mptrs-faq-item-new"><i class="fas fa-edit"></i></span>
                                        <span class="mptrs-faq-item-delete"><i class="fas fa-trash"></i></span>
                                    </div>
                                </label>
                            </section>
                            <section class="faq-content mB" data-collapse="#faq-content-<?php echo esc_attr($key); ?>">
								<?php echo wp_kses_post($value['content']); ?>
                            </section>
                        </div>
					<?php
					endforeach;
				endif;
			}
			public function faq_data_update() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'mptrs_admin_nonce')) {
					wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
				}
				$post_id = isset($_POST['mptrs_faq_postID']) ? sanitize_text_field(wp_unslash($_POST['mptrs_faq_postID'])) : '';
				$mptrs_faq_title = isset($_POST['mptrs_faq_title']) ? sanitize_text_field(wp_unslash($_POST['mptrs_faq_title'])) : '';
				$mptrs_faq_content = isset($_POST['mptrs_faq_content']) ? wp_kses_post(wp_unslash($_POST['mptrs_faq_content'])) : '';
				$mptrs_faq = get_post_meta($post_id, 'mptrs_faq', true);
				$mptrs_faq = !empty($mptrs_faq) ? $mptrs_faq : [];
				$new_data = ['title' => $mptrs_faq_title, 'content' => $mptrs_faq_content];
				if (!empty($mptrs_faq)) {
					if (isset($_POST['mptrs_faq_itemID'])) {
						$mptrs_faq[sanitize_text_field(wp_unslash($_POST['mptrs_faq_itemID']))] = $new_data;
					}
				}
				update_post_meta($post_id, 'mptrs_faq', $mptrs_faq);
				ob_start();
				$resultMessage = esc_html__('Data Updated Successfully', 'theaterly');
				$this->show_faq_data($post_id);
				$html_output = ob_get_clean();
				wp_send_json_success([
					'message' => $resultMessage,
					'html' => $html_output,
				]);
				die;
			}
			public function save_faq_data_settings() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'mptrs_admin_nonce')) {
					wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
				}
				$post_id = isset($_POST['mptrs_faq_postID']) ? sanitize_text_field(wp_unslash($_POST['mptrs_faq_postID'])) : '';
				update_post_meta($post_id, 'mptrs_faq_active', 'on');
				$mptrs_faq_title = isset($_POST['mptrs_faq_title']) ? sanitize_text_field(wp_unslash($_POST['mptrs_faq_title'])) : '';
				$mptrs_faq_content = isset($_POST['mptrs_faq_content']) ? wp_kses_post(wp_unslash($_POST['mptrs_faq_content'])) : '';
				$mptrs_faq = get_post_meta($post_id, 'mptrs_faq', true);
				$mptrs_faq = !empty($mptrs_faq) ? $mptrs_faq : [];
				$new_data = ['title' => $mptrs_faq_title, 'content' => $mptrs_faq_content];
				if (isset($post_id)) {
					array_push($mptrs_faq, $new_data);
				}
				$result = update_post_meta($post_id, 'mptrs_faq', $mptrs_faq);
				if ($result) {
					ob_start();
					$resultMessage = esc_html__('Data Added Successfully', 'theaterly');
					$this->show_faq_data($post_id);
					$html_output = ob_get_clean();
					wp_send_json_success([
						'message' => $resultMessage,
						'html' => $html_output,
					]);
				} else {
					wp_send_json_success([
						'message' => 'Data not inserted',
						'html' => 'error',
					]);
				}
				die;
			}
			public function faq_delete_item() {
				if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'mptrs_admin_nonce')) {
					wp_send_json_error('Invalid nonce!'); // Prevent unauthorized access
				}
				$post_id = isset($_POST['mptrs_faq_postID']) ? sanitize_text_field(wp_unslash($_POST['mptrs_faq_postID'])) : '';
				$mptrs_faq = get_post_meta($post_id, 'mptrs_faq', true);
				$mptrs_faq = !empty($mptrs_faq) ? $mptrs_faq : [];
				if (!empty($mptrs_faq)) {
					if (isset($_POST['itemId'])) {
						unset($mptrs_faq[sanitize_text_field(wp_unslash($_POST['itemId']))]);
						$mptrs_faq = array_values($mptrs_faq);
					}
				}
				$result = update_post_meta($post_id, 'mptrs_faq', $mptrs_faq);
				if ($result) {
					ob_start();
					$resultMessage = esc_html__('Data Deleted Successfully', 'theaterly');
					$this->show_faq_data($post_id);
					$html_output = ob_get_clean();
					wp_send_json_success([
						'message' => $resultMessage,
						'html' => $html_output,
					]);
				} else {
					wp_send_json_success([
						'message' => 'Data not inserted',
						'html' => '',
					]);
				}
				die;
			}
		}
		new MPTRS_Faq_Settings();
	}