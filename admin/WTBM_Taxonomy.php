<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('WTBM_Taxonomy')) {
		class WTBM_Taxonomy {
			public function __construct() {
				//add_action( 'init', [ $this, 'taxonomy' ] );
			}
			public function taxonomy() {
				$label = WTBM_Function::get_name();
				$cat_label = WTBM_Function::get_category_label();
				$cat_slug = WTBM_Function::get_category_slug();
				$labels = [
					'name' => $label . ' ' . $cat_label,
					'singular_name' => $label . ' ' . $cat_label,
					'menu_name' => $cat_label,
					'all_items' => esc_html__('All ', 'wptheaterly') . ' ' . $label . ' ' . $cat_label,
					'parent_item' => esc_html__('Parent ', 'wptheaterly') . ' ' . $cat_label,
					'parent_item_colon' => esc_html__('Parent ', 'wptheaterly') . ' ' . $cat_label,
					'new_item_name' => esc_html__('New ', 'wptheaterly') . ' ' . $cat_label . ' ' . esc_html__(' Name', 'wptheaterly'),
					'add_new_item' => esc_html__('Add New ', 'wptheaterly') . ' ' . $cat_label,
					'edit_item' => esc_html__('Edit ', 'wptheaterly') . ' ' . $cat_label,
					'update_item' => esc_html__('Update ', 'wptheaterly') . ' ' . $cat_label,
					'view_item' => esc_html__('View ', 'wptheaterly') . ' ' . $cat_label,
					'separate_items_with_commas' => esc_html__('Separate ', 'wptheaterly') . ' ' . $cat_label . ' ' . esc_html__(' with commas', 'wptheaterly'),
					'add_or_remove_items' => esc_html__('Add or remove ', 'wptheaterly') . ' ' . $cat_label,
					'choose_from_most_used' => esc_html__('Choose from the most used', 'wptheaterly'),
					'popular_items' => esc_html__('Popular ', 'wptheaterly') . ' ' . $cat_label,
					'search_items' => esc_html__('Search ', 'wptheaterly') . ' ' . $cat_label,
					'not_found' => esc_html__('Not Found', 'wptheaterly'),
					'no_terms' => esc_html__('No ', 'wptheaterly'),
					'items_list' => $cat_label . ' ' . esc_html__(' list', 'wptheaterly'),
					'items_list_navigation' => $cat_label . ' ' . esc_html__(' list navigation', 'wptheaterly'),
				];
				$args = [
					'hierarchical' => true,
					"public" => true,
					'labels' => $labels,
					'show_ui' => true,
					'show_admin_column' => true,
					'update_count_callback' => '_update_post_term_count',
					'query_var' => true,
					'rewrite' => ['slug' => $cat_slug],
					'show_in_rest' => true,
					'rest_base' => 'mptrs_category'
				];
				register_taxonomy('mptrs_category', 'mptrs_item', $args);
			}
		}
		new WTBM_Taxonomy();
	}