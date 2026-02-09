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
				$cpt = WTBM_Function::get_cpt();
				$label = WTBM_Function::get_name();
				$slug = WTBM_Function::get_slug();
				$icon = WTBM_Function::get_icon();
				$labels = [
					'name' => $label,
					'singular_name' => $label,
					'menu_name' => $label,
					'name_admin_bar' => $label,
					'archives' => $label . ' ' . esc_html__(' List', 'wptheaterly'),
					'attributes' => $label . ' ' . esc_html__(' List', 'wptheaterly'),
					'parent_item_colon' => $label . ' ' . esc_html__(' Item:', 'wptheaterly'),
					'all_items' => esc_html__('All ', 'wptheaterly') . ' ' . $label,
					'add_new_item' => esc_html__('Add New ', 'wptheaterly') . ' ' . $label,
					'add_new' => esc_html__('Add New ', 'wptheaterly') . ' ' . $label,
					'new_item' => esc_html__('New ', 'wptheaterly') . ' ' . $label,
					'edit_item' => esc_html__('Edit ', 'wptheaterly') . ' ' . $label,
					'update_item' => esc_html__('Update ', 'wptheaterly') . ' ' . $label,
					'view_item' => esc_html__('View ', 'wptheaterly') . ' ' . $label,
					'view_items' => esc_html__('View ', 'wptheaterly') . ' ' . $label,
					'search_items' => esc_html__('Search ', 'wptheaterly') . ' ' . $label,
					'not_found' => $label . ' ' . esc_html__(' Not found', 'wptheaterly'),
					'not_found_in_trash' => $label . ' ' . esc_html__(' Not found in Trash', 'wptheaterly'),
					'featured_image' => $label . ' ' . esc_html__(' Feature Image', 'wptheaterly'),
					'set_featured_image' => esc_html__('Set ', 'wptheaterly') . ' ' . $label . ' ' . esc_html__(' featured image', 'wptheaterly'),
					'remove_featured_image' => esc_html__('Remove ', 'wptheaterly') . ' ' . $label . ' ' . esc_html__(' featured image', 'wptheaterly'),
					'use_featured_image' => esc_html__('Use as', 'wptheaterly') . ' ' . $label . ' ' . esc_html__(' featured image', 'wptheaterly') . ' ' . $label . ' ' . esc_html__(' featured image', 'wptheaterly'),
					'insert_into_item' => esc_html__('Insert into', 'wptheaterly') . ' ' . $label,
					'uploaded_to_this_item' => esc_html__('Uploaded to this ', 'wptheaterly') . ' ' . $label,
					'items_list' => $label . ' ' . esc_html__(' list', 'wptheaterly'),
					'items_list_navigation' => $label . ' ' . esc_html__(' list navigation', 'wptheaterly'),
					'filter_items_list' => esc_html__('Filter ', 'wptheaterly') . ' ' . $label . ' ' . esc_html__(' list', 'wptheaterly')
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
                    'label' => esc_html__('Theater', 'wptheaterly'),
                    'supports' => ['title', 'thumbnail', 'editor'],
                    'show_in_menu' => false,
                    'capability_type' => 'post',
                ];
                register_post_type('wtbm_theater', $args);

                $args = [
                    'public' => true,
                    'label' => esc_html__('Show Time', 'wptheaterly'),
                    'supports' => ['title', 'thumbnail', 'editor'],
                    'show_in_menu' => false,
                    'capability_type' => 'post',
                ];
                register_post_type('wtbm_show_time', $args);

                $args = [
                    'public' => true,
                    'label' => esc_html__('Pricing Rules', 'wptheaterly'),
                    'supports' => ['title', 'thumbnail', 'editor'],
                    'show_in_menu' => false,
                    'capability_type' => 'post',
                ];
                register_post_type('wtbm_pricing', $args);

                $args = [
                    'public' => true,
                    'label' => esc_html__('Seat Booking', 'wptheaterly'),
                    'supports' => ['title', 'thumbnail', 'editor'],
                    'show_in_menu' => false,
                    'capability_type' => 'post',
                ];
                register_post_type('wtbm_booking', $args);

                $args = [
                    'public' => true,
                    'label' => esc_html__('Movie', 'wptheaterly'),
                    'supports' => ['title', 'thumbnail', 'editor'],
                    'show_in_menu' => false,
                    'capability_type' => 'post',
                    'publicly_queryable' => true,
                    'has_archive' => true,
                ];
                register_post_type('wtbm_movie', $args);

			}
		}
		new WTBP_CPT();
	}