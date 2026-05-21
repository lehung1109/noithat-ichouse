<?php
/**
Plugin Name: FormLayer
Plugin URI: https://formlayer.net
Description: Build fast and powerful contact forms in WordPress with an intuitive drag-and-drop builder packed with smart features.
Version: 1.0.3
Author: Softaculous Team
Author URI: https://softaculous.com/
Text Domain: formlayer
License: GPLv2
*/

if(!defined('ABSPATH')){
	exit;
}

if(!function_exists('add_action')){
	echo 'You are not allowed to access this page directly.';
	exit;
}

// Define Constants
define('FORMLAYER_VERSION', '1.0.3' );
define('FORMLAYER_FILE', __FILE__);
define('FORMLAYER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('FORMLAYER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('FORMLAYER_ASSETS_URL', FORMLAYER_PLUGIN_URL . 'assets');

function formlayer_autoloader($class){
	if(!preg_match('/^FormLayer\\\(.*)/is', $class, $m)){
		return;
	}

	$m[1] = str_replace('\\', '/', $m[1]);

	$file = FORMLAYER_PLUGIN_DIR . 'main/' . strtolower($m[1]) . '.php';
	if(file_exists($file)){
		include_once($file);
	}
}

spl_autoload_register('formlayer_autoloader');
register_activation_hook(FORMLAYER_FILE, '\FormLayer\Install::activate');
register_deactivation_hook(FORMLAYER_FILE, '\FormLayer\Install::deactivate');
register_uninstall_hook(FORMLAYER_FILE, '\FormLayer\Install::uninstall');
add_action('plugins_loaded', 'formlayer_load_plugin');

/**
 * Initialize plugin on plugins_loaded hook
 */
function formlayer_load_plugin(){
	global $formlayer;

	if(empty($formlayer)){
		$formlayer = new stdClass();
	}

	if(wp_doing_ajax()){
		\FormLayer\Ajax::hooks();
	}

	// Load frontend class and init (priority 15)
	add_action('init', '\FormLayer\Frontend::init', 15);

	if(is_admin()){
		\FormLayer\Admin::init();
		return;
	}
}