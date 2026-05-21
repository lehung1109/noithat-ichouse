<?php

namespace FormLayer;

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

class Install{

	static function activate(){
		update_option('formlayer_version', FORMLAYER_VERSION);
	}

	static function deactivate(){
		delete_option('formlayer_version');
		delete_option('formlayer_settings');
		delete_option('formlayer_id_counter');
	}

	static function uninstall(){
		// delete options
	}
	
}