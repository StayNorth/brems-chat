<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
Module Name: BREMS Chat
Module URI: https://perfexcrm.com
Description: The best and most integrated chat module for Perfex CRM
Version: 1.5.0
Author: Aleksandar Stojanov
Author URI: https://idevalex.com
*/

define('PR_CHAT_MODULE_NAME', 'prchat');
define('PR_CHAT_CSS_PATH', module_dir_url(PR_CHAT_MODULE_NAME, 'assets/css/'));
define('PR_CHAT_JS_PATH', module_dir_url(PR_CHAT_MODULE_NAME, 'assets/js/'));
define('PR_CHAT_ASSETS_PATH', module_dir_url(PR_CHAT_MODULE_NAME, 'assets/'));
define('PR_CHAT_MODULE_UPLOAD_PATH', module_dir_path(PR_CHAT_MODULE_NAME, 'uploads/'));
define('TABLE_CHATMESSAGES', 'tblchatmessages');
define('TABLE_CHATGROUPS', 'tblchatgroups');
define('TABLE_CHATGROUPMEMBERS', 'tblchatgroupmembers');
define('TABLE_CHATCLIENTMESSAGES', 'tblchatclientmessages');

/*
* Register the activation hook
*/
register_activation_hook(PR_CHAT_MODULE_NAME, 'prchat_activation_hook');

/*
* Register the deactivation hook
*/
register_deactivation_hook(PR_CHAT_MODULE_NAME, 'prchat_deactivation_hook');

/*
* Register the uninstall hook
*/
register_uninstall_hook(PR_CHAT_MODULE_NAME, 'prchat_uninstall_hook');

/*
* Register language files
*/
register_language_files(PR_CHAT_MODULE_NAME, ['chat']);

/*
* Load helper
*/
$CI = &get_instance();
$CI->load->helper(PR_CHAT_MODULE_NAME . '/prchat');

/*
* Load mutual and helper functions
*/
require_once(module_dir_path(PR_CHAT_MODULE_NAME, 'assets/module_includes/mutual_and_helper_functions.php'));

/*
* Load the chat model
*/
$CI->load->model(PR_CHAT_MODULE_NAME . '/prchat_model');

/*
* Inject the chat css and js files
*/
hooks()->add_action('app_admin_head', 'prchat_add_head_components');
hooks()->add_action('app_admin_footer', 'prchat_add_footer_components');

/*
* Inject the chat css and js files for clients
*/
hooks()->add_action('app_customers_head', 'prchat_add_clients_head_components');
hooks()->add_action('app_customers_footer', 'prchat_add_clients_footer_components');

/*
* Add settings tab
*/
hooks()->add_action('admin_init', 'prchat_add_settings_tab');

/*
* Add Sidebar Menu Item
*/
hooks()->add_action('admin_init', 'prchat_init_menu_items');

/*
* Check if chat is enabled
*/
if (get_option('prchat_active') == '1') {
    /*
    * Hook to inject the chat icon in the header
    */
    hooks()->add_action('after_render_top_navbar_items', 'prchat_inject_icon_in_header');
}

/**
 * Activation hook
 */
function prchat_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Deactivation hook
 */
function prchat_deactivation_hook()
{
    // Do something when module is deactivated
}

/**
 * Uninstall hook
 */
function prchat_uninstall_hook()
{
    // Do something when module is uninstalled
}

/**
 * Add Sidebar Menu Items
 */
function prchat_init_menu_items()
{
    $CI = &get_instance();

    if (is_staff_logged_in()) {
        $CI->app_menu->add_sidebar_menu_item('prchat', [
            'name'     => _l('pr_chat'),
            'href'     => admin_url('prchat/Prchat_Controller/chat_full_view'),
            'icon'     => 'fa fa-comments',
            'position' => 6,
        ]);
    }
}

/**
 * Inject the chat icon in the header
 */
function prchat_inject_icon_in_header()
{
    $CI = &get_instance();
    if (staff_can('view', 'prchat') || is_admin()) {
        echo '<li class="icon header-chat-icon" data-toggle="tooltip" title="' . _l('pr_chat') . '" data-placement="bottom">
                <a href="#" class="" onclick="prchat_toggle_chat(); return false;">
                    <i class="fa fa-comments"></i>
                    <span class="label label-info hidden" id="header-chat-icon-count"></span>
                </a>
            </li>';
    }
}

/**
 * Add head components for admin area
 */
function prchat_add_head_components()
{
    $CI = &get_instance();
    // Check if user is logged in
    if (!is_staff_logged_in()) {
        return;
    }

    // Check permissions
    if (!staff_can('view', 'prchat') && !is_admin()) {
        return;
    }

    echo '<link href="' . PR_CHAT_CSS_PATH . 'chat_styles.css?v=' . time() . '" rel="stylesheet">';
    echo '<link href="' . PR_CHAT_CSS_PATH . 'chat_statuses.css?v=' . time() . '" rel="stylesheet">';
    echo '<link href="' . PR_CHAT_CSS_PATH . 'lity.css?v=' . time() . '" rel="stylesheet">';
    echo '<link href="' . PR_CHAT_CSS_PATH . 'mentions.css?v=' . time() . '" rel="stylesheet">';
    
    // Check for theme preference
    if (get_option('prchat_theme_name') == 'dark') {
        echo '<link href="' . PR_CHAT_CSS_PATH . 'chat_full_dark_view.css?v=' . time() . '" rel="stylesheet">';
    } else {
        echo '<link href="' . PR_CHAT_CSS_PATH . 'chat_full_view.css?v=' . time() . '" rel="stylesheet">';
    }
}

/**
 * Add footer components for admin area
 */
function prchat_add_footer_components()
{
    $CI = &get_instance();
    // Check if user is logged in
    if (!is_staff_logged_in()) {
        return;
    }

    // Check permissions
    if (!staff_can('view', 'prchat') && !is_admin()) {
        return;
    }

    echo '<script src="' . PR_CHAT_JS_PATH . 'lity.min.js?v=' . time() . '"></script>';
    echo '<script src="' . PR_CHAT_JS_PATH . 'jscolor.js?v=' . time() . '"></script>';
    
    // Load Mentions
    echo '<script src="' . PR_CHAT_JS_PATH . 'mentions/underscore.js?v=' . time() . '"></script>';
    echo '<script src="' . PR_CHAT_JS_PATH . 'mentions/jquery-elastic.js?v=' . time() . '"></script>';
    echo '<script src="' . PR_CHAT_JS_PATH . 'mentions/mentions.js?v=' . time() . '"></script>';

    echo '<script src="' . PR_CHAT_JS_PATH . 'pr-chat.js?v=' . time() . '"></script>';
    echo '<script src="' . PR_CHAT_JS_PATH . 'audio/sound_app.js?v=' . time() . '"></script>';
    
    // Load emoparser
    echo '<script src="' . PR_CHAT_JS_PATH . 'emoparser.js?v=' . time() . '"></script>';

    // Init the chat
    require_once(module_views_path(PR_CHAT_MODULE_NAME, 'initViewCheck.php'));
}

/**
 * Add head components for clients area
 */
function prchat_add_clients_head_components()
{
    // Check if clients chat is enabled
    if (get_option('prchat_clients_enable_chat') == '0') {
        return;
    }

    if (is_client_logged_in()) {
        echo '<link href="' . PR_CHAT_ASSETS_PATH . 'clients/styles.css?v=' . time() . '" rel="stylesheet">';
        echo '<link href="' . PR_CHAT_CSS_PATH . 'lity.css?v=' . time() . '" rel="stylesheet">';
    }
}

/**
 * Add footer components for clients area
 */
function prchat_add_clients_footer_components()
{
    // Check if clients chat is enabled
    if (get_option('prchat_clients_enable_chat') == '0') {
        return;
    }

    if (is_client_logged_in()) {
        echo '<script src="' . PR_CHAT_JS_PATH . 'lity.min.js?v=' . time() . '"></script>';
        echo '<script src="' . PR_CHAT_JS_PATH . 'emoparser.js?v=' . time() . '"></script>';
        
        require_once(module_views_path(PR_CHAT_MODULE_NAME, 'initViewCheckClients.php'));
    }
}
