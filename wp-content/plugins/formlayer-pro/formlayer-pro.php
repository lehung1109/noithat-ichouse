<?php
/**
Plugin Name: FormLayer Pro
Plugin URI: https://formlayer.net
Description: Build fast and powerful contact forms in WordPress with an intuitive drag-and-drop builder packed with smart features.
Version: 1.0.3
Author: Softaculous Team
Author URI: https://softaculous.com/
Text Domain: formlayer-pro
License: GPLv2
*/

if(!defined( 'ABSPATH')){
	exit;
}

if(!function_exists('add_action')){
	echo 'You are not allowed to access this page directly.';
	exit;
}

// Define Constants
define('FORMLAYER_PRO_VERSION', '1.0.3' );
define('FORMLAYER_PRO_FILE', __FILE__);
define('FORMLAYER_PRO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FORMLAYER_PRO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FORMLAYER_PRO_ASSETS_URL', FORMLAYER_PRO_PLUGIN_URL . 'assets');
define('FORMLAYER_API', 'https://a.softaculous.com/formlayer');

include_once FORMLAYER_PRO_PLUGIN_DIR . 'functions.php';

function formlayer_pro_autoloader($class){
	if(!preg_match('/^FormLayerPro\\\(.*)/is', $class, $m)){
		return;
	}

	$m[1] = str_replace('\\', '/', $m[1]);

	// Include file
	if(file_exists(FORMLAYER_PRO_PLUGIN_DIR . 'main/'.strtolower($m[1]).'.php')){
		include_once(FORMLAYER_PRO_PLUGIN_DIR.'main/'.strtolower($m[1]).'.php');
	}
}

spl_autoload_register('formlayer_pro_autoloader');
register_activation_hook(FORMLAYER_PRO_FILE, '\FormLayerPro\Install::activate');
register_deactivation_hook(FORMLAYER_PRO_FILE, '\FormLayerPro\Install::deactivate');
register_uninstall_hook(FORMLAYER_PRO_FILE, '\FormLayerPro\Install::uninstall');
add_action('plugins_loaded', 'formlayer_pro_load_plugin');

add_filter('site_transient_update_plugins', 'formlayer_pro_disable_manual_update_for_plugin', 20);
add_filter('pre_site_transient_update_plugins', 'formlayer_pro_disable_manual_update_for_plugin', 20);
 
/**
 * Initialize plugin on plugins_loaded hook
 */
function formlayer_pro_load_plugin(){
	global $formlayer;

	if(empty($formlayer)){
		$formlayer = new stdClass();
	}

	formlayer_pro_load_license();

	formlayer_check_updates();

	include_once(FORMLAYER_PRO_PLUGIN_DIR . 'main/plugin-update-checker.php');
	$formlayer_updater = FormLayer_PucFactory::buildUpdateChecker(formlayer_pro_api_url().'/updates.php?version='.FORMLAYER_PRO_VERSION, FORMLAYER_PRO_FILE);

	if(defined('FORMLAYER_VERSION') && defined('FORMLAYER_FILE')){
		FormLayer_PucFactory::buildUpdateChecker(formlayer_pro_api_url().'/updates.php?type=free&version='.FORMLAYER_VERSION, FORMLAYER_FILE);
	}

	// Add the license key to query arguments
	$formlayer_updater->addQueryArgFilter('formlayer_pro_updater_filter_args');

	// Show the text to install the license key
	add_filter('puc_manual_final_check_link-formlayer-pro', 'formlayer_pro_updater_check_link', 10, 1);

	if(wp_doing_ajax()){
		\FormLayerPro\Ajax::hooks();
	}

	\FormLayerPro\Integrations::init();
	\FormLayerPro\Templates::init();
	\FormLayerPro\Frontend::init();

	if(is_admin()){
		\FormLayerPro\Admin::init();
		return;
	}
}

function formlayer_check_updates(){
	$current_version = get_option('formlayer_pro_version');
	$version = (int) str_replace('.', '', $current_version);

	// Is it first run ?
	if(empty($current_version)){
		\FormLayerPro\Install::activate();
		return;
	}

	update_option('formlayer_pro_version', FORMLAYER_PRO_VERSION);

}