<?php
/*
* FormLayer Pro
* https://formlayer.net
* (c) FormLayer Team
*/

namespace FormLayerPro;

if(!defined('ABSPATH')){
	exit;
}

class Admin{

	static function init(){
		add_action('admin_enqueue_scripts', '\FormLayerPro\Admin::admin_enqueue');
		add_action('formlayer_render_license_page', '\FormLayerPro\Settings\License::template');
		add_action('formlayer_render_integrations', '\FormLayerPro\Settings\UI::integrations');
		add_action('formlayer_render_entries_tab', '\FormLayerPro\Settings\UI::entries');
		add_action('formlayer_render_form_integrations', '\FormLayerPro\Settings\UI::render_form_integrations');
		add_action('formlayer_render_modals', '\FormLayerPro\Settings\UI::render_modal');
		add_action('formlayer_render_turnstile_settings', '\FormLayerPro\Settings\UI::turnstile', 10, 1);
		add_action('formlayer_render_recaptcha_settings', '\FormLayerPro\Settings\UI::recaptcha', 10, 1);

		// Filter for dynamic field types and categories
		add_filter('formlayer_builder_categories', '\FormLayerPro\Fields::add_categories');
		add_filter('formlayer_field_types', '\FormLayerPro\Fields::add_field_types');
	}
	
	static function admin_enqueue($hook){
		if(false === strpos($hook, 'formlayer')){
			return;
		}

		wp_enqueue_style('formlayer-pro-admin', FORMLAYER_PRO_PLUGIN_URL.'assets/css/admin.css', [], FORMLAYER_PRO_VERSION);

		wp_enqueue_script('formlayer-pro-admin', FORMLAYER_PRO_PLUGIN_URL.'assets/js/admin.js', ['jquery'], FORMLAYER_PRO_VERSION, true);

		$all_settings = get_option('formlayer_integration_settings', []);
		$js_settings = [];
		
		// Map saved settings to the structure expected by the JS
		foreach($all_settings as $slug => $data){
			if(!empty($data['settings'])){
				$js_settings[$slug] = $data['settings'];
			}
		}

		wp_localize_script('formlayer-pro-admin', 'formlayer_pro', [
			'nonce' => wp_create_nonce('formlayer_pro_admin_nonce'),
			'ajax_url' => admin_url('admin-ajax.php'),
			'admin_page_url' => admin_url('admin.php?page=formlayer'),
			'settings' => $js_settings,
			'html_templates' => \FormLayerPro\Templates::js_templates()
		]);
	}
}