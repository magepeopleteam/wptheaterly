<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}  // if direct access
	if ( ! class_exists( 'MPTRS_Hidden_Product' ) ) {
		class MPTRS_Hidden_Product {
			public function __construct() {
				add_action( 'wp_insert_post', array( $this, 'create_hidden_wc_product_on_publish' ), 10, 3 );
				add_action( 'save_post', array( $this, 'run_link_product_on_save' ), 99, 1 );
				add_action( 'parse_query', array( $this, 'hide_wc_hidden_product_from_product_list' ) );
				add_action( 'wp', array( $this, 'hide_hidden_wc_product_from_frontend' ) );
			}
			public function create_hidden_wc_product_on_publish( $post_id, $post ) {
				if ( $post->post_type == MPTRS_Function::get_cpt() && $post->post_status == 'publish' && empty( MPTRS_Function::get_post_info( $post_id, 'check_if_run_once' ) ) ) {
					$new_post     = array(
						'post_title'    => $post->post_title,
						'post_content'  => '',
						'post_name'     => uniqid(),
						'post_category' => array(),  // Usable for custom taxonomies too
						'tags_input'    => array(),
						'post_status'   => 'publish', // Choose: publish, preview, future, draft, etc.
						'post_type'     => 'product'  //'post',page' or use a custom post type if you want to
					);
					$pid          = wp_insert_post( $new_post );
					$product_type = 'yes';
					update_post_meta( $post_id, 'link_wc_product', $pid );
					update_post_meta( $pid, 'link_mptrs_id', $post_id );
					update_post_meta( $pid, '_price', 0.01 );
					update_post_meta( $pid, '_sold_individually', 'yes' );
					update_post_meta( $pid, '_virtual', $product_type );
					$terms = array( 'exclude-from-catalog', 'exclude-from-search' );
					wp_set_object_terms( $pid, $terms, 'product_visibility' );
					update_post_meta( $post_id, 'check_if_run_once', true );
				}
			}
			public function run_link_product_on_save( $post_id ) {
				if ( get_post_type( $post_id ) == MPTRS_Function::get_cpt() ) {
					if ( ! isset( $_POST['mptrs_nonce'] ) || ! wp_verify_nonce( sanitize_text_field(wp_unslash($_POST['mptrs_nonce'])), 'mptrs_nonce' ) ) {
						return;
					}
					if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
						return;
					}
					if ( ! current_user_can( 'edit_post', $post_id ) ) {
						return;
					}
					$title = get_the_title( $post_id );
					if ( $this->count_hidden_wc_product( $post_id ) == 0 || empty( MPTRS_Function::get_post_info( $post_id, 'link_wc_product' ) ) ) {
						$this->create_hidden_wc_product( $post_id, $title );
					}
					$product_id = MPTRS_Function::get_post_info( $post_id, 'link_wc_product', $post_id );
					set_post_thumbnail( $product_id, get_post_thumbnail_id( $post_id ) );
					wp_publish_post( $product_id );
					$product_type = 'yes';
					$_tax_status = isset($_POST['_tax_status']) ? sanitize_text_field(wp_unslash($_POST['_tax_status'])) : 'none';
					$_tax_class = isset($_POST['_tax_class']) ? sanitize_text_field(wp_unslash($_POST['_tax_class'])) : '';
					update_post_meta( $product_id, '_tax_status', $_tax_status );
					update_post_meta( $product_id, '_tax_class', $_tax_class );
					update_post_meta( $product_id, '_stock_status', 'instock' );
					update_post_meta( $product_id, '_manage_stock', 'no' );
					update_post_meta( $product_id, '_virtual', $product_type );
					update_post_meta( $product_id, '_sold_individually', 'yes' );
					$my_post = array(
						'ID'         => $product_id,
						'post_title' => $title,
						'post_name'  => uniqid()
					);
					remove_action( 'save_post', 'run_link_product_on_save' );
					wp_update_post( $my_post );
					add_action( 'save_post', 'run_link_product_on_save' );
				}
			}
			public function hide_wc_hidden_product_from_product_list( $query ) {
				global $pagenow;
				$q_vars = &$query->query_vars;
				if ( $pagenow == 'edit.php' && isset( $q_vars['post_type'] ) && $q_vars['post_type'] == 'product' ) {
					$tax_query = array(
						[
							'taxonomy' => 'product_visibility',
							'field'    => 'slug',
							'terms'    => 'exclude-from-catalog',
							'operator' => 'NOT IN',
						]
					);
					$query->set( 'tax_query', $tax_query );
				}
			}
			public function hide_hidden_wc_product_from_frontend() {
				global $post, $wp_query;
				if ( is_product() ) {
					$post_id    = $post->ID;
					$visibility = get_the_terms( $post_id, 'product_visibility' );
					if ( is_object( $visibility ) ) {
						if ( $visibility[0]->name == 'exclude-from-catalog' ) {
							$check_event_hidden = MPTRS_Function::get_post_info( $post_id, 'link_mptrs_id', 0 );
							if ( $check_event_hidden > 0 ) {
								$wp_query->set_404();
								status_header( 404 );
								get_template_part( 404 );
								exit();
							}
						}
					}
				}
			}
			/**********************/
			public function create_hidden_wc_product( $post_id, $title ) {
				$new_post = array(
					'post_title'    => $title,
					'post_content'  => '',
					'post_name'     => uniqid(),
					'post_category' => array(),
					'tags_input'    => array(),
					'post_status'   => 'publish',
					'post_type'     => 'product'
				);
				$pid      = wp_insert_post( $new_post );
				update_post_meta( $post_id, 'link_wc_product', $pid );
				update_post_meta( $pid, 'link_mptrs_id', $post_id );
				update_post_meta( $pid, '_price', 0.01 );
				update_post_meta( $pid, '_sold_individually', 'yes' );
				update_post_meta( $pid, '_virtual', 'yes' );
				$terms = array( 'exclude-from-catalog', 'exclude-from-search' );
				wp_set_object_terms( $pid, $terms, 'product_visibility' );
				update_post_meta( $post_id, 'check_if_run_once', true );
			}
			public function count_hidden_wc_product( $post_id ): int {
				$args = array(
					'post_type'      => 'product',
					'posts_per_page' => - 1,
					'meta_query'     => array(
						array(
							'key'     => 'link_mptrs_id',
							'value'   => $post_id,
							'compare' => '='
						)
					)
				);
				$loop = new WP_Query( $args );
				return $loop->post_count;
			}
		}
		new MPTRS_Hidden_Product();
	}