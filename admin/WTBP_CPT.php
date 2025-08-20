<?php
	/*
   * @Author 		engr.sumonazma@gmail.com
   * Copyright: 	mage-people.com
   */
	if (!defined('ABSPATH')) {
		die;
	} // Cannot access pages directly.
	if (!class_exists('WTBP_CPT')) {
		class WTBP_CPT {
			public function __construct() {
				add_action('init', [$this, 'add_cpt']);
			}
			public function add_cpt(): void {
				$cpt = MPTRS_Function::get_cpt();
				$label = MPTRS_Function::get_name();
				$slug = MPTRS_Function::get_slug();
				$icon = MPTRS_Function::get_icon();
				$labels = [
					'name' => $label,
					'singular_name' => $label,
					'menu_name' => $label,
					'name_admin_bar' => $label,
					'archives' => $label . ' ' . esc_html__(' List', 'theaterly'),
					'attributes' => $label . ' ' . esc_html__(' List', 'theaterly'),
					'parent_item_colon' => $label . ' ' . esc_html__(' Item:', 'theaterly'),
					'all_items' => esc_html__('All ', 'theaterly') . ' ' . $label,
					'add_new_item' => esc_html__('Add New ', 'theaterly') . ' ' . $label,
					'add_new' => esc_html__('Add New ', 'theaterly') . ' ' . $label,
					'new_item' => esc_html__('New ', 'theaterly') . ' ' . $label,
					'edit_item' => esc_html__('Edit ', 'theaterly') . ' ' . $label,
					'update_item' => esc_html__('Update ', 'theaterly') . ' ' . $label,
					'view_item' => esc_html__('View ', 'theaterly') . ' ' . $label,
					'view_items' => esc_html__('View ', 'theaterly') . ' ' . $label,
					'search_items' => esc_html__('Search ', 'theaterly') . ' ' . $label,
					'not_found' => $label . ' ' . esc_html__(' Not found', 'theaterly'),
					'not_found_in_trash' => $label . ' ' . esc_html__(' Not found in Trash', 'theaterly'),
					'featured_image' => $label . ' ' . esc_html__(' Feature Image', 'theaterly'),
					'set_featured_image' => esc_html__('Set ', 'theaterly') . ' ' . $label . ' ' . esc_html__(' featured image', 'theaterly'),
					'remove_featured_image' => esc_html__('Remove ', 'theaterly') . ' ' . $label . ' ' . esc_html__(' featured image', 'theaterly'),
					'use_featured_image' => esc_html__('Use as', 'theaterly') . ' ' . $label . ' ' . esc_html__(' featured image', 'theaterly') . ' ' . $label . ' ' . esc_html__(' featured image', 'theaterly'),
					'insert_into_item' => esc_html__('Insert into', 'theaterly') . ' ' . $label,
					'uploaded_to_this_item' => esc_html__('Uploaded to this ', 'theaterly') . ' ' . $label,
					'items_list' => $label . ' ' . esc_html__(' list', 'theaterly'),
					'items_list_navigation' => $label . ' ' . esc_html__(' list navigation', 'theaterly'),
					'filter_items_list' => esc_html__('Filter ', 'theaterly') . ' ' . $label . ' ' . esc_html__(' list', 'theaterly')
				];
				$args = [
					'public' => true,
					'labels' => $labels,
					'menu_icon' => $icon,
					'supports' => ['title', 'editor', 'thumbnail'],
					'show_in_rest' => true,
					'capability_type' => 'post',
					'publicly_queryable' => true,  // you should be able to query it
					'show_ui' => false,  // you should be able to edit it in wp-admin
					'exclude_from_search' => true,  // you should exclude it from search results
					'show_in_nav_menus' => false,  // you shouldn't be able to add it to menus
					'has_archive' => false,  // it shouldn't have archive page
					'rewrite' => ['slug' => $slug],
				];
				register_post_type($cpt, $args);


                $args = [
                    'public' => true,
                    'label' => esc_html__('Theater', 'theaterly'),
                    'supports' => ['title', 'thumbnail', 'editor'],
                    'show_in_menu' => false,
                    'capability_type' => 'post',
                ];
                register_post_type('wtbm_theater', $args);

                $args = [
                    'public' => true,
                    'label' => esc_html__('Show Time', 'theaterly'),
                    'supports' => ['title', 'thumbnail', 'editor'],
                    'show_in_menu' => false,
                    'capability_type' => 'post',
                ];
                register_post_type('wtbm_show_time', $args);

                $args = [
                    'public' => true,
                    'label' => esc_html__('Pricing Rules', 'theaterly'),
                    'supports' => ['title', 'thumbnail', 'editor'],
                    'show_in_menu' => false,
                    'capability_type' => 'post',
                ];
                register_post_type('wtbm_pricing', $args);

			}
		}
		new WTBP_CPT();
	}