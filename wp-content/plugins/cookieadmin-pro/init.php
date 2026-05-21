<?php
/*
* CookieAdmin
* https://cookieadmin.net
* (c) Softaculous Team
*/

if(!defined('ABSPATH')){
	die('Hacking Attempt!');
}


if(!class_exists('CookieAdminPro')){
#[\AllowDynamicProperties]
class CookieAdminPro{
}
}

// Prevent update of cookieadmin free
// This also work for auto update
if(!defined('SITEPAD')){
	add_filter('site_transient_update_plugins', 'cookieadmin_pro_disable_manual_update_for_plugin');
	add_filter('pre_site_transient_update_plugins', 'cookieadmin_pro_disable_manual_update_for_plugin');

	// Auto update free version after update pro version
	add_action('upgrader_process_complete', 'cookieadmin_pro_update_free_after_pro', 10, 2);
}

// Customize the powered by div in consent banner (if needed)
add_filter('cookieadmin_powered_by_html', '\CookieAdminPro\Enduser::powered_by', 10, 1);

// Add custom privacy policy and cookie policy links to the consent banner
add_filter('cookieadmin_privacy_policy_links', '\CookieAdminPro\Enduser::privacy_policy_links', 10, 2);
// Get reconsent icon URL
add_filter('cookieadmin_reconsent_icon_url', '\CookieAdminPro\Enduser::reconsent_icon_url', 10, 2);
// Scan Cookies pro
add_filter('cookieadmin_pro_scan_cookies', 'cookieadmin_pro_scan_cookies', 10, 1);

//Prune Consent logs
add_action('cookieadmin_daily_consent_log_pruning', '\CookieAdminPro\Cron::consent_log_pruning');
add_action('cookieadmin_daily_log_pruning_next_batch', '\CookieAdminPro\Cron::consent_log_pruning_batch', 10, 1);

//scan Hooks
add_action('cookieadmin_run_auto_cookie_scan', '\CookieAdminPro\Cron::cookieadmin_pro_run_auto_scan');
add_action('cookieadmin_run_auto_scan_batch', '\CookieAdmin\Admin\Scan::scan_cookies', 10, 1);

add_action('init', 'cookieadmin_pro_defaults');

//Reconsent Icon Hook
add_filter('cookieadmin_reconsent_icons', '\CookieAdminPro\Admin::reconsent_icons', 10, 2);

// Dashboard Consent Logs widget hook (free plugin provides the anchor)
add_action('cookieadmin_dashboard_main_widgets', '\CookieAdminPro\Admin::dashboard_consent_logs_widget');

// Add action to load CookieAdmin
add_action('plugins_loaded', 'cookieadmin_pro_load_plugin');
function cookieadmin_pro_load_plugin(){

	global $cookieadmin, $cookieadmin_settings;
	
	//Check if our scanner is visiting
	if(isset($_GET['cookieadmin_scanner'])){
		define('COOKIEADMIN_SCANNER', 1);
		return;
	}

	$cookieadmin_settings = get_option('cookieadmin_settings', []);
	
	// Load license
	cookieadmin_pro_load_license();
	
	cookieadmin_pro_update_checker();
			
	if(!defined('SITEPAD') && current_user_can('activate_plugins')){
		add_action('admin_notices', 'cookieadmin_pro_free_version_nag');
		
		// Softaculous Common notice to show that the license has expired.
		if(!empty($cookieadmin['license']) && empty($cookieadmin['license']['active']) && strpos($cookieadmin['license']['license'], 'SOFTWP') !== FALSE){
			add_action('admin_notices', 'cookieadmin_pro_expiry_notice');
			add_filter('softaculous_expired_licenses', 'cookieadmin_pro_plugins_expired');
		}
	}
	
	add_filter('cron_schedules', 'cookieadmin_pro_cron_schedules');
	
	if(!empty($cookieadmin_settings['cookieadmin_auto_scan']) && !wp_next_scheduled('cookieadmin_run_auto_cookie_scan')) {
		wp_schedule_event(time() + 30, 'cookieadmin_every_month', 'cookieadmin_run_auto_cookie_scan');
	}
	
	if(!empty($cookieadmin_settings['consent_logs_expiry']) && !wp_next_scheduled('cookieadmin_daily_consent_log_pruning')){
		
		//prefered night time for pruning
		$hour   = wp_rand(2, 4);
		$minute = wp_rand(0, 59);

		$random_time = sprintf('%02d:%02d:00', $hour, $minute);
		
		wp_schedule_event(strtotime($random_time, time()), 'daily', 'cookieadmin_daily_consent_log_pruning');
		
	}
	
	if(wp_doing_ajax()){
		add_action('wp_ajax_cookieadmin_pro_ajax_handler', 'cookieadmin_pro_ajax_handler');
		add_action('wp_ajax_nopriv_cookieadmin_pro_ajax_handler', 'cookieadmin_pro_ajax_handler');
	}

	if(!defined('SITEPAD')){
		// Check for updates
		include_once(COOKIEADMIN_PRO_DIR.'/includes/plugin-update-checker.php');
		$cookieadmin_updater = CookieAdmin_PucFactory::buildUpdateChecker(cookieadmin_pro_api_url().'updates.php?version='.COOKIEADMIN_PRO_VERSION, COOKIEADMIN_PRO_FILE);
		
		// Add the license key to query arguments
		$cookieadmin_updater->addQueryArgFilter('cookieadmin_pro_updater_filter_args');
		
		// Show the text to install the license key
		add_filter('puc_manual_final_check_link-cookieadmin-pro', 'cookieadmin_pro_updater_check_link', 10, 1);
	}
	
	if(is_admin()){
		return cookieadmin_pro_load_plugin_admin();
	}
	
	// Shortcodes
	add_shortcode('cookieadmin_render', '\CookieAdminPro\Enduser::render_cookie_data');
	//add_shortcode('cookieadmin_show_preference', '\CookieAdminPro\Enduser::show_cookie_preference');

	add_action('wp_enqueue_scripts', '\CookieAdminPro\Enduser::enqueue_scripts');
	add_action('wp_enqueue_scripts', '\CookieAdminPro\Enduser::enqueue_styles', 11);
	
	if(!empty($cookieadmin_settings['google_consent_mode_v2']) || !empty($cookieadmin_settings['clarity_consent'])){
		add_action('wp_head', '\CookieAdminPro\Enduser::wp_head', 0);
	}
	
	if(!empty($cookieadmin_settings['respect_gpc'])){
		add_filter('cookieadmin_override_gpc_html', '\CookieAdminPro\GPC::override_gpc', 10, 1);
		add_filter('cookieadmin_after_banner', '\CookieAdminPro\GPC::toast', 10, 1);
	}

	if(!empty($cookieadmin_settings['content_blocking'])){
		\CookieAdminPro\ContentBlock::init();
	}

}

function cookieadmin_pro_load_plugin_admin(){
	
	global $cookieadmin;
	
	if(!is_admin() || !current_user_can('administrator')){
		return false;
	}
	
	add_action('admin_enqueue_scripts', '\CookieAdminPro\Admin::enqueue_scripts');
	
	add_action('cookieadmin_before_scan_results', ['\CookieAdminPro\Admin', 'render_scan_pages_ui']);
	add_action('admin_menu', '\CookieAdminPro\Admin::plugin_menu');
	
	//Notices for background tasks
	if(method_exists('\CookieAdmin\Admin','scan_notice')){
		add_action('admin_notices', '\CookieAdmin\Admin::scan_notice');
	}
	if(method_exists('\CookieAdmin\Admin','consent_log_purge_notices')){
		add_action('admin_notices', '\CookieAdmin\Admin::consent_log_purge_notice');
	}
	
	// Showing user notice if Consent table is not present
	add_action('admin_notices', '\CookieAdminPro\Admin::table_missing_notice', 999);
	add_action('pre_update_option_cookieadmin_settings', '\CookieAdminPro\Admin::save_settings', 10, 3);

}

function cookieadmin_pro_free_version_nag(){
	
	if(!defined('COOKIEADMIN_VERSION')){
		return;
	}

	$dismissed_free = (int) get_option('cookieadmin_version_free_nag');
	$dismissed_pro = (int) get_option('cookieadmin_version_pro_nag');

	// Checking if time has passed since the dismiss.
	if(!empty($dismissed_free) && time() < $dismissed_pro && !empty($dismissed_pro) && time() < $dismissed_pro){
		return;
	}

	$showing_error = false;
	if(version_compare(COOKIEADMIN_VERSION, COOKIEADMIN_PRO_VERSION) > 0 && (empty($dismissed_pro) || time() > $dismissed_pro)){
		$showing_error = true;

		echo '<div class="notice notice-warning is-dismissible" id="cookieadmin-pro-version-notice" onclick="cookieadmin_pro_dismiss_notice(event)" data-type="pro">
		<p style="font-size:16px;">'.esc_html__('You are using an older version of CookieAdmin Pro. We recommend updating to the latest version to ensure seamless and uninterrupted use of the application.', 'cookieadmin').'</p>
	</div>';
	}elseif(version_compare(COOKIEADMIN_VERSION, COOKIEADMIN_PRO_VERSION) < 0 && (empty($dismissed_free) || time() > $dismissed_free)){
		$showing_error = true;

		echo '<div class="notice notice-warning is-dismissible" id="cookieadmin-pro-version-notice" onclick="cookieadmin_pro_dismiss_notice(event)" data-type="free">
		<p style="font-size:16px;">'.esc_html__('You are using an older version of CookieAdmin. We recommend updating to the latest free version to ensure smooth and uninterrupted use of the application.', 'cookieadmin').'</p>
	</div>';
	}
	
	if(!empty($showing_error)){
		wp_register_script('cookieadmin-pro-version-notice', '', array('jquery'), COOKIEADMIN_PRO_VERSION, true );
		wp_enqueue_script('cookieadmin-pro-version-notice');
		wp_add_inline_script('cookieadmin-pro-version-notice', '
	function cookieadmin_pro_dismiss_notice(e){
		e.preventDefault();
		let target = jQuery(e.target);

		if(!target.hasClass("notice-dismiss")){
			return;
		}

		let jEle = target.closest("#cookieadmin-pro-version-notice"),
		type = jEle.data("type");

		jEle.slideUp();
		
		jQuery.post("'.admin_url('admin-ajax.php').'", {
			cookieadmin_pro_security : "'.wp_create_nonce('cookieadmin_pro_admin_js_nonce').'",
			action: "cookieadmin_pro_ajax_handler",
			cookieadmin_act: "version_notice",
			type: type
		}, function(res){
			if(!res["success"]){
				alert(res["data"]);
			}
		}).fail(function(data){
			alert("There seems to be some issue dismissing this alert");
		});
	}');
	}
}
