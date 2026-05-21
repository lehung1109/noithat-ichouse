<?php

namespace CookieAdminPro;

if(!defined('COOKIEADMIN_PRO_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class GPC {
	
	/**
	 * Detect GPC signal from HTTP header or JavaScript
	 * 
	 * @return bool True if GPC is enabled, false otherwise
	 */
	static function detect_gpc_signal() {
		// Check HTTP header first
		if (isset($_SERVER['HTTP_SEC_GPC']) && $_SERVER['HTTP_SEC_GPC'] === '1') {
			return true;
		}
		
		return false;
	}
    
    /**
     * Create GPC compliance JSON file
     * 
     * @return bool True if file created successfully, false otherwise
     */
	static function create_gpc_json_file() {
		$gpc_path = ABSPATH . '.well-known/gpc.json';

		// Create directory if it doesn't exist
		if(!file_exists(dirname($gpc_path))){
			mkdir(dirname($gpc_path), 0755, true);
		}

		$gpc_content = json_encode([
			'gpc' => true,
			'lastUpdate' => date('c'),
		], JSON_PRETTY_PRINT);

		return file_put_contents($gpc_path, $gpc_content) !== false;
    }
	
	static function override_gpc($html){
		global $cookieadmin, $cookieadmin_settings;
		
		if(!self::detect_gpc_signal()){
		    return '';
		}
		
		// If GPC is not enabled we do not need Override
		if(empty($cookieadmin_settings) || empty($cookieadmin_settings['respect_gpc'])){
			return '';
		}
		
		$law = get_option('cookieadmin_law', 'cookieadmin_gdpr');
		$settings = get_option('cookieadmin_consent_settings', []);
		
		$text_color = '';
		if(!empty($settings) && !empty($settings[$law]) && !empty($settings[$law]['cookieadmin_details_wrapper_color'])){
			$text_color =  'color:'.$settings[$law]['cookieadmin_details_wrapper_color'];
		}

		return '<div id="cookieadmin_gpc_override" role="region" aria-labelledby="cookieadmin-override-gpc-heading" style="'.esc_attr($text_color).'">
			<div class="cookieadmin_header">
				<span>
					<label class="stitle" id="cookieadmin-override-gpc-heading" for="cookieadmin-respect-gpc">'.esc_html__('Override GPC', 'cookieadmin').'</label>
					<label class="cookieadmin_remark">[[remark]]</label>
				</span>
				<label class="cookieadmin_toggle" aria-labelledby="cookieadmin-override-heading">
					<input type="checkbox" id="cookieadmin-override_gpc" value="true">
					<span class="cookieadmin_slider"></span>
				</label>
			</div>
			<div class="cookieadmin_desc">'.esc_html(!empty($cookieadmin_settings['gpc_override_warning']) ? $cookieadmin_settings['gpc_override_warning'] : $cookieadmin['gpc_override_warning_default']).'</div>
		</div>';
	}
	
	static function toast($template){
		global $cookieadmin, $cookieadmin_settings;
		
		if(!self::detect_gpc_signal()){
		    return $template;
		}
		
		$law = get_option('cookieadmin_law', 'cookieadmin_gdpr');
		$settings = get_option('cookieadmin_consent_settings', []);
		
		$background_color = '#374FD4';
		if(!empty($settings) && !empty($settings[$law]) && !empty($settings[$law]['cookieadmin_re_consent_bg_color'])){
			$background_color =  $settings[$law]['cookieadmin_re_consent_bg_color'];
		}

		$toast = '<div id="cookieadmin-gpc-toast" style="background-color: '.esc_attr($background_color).'">
	<span style="line-height: 1.4;">'.esc_html(!empty($cookieadmin_settings['gpc_message']) ? $cookieadmin_settings['gpc_message'] : $cookieadmin['gpc_message_default']).'</span>
	<button style="background: transparent; border: none; color: #ffffff; font-size: 20px; cursor: pointer; padding: 0; line-height: 1; opacity: 0.7; transition: opacity 0.2s;" aria-label="'.esc_html__('Close notification', 'cookieadmin').'">&times;</button>
</div>';

		return $template . $toast;
	}
}
