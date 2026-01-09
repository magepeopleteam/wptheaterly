<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'WTBM_Frontend' ) ) {
		class WTBM_Frontend {

			public function __construct() {
				$this->load_file();
				add_filter( 'single_template', array( $this, 'load_single_template' ) );
				add_filter( 'body_class', [ $this, 'add_body_class' ] );
			}

			private function load_file(){
				require_once MPTRS_PLUGIN_DIR . '/frontend/WTBM_Shortcodes.php';
				require_once MPTRS_PLUGIN_DIR . '/frontend/WTBM_Details_Layout.php';
				require_once MPTRS_PLUGIN_DIR . '/frontend/WTBM_Woocommerce.php';
				require_once MPTRS_PLUGIN_DIR . '/frontend/WTBM_Manage_Ajax.php';
			}

			public function load_single_template( $template ): string {
				global $post;
				if ( $post->post_type && $post->post_type == WTBM_Function::get_cpt()) {
					$template = WTBM_Function::template_path( 'single_page/details.php' );
				}
				return $template;
			}

			public function add_body_class( $classes ) {
				return array_merge( $classes, ['wtbm-theaterly'] );
			}

		}
		
		new WTBM_Frontend();
	}