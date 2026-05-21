<?php

namespace CookieAdmin\Admin;

if(!defined('COOKIEADMIN_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class Consent{
	
	static function consent_form(){
		global $cookieadmin_lang, $cookieadmin_error, $cookieadmin_msg;
		
		$view = get_option('cookieadmin_law', 'cookieadmin_gdpr');		
		$policy = cookieadmin_load_policy();
		
		$raw_template = cookieadmin_load_consent_template($policy[$view], $view);
		
		if(!is_array($raw_template) || empty($raw_template)){
			return false;
		}

		$templates = implode('', $raw_template);
		
		if(empty($templates)){
			echo '<div class="notice notice-error">
					<p>
						<strong>CookieAdmin Error:</strong> Cannot load required template file. 
						Please reinstall the plugin or contact support.
					</p>
				</div>';
			return false;
		}
		
		$cookieadmin_requires_pro = \CookieAdmin\Admin::is_feature_available(1);
		
		$icons_grid = apply_filters('cookieadmin_reconsent_icons', '', $policy[$view]);

		//Start UI
		\CookieAdmin\Admin::header_theme(__('Consent Form', 'cookieadmin'));

		echo '
		<div class="cookieadmin_consent-wrap">
			<form action="" method="post" id="consent_submenu">
			
			<div class="cookieadmin-card">
				<div class="cookieadmin-card-header">
					<span class="cookieadmin-card-title">'.esc_html__('General Settings', 'cookieadmin').'</span>
				</div>
				<div class="cookieadmin-card-body">

					<div class="cookieadmin-setting">
						<label class="cookieadmin-title" for="cookieadmin_consent_type">'.esc_html__('Consent Type', 'cookieadmin').'</label>
						<div class="cookieadmin-setting-contents">
							<select name="cookieadmin_consent_type" id="cookieadmin_consent_type">
								<option name="cookieadmin_gdpr" id="cookieadmin_gdpr" '.((!empty($view) && $view === 'cookieadmin_gdpr') ? 'selected' : '').' value="cookieadmin_gdpr">'.esc_html__('GDPR', 'cookieadmin').'</option>
								<option name="cookieadmin_us" id="cookieadmin_us" '.((!empty($view) && $view === 'cookieadmin_us') ? 'selected' : '').' value="cookieadmin_us">'.esc_html__('US State Laws', 'cookieadmin').'</option>
							</select>
						</div>
					</div>

					<div class="cookieadmin-setting cookieadmin_consent-expiry">
						<label class="cookieadmin-title" for="cookieadmin_consent_expiry">'.esc_html__('Consent Expiry', 'cookieadmin').'</label>
						<div class="cookieadmin-setting-contents">
							<input type="number" name="cookieadmin_days" id="cookieadmin_consent_expiry" style="max-width:80px;" value="'.esc_attr($policy[$view]['cookieadmin_days']).'"> <span class="cookieadmin-text-muted" style="font-size:13px;">'.esc_html__('days', 'cookieadmin').'</span>
						</div>
					</div>

					<div class="cookieadmin-setting consent-layout">
						<label class="cookieadmin-title">'.esc_html__('Notice Type', 'cookieadmin').'</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
								<input name="cookieadmin_layout" type="radio" id="cookieadmin_layout_box" value="box">
								'.esc_html__('Box', 'cookieadmin').'
							</label>
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
								<input name="cookieadmin_layout" type="radio" id="cookieadmin_layout_footer" value="footer">
								'.esc_html__('Footer', 'cookieadmin').'
							</label>
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
								<input name="cookieadmin_layout" type="radio" id="cookieadmin_layout_popup" value="popup">
								'.esc_html__('Popup', 'cookieadmin').'
							</label>
						</div>
					</div>

					<div class="cookieadmin-setting consent-position">
						<label class="cookieadmin-title">'.esc_html__('Notice Position', 'cookieadmin').'</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
								<input class="cookieadmin_box_layout" id="cookieadmin_position_bottom_left" name="cookieadmin_position" type="radio" value="bottom_left" checked>
								'.esc_html__('Bottom Left', 'cookieadmin').'
							</label>
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
								<input class="cookieadmin_box_layout" id="cookieadmin_position_bottom_right" name="cookieadmin_position" type="radio" value="bottom_right">
								'.esc_html__('Bottom Right', 'cookieadmin').'
							</label>
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
								<input class="cookieadmin_box_layout" id="cookieadmin_position_top_left" name="cookieadmin_position" type="radio" value="top_left">
								'.esc_html__('Top Left', 'cookieadmin').'
							</label>
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
								<input class="cookieadmin_box_layout" id="cookieadmin_position_top_right" name="cookieadmin_position" type="radio" value="top_right">
								'.esc_html__('Top Right', 'cookieadmin').'
							</label>
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400; display:none;">
								<input class="cookieadmin_footer_layout" id="cookieadmin_position_top" name="cookieadmin_position" type="radio" value="top">
								'.esc_html__('Top', 'cookieadmin').'
							</label>
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400; display:none;">
								<input class="cookieadmin_footer_layout" id="cookieadmin_position_bottom" name="cookieadmin_position" type="radio" value="bottom">
								'.esc_html__('Bottom', 'cookieadmin').'
							</label>
						</div>
					</div>

					<div class="cookieadmin-setting consent-modal-layout">
						<label class="cookieadmin-title">'.esc_html__('Preference Position', 'cookieadmin').'</label>
						<div class="cookieadmin-setting-contents">
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
								<input id="cookieadmin_modal_center" name="cookieadmin_modal" type="radio" value="center" checked>
								'.esc_html__('Center', 'cookieadmin').'
							</label>
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
								<input id="cookieadmin_modal_side" name="cookieadmin_modal" type="radio" value="side">
								'.esc_html__('Side', 'cookieadmin').'
							</label>
							<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
								<input id="cookieadmin_modal_down" name="cookieadmin_modal" type="radio" value="down">
								'.esc_html__('Draw down', 'cookieadmin').'
							</label>
						</div>
					</div>
				</div>
			</div>
			<div class="cookieadmin-card cookieadmin-mt-16">
				<div class="cookieadmin-card-header">
					<span class="cookieadmin-card-title">'.esc_html__('Notice Content', 'cookieadmin').'</span>
				</div>
				<div class="cookieadmin-card-body">
					<div class="cookieadmin-vertical" style="gap:16px;">
						<div>
							<label for="cookieadmin_notice_title_layout" style="display:block; font-weight:600; font-size:13px; margin-bottom:6px;">'.esc_html__('Title', 'cookieadmin').'</label>
							<input type="text" id="cookieadmin_notice_title_layout" name="cookieadmin_notice_title" style="width:100%; max-width:600px;" value="'.esc_attr($policy[$view]['cookieadmin_notice_title']).'">
						</div>
						<div>
							<label for="cookieadmin_notice_layout" style="display:block; font-weight:600; font-size:13px; margin-bottom:6px;">'.esc_html__('Notice Text', 'cookieadmin').'</label>
							<textarea rows="4" id="cookieadmin_notice_layout" name="cookieadmin_notice" style="width:100%; max-width:600px;">'.esc_html($policy[$view]['cookieadmin_notice']).'</textarea>
						</div>
						<div>
							<label style="display:block; font-weight:600; font-size:13px; margin-bottom:10px;">'.esc_html__('Colors', 'cookieadmin').'</label>
							<div class="cookieadmin-horizontal" style="flex-wrap:wrap; gap:16px;">
								<div class="cookieadmin-vertical" style="gap:4px;">
									<label for="cookieadmin_notice_title_color" style="font-size:12px; font-weight:500;">'.esc_html__('Title', 'cookieadmin').'</label>
									<div class="cookieadmin-color-holder">
										<input type="color" id="cookieadmin_notice_title_color_box" name="cookieadmin_notice_title_color_box" value="'.esc_attr($policy[$view]['cookieadmin_notice_title_color']).'">
										<input type="text" id="cookieadmin_notice_title_color" name="cookieadmin_notice_title_color" value="'.esc_attr($policy[$view]['cookieadmin_notice_title_color']).'" class="cookieadmin-color-input">
									</div>
								</div>
								<div class="cookieadmin-vertical" style="gap:4px;">
									<label for="cookieadmin_notice_color" style="font-size:12px; font-weight:500;">'.esc_html__('Content', 'cookieadmin').'</label>
									<div class="cookieadmin-color-holder">
										<input type="color" id="cookieadmin_notice_color_box" name="cookieadmin_notice_color_box" value="'.esc_attr($policy[$view]['cookieadmin_notice_color']).'">
										<input type="text" id="cookieadmin_notice_color" name="cookieadmin_notice_color" value="'.esc_attr($policy[$view]['cookieadmin_notice_color']).'" class="cookieadmin-color-input">
									</div>
								</div>
								<div class="cookieadmin-vertical" style="gap:4px;">
									<label for="cookieadmin_consent_inside_bg_color" style="font-size:12px; font-weight:500;">'.esc_html__('Background', 'cookieadmin').'</label>
									<div class="cookieadmin-color-holder">
										<input type="color" id="cookieadmin_consent_inside_bg_color_box" name="cookieadmin_consent_inside_bg_color_box" value="'.esc_attr($policy[$view]['cookieadmin_consent_inside_bg_color']).'">
										<input type="text" id="cookieadmin_consent_inside_bg_color" name="cookieadmin_consent_inside_bg_color" value="'.esc_attr($policy[$view]['cookieadmin_consent_inside_bg_color']).'" class="cookieadmin-color-input">
									</div>
								</div>
								<div class="cookieadmin-vertical" style="gap:4px;">
									<label for="cookieadmin_consent_inside_border_color" style="font-size:12px; font-weight:500;">'.esc_html__('Border', 'cookieadmin').'</label>
									<div class="cookieadmin-color-holder">
										<input type="color" id="cookieadmin_consent_inside_border_color_box" name="cookieadmin_consent_inside_border_color_box" value="'.esc_attr($policy[$view]['cookieadmin_consent_inside_border_color']).'">
										<input type="text" id="cookieadmin_consent_inside_border_color" name="cookieadmin_consent_inside_border_color" value="'.esc_attr($policy[$view]['cookieadmin_consent_inside_border_color']).'" class="cookieadmin-color-input">
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="cookieadmin-card">
				<div class="cookieadmin-card-header">
					<span class="cookieadmin-card-title">'.esc_html__('Button Settings', 'cookieadmin').'</span>
				</div>
				<div class="cookieadmin-card-body">
					<div class="cookieadmin-vertical" style="gap:24px;">';

			$button_labels = [
				'cookieadmin_customize_btn' => __('Customize', 'cookieadmin'),
				'cookieadmin_reject_btn' => __('Reject All', 'cookieadmin'),
				'cookieadmin_accept_btn' => __('Accept All', 'cookieadmin'),
				'cookieadmin_save_btn' => __('Save Preferences', 'cookieadmin')
			];

			foreach($button_labels as $btn_key => $btn_label):
				echo '
					<div class="cookieadmin-horizontal" style="align-items:flex-start; gap:24px; flex-wrap:wrap;">
						<div class="cookieadmin-vertical" style="gap:4px; min-width:180px;">
							<label for="'.esc_attr($btn_key).'" style="font-size:12px; font-weight:500;">'.esc_html($btn_label).'</label>
							<input id="'.esc_attr($btn_key).'" name="'.esc_attr($btn_key).'" style="max-width:180px; text-align:center;" value="'.esc_attr($policy[$view][$btn_key]).'">
						</div>
						<div class="cookieadmin-vertical" style="gap:4px;">
							<label for="'.esc_attr($btn_key.'_color').'" style="font-size:12px; font-weight:500;">'.esc_html__('Text Color', 'cookieadmin').'</label>
							<div class="cookieadmin-color-holder">
								<input type="color" id="'.esc_attr($btn_key.'_color_box').'" name="'.esc_attr($btn_key.'_color_box').'" value="'.esc_attr($policy[$view][$btn_key.'_color']).'">
								<input type="text" id="'.esc_attr($btn_key.'_color').'" name="'.esc_attr($btn_key.'_color').'" value="'.esc_attr($policy[$view][$btn_key.'_color']).'" class="cookieadmin-color-input">
							</div>
						</div>
						<div class="cookieadmin-vertical" style="gap:4px;">
							<label for="'.esc_attr($btn_key.'_bg_color').'" style="font-size:12px; font-weight:500;">'.esc_html__('Background', 'cookieadmin').'</label>
							<div class="cookieadmin-color-holder">
								<input type="color" id="'.esc_attr($btn_key.'_bg_color_box').'" name="'.esc_attr($btn_key.'_bg_color_box').'" value="'.esc_attr($policy[$view][$btn_key.'_bg_color']).'">
								<input type="text" id="'.esc_attr($btn_key.'_bg_color').'" name="'.esc_attr($btn_key.'_bg_color').'" value="'.esc_attr($policy[$view][$btn_key.'_bg_color']).'" class="cookieadmin-color-input">
							</div>
						</div>
					</div>';
			endforeach;

				echo '</div>
				</div>
			</div>
			<div class="cookieadmin-card">
				<div class="cookieadmin-card-header">
					<span class="cookieadmin-card-title">'.esc_html__('Preference Content', 'cookieadmin').'</span>
				</div>
				<div class="cookieadmin-card-body">
					<div class="cookieadmin-vertical" style="gap:16px;">
						<div>
							<label for="cookieadmin_preference_title_layout" style="display:block; font-weight:600; font-size:13px; margin-bottom:6px;">'.esc_html__('Title', 'cookieadmin').'</label>
							<input type="text" id="cookieadmin_preference_title_layout" name="cookieadmin_preference_title" style="width:100%; max-width:600px;" value="'.esc_html($policy[$view]['cookieadmin_preference_title']).'">
						</div>
						<div>
							<label for="cookieadmin_preference_layout" style="display:block; font-weight:600; font-size:13px; margin-bottom:6px;">'.esc_html__('Privacy Notice', 'cookieadmin').'</label>
							<textarea rows="4" id="cookieadmin_preference_layout" name="cookieadmin_preference" style="width:100%; max-width:600px;">'.esc_html($policy[$view]['cookieadmin_preference']).'</textarea>
						</div>
						<div>
							<label style="display:block; font-weight:600; font-size:13px; margin-bottom:10px;">'.esc_html__('Colors', 'cookieadmin').'</label>
							<div class="cookieadmin-horizontal" style="flex-wrap:wrap; gap:16px;">
								<div class="cookieadmin-vertical" style="gap:4px;">
									<label for="cookieadmin_preference_title_color" style="font-size:12px; font-weight:500;">'.esc_html__('Title', 'cookieadmin').'</label>
									<div class="cookieadmin-color-holder">
										<input type="color" id="cookieadmin_preference_title_color_box" name="cookieadmin_preference_title_color_box" value="'.esc_attr($policy[$view]['cookieadmin_preference_title_color']).'">
										<input type="text" id="cookieadmin_preference_title_color" name="cookieadmin_preference_title_color" value="'.esc_attr($policy[$view]['cookieadmin_preference_title_color']).'" class="cookieadmin-color-input">
									</div>
								</div>
								<div class="cookieadmin-vertical" style="gap:4px;">
									<label for="cookieadmin_details_wrapper_color" style="font-size:12px; font-weight:500;">'.esc_html__('Content', 'cookieadmin').'</label>
									<div class="cookieadmin-color-holder">
										<input type="color" id="cookieadmin_details_wrapper_color_box" name="cookieadmin_details_wrapper_color_box" value="'.esc_attr($policy[$view]['cookieadmin_details_wrapper_color']).'">
										<input type="text" id="cookieadmin_details_wrapper_color" name="cookieadmin_details_wrapper_color" value="'.esc_attr($policy[$view]['cookieadmin_details_wrapper_color']).'" class="cookieadmin-color-input">
									</div>
								</div>
								<div class="cookieadmin-vertical" style="gap:4px;">
									<label for="cookieadmin_cookie_modal_bg_color" style="font-size:12px; font-weight:500;">'.esc_html__('Background', 'cookieadmin').'</label>
									<div class="cookieadmin-color-holder">
										<input type="color" id="cookieadmin_cookie_modal_bg_color_box" name="cookieadmin_cookie_modal_bg_color_box" value="'.esc_attr($policy[$view]['cookieadmin_cookie_modal_bg_color']).'">
										<input type="text" id="cookieadmin_cookie_modal_bg_color" name="cookieadmin_cookie_modal_bg_color" value="'.esc_attr($policy[$view]['cookieadmin_cookie_modal_bg_color']).'" class="cookieadmin-color-input">
									</div>
								</div>
								<div class="cookieadmin-vertical" style="gap:4px;">
									<label for="cookieadmin_cookie_modal_border_color" style="font-size:12px; font-weight:500;">'.esc_html__('Border', 'cookieadmin').'</label>
									<div class="cookieadmin-color-holder">
										<input type="color" id="cookieadmin_cookie_modal_border_color_box" name="cookieadmin_cookie_modal_border_color_box" value="'.esc_attr($policy[$view]['cookieadmin_cookie_modal_border_color']).'">
										<input type="text" id="cookieadmin_cookie_modal_border_color" name="cookieadmin_cookie_modal_border_color" value="'.esc_attr($policy[$view]['cookieadmin_cookie_modal_border_color']).'" class="cookieadmin-color-input">
									</div>
								</div>
							</div>
						</div>
						<div>
						<label style="display:block; font-weight:600; font-size:13px; margin-bottom:10px;">'.esc_html__('Additional Colors', 'cookieadmin').'</label>
						<div class="cookieadmin-horizontal" style="flex-wrap:wrap; gap:16px;">
							<div class="cookieadmin-vertical" style="gap:4px;">
								<label for="cookieadmin_links_color" style="font-size:12px; font-weight:500;">'.esc_html__('Links', 'cookieadmin').'</label>
								<div class="cookieadmin-color-holder">
									<input type="color" id="cookieadmin_links_color_box" name="cookieadmin_links_color_box" value="'.esc_attr($policy[$view]['cookieadmin_links_color']).'">
									<input type="text" id="cookieadmin_links_color" name="cookieadmin_links_color" value="'.esc_attr($policy[$view]['cookieadmin_links_color']).'" class="cookieadmin-color-input">
								</div>
							</div>
							<div class="cookieadmin-vertical" style="gap:4px;">
								<label for="cookieadmin_slider_on_bg_color" style="font-size:12px; font-weight:500;">'.esc_html__('Button Switch On', 'cookieadmin').'</label>
								<div class="cookieadmin-color-holder">
									<input type="color" id="cookieadmin_slider_on_bg_color_box" name="cookieadmin_slider_on_bg_color_box" value="'.esc_attr($policy[$view]['cookieadmin_slider_on_bg_color']).'">
									<input type="text" id="cookieadmin_slider_on_bg_color" name="cookieadmin_slider_on_bg_color" value="'.esc_attr($policy[$view]['cookieadmin_slider_on_bg_color']).'" class="cookieadmin-color-input">
								</div>
							</div>
							<div class="cookieadmin-vertical" style="gap:4px;">
								<label for="cookieadmin_slider_off_bg_color" style="font-size:12px; font-weight:500;">'.esc_html__('Button Switch Off', 'cookieadmin').'</label>
								<div class="cookieadmin-color-holder">
									<input type="color" id="cookieadmin_slider_off_bg_color_box" name="cookieadmin_slider_off_bg_color_box" value="'.esc_attr($policy[$view]['cookieadmin_slider_off_bg_color']).'">
									<input type="text" id="cookieadmin_slider_off_bg_color" name="cookieadmin_slider_off_bg_color" value="'.esc_attr($policy[$view]['cookieadmin_slider_off_bg_color']).'" class="cookieadmin-color-input">
								</div>
							</div>
						</div>
					</div>
					</div>
				</div>
			</div>
				
			<div class="cookieadmin-card" cookieadmin-pro-only="1">
				<div class="cookieadmin-card-header">
					<span class="cookieadmin-card-title">'.esc_html__('Re-consent Icon', 'cookieadmin').wp_kses_post($cookieadmin_requires_pro).'</span>
				</div>
				<div class="cookieadmin-card-body">
					<div class="cookieadmin-vertical" style="gap:12px;">
						<div class="cookieadmin-reconsent-icons-grid">
							<div class="cookieadmin-reconsent-icons-list">
							' . wp_kses($icons_grid, cookieadmin_kses_allowed_html()) . '
							</div>
							<div class="cookieadmin-custom-reconsent-url cookieadmin-mt-16">
								<input type="text" id="cookieadmin_reconsent_img_url" name="cookieadmin_reconsent_img_url" style="width:100%; max-width:500px;" placeholder="'.esc_attr__('Insert custom icon url here', 'cookieadmin').'" value="'.(!empty($policy[$view]['cookieadmin_reconsent_img_url']) ? esc_attr($policy[$view]['cookieadmin_reconsent_img_url']) : '').'">
							</div>
							<div class="cookieadmin-mt-16">
								<input type="button" class="cookieadmin-btn cookieadmin-btn-secondary" id="cookieadmin_upload_icon_btn" value="'.esc_attr__( 'Upload Icon', 'cookieadmin' ).'">
							</div>
						</div>
						<div class="cookieadmin-vertical" style="gap:4px; margin-top:16px;">
							<label for="cookieadmin_re_consent_bg_color" style="font-size:12px; font-weight:500;">'.esc_html__('Background', 'cookieadmin').'</label>
							<div class="cookieadmin-color-holder">
								<input type="color" id="cookieadmin_re_consent_bg_color_box" name="cookieadmin_re_consent_bg_color_box" value="'.(!empty($policy[$view]['cookieadmin_re_consent_bg_color']) ? esc_attr($policy[$view]['cookieadmin_re_consent_bg_color']) : '#374FD4').'">
								<input type="text" id="cookieadmin_re_consent_bg_color" name="cookieadmin_re_consent_bg_color" value="'.(!empty($policy[$view]['cookieadmin_re_consent_bg_color']) ? esc_attr($policy[$view]['cookieadmin_re_consent_bg_color']) : '#374FD4').'" class="cookieadmin-color-input">
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="cookieadmin-card cookieadmin-mt-16" cookieadmin-pro-only="1">
				<div class="cookieadmin-card-header">
					<span class="cookieadmin-card-title">'.esc_html__('Policy Links', 'cookieadmin').wp_kses_post($cookieadmin_requires_pro).'</span>
				</div>
				<div class="cookieadmin-card-body">
					<div class="cookieadmin-vertical" style="gap:16px;">
						<div class="cookieadmin-vertical" style="gap:4px;">
							<label for="cookieadmin_privacy_policy" style="font-size:13px; font-weight:500;">'.esc_html__('Privacy Policy', 'cookieadmin').'</label>
							<input type="text" id="cookieadmin_privacy_policy" name="cookieadmin_privacy_policy" style="width:100%; max-width:500px;" placeholder="'.__('Insert Privacy Policy link here...', 'cookieadmin').'" value="'.(!empty($policy[$view]['cookieadmin_privacy_policy']) ? esc_attr($policy[$view]['cookieadmin_privacy_policy']) : '').'">
						</div>
						<div class="cookieadmin-vertical" style="gap:4px;">
							<label for="cookieadmin_cookie_policy" style="font-size:13px; font-weight:500;">'.esc_html__('Cookie Policy', 'cookieadmin').'</label>
							<input type="text" id="cookieadmin_cookie_policy" name="cookieadmin_cookie_policy" style="width:100%; max-width:500px;" placeholder="'.__('Insert Cookie Policy link here...', 'cookieadmin').'" value="'.(!empty($policy[$view]['cookieadmin_cookie_policy']) ? esc_attr($policy[$view]['cookieadmin_cookie_policy']) : '').'">
						</div>
						<div>
							<label style="font-size:13px; font-weight:500; display:block; margin-bottom:8px;">'.esc_html__('Visibility', 'cookieadmin').'</label>
							<div class="cookieadmin-horizontal" style="gap:16px;">
								<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
									<input type="checkbox" id="cookieadmin_privacy_policy_banner" name="cookieadmin_privacy_policy_banner" '.(!empty($policy[$view]['cookieadmin_privacy_policy_banner']) ? 'checked' : '').'>
									'.esc_html__('Banner', 'cookieadmin').'
								</label>
								<label class="cookieadmin-horizontal" style="cursor:pointer; font-weight:400;">
									<input type="checkbox" id="cookieadmin_privacy_policy_pref" name="cookieadmin_privacy_policy_pref" '.(!empty($policy[$view]['cookieadmin_privacy_policy_pref']) ? 'checked' : '').'>
									'.esc_html__('Preference', 'cookieadmin').'
								</label>
							</div>
						</div>
						<div class="cookieadmin-vertical" style="gap:4px;">
							<label for="cookieadmin_policy_link_color" style="font-size:12px; font-weight:500;">'.esc_html__('Link Color', 'cookieadmin').'</label>
							<div class="cookieadmin-color-holder">
								<input type="color" id="cookieadmin_policy_link_color_box" name="cookieadmin_policy_link_color_box" value="'.(!empty($policy[$view]['cookieadmin_policy_link_color']) ? esc_attr($policy[$view]['cookieadmin_policy_link_color']) : '').'">
								<input type="text" id="cookieadmin_policy_link_color" name="cookieadmin_policy_link_color" value="'.(!empty($policy[$view]['cookieadmin_policy_link_color']) ? esc_attr($policy[$view]['cookieadmin_policy_link_color']) : '').'" class="cookieadmin-color-input">
							</div>
						</div>
					</div>
				</div>
			</div>';

			wp_nonce_field('cookieadmin_admin_nonce', 'cookieadmin_security');

			echo '
			<div class="cookieadmin-save-bar">
				<button type="button" id="cookieadmin_show_preview" class="cookieadmin-btn cookieadmin-btn-ghost"><span class="dashicons dashicons-visibility"></span>'.esc_html__('Show Preview', 'cookieadmin').'</button>
				<button type="button" id="cookieadmin_hide_preview" class="cookieadmin-btn cookieadmin-btn-ghost" style="display:none;"><span class="dashicons dashicons-hidden"></span>'.esc_html__('Hide Preview', 'cookieadmin').'</button>
				<input type="submit" name="cookieadmin_save_settings" class="cookieadmin-btn cookieadmin-btn-primary" value="'.esc_html__('Save Settings', 'cookieadmin').'">
			</div>

			</form>
		</div>';
		\CookieAdmin\Admin::footer_theme();
		
		$allowed_tags = cookieadmin_kses_allowed_html();
		echo wp_kses($templates, $allowed_tags);
	}
	
	static function save_consent_form(){
		global $cookieadmin_lang, $cookieadmin_error, $cookieadmin_msg, $cookieadmin_settings, $cookieadmin_policies;
		// debug_print_backtrace();die;
		
		check_admin_referer('cookieadmin_admin_nonce', 'cookieadmin_security');
		
		if(!current_user_can('administrator')){
			wp_send_json_error(array('message' => __('Sorry, but you do not have permissions to perform this action', 'cookieadmin')));
		}
		
		$policy = cookieadmin_load_policy();
		
		$cookieadmin_consent_type = isset( $_REQUEST['cookieadmin_consent_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['cookieadmin_consent_type'] ) ) : '';
		
		if(!empty($cookieadmin_consent_type)){
			
			$laws = array('cookieadmin_gdpr' => '', 'cookieadmin_us' => '');
			
			$law = array_key_exists($cookieadmin_consent_type, $laws) ? $cookieadmin_consent_type : 'cookieadmin_gdpr';
			
			if(empty($cookieadmin_error)){
				update_option('cookieadmin_law', $law);
			}
		}
		
		$setting['cookieadmin_geo_tgt'] = (!empty($_REQUEST['cookieadmin_geo_tgt'])) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_geo_tgt'])) : 'www';
		
		$setting['cookieadmin_layout'] = (!empty($_REQUEST['cookieadmin_layout']) && in_array($_REQUEST['cookieadmin_layout'], array('box', 'footer', 'popup'))) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_layout'])) : (!empty($policy[$law]['cookieadmin_layout']) ? $policy[$law]['cookieadmin_layout'] : 'box');
		
		$setting['cookieadmin_position'] = (!empty($_REQUEST['cookieadmin_position']) && in_array($_REQUEST['cookieadmin_position'],  array('bottom_left', 'bottom_right', 'top_left', 'top_right', 'top', 'bottom'))) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_position'])) : (!empty($policy[$law]['cookieadmin_position']) ? $policy[$law]['cookieadmin_position'] : 'bottom_left');

		$setting['cookieadmin_modal'] = (isset($_REQUEST['cookieadmin_modal']) && in_array($_REQUEST['cookieadmin_modal'], array('center', 'side', 'down'))) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_modal'])) : (!empty($policy[$law]['cookieadmin_modal']) ? $policy[$law]['cookieadmin_modal'] : 'center');
		
		if($setting['cookieadmin_layout'] == 'popup'){
			$setting['cookieadmin_modal'] = 'center';
			unset($setting['cookieadmin_position']);
		}		

		$setting['cookieadmin_notice_title'] = !empty($_REQUEST['cookieadmin_notice_title']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_notice_title'])) : $policy[$law]['cookieadmin_notice_title'];
		$setting['cookieadmin_notice_title_color'] = !empty($_REQUEST['cookieadmin_notice_title_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_notice_title_color'])) : (!empty($policy[$law]['cookieadmin_notice_title_color']) ? $policy[$law]['cookieadmin_notice_title_color'] : '#000000');
		
		$setting['cookieadmin_notice'] = !empty($_REQUEST['cookieadmin_notice']) ? wp_kses(wp_unslash($_REQUEST['cookieadmin_notice']), cookieadmin_kses_allowed_html()) : $policy[$law]['cookieadmin_notice'];
		$setting['cookieadmin_notice_color'] = !empty($_REQUEST['cookieadmin_notice_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_notice_color'])) : (!empty($policy[$law]['cookieadmin_notice_color']) ? $policy[$law]['cookieadmin_notice_color'] : '#000000');
		
		$setting['cookieadmin_consent_inside_bg_color'] = !empty($_REQUEST['cookieadmin_consent_inside_bg_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_consent_inside_bg_color'])) : (!empty($policy[$law]['cookieadmin_consent_inside_bg_color']) ? $policy[$law]['cookieadmin_consent_inside_bg_color'] : '#ffffff');
		$setting['cookieadmin_consent_inside_border_color'] = !empty($_REQUEST['cookieadmin_consent_inside_border_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_consent_inside_border_color'])) : (!empty($policy[$law]['cookieadmin_consent_inside_border_color']) ? $policy[$law]['cookieadmin_consent_inside_border_color'] : '#000000');
		
		$setting['cookieadmin_customize_btn'] = !empty($_REQUEST['cookieadmin_customize_btn']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_customize_btn'])) : (!empty($policy[$law]['cookieadmin_customize_btn']) ? $policy[$law]['cookieadmin_customize_btn'] : 'Customize');
		$setting['cookieadmin_customize_btn_color'] = !empty($_REQUEST['cookieadmin_customize_btn_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_customize_btn_color'])) : (!empty($policy[$law]['cookieadmin_customize_btn_color']) ? $policy[$law]['cookieadmin_customize_btn_color'] : '#ffffff');
		$setting['cookieadmin_customize_btn_bg_color'] = !empty($_REQUEST['cookieadmin_customize_btn_bg_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_customize_btn_bg_color'])) : (!empty($policy[$law]['cookieadmin_customize_btn_bg_color']) ? $policy[$law]['cookieadmin_customize_btn_bg_color'] : '#0000ff');
		
		$setting['cookieadmin_reject_btn'] = !empty($_REQUEST['cookieadmin_reject_btn']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_reject_btn'])) : (!empty($policy[$law]['cookieadmin_reject_btn']) ? $policy[$law]['cookieadmin_reject_btn'] : 'Reject All');
		$setting['cookieadmin_reject_btn_color'] = !empty($_REQUEST['cookieadmin_reject_btn_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_reject_btn_color'])) : (!empty($policy[$law]['cookieadmin_reject_btn_color']) ? $policy[$law]['cookieadmin_reject_btn_color'] : '#ffffff');
		$setting['cookieadmin_reject_btn_bg_color'] = !empty($_REQUEST['cookieadmin_reject_btn_bg_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_reject_btn_bg_color'])) : (!empty($policy[$law]['cookieadmin_reject_btn_bg_color']) ? $policy[$law]['cookieadmin_reject_btn_bg_color'] : '#ff0000');

		$setting['cookieadmin_accept_btn'] = !empty($_REQUEST['cookieadmin_accept_btn']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_accept_btn'])) : (!empty($policy[$law]['cookieadmin_accept_btn']) ? $policy[$law]['cookieadmin_accept_btn'] : 'Accept All');
		$setting['cookieadmin_accept_btn_color'] = !empty($_REQUEST['cookieadmin_accept_btn_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_accept_btn_color'])) : (!empty($policy[$law]['cookieadmin_accept_btn']) ? $policy[$law]['cookieadmin_accept_btn_color'] : '#ffffff');
		$setting['cookieadmin_accept_btn_bg_color'] = !empty($_REQUEST['cookieadmin_accept_btn_bg_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_accept_btn_bg_color'])) : (!empty($policy[$law]['cookieadmin_accept_btn_bg_color']) ? $policy[$law]['cookieadmin_accept_btn_bg_color'] : '#00ff00');

		$setting['cookieadmin_save_btn'] = !empty($_REQUEST['cookieadmin_save_btn']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_save_btn'])) : (!empty($policy[$law]['cookieadmin_save_btn']) ? $policy[$law]['cookieadmin_save_btn'] : 'Save Preferences');
		$setting['cookieadmin_save_btn_color'] = !empty($_REQUEST['cookieadmin_save_btn_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_save_btn_color'])) : (!empty($policy[$law]['cookieadmin_save_btn_color']) ? $policy[$law]['cookieadmin_save_btn_color'] : '#ffffff');
		$setting['cookieadmin_save_btn_bg_color'] = !empty($_REQUEST['cookieadmin_save_btn_bg_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_save_btn_bg_color'])) : (!empty($policy[$law]['cookieadmin_save_btn_bg_color']) ? $policy[$law]['cookieadmin_save_btn_bg_color'] : '#183833');

		$setting['cookieadmin_preference_title'] = !empty($_REQUEST['cookieadmin_preference_title']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_preference_title'])) : $policy[$law]['cookieadmin_preference_title'];
		$setting['cookieadmin_preference_title_color'] = !empty($_REQUEST['cookieadmin_preference_title_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_preference_title_color'])) : (!empty($policy[$law]['cookieadmin_preference_title_color']) ? $policy[$law]['cookieadmin_preference_title_color'] : '#000000');
		
		$setting['cookieadmin_preference'] = !empty($_REQUEST['cookieadmin_preference']) ? wp_kses(wp_unslash($_REQUEST['cookieadmin_preference']), cookieadmin_kses_allowed_html()) : $policy[$law]['cookieadmin_preference'];
		$setting['cookieadmin_details_wrapper_color'] = !empty($_REQUEST['cookieadmin_details_wrapper_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_details_wrapper_color'])) : (!empty($policy[$law]['cookieadmin_details_wrapper_color']) ? $policy[$law]['cookieadmin_details_wrapper_color'] : '#000000');
		
		$setting['cookieadmin_cookie_modal_bg_color'] = !empty($_REQUEST['cookieadmin_cookie_modal_bg_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_cookie_modal_bg_color'])) : (!empty($policy[$law]['cookieadmin_cookie_modal_bg_color']) ? $policy[$law]['cookieadmin_cookie_modal_bg_color'] : '#ffffff');
		$setting['cookieadmin_cookie_modal_border_color'] = !empty($_REQUEST['cookieadmin_cookie_modal_border_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_cookie_modal_border_color'])) : (!empty($policy[$law]['cookieadmin_cookie_modal_border_color']) ? $policy[$law]['cookieadmin_cookie_modal_border_color'] : '#000000');

		$setting['cookieadmin_slider_off_bg_color'] = !empty($_REQUEST['cookieadmin_slider_off_bg_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_slider_off_bg_color'])) : (!empty($policy[$law]['cookieadmin_slider_off_bg_color']) ? $policy[$law]['cookieadmin_slider_off_bg_color'] : '#808080');
		$setting['cookieadmin_slider_on_bg_color'] = !empty($_REQUEST['cookieadmin_slider_on_bg_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_slider_on_bg_color'])) : (!empty($policy[$law]['cookieadmin_slider_on_bg_color']) ? $policy[$law]['cookieadmin_slider_on_bg_color'] : '#3582c4');
		$setting['cookieadmin_links_color'] = !empty($_REQUEST['cookieadmin_links_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_links_color'])) : (!empty($policy[$law]['cookieadmin_links_color']) ? $policy[$law]['cookieadmin_links_color'] : '#1863dc');
		
		// Set Reconsent Icons 
		$setting['cookieadmin_reconsent_icon'] = !empty($_REQUEST['cookieadmin_reconsent_icon']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_reconsent_icon'])) : '';
		$setting['cookieadmin_reconsent_img_url'] = !empty($_REQUEST['cookieadmin_reconsent_img_url']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_reconsent_img_url'])) : '';
		$setting['cookieadmin_re_consent_bg_color'] = !empty($_REQUEST['cookieadmin_re_consent_bg_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_re_consent_bg_color'])) : (!empty($policy[$law]['cookieadmin_re_consent_bg_color']) ? $policy[$law]['cookieadmin_re_consent_bg_color'] : '#374FD4');

		// Set Policy Links
		$setting['cookieadmin_privacy_policy'] = !empty($_REQUEST['cookieadmin_privacy_policy']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_privacy_policy'])) : '';
		$setting['cookieadmin_cookie_policy'] = !empty($_REQUEST['cookieadmin_cookie_policy']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_cookie_policy'])) : '';
		$setting['cookieadmin_privacy_policy_banner'] = !empty($_REQUEST['cookieadmin_privacy_policy_banner']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_privacy_policy_banner'])) : 0;
		$setting['cookieadmin_privacy_policy_pref'] = !empty($_REQUEST['cookieadmin_privacy_policy_pref']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_privacy_policy_pref'])) : 0;
		$setting['cookieadmin_policy_link_color'] = !empty($_REQUEST['cookieadmin_policy_link_color']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_policy_link_color'])) : '#cbba8d';
		
		$setting['cookieadmin_days'] = !empty($_REQUEST['cookieadmin_days']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_days'])) : (!empty($policy[$law]['cookieadmin_days']) ? $policy[$law]['cookieadmin_days'] : '365');
		
		$policy[$law] = $setting;
		
		// Check for certain fields to be saved only if their values is not the same as default
		$cookieadmin_check_changes = array('cookieadmin_notice_title', 'cookieadmin_notice', 'cookieadmin_preference_title', 'cookieadmin_preference', 'reConsent_title', 'cookieadmin_customize_btn', 'cookieadmin_reject_btn', 'cookieadmin_accept_btn', 'cookieadmin_save_btn');
		
		foreach($cookieadmin_check_changes as $c_field){
			foreach($policy as $c_law => $c_val){
				if(!empty($c_val[$c_field]) && $c_val[$c_field] == $cookieadmin_policies[$c_law][$c_field]){
					unset($policy[$c_law][$c_field]);
				}
			}
		}
		
		update_option('cookieadmin_consent_settings', $policy);
		
		if(empty($cookieadmin_error)){
			$cookieadmin_msg = __('Settings saved successfully', 'cookieadmin');
		}
	}
}