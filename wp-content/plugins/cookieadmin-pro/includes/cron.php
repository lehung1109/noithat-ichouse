<?php

namespace CookieAdminPro;

if(!defined('COOKIEADMIN_PRO_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class Cron{
	
	// Handles the consent logs deletion
	static function consent_log_pruning(){

		if(get_transient('cookieadmin_pruning_in_progress')){
			return;
		}

		$cookieadmin_settings = get_option('cookieadmin_settings', array('consent_logs_expiry' => 0, 'consent_logs_expiry_days' => 0));
		$consent_logs_expiry = (int) $cookieadmin_settings['consent_logs_expiry_days'];

		if(empty($cookieadmin_settings['consent_logs_expiry']) || $consent_logs_expiry <= 0){
			return;
		}

		$retention_limit = time() - ($consent_logs_expiry * DAY_IN_SECONDS);

		set_transient('cookieadmin_pruning_in_progress', 'true', 3600);

		self::consent_log_pruning_batch($retention_limit);
	}
	
	// Does the actual logs cleaning
	static function consent_log_pruning_batch($retention_limit = 0){
		global $wpdb;

		$retention_limit = absint($retention_limit);
		if(empty($retention_limit)){
			delete_transient('cookieadmin_pruning_in_progress');
			return;
		}
		
		$table_name = $wpdb->prefix . 'cookieadmin_consents';
		
		$rows_deleted = $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table_name} WHERE consent_time < %d LIMIT 100",
				$retention_limit
			)
		);
		
		if(!empty($wpdb->last_error)){
			update_option('cookieadmin_consent_purge', [
				'status' => 3,
				'success' => false,
				'message'  => $wpdb->last_error
			]);

			delete_transient('cookieadmin_pruning_in_progress');
			return;
		}
		
		$deletion_option = get_option('cookieadmin_consent_purge', ['status' => 2, 'count' => 0]);
		$deletion_count = $rows_deleted + (empty($deletion_option['count']) ? 0 : $deletion_option['count']);
		update_option('cookieadmin_consent_purge', ['status' => 2, 'count' => $deletion_count]);

		if ($rows_deleted >= 100) {
			wp_schedule_single_event(time() + 10, 'cookieadmin_daily_log_pruning_next_batch', array($retention_limit));
		}else{
			update_option('cookieadmin_consent_purge', ['success' => true, 'status' => 3, 'count' => $deletion_count]);
			delete_transient('cookieadmin_pruning_in_progress');
		}
			
	}
	
	static function cookieadmin_pro_run_auto_scan(){

		$loaded_settings = get_option('cookieadmin_settings');

		if(empty($loaded_settings['cookieadmin_auto_scan']) || get_transient('cookieadmin_auto_scan_in_progress')){
			return;
		}

		set_transient('cookieadmin_auto_scan_in_progress', time(), 10 * MINUTE_IN_SECONDS);

		// Status : 2,3 - running,completed
		update_option('cookieadmin_scan', array('status' => 2, 'update' => time())); 

		\CookieAdmin\Admin\Scan::scan_cookies([]);

	}
	
}

