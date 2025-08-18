<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPTRS_Quick_Setup')) {
		class MPTRS_Quick_Setup {
			public function __construct() {
				add_action('admin_menu', array($this, 'quick_setup_menu'));
			}
			public function quick_setup_menu() {
				$status = MPTRS_Function::check_woocommerce();
				if ($status == 1) {
					add_submenu_page('edit.php?post_type=mptrs_item', esc_html__('Quick Setup', 'theaterly'), '<span style="color:#10dd10">' . esc_html__('Quick Setup', 'theaterly') . '</span>', 'manage_options', 'mptrs_quick_setup', array($this, 'quick_setup'));
					add_submenu_page('mptrs_item', esc_html__('Quick Setup', 'theaterly'), '<span style="color:#10dd10">' . esc_html__('Quick Setup', 'theaterly') . '</span>', 'manage_options', 'mptrs_quick_setup', array($this, 'quick_setup'));
				} else {
					add_menu_page(esc_html__('Tablely', 'theaterly'), esc_html__('Tablely', 'theaterly'), 'manage_options', 'mptrs_item', array($this, 'quick_setup'), 'dashicons-admin-site-alt2', 6);
					add_submenu_page('mptrs_item', esc_html__('Quick Setup', 'theaterly'), '<span style="color:#10dd17">' . esc_html__('Quick Setup', 'theaterly') . '</span>', 'manage_options', 'mptrs_quick_setup', array($this, 'quick_setup'));
				}
			}
			public function quick_setup() {
				$status = MPTRS_Function::check_woocommerce();
				if (isset($_POST['mptrs_quick_setup_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['mptrs_quick_setup_nonce'])), 'mptrs_quick_setup_nonce')) {
					if (isset($_POST['active_woo_btn'])) {
						?>
                        <script>
                            mptrs_loader_body();
                        </script>
						<?php
						activate_plugin('woocommerce/woocommerce.php');
						?>
                        <script>
                            (function ($) {
                                "use strict";
                                $(document).ready(function () {
                                    let mptrs_admin_location = window.location.href;
                                    mptrs_admin_location = mptrs_admin_location.replace('admin.php?post_type=mptrs_item&page=mptrs_quick_setup', 'edit.php?post_type=mptrs_item&page=mptrs_quick_setup');
                                    mptrs_admin_location = mptrs_admin_location.replace('admin.php?page=mptrs_item', 'edit.php?post_type=mptrs_item&page=mptrs_quick_setup');
                                    mptrs_admin_location = mptrs_admin_location.replace('admin.php?page=mptrs_quick_setup', 'edit.php?post_type=mptrs_item&page=mptrs_quick_setup');
                                    window.location.href = mptrs_admin_location;
                                });
                            }(jQuery));
                        </script>
						<?php
					}
					if (isset($_POST['install_and_active_woo_btn'])) {
						echo '<div style="display:none">';
						include_once(ABSPATH . 'wp-admin/includes/plugin-install.php');
						include_once(ABSPATH . 'wp-admin/includes/file.php');
						include_once(ABSPATH . 'wp-admin/includes/misc.php');
						include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
						$plugin = 'woocommerce';
						$api = plugins_api('plugin_information', array(
							'slug' => $plugin,
							'fields' => array(
								'short_description' => false,
								'sections' => false,
								'requires' => false,
								'rating' => false,
								'ratings' => false,
								'downloaded' => false,
								'last_updated' => false,
								'added' => false,
								'tags' => false,
								'compatibility' => false,
								'homepage' => false,
								'donate_link' => false,
							),
						));
						$title = 'title';
						$url = 'url';
						$nonce = 'nonce';
						$woocommerce_plugin = new Plugin_Upgrader(new Plugin_Installer_Skin(compact('title', 'url', 'nonce', 'plugin', 'api')));
						$woocommerce_plugin->install($api->download_link);
						activate_plugin('woocommerce/woocommerce.php');
						echo '</div>';
						?>
                        <script>
                            (function ($) {
                                "use strict";
                                $(document).ready(function () {
                                    let mptrs_admin_location = window.location.href;
                                    mptrs_admin_location = mptrs_admin_location.replace('admin.php?post_type=mptrs_item&page=mptrs_quick_setup', 'edit.php?post_type=mptrs_item&page=mptrs_quick_setup');
                                    mptrs_admin_location = mptrs_admin_location.replace('admin.php?page=mptrs_item', 'edit.php?post_type=mptrs_item&page=mptrs_quick_setup');
                                    mptrs_admin_location = mptrs_admin_location.replace('admin.php?page=mptrs_quick_setup', 'edit.php?post_type=mptrs_item&page=mptrs_quick_setup');
                                    window.location.href = mptrs_admin_location;
                                });
                            }(jQuery));
                        </script>
						<?php
					}
					if (isset($_POST['finish_quick_setup'])) {
						$label = isset($_POST['mptrs_label']) ? sanitize_text_field(wp_unslash($_POST['mptrs_label'])) : 'theaterly';
						$slug = isset($_POST['mptrs_slug']) ? sanitize_text_field(wp_unslash($_POST['mptrs_slug'])) : 'theaterly';
						$general_settings_data = get_option('mptrs_general_settings');
						$update_general_settings_arr = [
							'label' => $label,
							'slug' => $slug
						];
						$new_general_settings_data = is_array($general_settings_data) ? array_replace($general_settings_data, $update_general_settings_arr) : $update_general_settings_arr;
						update_option('mptrs_general_settings', $new_general_settings_data);
						flush_rewrite_rules();
						wp_redirect(admin_url('edit.php?post_type=mptrs_item'));
					}
				}
				?>
                <div class="mptrs_area">
                    <div class=_dShadow_6_adminLayout">
                        <form method="post" action="">
							<?php wp_nonce_field('mptrs_quick_setup_nonce', 'mptrs_quick_setup_nonce'); ?>
                            <div class="mptrs_tab_next">
                                <div class="tabListsNext _max_700_mAuto">
                                    <div data-tabs-target-next="#mptrs_qs_welcome" class="tabItemNext" data-open-text="1" data-close-text=" " data-open-icon="" data-close-icon="fas fa-check" data-add-class="success">
                                        <h4 class="circleIcon" data-class>
                                            <span class="mp_zero" data-icon></span>
                                            <span class="mp_zero" data-text>1</span>
                                        </h4>
                                        <h6 class="circleTitle" data-class><?php esc_html_e('Welcome', 'theaterly'); ?></h6>
                                    </div>
                                    <div data-tabs-target-next="#mptrs_qs_general" class="tabItemNext" data-open-text="2" data-close-text="" data-open-icon="" data-close-icon="fas fa-check" data-add-class="success">
                                        <h4 class="circleIcon" data-class>
                                            <span class="mp_zero" data-icon></span>
                                            <span class="mp_zero" data-text>2</span>
                                        </h4>
                                        <h6 class="circleTitle" data-class><?php esc_html_e('General', 'theaterly'); ?></h6>
                                    </div>
                                    <div data-tabs-target-next="#mptrs_qs_done" class="tabItemNext" data-open-text="3" data-close-text="" data-open-icon="" data-close-icon="fas fa-check" data-add-class="success">
                                        <h4 class="circleIcon" data-class>
                                            <span class="mp_zero" data-icon></span>
                                            <span class="mp_zero" data-text>3</span>
                                        </h4>
                                        <h6 class="circleTitle" data-class><?php esc_html_e('Done', 'theaterly'); ?></h6>
                                    </div>
                                </div>
                                <div class="tabsContentNext _infoLayout_mT">
									<?php
										$this->setup_welcome_content();
										$this->setup_general_content();
										$this->setup_content_done();
									?>
                                </div>
								<?php if ($status == 1) { ?>
                                    <div class="justifyBetween">
                                        <button type="button" class="mpBtn mptrs_tab_prev">
                                            <span>&longleftarrow;<?php esc_html_e('Previous', 'theaterly'); ?></span>
                                        </button>
                                        <div></div>
                                        <button type="button" class="themeButton mptrs_tab_next">
                                            <span><?php esc_html_e('Next', 'theaterly'); ?>&longrightarrow;</span>
                                        </button>
                                    </div>
								<?php } ?>
                            </div>
                        </form>
                    </div>
                </div>
				<?php
			}
			public function setup_welcome_content() {
				$status = MPTRS_Function::check_woocommerce();
				?>
                <div data-tabs-next="#mptrs_qs_welcome">
                    <h2><?php esc_html_e('Tablely Manager For Woocommerce Plugin', 'theaterly'); ?></h2>
                    <p class="mTB_xs"><?php esc_html_e('Tablely Manager Plugin for WooCommerce for your site, Please go step by step and choose some options to get started.', 'theaterly'); ?></p>
                    <div class="_dLayout_mT_alignCenter justifyBetween">
                        <h5>
							<?php if ($status == 1) {
								esc_html_e('Woocommerce already installed and activated', 'theaterly');
							} elseif ($status == 0) {
								esc_html_e('Woocommerce need to install and active', 'theaterly');
							} else {
								esc_html_e('Woocommerce already install , please activate it', 'theaterly');
							} ?>
                        </h5>
						<?php if ($status == 1) { ?>
                            <h5><span class="fas fa-check-circle textSuccess"></span></h5>
						<?php } elseif ($status == 0) { ?>
                            <button class="warningButton" type="submit" name="install_and_active_woo_btn"><?php esc_html_e('Install & Active Now', 'theaterly'); ?></button>
						<?php } else { ?>
                            <button class="themeButton" type="submit" name="active_woo_btn"><?php esc_html_e('Active Now', 'theaterly'); ?></button>
						<?php } ?>
                    </div>
                </div>
				<?php
			}
			public function setup_general_content() {
				$label = MPTRS_Function::get_settings('mptrs_general_settings', 'label', 'Tablely');
				$slug = MPTRS_Function::get_settings('mptrs_general_settings', 'slug', 'service-booking');
				?>
                <div data-tabs-next="#mptrs_qs_general">
                    <div class="section">
                        <h2><?php esc_html_e('General settings', 'theaterly'); ?></h2>
                        <p class="mTB_xs"><?php esc_html_e('Choose some general option.', 'theaterly'); ?></p>
                        <div class="_dLayout_mT">
                            <label class="fullWidth">
                                <span class="min_300"><?php esc_html_e('Tablely Manager Label:', 'theaterly'); ?></span>
                                <input type="text" class="formControl" name="mptrs_label" value='<?php echo esc_attr($label); ?>'/>
                            </label>
                            <i class="info_text">
                                <span class="fas fa-info-circle"></span>
								<?php esc_html_e('It will change the Tablely Manager post type label on the entire plugin.', 'theaterly'); ?>
                            </i>
                            <div class="divider"></div>
                            <label class="fullWidth">
                            <span
                                class="min_300"><?php esc_html_e('Tablely Manager Slug:', 'theaterly'); ?></span>
                                <input type="text" class="formControl" name="mptrs_slug" value='<?php echo esc_attr($slug); ?>'/>
                            </label>
                            <i class="info_text">
                                <span class="fas fa-info-circle"></span>
								<?php esc_html_e('It will change the Tablely Manager slug on the entire plugin. Remember after changing this slug you need to flush permalinks. Just go to Settings->Permalinks hit the Save Settings button', 'theaterly'); ?>
                            </i>
                        </div>
                    </div>
                </div>
				<?php
			}
			public function setup_content_done() {
				?>
                <div data-tabs-next="#mptrs_qs_done">
                    <h2><?php esc_html_e('Finalize Setup', 'theaterly'); ?></h2>
                    <p class="mTB_xs"><?php esc_html_e('You are about to Finish & Save theaterly For Woocommerce Plugin setup process', 'theaterly'); ?></p>
                    <div class="mT allCenter">
                        <button type="submit" name="finish_quick_setup" class="themeButton"><?php esc_html_e('Finish & Save', 'theaterly'); ?></button>
                    </div>
                </div>
				<?php
			}
		}
		new MPTRS_Quick_Setup();
	}