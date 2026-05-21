<?php

namespace CookieAdmin\Admin;

if(!defined('COOKIEADMIN_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class Settings{
	
	static function settings(){
		global $cookieadmin, $cookieadmin_lang, $cookieadmin_error, $cookieadmin_msg, $cookieadmin_settings;
		
		$view = get_option('cookieadmin_law', 'cookieadmin_gdpr');	
		$policy = cookieadmin_load_policy();

		$cookieadmin_requires_pro = \CookieAdmin\Admin::is_feature_available(1);
		
		\CookieAdmin\Admin::header_theme(__('Settings', 'cookieadmin'));
		
		if(empty($cookieadmin_settings)){
			$cookieadmin_settings = get_option('cookieadmin_settings', []);
		}
		
		echo '
		<div class="cookieadmin_consent-wrap">
			<form action="" method="post" id="setting_submenu">

			<div class="cookieadmin-card">
				<div class="cookieadmin-card-header">
					<span class="cookieadmin-card-title"><span class="dashicons dashicons-download"></span> '.esc_html__('Cookie Loading', 'cookieadmin').'</span>
				</div>
				<div class="cookieadmin-card-body">
					<div class="cookieadmin-setting">
						<label class="cookieadmin-title">'.esc_html__('Load cookies prior to consent', 'cookieadmin').'
							<span class="dashicons dashicons-info cookieadmin-tooltip-box" data-tip="'.esc_html__('Selected category of cookies will be loaded before user consent.', 'cookieadmin').'"></span>
						</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
								<input name="cookieadmin_preload[]" type="checkbox" value="necessary" checked disabled>
								'.esc_html__('Necessary', 'cookieadmin').'
							</label>
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
								<input name="cookieadmin_preload[]" type="checkbox" id="functional_preload" value="functional" '.(!empty($policy[$view]['preload']) && in_array("functional", $policy[$view]['preload']) ? 'checked' : '').'>
								'.esc_html__('Functional', 'cookieadmin').'
							</label>
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
								<input name="cookieadmin_preload[]" type="checkbox" id="analytics_preload" value="analytics" '.(!empty($policy[$view]['preload']) && in_array("analytics", $policy[$view]['preload']) ? 'checked' : '').'>
								'.esc_html__('Analytical', 'cookieadmin').'
							</label>
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
								<input name="cookieadmin_preload[]" type="checkbox" id="marketing_preload" value="marketing" '.(!empty($policy[$view]['preload']) && in_array("marketing", $policy[$view]['preload']) ? 'checked' : '').'>
								'.esc_html__('Advertisement', 'cookieadmin').'
							</label>
							
							<div class="cookieadmin-collapsible-notice" style="display:'.(!empty($policy[$view]['preload'])? 'block':'none').'">'.esc_html__('Loading cookies prior to receiving user consent will make your website non-compliant with GDPR.', 'cookieadmin').'</div>
						</div>
					</div>

					<div class="cookieadmin-setting">
						<label class="cookieadmin-title" for="cookieadmin_reload_on_consent">'.esc_html__('Reload page on consent', 'cookieadmin').'
							<span class="dashicons dashicons-info cookieadmin-tooltip-box" data-tip="'.esc_html__('Page will be loaded on user consent.', 'cookieadmin').'"></span>
						</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-toggle-wrap">
								<input name="cookieadmin_reload_on_consent" type="checkbox" id="cookieadmin_reload_on_consent" '.(!empty($policy[$view]['reload_on_consent']) ? 'checked' : '').'>
								<div class="cookieadmin-toggle-track">
									<div class="cookieadmin-toggle-thumb"></div>
								</div>
							</label>
						</div>
					</div>
				</div>
			</div>';
			
		// Card: Resource blocking
		echo '
			<div class="cookieadmin-card cookieadmin-mt-16">
				<div class="cookieadmin-card-header">
					<span class="cookieadmin-card-title"><span class="dashicons dashicons-media-document"></span> '.esc_html__('Resource Blocking', 'cookieadmin').'</span>
				</div>
				<div class="cookieadmin-card-body">
					<div class="cookieadmin-setting">
						<label class="cookieadmin-title" for="cookieadmin_block_scripts">'.esc_html__('Block scripts', 'cookieadmin').'
							<span class="dashicons dashicons-info cookieadmin-tooltip-box" data-tip="'.esc_html__('It blocks the scripts related to the scanned Cookies.', 'cookieadmin').'"></span>
						</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-toggle-wrap">
								<input name="cookieadmin_block_scripts" type="checkbox" id="cookieadmin_block_scripts" '.(!empty($cookieadmin_settings['block_scripts']) ? 'checked' : '').'>
								<div class="cookieadmin-toggle-track">
									<div class="cookieadmin-toggle-thumb"></div>
								</div>
							</label>
						</div>
					</div>
					<div class="cookieadmin-setting setting-blocking" cookieadmin-pro-only="1">
						<label class="cookieadmin-title" for="cookieadmin_content_blocking">'.esc_html__('Content Blocking', 'cookieadmin').'
							<span class="dashicons dashicons-info cookieadmin-tooltip-box"  data-tip="'.esc_html__('Block third-party content which uses iframes to load cookies until user consent is given.', 'cookieadmin').'"></span>
						</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-toggle-wrap">';
								if(defined('COOKIEADMIN_PRO_VERSION')){
									echo '<input name="cookieadmin_content_blocking" type="checkbox" id="cookieadmin_content_blocking" '.(!empty($cookieadmin_settings['content_blocking']) && cookieadmin_is_pro() ? 'checked' : '').' />';
								} else {
									echo '<input type="checkbox" id="cookieadmin_content_blocking" />';
								}
								
								echo '<div class="cookieadmin-toggle-track">
									<div class="cookieadmin-toggle-thumb"></div>
								</div>
								'.wp_kses_post($cookieadmin_requires_pro).'
							</label>
						</div>
					</div>

					<div class="cookieadmin-content-blocking-options" style="display: '.(!empty($cookieadmin_settings['content_blocking']) ? 'block' : 'none').';">
						<div class="cookieadmin-cb-services">
							<label class="cookieadmin-title"><input type="checkbox" name="cookieadmin_content_blocking_services[]" value="youtube" '.(!empty($cookieadmin_settings['content_blocking_services']) && in_array('youtube', $cookieadmin_settings['content_blocking_services']) ? 'checked' : '').'> '.esc_html__('YouTube', 'cookieadmin').'</label>
							<label class="cookieadmin-title"><input type="checkbox" name="cookieadmin_content_blocking_services[]" value="vimeo" '.(!empty($cookieadmin_settings['content_blocking_services']) && in_array('vimeo', $cookieadmin_settings['content_blocking_services']) ? 'checked' : '').'> '.esc_html__('Vimeo', 'cookieadmin').'</label>
							<label class="cookieadmin-title"><input type="checkbox" name="cookieadmin_content_blocking_services[]" value="soundcloud" '.(!empty($cookieadmin_settings['content_blocking_services']) && in_array('soundcloud', $cookieadmin_settings['content_blocking_services']) ? 'checked' : '').'> '.esc_html__('SoundCloud', 'cookieadmin').'</label>
							<label class="cookieadmin-title"><input type="checkbox" name="cookieadmin_content_blocking_services[]" value="dailymotion" '.(!empty($cookieadmin_settings['content_blocking_services']) && in_array('dailymotion', $cookieadmin_settings['content_blocking_services']) ? 'checked' : '').'> '.esc_html__('Dailymotion', 'cookieadmin').'</label>
							<label class="cookieadmin-title"><input type="checkbox" name="cookieadmin_content_blocking_services[]" value="maps" '.(!empty($cookieadmin_settings['content_blocking_services']) && in_array('maps', $cookieadmin_settings['content_blocking_services']) ? 'checked' : '').'> '.esc_html__('Google Maps', 'cookieadmin').'</label>
						</div>
					</div>
				</div>
			</div>';

		// Card: Advanced Features (PRO)
		echo '
			<div class="cookieadmin-card cookieadmin-mt-16" cookieadmin-pro-only="1">
				<div class="cookieadmin-card-header">
					<span class="cookieadmin-card-title"><span class="dashicons dashicons-admin-generic"></span> '.esc_html__('Advanced Features', 'cookieadmin').wp_kses_post($cookieadmin_requires_pro).'</span>
				</div>
				<div class="cookieadmin-card-body">
					<div class="cookieadmin-setting">
						<label class="cookieadmin-title" for="cookieadmin_google_consent_mode_v2">'.esc_html__('Google Consent Mode v2', 'cookieadmin').'
							<span class="dashicons dashicons-info cookieadmin-tooltip-box" data-tip="'.esc_html__('Enable Google consent mode v2.', 'cookieadmin').'"></span>
						</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-toggle-wrap">
								<input name="cookieadmin_google_consent_mode_v2" type="checkbox" id="cookieadmin_google_consent_mode_v2" '.(!empty($cookieadmin_settings['google_consent_mode_v2']) && cookieadmin_is_pro() ? 'checked' : '').'>
								<div class="cookieadmin-toggle-track">
									<div class="cookieadmin-toggle-thumb"></div>
								</div>
							</label>
						</div>
					</div>

					<div class="cookieadmin-setting">
						<label class="cookieadmin-title" for="cookieadmin_clarity_consent">'.esc_html__('Clarity Consent Mode V2', 'cookieadmin').'
							<span class="dashicons dashicons-info cookieadmin-tooltip-box" data-tip="'.esc_html__('Enable Microsoft Clarity consent mode v2.', 'cookieadmin').'"></span>
						</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-toggle-wrap">
								<input name="cookieadmin_clarity_consent" type="checkbox" id="cookieadmin_clarity_consent" '.(!empty($cookieadmin_settings['clarity_consent']) && cookieadmin_is_pro() ? 'checked' : '').'>
								<div class="cookieadmin-toggle-track">
									<div class="cookieadmin-toggle-thumb"></div>
								</div>
							</label>
						</div>
					</div>

					<div class="cookieadmin-setting">
						<label class="cookieadmin-title" for="cookieadmin_hide_powered_by">'.esc_html__('Hide Powered by Link', 'cookieadmin').'
							<span class="dashicons dashicons-info cookieadmin-tooltip-box" data-tip="'.esc_html__('Hide powered by CookieAdmin on banner.', 'cookieadmin').'"></span>
						</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-toggle-wrap">
								<input name="cookieadmin_hide_powered_by" type="checkbox" id="cookieadmin_hide_powered_by" '.(!empty($cookieadmin_settings['hide_powered_by']) && cookieadmin_is_pro() ? 'checked' : '').'>
								<div class="cookieadmin-toggle-track">
									<div class="cookieadmin-toggle-thumb"></div>
								</div>
							</label>
						</div>
					</div>

					<div class="cookieadmin-setting">
						<label class="cookieadmin-title" for="cookieadmin_hide_reconsent">'.esc_html__('Hide Re-consent Icon', 'cookieadmin').'
							<span class="dashicons dashicons-info cookieadmin-tooltip-box" data-tip="'.esc_html__('Hide reconsent icon after user consent.', 'cookieadmin').'"></span>
						</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-toggle-wrap">
								<input name="cookieadmin_hide_reconsent" type="checkbox" id="cookieadmin_hide_reconsent" '.(!empty($cookieadmin_settings['hide_reconsent']) && cookieadmin_is_pro() ? 'checked' : '').'>
								<div class="cookieadmin-toggle-track">
									<div class="cookieadmin-toggle-thumb"></div>
								</div>
							</label>
						</div>
					</div>

					<div class="cookieadmin-setting">
						<label class="cookieadmin-title" for="cookieadmin_auto_scan">'.esc_html__('Auto Cookies Scan', 'cookieadmin').'
							<span class="dashicons dashicons-info cookieadmin-tooltip-box" data-tip="'.esc_html__('Monthly auto scan will detect cookies.', 'cookieadmin').'"></span>
						</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-toggle-wrap">
								<input name="cookieadmin_auto_scan" type="checkbox" id="cookieadmin_auto_scan" '.(!empty($cookieadmin_settings['cookieadmin_auto_scan']) && cookieadmin_is_pro() ? 'checked' : '').'>
								<div class="cookieadmin-toggle-track">
									<div class="cookieadmin-toggle-thumb"></div>
								</div>
							</label>
						</div>
					</div>
					<div class="cookieadmin-setting">
						<label class="cookieadmin-title" for="cookieadmin_shared_subdomain_consent">'.esc_html__('Shared Subdomain Consent', 'cookieadmin').'
							<span class="dashicons dashicons-info cookieadmin-tooltip-box"  data-tip="'.esc_html__('Enable shared consent across subdomains.', 'cookieadmin').'"></span>
						</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-toggle-wrap">
								<input name="cookieadmin_shared_subdomain_consent" type="checkbox" id="cookieadmin_shared_subdomain_consent" '.(!empty($cookieadmin_settings['shared_subdomain_consent']) && cookieadmin_is_pro() ? 'checked' : '').'>
								<div class="cookieadmin-toggle-track">
									<div class="cookieadmin-toggle-thumb"></div>
								</div>
							</label>
						</div>
					</div>
				</div>
			</div>';

		// Card: Data Management (PRO)
		echo '
			<div class="cookieadmin-card cookieadmin-mt-16" cookieadmin-pro-only="1">
				<div class="cookieadmin-card-header">
					<span class="cookieadmin-card-title"><span class="dashicons dashicons-database"></span> '.esc_html__('Data Management', 'cookieadmin').wp_kses_post($cookieadmin_requires_pro).'</span>
				</div>
				<div class="cookieadmin-card-body">
					<div class="cookieadmin-setting">
						<label class="cookieadmin-title" for="cookieadmin_consent_logs_expiry">'.esc_html__('Consent Log Cleanup', 'cookieadmin').'
							<span class="dashicons dashicons-info cookieadmin-tooltip-box" data-tip="'.esc_html__('Daily auto delete consent logs older than the set limit.', 'cookieadmin').'"></span>
						</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-toggle-wrap">
								<input name="cookieadmin_consent_logs_expiry" type="checkbox" id="cookieadmin_consent_logs_expiry" '.(!empty($cookieadmin_settings['consent_logs_expiry']) && cookieadmin_is_pro() ? 'checked' : '').'>
								<div class="cookieadmin-toggle-track">
									<div class="cookieadmin-toggle-thumb"></div>
								</div>
							</label>
							<input name="cookieadmin_consent_logs_expiry_days" id="cookieadmin_consent_logs_expiry_days" class="cookieadmin-tooltip-box" value="'.((!empty($cookieadmin_settings['consent_logs_expiry_days']) && cookieadmin_is_pro()) ? esc_attr($cookieadmin_settings['consent_logs_expiry_days']) : '365').'" data-tip="'.esc_html__('Keep consent logs for these many days', 'cookieadmin').'" style="width:60px; text-align:center; margin:0 10px;">
							<input type="button" class="cookieadmin-btn cookieadmin-btn-sm '.((cookieadmin_is_pro()) ? ' cookieadmin-btn-danger cookieadmin-tooltip-box cookieadmin-purge-consent-btn' : '').'" data-tip="'.esc_html__('Delete consent logs older than the set limit (runs once)', 'cookieadmin').'" value="'.esc_html__('Delete Now', 'cookieadmin').'"/>
						</div>
					</div>
				</div>
			</div>';

		// Card: Global Privacy Control (PRO)
		echo '
			<div class="cookieadmin-card cookieadmin-mt-16" cookieadmin-pro-only="1">
				<div class="cookieadmin-card-header">
					<span class="cookieadmin-card-title"><span class="dashicons dashicons-privacy"></span> '.esc_html__('Global Privacy Control', 'cookieadmin').wp_kses_post($cookieadmin_requires_pro).'</span>
				</div>
				<div class="cookieadmin-card-body">
					<div class="cookieadmin-setting">
						<label class="cookieadmin-title" for="cookieadmin_respect_gpc">'.esc_html__('Respect GPC', 'cookieadmin').'
							<span class="dashicons dashicons-info cookieadmin-tooltip-box" data-tip="'.esc_html__('Automatically honor GPC signals from browsers. When enabled, users with GPC enabled will automatically have non-essential cookies rejected.', 'cookieadmin').'"></span>
						</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-toggle-wrap">
								<input name="cookieadmin_respect_gpc" type="checkbox" id="cookieadmin_respect_gpc" '.(!empty($cookieadmin_settings['respect_gpc']) && cookieadmin_is_pro() ? 'checked' : '').'>
								<div class="cookieadmin-toggle-track">
									<div class="cookieadmin-toggle-thumb"></div>
								</div>
							</label>
						</div>
					</div>

					<div class="cookieadmin-setting">
						<label class="cookieadmin-title" for="cookieadmin_gpc_message">'.esc_html__('GPC Message', 'cookieadmin').'
							<span class="dashicons dashicons-info cookieadmin-tooltip-box" data-tip="'.esc_html__('Custom message shown when GPC preference is honored.', 'cookieadmin').'"></span>
						</label>
						<div class="cookieadmin-setting-contents">
							<textarea name="cookieadmin_gpc_message" id="cookieadmin_gpc_message" rows="4" style="width:100%; max-width:500px;" '.(!cookieadmin_is_pro() ? 'disabled' : '').'>'.esc_textarea(!empty($cookieadmin_settings['gpc_message']) ? $cookieadmin_settings['gpc_message'] : (!empty($cookieadmin['gpc_message_default']) ? $cookieadmin['gpc_message_default'] : '')).'</textarea>
						</div>
					</div>

					<div class="cookieadmin-setting">
						<label class="cookieadmin-title" for="cookieadmin_gpc_override_warning">'.esc_html__('GPC Override Warning', 'cookieadmin').'
							<span class="dashicons dashicons-info cookieadmin-tooltip-box" data-tip="'.esc_html__('Warning shown when user tries to enable cookies while GPC signal is active.', 'cookieadmin').'"></span>
						</label>
						<div class="cookieadmin-setting-contents">
							<textarea name="cookieadmin_gpc_override_warning" id="cookieadmin_gpc_override_warning" rows="4" style="width:100%; max-width:500px;" '.(!cookieadmin_is_pro() ? 'disabled' : '').'>'.esc_textarea(!empty($cookieadmin_settings['gpc_override_warning']) ? $cookieadmin_settings['gpc_override_warning'] : (!empty($cookieadmin['gpc_override_warning_default']) ? $cookieadmin['gpc_override_warning_default'] : '')).'</textarea>
						</div>
					</div>
				</div>
			</div>';
			wp_nonce_field('cookieadmin_admin_nonce', 'cookieadmin_security');

			echo '
			<div class="cookieadmin-save-bar">
				<input type="submit" name="cookieadmin_save_settings" class="cookieadmin-btn cookieadmin-btn-primary" value="'.esc_html__('Save Settings', 'cookieadmin').'">
			</div>

			</form>
		</div>';

		\CookieAdmin\Admin::footer_theme();
	}
	
	static function save_settings(){
		global $cookieadmin_lang, $cookieadmin_error, $cookieadmin_msg, $cookieadmin_settings, $cookieadmin_policies;
	
		// debug_print_backtrace();die;
		
		check_admin_referer('cookieadmin_admin_nonce', 'cookieadmin_security');
	 
		if(!current_user_can('administrator')){
			wp_send_json_error(array('message' => __('Sorry, but you do not have permissions to perform this action', 'cookieadmin')));
		}
		
		$cookieadmin_settings = get_option('cookieadmin_settings', []);
		
		// Save cookieadmin_settings only on settings page
		$cookieadmin_settings['block_scripts'] = !empty($_REQUEST['cookieadmin_block_scripts']);
		$cookieadmin_settings['google_consent_mode_v2'] = (isset( $_REQUEST['cookieadmin_google_consent_mode_v2'] ) ? 1 : 0);
		$cookieadmin_settings['hide_powered_by'] = (isset( $_REQUEST['cookieadmin_hide_powered_by'] ) ? 1 : 0);
		$cookieadmin_settings['hide_reconsent'] = (isset( $_REQUEST['cookieadmin_hide_reconsent'] ) ? 1 : 0);
		$cookieadmin_settings['cookieadmin_auto_scan'] = (isset( $_REQUEST['cookieadmin_auto_scan'] ) ? 1 : 0);
		$cookieadmin_settings['consent_logs_expiry'] = (isset( $_REQUEST['cookieadmin_consent_logs_expiry'] ) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_consent_logs_expiry'])) : 0);
		$cookieadmin_settings['consent_logs_expiry_days'] = (isset( $_REQUEST['cookieadmin_consent_logs_expiry_days'] ) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_consent_logs_expiry_days'])) : 365);
		
		$cookieadmin_settings['clarity_consent'] = !empty($_REQUEST['cookieadmin_clarity_consent']);
		$cookieadmin_settings['shared_subdomain_consent'] = !empty($_REQUEST['cookieadmin_shared_subdomain_consent']);
		$cookieadmin_settings['content_blocking'] = !empty($_REQUEST['cookieadmin_content_blocking']);
		$cookieadmin_settings['content_blocking_services'] = [];
		if(!empty($_REQUEST['cookieadmin_content_blocking_services'])){
			$cookieadmin_settings['content_blocking_services'] = array_map('sanitize_text_field', wp_unslash($_REQUEST['cookieadmin_content_blocking_services']));
		}
		
		if(empty($cookieadmin_error)){
			update_option('cookieadmin_settings', $cookieadmin_settings);
		}
		
		//Clear schedule if logs deletion is disabled
		if(empty($cookieadmin_settings['consent_logs_expiry'])){
			wp_clear_scheduled_hook('cookieadmin_daily_consent_log_pruning');
		}
		//Clear schedule if auto scan is disabled
		if(empty($cookieadmin_settings['cookieadmin_auto_scan'])){
			wp_clear_scheduled_hook('cookieadmin_run_auto_cookie_scan');
		}
		
		// get the consent type from option table, if not saved then return default as 'gdpr'
		$law = get_option('cookieadmin_law', 'cookieadmin_gdpr');
		$policy = cookieadmin_load_policy();

		//set preload and consent field for "cookieadmin-settings" page
		$policy[$law]['preload'] = !empty($_REQUEST['cookieadmin_preload']) ? array_map('sanitize_text_field', wp_unslash($_REQUEST['cookieadmin_preload'])) : [];
		$policy[$law]['reload_on_consent'] = !empty($_REQUEST['cookieadmin_reload_on_consent']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_reload_on_consent'])) : '';
		
		update_option('cookieadmin_consent_settings', $policy);
		
		if(empty($cookieadmin_error)){
			$cookieadmin_msg = __('Settings saved successfully', 'cookieadmin');
		}
	}
}
