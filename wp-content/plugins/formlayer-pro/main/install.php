<?php
/*
* FormLayer Pro
* https://formlayer.net
* (c) FormLayer Team
*/

namespace FormLayerPro;

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

class Install{

	static function activate(){
		global $wpdb;

		$table_name = $wpdb->prefix . 'formlayer_entries';
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			form_id bigint(20) NOT NULL,
			data longtext NOT NULL,
			status varchar(20) DEFAULT 'unread' NOT NULL,
			ip_address varchar(100) DEFAULT '' NOT NULL,
			user_agent text DEFAULT '' NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY form_id (form_id)
		) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);

		update_option('formlayer_pro_version', FORMLAYER_PRO_VERSION);
	}

	static function deactivate(){
		delete_option('formlayer_pro_version');
		delete_option('formlayer_integration_settings');
	}

	static function uninstall(){
		delete_option('formlayer_integration_settings');
	}

}