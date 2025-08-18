<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('MPTRS_Taxonomy')) {
		class MPTRS_Taxonomy {
			public function __construct() {
				//add_action( 'init', [ $this, 'taxonomy' ] );
			}
			public function taxonomy() {
				$label = MPTRS_Function::get_name();
				$cat_label = MPTRS_Function::get_category_label();
				$cat_slug = MPTRS_Function::get_category_slug();
				$labels = [
					'name' => $label . ' ' . $cat_label,
					'singular_name' => $label . ' ' . $cat_label,
					'menu_name' => $cat_label,
					'all_items' => esc_html__('All ', 'theaterly') . ' ' . $label . ' ' . $cat_label,
					'parent_item' => esc_html__('Parent ', 'theaterly') . ' ' . $cat_label,
					'parent_item_colon' => esc_html__('Parent ', 'theaterly') . ' ' . $cat_label,
					'new_item_name' => esc_html__('New ', 'theaterly') . ' ' . $cat_label . ' ' . esc_html__(' Name', 'theaterly'),
					'add_new_item' => esc_html__('Add New ', 'theaterly') . ' ' . $cat_label,
					'edit_item' => esc_html__('Edit ', 'theaterly') . ' ' . $cat_label,
					'update_item' => esc_html__('Update ', 'theaterly') . ' ' . $cat_label,
					'view_item' => esc_html__('View ', 'theaterly') . ' ' . $cat_label,
					'separate_items_with_commas' => esc_html__('Separate ', 'theaterly') . ' ' . $cat_label . ' ' . esc_html__(' with commas', 'theaterly'),
					'add_or_remove_items' => esc_html__('Add or remove ', 'theaterly') . ' ' . $cat_label,
					'choose_from_most_used' => esc_html__('Choose from the most used', 'theaterly'),
					'popular_items' => esc_html__('Popular ', 'theaterly') . ' ' . $cat_label,
					'search_items' => esc_html__('Search ', 'theaterly') . ' ' . $cat_label,
					'not_found' => esc_html__('Not Found', 'theaterly'),
					'no_terms' => esc_html__('No ', 'theaterly'),
					'items_list' => $cat_label . ' ' . esc_html__(' list', 'theaterly'),
					'items_list_navigation' => $cat_label . ' ' . esc_html__(' list navigation', 'theaterly'),
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
		new MPTRS_Taxonomy();
	}