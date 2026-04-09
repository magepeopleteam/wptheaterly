<?php
if (! defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_wtbm_install_required_plugins', 'wtbm_install_required_plugins_callback');
function wtbm_install_required_plugins_callback() {
    check_ajax_referer('wtbm_installer_nonce', 'security');

    if (!current_user_can('install_plugins')) {
        wp_send_json_error('Permission denied.');
    }

    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    include_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';

    $github_zip_url = 'https://github.com/magepeopleteam/magepeople-pdf-support/archive/refs/heads/master.zip';
    $github_plugin_file = 'magepeople-pdf-support-master/mage-pdf.php'; // Expected path

    // 1. Check if MagePeople PDF Support is installed
    $is_installed = false;
    $plugins = get_plugins();
    foreach ($plugins as $path => $data) {
        if (strpos($path, 'mage-pdf.php') !== false) {
            $github_plugin_file = $path;
            $is_installed = true;
            break;
        }
    }

    // 2. Install if missing
    if (!$is_installed) {
        $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
        $install = $upgrader->install($github_zip_url);

        if (is_wp_error($install) || !$install) {
            wp_send_json_error('Failed to download/install MagePeople PDF Support from GitHub.');
        }

        // Refresh plugin list to find the newly installed path
        wp_cache_delete('plugins', 'plugins');
        $plugins = get_plugins();
        foreach ($plugins as $path => $data) {
            if (strpos($path, 'mage-pdf.php') !== false) {
                $github_plugin_file = $path;
                break;
            }
        }
    }

    // 3. Activate the plugin
    $activate = activate_plugin($github_plugin_file);

    if (is_wp_error($activate)) {
        wp_send_json_error('Activation failed: ' . $activate->get_error_message());
    }


    // NUCLEAR OPTION: Clear all caches so the next page load sees the "Active" status
    wp_cache_flush();

    // 4. Redirect Logic (matching your setup)
    $finish_setup = get_option('mptrs_finish_quick_setup', 'No');
    $url = ($finish_setup === 'Yes') 
           ? admin_url('admin.php?page=mptrs_main_menu') 
           : admin_url('admin.php?page=mptrs_main_menu');

    wp_send_json_success($url);
}

 add_action('admin_enqueue_scripts', 'wbbm_plugin_admin_scripts');
function wbbm_plugin_admin_scripts() {
    // Load jQuery UI Dialog and the base theme
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style('wp-jquery-ui-dialog');
}

add_action('wp_ajax_wtbm_install_woocommerce',  'wtbm_install_woocommerce_callback');
function wtbm_install_woocommerce_callback() {
    check_ajax_referer('wtbm_installer_nonce', 'security');            
    if (!current_user_can('install_plugins')) {
                wp_send_json_error('Permission denied.');
            }
            $plugin_slug = 'woocommerce/woocommerce.php';            
            // Check if files exist. If not, install.
            if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin_slug)) {
                include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
                include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
                include_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
                $api = plugins_api('plugin_information', array('slug' => 'woocommerce', 'fields' => array('sections' => false)));
                if (is_wp_error($api)) {
                    wp_send_json_error('API Error: ' . $api->get_error_message());
                }
                // IMPORTANT: Use WP_Ajax_Upgrader_Skin for AJAX calls
                $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
                $install = $upgrader->install($api->download_link);
                if (is_wp_error($install) || !$install) {
                    wp_send_json_error('Installation failed. Check folder permissions.');
                }
                // FORCE WordPress to refresh the plugin list from disk
                wp_cache_delete('plugins', 'plugins');
            }

            // Double check search - sometimes folder is named 'woocommerce-1'
            $all_plugins = get_plugins();
            $actual_path = '';
            foreach ($all_plugins as $path => $data) {
                if (strpos($path, 'woocommerce.php') !== false) {
                    $actual_path = $path;
                    break;
                }
            }

            if (!$actual_path) {
                wp_send_json_error('Could not find the plugin file on disk.');
            }

            // Activate
            $activate = activate_plugin($actual_path);
            if (is_wp_error($activate)) {
                wp_send_json_error('Activation Error: ' . $activate->get_error_message());
            }

            // Determine redirect
            $finish_quick_setup = get_option('wbbm_finish_quick_setup', 'No');
            $url = ($finish_quick_setup === 'Yes') 
                ? admin_url('admin.php?page=mptrs_main_menu') 
                : admin_url('admin.php?page=mptrs_main_menu');

            wp_send_json_success($url);
        }

add_action('admin_enqueue_scripts', 'wtbm_enqueue_installer_script');
function wtbm_enqueue_installer_script() {
	// wp_enqueue_script('wtbm-installer', WTBM_PLUGIN_URL . '/admin/wc_installer.js', ['jquery'], time(), true);
	wp_localize_script('wtbm-installer', 'wtbmInstallerData', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('wtbm_installer_nonce')
				));
}


add_action('wp_ajax_wtbm_install_pdf_component', 'wtbm_install_pdf_component_callback');
function wtbm_install_pdf_component_callback() {
    check_ajax_referer('wtbm_installer_nonce', 'security');

    if (!current_user_can('install_plugins')) {
        wp_send_json_error(__('Permission denied.', 'wptheaterly'));
    }

    include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
    include_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
    include_once ABSPATH . 'wp-admin/includes/plugin.php';

    $url = 'https://github.com/magepeopleteam/magepeople-pdf-support/archive/refs/heads/master.zip';
    
    // 1. Check if it's already on the disk before trying to install
    $all_plugins = get_plugins();
    $activate_path = '';
    
    foreach ($all_plugins as $path => $data) {
        if (strpos($path, 'mage-pdf.php') !== false) {
            $activate_path = $path;
            break;
        }
    }

    // 2. Only run the installer if we can't find the file
    if ( empty($activate_path) ) {
        $upgrader = new Plugin_Upgrader(new WP_Ajax_Upgrader_Skin());
        $install = $upgrader->install($url);

        // If install returns an error, it might be because the folder exists but isn't in the plugin list yet
        if (is_wp_error($install)) {
            // If the error is 'folder_exists', we can still try to find and activate it
            if ($install->get_error_code() !== 'folder_exists') {
                wp_send_json_error(__('Installation failed: ', 'wptheaterly') . $install->get_error_message());
            }
        }

        // Refresh the list after installation attempt
        wp_cache_delete('plugins', 'plugins');
        $all_plugins = get_plugins();
        foreach ($all_plugins as $path => $data) {
            if (strpos($path, 'mage-pdf.php') !== false) {
                $activate_path = $path;
                break;
            }
        }
    }

    // 3. Activation Logic
    if ($activate_path) {
        $result = activate_plugin($activate_path);
        
        if (is_wp_error($result)) {
            wp_send_json_error(__('Activation failed: ', 'wptheaterly') . $result->get_error_message());
        }
        
        wp_cache_flush(); 
        wp_send_json_success();
    }

    wp_send_json_error(__('Could not find or activate mage-pdf.php. Please check folder permissions.', 'wptheaterly'));
}
