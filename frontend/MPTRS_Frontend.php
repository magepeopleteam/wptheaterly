<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'MPTRS_Frontend' ) ) {
		class MPTRS_Frontend {
			public function __construct() {
				$this->load_file();
				add_filter( 'single_template', array( $this, 'load_single_template' ) );
			}
			private function load_file(){
				require_once MPTRS_PLUGIN_DIR . '/frontend/WTBM_Shortcodes.php';
				require_once MPTRS_PLUGIN_DIR . '/frontend/WTBM_Details_Layout.php';
				require_once MPTRS_PLUGIN_DIR . '/frontend/MPTRS_Woocommerce.php';
				require_once MPTRS_PLUGIN_DIR . '/frontend/WTBM_Manage_Ajax.php';
			}
			public function load_single_template( $template ): string {
				global $post;
				if ( $post->post_type && $post->post_type == MPTRS_Function::get_cpt()) {
					$template = MPTRS_Function::template_path( 'single_page/details.php' );
				}
				return $template;
			}
		}
		new MPTRS_Frontend();
	}