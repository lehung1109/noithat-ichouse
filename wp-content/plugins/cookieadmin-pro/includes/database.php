<?php

namespace CookieAdminPro;

if(!defined('COOKIEADMIN_PRO_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class Database{
	
	static $wpdb = '';
	static $consent_table = '';
	
	static function activate(){
		
		global $wpdb;
		
		self::$wpdb = $wpdb;
		self::$consent_table = esc_sql(self::$wpdb->prefix . 'cookieadmin_consents');
		self::cookieadmin_create_tables();
	}
	
	static function cookieadmin_create_tables() {
		
		$charset_collate = self::$wpdb->get_charset_collate();
		$db_path = !defined('SITEPAD') ? ABSPATH . 'wp-admin/includes/upgrade.php' : ABSPATH . 'site-admin/includes/upgrade.php';
		require_once($db_path);
		
		/*
			dbDelta does not supports comments and these comments are not being applied,
			So they could be the cause of issue which is preventing creation of this table
			for some user.

			consent_id -- Designed to store up to 128 characters for future expansion
			user_ip -- For storing anonymized IP (IPv4 or IPv6)
			country -- Full country name
			browser -- Browser User agent string
			domain -- Domain from which consent was submitted
			consent_status -- Stores 'accepted', 'rejected', 'partially accepted', etc.
		*/
		//Create Consent table
		$sql = "CREATE TABLE IF NOT EXISTS ".self::$consent_table." (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			consent_id VARCHAR(128) NOT NULL UNIQUE,
			user_ip VARBINARY(16) DEFAULT NULL,
			consent_time INT NOT NULL,
			country VARCHAR(150) DEFAULT NULL,
			browser TEXT DEFAULT NULL,
			domain VARCHAR(255) DEFAULT NULL,
			consent_status VARCHAR(50) NOT NULL
		) {$charset_collate};";
		
		dbDelta($sql);
	}
}


