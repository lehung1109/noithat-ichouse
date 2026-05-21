<?php
namespace FormLayer\Settings;

if(!defined('ABSPATH')){
	exit;
}

class UI{

	static function header(){
		echo '<div class="formlayer-admin-wrapper">
			<header class="formlayer-admin-header main-header-sticky">
				<div class="formlayer-header-left">
					<div class="formlayer-sidebar-logo" style="padding: 0; margin-right: 25px; border: none; background: none;">
						<div class="formlayer-logo-icon">
							<img alt="' . esc_html__('FormLayer logo', 'formlayer') . '" height="52" src="' . esc_url(FORMLAYER_ASSETS_URL) . '/img/formlayer-logo.png' . '" width="58" style="filter: drop-shadow(0 4px 6px rgba(0,0,0,0.05));"/>
						</div>
					</div>

					<nav class="formlayer-header-tabs glass-tabs" id="formlayer-main-nav">';

					self::tab_item('forms', 'Forms', '<span class="dashicons dashicons-feedback"></span>');
					self::tab_item('formbuilder', 'Form Builder', '<span class="dashicons dashicons-welcome-add-page"></span>');
					
					if(defined('FORMLAYER_PRO_VERSION')){
						$unread = \FormLayerPro\Util::get_unread_count();
						$unread_badge = $unread > 0 ? ' <span class="formlayer-unread-count formlayer-unread-badge">' . $unread . '</span>' : '';
					} else {
						$unread_badge = '';
					}

					self::tab_item('entries', 'Entries' . $unread_badge, '<span class="dashicons dashicons-database-view"></span>');
					self::tab_item('integrations', 'Integrations', '<span class="dashicons dashicons-admin-links"></span>');
					self::tab_item('settings', 'Settings', '<span class="dashicons dashicons-admin-settings"></span>');
					self::tab_item('support', 'Support', '<span class="dashicons dashicons-sos"></span>');
					if (defined('FORMLAYER_PRO_VERSION')) {
						self::tab_item('license', 'License', '<span class="dashicons dashicons-admin-network"></span>');
					}

			echo '</nav>
				</div>
				<div class="formlayer-header-right">
					<div class="formlayer-header-version-badge">v' . esc_html(FORMLAYER_VERSION) . '</div>
				</div>
			</header>
			<main class="formlayer-admin-main-content">';
	}

	static function tab_item($slug, $label, $icon){
		$url = '#' . $slug;
		echo '<a href="' . esc_url($url) . '" class="formlayer-tab-item" data-tab="' . esc_attr($slug) . '">' . wp_kses_post($icon) . '<span>' . wp_kses_post($label) . '</span></a>';
	}

	static function forms_tab(){
		$forms = get_posts([
			'post_type' => 'formlayer_form',
			'posts_per_page' => -1,
			'post_status' => 'any'
		]);

		$count = is_array($forms) ? count($forms) : 0;


		echo '<div class="formlayer-table-card">
			<div class="formlayer-table-card-header">
				<div class="formlayer-bulk-action-wrap">
					<select id="formlayer-bulk-action" class="formlayer-select formlayer-select-compact">
						<option value="">' . esc_html__('Bulk Actions', 'formlayer') . '</option>
						<option value="delete">' . esc_html__('Delete Permanently', 'formlayer') . '</option>
					</select>
					<button id="formlayer-apply-bulk" class="formlayer-btn formlayer-btn-outline">' . esc_html__('Apply', 'formlayer') . '</button>
				</div>
				<a href="#formbuilder" id="formlayer-add-new-form" class="formlayer-btn formlayer-btn-primary">+ ' . esc_html__('Add New Form', 'formlayer') . '</a>
			</div>

			<div style="padding: 0 32px 32px 32px;">
				<table class="formlayer-table">
					<thead>
						<tr>
							<th style="width:40px;"><input type="checkbox" id="formlayer-select-all"></th>
							<th>'.esc_html__('Name', 'formlayer') . '</th>
							<th>'.esc_html__('Last Modified', 'formlayer') . '</th>
							<th>'.esc_html__('Shortcode', 'formlayer') . '</th>
							<th>'.esc_html__('Actions', 'formlayer') . '</th>
						</tr>
					</thead>
					<tbody>';

		if(empty($forms)){
			echo '<tr>
					<td colspan="7" style="text-align:center; padding:60px 0; color:var(--formlayer-text-muted);">
						<div class="formlayer-empty-title">' . esc_html__('No forms found', 'formlayer') . '</div>
						<p style="margin:0;">' . esc_html__('Create your first form to start collecting data.', 'formlayer') . '</p>
					</td>
				</tr>';

		} else {
			foreach ($forms as $form) {
				self::render_form_row($form);
			}
		}

		echo '</tbody>
				</table>
			</div>
		</div>';
	}

	static function formbuilder_tab(){
		echo '<div id="formlayer-formbuilder-app">
			<!-- Templates View (Hidden by default or shown based on state) -->
			<div id="formlayer-templates-view" style="display:none;">
				<div class="formlayer-templates-wrapper">
					<div class="formlayer-templates-sidebar">
						<h3>' . esc_html__('Categories', 'formlayer') . '</h3>
						<ul class="formlayer-template-cats" id="formlayer-template-cats-list">
							<!-- Populated by JS -->
						</ul>
					</div>
					<div class="formlayer-templates-main">
						<div class="formlayer-templates-header">
							<div class="formlayer-templates-header-row">
								<div>
									<h3>' . esc_html__('Select a Template', 'formlayer') . '</h3>
									<p style="margin: 0;">' . esc_html__('Choose a base to start from or build a custom form from scratch.', 'formlayer') . '</p>
								</div>
								<div class="formlayer-template-search-wrapper" style="width: 300px; position: relative;">
									<input type="text" id="formlayer-template-search" class="formlayer-input formlayer-template-search-input" placeholder="' . esc_attr__('Search templates...', 'formlayer') . '">
								</div>
							</div>
						</div>
						<div class="formlayer-templates-grid" id="formlayer-templates-grid">
							<!-- Populated by JS -->
						</div>
					</div>
				</div>
			</div>

			<!-- Builder View -->
			<div id="formlayer-builder-view" style="display:none;">
				<div class="formlayer-builder-wrapper">
					<div class="formlayer-builder-card">
						<div class="formlayer-builder-header-info formlayer-builder-header-info-wrap">
							<button class="formlayer-btn-back-templates">
								<span class="dashicons dashicons-arrow-left-alt2"></span>
								' . esc_html__('Back', 'formlayer') . '
							</button>
							<div class="formlayer-title-edit-wrapper formlayer-title-edit-container">
								<h2 id="formlayer-builder-title">' . esc_html__('Untitled Form', 'formlayer') . '</h2>
								<input type="text" id="formlayer-builder-title-input" class="formlayer-builder-title-input-el">
								<span class="dashicons dashicons-edit" id="formlayer-edit-title-btn" style="color: #94a3b8; cursor: pointer; font-size: 18px; margin-top: 2px;"></span>
							</div>
							<div class="info-right formlayer-builder-info-right">
								<button id="formlayer-btn-form-settings" class="formlayer-btn formlayer-btn-outline" style="display: flex; align-items: center; gap: 8px;">
									<span class="dashicons dashicons-admin-settings" style="font-size: 16px; width: 16px; height: 16px; margin-top: 2px;"></span>
									' . esc_html__('Form Settings', 'formlayer') . '
								</button>
							
								<div class="formlayer-shortcode-box">
									<div style="margin-right: 15px;">
										<input type="text" value="" readonly id="formlayer-shortcode-val" >									</div>
									<button class="formlayer-copy-shortcode">
										<span class="dashicons dashicons-admin-page" style="font-size: 18px; width: 18px; height: 18px; color: #64748b;"></span>' . esc_html__('Copy', 'formlayer') . '
									</button>
									<button type="button" class="formlayer-fullscreen-toggle formlayer-fullscreen-toggle-btn" title="' . esc_attr__('Toggle Fullscreen', 'formlayer') . '">
										<span class="dashicons dashicons-fullscreen-alt" style="font-size: 20px; width: 20px; height: 20px; color: #64748b;"></span>
									</button>
								</div>
								
								<div class="formlayer-builder-action-bar">
							<div class="action-right">
								<a href="#forms" class="formlayer-btn-all-forms btn-primary">
									' . esc_html__('All Forms', 'formlayer') . '
								</a>
								<button class="formlayer-btn-save btn-primary ">
									' . esc_html__('Save Form', 'formlayer') . '
								</button>
							</div>
						</div>
							</div>
						</div>

						<div class="formlayer-builder-main">
							<div class="formlayer-preview-column">
								<div class="formlayer-preview-container">
									<div class="formlayer-preview-header">
										<div class="formlayer-device-toggles">
											<button class="formlayer-device-btn active" data-device="desktop"><span class="dashicons dashicons-desktop"></span></button>
											<button class="formlayer-device-btn" data-device="tablet"><span class="dashicons dashicons-tablet"></span></button>
											<button class="formlayer-device-btn" data-device="mobile"><span class="dashicons dashicons-smartphone"></span></button>
										</div>
									</div>
									<div class="formlayer-canvas-wrapper">
										<div class="formlayer-builder-canvas">
											<div class="formlayer-canvas-inner">
												<div class="formlayer-dropzone" id="formlayer-dropzone">
													<!-- Fields rendered by JS -->
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							
							<div class="formlayer-sidebar-column">
								<div class="formlayer-sidebar-tabs">
									<button class="formlayer-sidebar-tab active" data-tab="input-fields">
										' . esc_html__('General', 'formlayer') . '
									</button>
									<button class="formlayer-sidebar-tab" data-tab="input-customization">
										' . esc_html__('Advanced', 'formlayer') . '
									</button>
								</div>
								
								<div class="formlayer-sidebar-panes">
									<div class="formlayer-sidebar-pane active" id="formlayer-pane-fields">
										<div class="formlayer-search-wrapper">
											<span class="dashicons dashicons-search"></span>
											<input type="text" class="formlayer-fields-search" placeholder="' . esc_attr__('Search (press / to focus)', 'formlayer') . '">
										</div>
										<div class="formlayer-categories" id="formlayer-builder-categories">
											<!-- Categories and fields by JS -->
										</div>
									</div>
									
									<div class="formlayer-sidebar-pane" id="formlayer-pane-customization">
										<!-- Customization form by JS -->
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>';
	}

	static function render_form_row($post){

		$display_id = get_post_meta($post->ID, '_formlayer_display_id', true);
		if (!$display_id) {
			$counter = get_option('formlayer_id_counter', 0);
			$counter++;
			update_option('formlayer_id_counter', $counter);
			update_post_meta($post->ID, '_formlayer_display_id', $counter);
			$display_id = $counter;
		}

		$shortcode = '[formlayer id="' . $display_id . '"]';
		$time = human_time_diff(get_the_modified_time('U', $post->ID), current_time('timestamp')) . ' ago';


		echo '<tr>
			<td><input type="checkbox" class="formlayer-row-cb" value="' . esc_attr($display_id) . '"></td>
			<td>
				<div style="font-weight:600;">' . esc_html(get_the_title($post->ID)) . '</div>
			</td>
			<td>' . esc_html($time) . '</td>
			<td>
				<code style="background:#f1f5f9; padding:4px 8px; border-radius:4px; font-size:12px;">' . esc_html($shortcode) . '</code>
			</td>
			<td>
				<div style="display:flex; gap:12px; color:var(--formlayer-text-muted);">
					<a href="#" class="formlayer-copy-shortcode-list" data-shortcode="' . esc_attr($shortcode) . '" title="Copy Shortcode" style="color:inherit; cursor:pointer;">
						<span class="dashicons dashicons-admin-page"></span>
					</a>
					<a href="' . esc_url(admin_url('admin.php?page=formlayer&tab=formbuilder&form_id=' . $display_id)) . '" class="formlayer-action-btns" title="Edit" style="color:inherit;">
						<span class="dashicons dashicons-edit"></span>
					</a>
					<a href="#" class="formlayer-delete-form" data-form-id="' . esc_attr($display_id) . '" title="Delete" style="color:inherit; cursor:pointer;">
						<span class="dashicons dashicons-trash"></span>
					</a>
				</div>
			</td>
		</tr>';
	}

	static function entries_tab(){
		
		if (defined('FORMLAYER_PRO_VERSION')) {
			do_action('formlayer_render_entries_tab');
		} else {
			self::pro_placeholder_page();
		}
	}

	static function integrations_tab(){

		if (defined('FORMLAYER_PRO_VERSION')) {
			do_action('formlayer_render_integrations');
		} else {
			self::pro_placeholder_page();
		}
	}

	static function pro_placeholder_page(){
		echo '<div class="formlayer-placeholder-page">
			<div class="notice notice-warning">
				<p>' . esc_html__('This is a part of FormLayer Pro, so update/upgrade to pro to utilize this feature', 'formlayer') . '</p>
			</div>
		</div>';
	}

	static function support_tab(){
		echo '<div class="formlayer-support-page">
			<div class="formlayer-logo">
				<img alt="' . esc_html__('formlayer logo', 'formlayer') . '" style="max-width: 280px; height: auto;" src="' . esc_url(FORMLAYER_ASSETS_URL) . '/img/formlayer-banner.png' . '"/>	
			</div>
			<h2>' . esc_html__('Help & Support', 'formlayer') . '</h2>
			<p>' . esc_html__('You can contact the FormLayer team via email at ', 'formlayer') . '<a href="mailto:support@formlayer.net">support@formlayer.net</a> ' . esc_html__('or through our Support Ticket System.', 'formlayer') . '</p>
			<p>' . esc_html__('You can also check the documentation here:', 'formlayer') . ' <a href="https://formlayer.net/docs/" target="_blank" rel="noopener noreferrer">https://formlayer.net/docs/</a></p>
		</div>';
	}

	static function settings_tab(){
		$settings = get_option('formlayer_settings', []);

		// Defaults
		$defaults = [
			'msg_success' => __('Thank you! Your form has been submitted successfully.', 'formlayer'),
			'msg_error' => __('Something went wrong. Please try again.', 'formlayer'),
			'msg_required' => __('This field is required.', 'formlayer'),
			'msg_email' => __('Please enter a valid email address.', 'formlayer'),
			'msg_url' => __('Please enter a valid URL.', 'formlayer'),
			'msg_number' => __('Please enter a valid number.', 'formlayer'),
			'msg_file_type' => __('This file type is not permitted.', 'formlayer'),
			'msg_file_size' => __('The file is too large.', 'formlayer'),
			'msg_terms' => __('You must accept the terms and conditions.', 'formlayer'),
			'captcha_provider' => 'none',
			'captcha_h_site_key' => '',
			'captcha_h_secret_key' => '',
			'captcha_h_theme' => 'light',
			'captcha_t_site_key' => '',
			'captcha_t_secret_key' => '',
			'captcha_t_theme' => 'light',
			'captcha_r_site_key' => '',
			'captcha_r_secret_key' => '',
			'captcha_r_theme' => 'light',
		];
		$settings = array_merge($defaults, $settings);

		echo'<div class="formlayer-settings-container">
			<!-- Validation Section -->
			<div class="formlayer-settings-card">
				<div class="formlayer-settings-header">
					<div style="display: flex; align-items: center; gap: 12px;">
						<div style="background: var(--fl-primary-light); color: var(--fl-primary); width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
							<span class="dashicons dashicons-yes-alt"></span>
						</div>
						<div>
							<h3>' . esc_html__('Form Validation & Messages', 'formlayer') . '</h3>
							<p>' . esc_html__('Customize error and success messages for all forms.', 'formlayer') . '</p>
						</div>
					</div>
				</div>
				<div class="formlayer-settings-body">
					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
						<!-- Left Column -->
						<div>
							<div class="formlayer-setting-row-v">
								<label>' . esc_html__('Success Message', 'formlayer') . '</label>
								<textarea name="msg_success" class="formlayer-input" rows="2">' . esc_textarea($settings['msg_success']) . '</textarea>
							</div>
							<div class="formlayer-setting-row-v">
								<label>' . esc_html__('General Error', 'formlayer') . '</label>
								<textarea name="msg_error" class="formlayer-input" rows="2">' . esc_textarea($settings['msg_error']) . '</textarea>
							</div>
							<div class="formlayer-setting-row-v">
								<label>' . esc_html__('Required Field', 'formlayer') . '</label>
								<input type="text" name="msg_required" class="formlayer-input" value="' . esc_attr($settings['msg_required']) . '">
							</div>
							<div class="formlayer-setting-row-v">
								<label>' . esc_html__('Terms Acceptance', 'formlayer') . '</label>
								<input type="text" name="msg_terms" class="formlayer-input" value="' . esc_attr($settings['msg_terms']) . '">
							</div>
						</div>
						<!-- Right Column -->
						<div>
							<div class="formlayer-setting-row-v">
								<label>' . esc_html__('Invalid Email', 'formlayer') . '</label>
								<input type="text" name="msg_email" class="formlayer-input" value="' . esc_attr($settings['msg_email']) . '">
							</div>
							<div class="formlayer-setting-row-v">
								<label>' . esc_html__('Invalid URL', 'formlayer') . '</label>
								<input type="text" name="msg_url" class="formlayer-input" value="' . esc_attr($settings['msg_url']) . '">
							</div>
							<div class="formlayer-setting-row-v">
								<label>' . esc_html__('Invalid Number', 'formlayer') . '</label>
								<input type="text" name="msg_number" class="formlayer-input" value="' . esc_attr($settings['msg_number']) . '">
							</div>
							<div class="formlayer-setting-row-v">
								<label>' . esc_html__('Invalid File Type', 'formlayer') . '</label>
								<input type="text" name="msg_file_type" class="formlayer-input" value="' . esc_attr($settings['msg_file_type']) . '">
							</div>
							<div class="formlayer-setting-row-v">
								<label>' . esc_html__('File Too Large', 'formlayer') . '</label>
								<input type="text" name="msg_file_size" class="formlayer-input" value="' . esc_attr($settings['msg_file_size']) . '">
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Captcha Section -->
			<div class="formlayer-settings-card" style="margin-top: 30px;">
				<div class="formlayer-settings-header">
					<div class="formlayer-captcha-header-left">
						<div class="formlayer-captcha-icon-box">
							<span class="dashicons dashicons-shield"></span>
						</div>
						<div>
							<h3 style="margin: 0; font-size: 18px; font-weight: 700;">' . esc_html__('Spam Protection (Captcha)', 'formlayer') . '</h3>
							<p style="margin: 4px 0 0 0; font-size: 13px; color: var(--fl-text-light);">' . esc_html__('Configure and select your preferred captcha service to block bots.', 'formlayer') . '</p>
						</div>
					</div>
				</div>
				<div class="formlayer-settings-body" style="padding: 0 32px 32px 32px;">
					
					<div class="formlayer-captcha-tabs-wrapper">
						<div class="formlayer-captcha-tabs-nav">
							<div class="captcha-tab-item active" data-target="hcaptcha">
								<div class="tab-icon-wrap" style="background: #f0fdf4; color: #22c55e;">
									<span class="dashicons dashicons-shield"></span>
								</div>
								<span>hCaptcha</span>
							</div>
							<div class="captcha-tab-item" data-target="turnstile">';
								if(!defined('FORMLAYER_PRO_VERSION')){
									echo'<span class="dashicons dashicons-lock" style="opacity:0.5;margin-right:3px;"></span>';
								}
								echo'<span>Turnstile</span>
							</div>
							<div class="captcha-tab-item" data-target="recaptcha">
								<div class="tab-icon-wrap" style="background: #fff7ed; color: #f97316;">
									<span class="dashicons dashicons-google"></span>
								</div>
								<span>reCAPTCHA</span>
							</div>
						</div>

						<div class="formlayer-captcha-tab-content">
							<!-- hCaptcha Details -->
							<div class="captcha-pane active" id="pane-hcaptcha">
								<div class="pane-header">
									<h4 style="margin: 0; font-size: 15px;">hCaptcha Configuration</h4>
									<p>' . esc_html__('hCaptcha is a free, privacy-focused alternative to Google reCAPTCHA. Visit hcaptcha.com to get your keys.', 'formlayer') . '</p>
								</div>
								
								<div class="formlayer-setting-row-v">
									<label>' . esc_html__('Site Key', 'formlayer') . '</label>
									<input type="text" name="captcha_h_site_key" class="formlayer-input" value="' . esc_attr($settings['captcha_h_site_key']) . '" placeholder="Enter your hCaptcha site key">
								</div>
								<div class="formlayer-setting-row-v">
									<label>' . esc_html__('Secret Key', 'formlayer') . '</label>
									<input type="password" name="captcha_h_secret_key" class="formlayer-input" value="' . esc_attr($settings['captcha_h_secret_key']) . '" placeholder="••••••••••••••••">
								</div>
								<div class="formlayer-setting-row-v">
									<label>' . esc_html__('Default Theme', 'formlayer') . '</label>
									<select name="captcha_h_theme" class="formlayer-select">
										<option value="light" ' . selected($settings['captcha_h_theme'], 'light', false) . '>Light</option>
										<option value="dark" ' . selected($settings['captcha_h_theme'], 'dark', false) . '>Dark</option>
									</select>
								</div>
							</div>

							<!-- Turnstile Details -->
							<div class="captcha-pane" id="pane-turnstile">';
							if(!defined('FORMLAYER_PRO_VERSION')){
								echo'<div class="formlayer-pro-lock-pane">
									<div class="lock-inner">
										<div class="lock-icon">
											<span class="dashicons dashicons-lock"></span>
										</div>
										<h4>'.esc_html__('Cloudflare Turnstile is Pro', 'formlayer').'</h4>
										<p>'.esc_html__('Enjoy the seamless, non-intrusive CAPTCHA experience of Cloudflare Turnstile by upgrading to FormLayer Pro.', 'formlayer').'</p>
										<a href="#" class="formlayer-btn formlayer-btn-primary">'.esc_html__('Get FormLayer Pro', 'formlayer').'</a>
									</div>
								</div>';
							} else{
								do_action('formlayer_render_turnstile_settings', $settings);
							}
							
							echo'</div>

							<!-- reCAPTCHA Details -->
							<div class="captcha-pane" id="pane-recaptcha">';
							if(!defined('FORMLAYER_PRO_VERSION')){
								echo'<div class="formlayer-pro-lock-pane">
									<div class="lock-inner">
										<div class="lock-icon">
											<span class="dashicons dashicons-lock"></span>
										</div>
										<h4>'.esc_html__('Google reCAPTCHA is Pro', 'formlayer').'</h4>
										<p>'.esc_html__('Enjoy the legacy and advanced features of Google reCAPTCHA v2 and v3 by upgrading to FormLayer Pro.', 'formlayer').'</p>
										<a href="#" class="formlayer-btn formlayer-btn-primary">'.esc_html__('Get FormLayer Pro', 'formlayer').'</a>
									</div>
								</div>';
							} else {
								do_action('formlayer_render_recaptcha_settings', $settings);
							}
							echo'</div>
						</div>
					</div>

					<div class="captcha-notice-box formlayer-captcha-notice-box">
						<span class="dashicons dashicons-info" style="color: #0284c7; margin-top: 2px;"></span>
						<p style="margin: 0; font-size: 13px; color: #0369a1; line-height: 1.5;">
							' . esc_html__('Configure your credentials above. You can then add a "Captcha" field to your individual forms and select which provider to use for that specific form.', 'formlayer') . '
						</p>
					</div>

				</div>
			</div>

			<div class="formlayer-settings-footer">
				<div class="formlayer-footer-status" id="formlayer-settings-status"></div>
				<button id="formlayer-save-settings" class="formlayer-btn formlayer-btn-primary" style="padding: 12px 30px; font-size: 15px;">
					' . esc_html__('Save Settings', 'formlayer') . '
				</button>
			</div>
		</div>';
	}

	static function footer(){
		echo '</main></div>'; // Close main and wrapper
		self::modals();
	}

	static function modals(){
		$settings = get_option('formlayer_settings', []);

		// Entry Detail Modal
		echo '<div id="formlayer-entry-modal" class="formlayer-modal-overlay formlayer-entry-modal">
			<div class="formlayer-modal-content">
				<button class="formlayer-modal-close formlayer-modal-close-btn">&times;</button>
				<div id="formlayer-entry-detail-body">
					<!-- Loaded via AJAX -->
				</div>
			</div>
		</div>';

		// Form Settings Modal
		echo '<div id="formlayer-form-settings-modal" class="formlayer-modal-overlay">
			<div class="formlayer-modal-panel">
				<div class="formlayer-modal-header">
					<div class="header-left">
						<span class="dashicons dashicons-admin-settings"></span>
						<h2>' . esc_html__('Form Settings', 'formlayer') . '</h2>
					</div>
					<div class="header-right">
						<button class="formlayer-btn formlayer-btn-primary" id="formlayer-settings-apply-btn">' . esc_html__('Apply Settings', 'formlayer') . '</button>
						<button class="formlayer-modal-close" id="formlayer-settings-close-btn">&times;</button>
					</div>
				</div>
				<div class="formlayer-modal-body">
					<div class="formlayer-modal-sidebar">
						<ul>
							<li class="active" data-section="notifications">
								<span class="dashicons dashicons-email"></span>' . esc_html__('Email Notifications', 'formlayer') . '
							</li>
							<li data-section="confirmations">
								<span class="dashicons dashicons-yes"></span>' . esc_html__('Form Confirmations', 'formlayer') . '
							</li>
							<li data-section="integrations">
								<span class="dashicons dashicons-admin-links"></span>' . esc_html__('Integrations', 'formlayer') . '
							</li>
							<li data-section="custom_css">
								<span class="dashicons dashicons-editor-code"></span>' . esc_html__('Custom CSS', 'formlayer') . '
							</li>
						</ul>
					</div>
					<div class="formlayer-modal-main">
						<!-- Notifications Section -->
						<div class="formlayer-settings-section active" id="formlayer-settings-notifications">
							<div class="section-header">
								<h3>' . esc_html__('Email Notifications', 'formlayer') . '</h3>
								<p>' . esc_html__('Configure how you want to be notified when a form is submitted.', 'formlayer') . '</p>
							</div>
							<div class="formlayer-settings-field">
								<label>' . esc_html__('Enabled', 'formlayer') . '</label>
								<div class="formlayer-switch-wrapper">
									<label class="formlayer-switch">
										<input type="checkbox" id="form-setting-notif-enabled" checked>
										<span class="slider round"></span>
									</label>
								</div>
							</div>
							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
								<div class="formlayer-settings-field">
									<label>' . esc_html__('Send To Email', 'formlayer') . '</label>
									<input type="text" id="form-setting-notif-email" class="formlayer-input" placeholder="{admin_email}">
								</div>
								<div class="formlayer-settings-field">
									<label>' . esc_html__('Reply To', 'formlayer') . '</label>
									<input type="text" id="form-setting-notif-replyto" class="formlayer-input" placeholder="e.g. {field_email}">
								</div>
							</div>
							<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
								<div class="formlayer-settings-field">
									<label>' . esc_html__('From Name', 'formlayer') . '</label>
									<input type="text" id="form-setting-notif-fromname" class="formlayer-input" placeholder="FormLayer">
								</div>
								<div class="formlayer-settings-field">
									<label>' . esc_html__('From Email', 'formlayer') . '</label>
									<input type="text" id="form-setting-notif-fromemail" class="formlayer-input" placeholder="{admin_email}">
								</div>
							</div>
							<div class="formlayer-settings-field">
								<label>' . esc_html__('BCC', 'formlayer') . '</label>
								<input type="text" id="form-setting-notif-bcc" class="formlayer-input" placeholder="comma separated emails">
							</div>
							<div class="formlayer-settings-field">
								<label>' . esc_html__('Subject', 'formlayer') . '</label>
								<input type="text" id="form-setting-notif-subject" class="formlayer-input" placeholder="New Form Submission">
							</div>
							<div class="formlayer-settings-field">
								<label>' . esc_html__('Message Body', 'formlayer') . '</label>
								<textarea id="form-setting-notif-message" class="formlayer-input" style="height:120px;"></textarea>
								<p class="description">Use {all_fields} to include all form data.</p>
							</div>
						</div>

						<!-- Confirmations Section -->
						<div class="formlayer-settings-section" id="formlayer-settings-confirmations" style="display:none;">
							<div class="section-header">
								<h3>' . esc_html__('Form Confirmations', 'formlayer') . '</h3>
								<p>' . esc_html__('What happens after a user submits the form?', 'formlayer') . '</p>
							</div>
							<div class="formlayer-settings-field">
								<label>' . esc_html__('Confirmation Type', 'formlayer') . '</label>
								<select id="form-setting-conf-type" class="formlayer-select">
									<option value="message">' . esc_html__('Display Message', 'formlayer') . '</option>
									<option value="redirect">' . esc_html__('Redirect to Page/URL', 'formlayer') . '</option>
								</select>
							</div>
							
							<div id="conf-group-message">
								<div class="formlayer-settings-field">
									<label>' . esc_html__('Success Message', 'formlayer') . '</label>
									<textarea id="form-setting-conf-message" class="formlayer-input" style="height:100px;"></textarea>
								</div>
								<div class="formlayer-switch-wrapper">
									<label class="formlayer-switch">
										<input type="checkbox" id="form-setting-conf-hide" checked>
										<span class="slider round"></span>
									</label>
								</div>
							</div>

							<div id="conf-group-redirect" style="display:none;">
								<div class="formlayer-settings-field">
									<label>' . esc_html__('Redirect URL', 'formlayer') . '</label>
									<input type="text" id="form-setting-conf-url" class="formlayer-input" placeholder="https://example.com/thanks">
								</div>
							</div>
						</div>

						<div class="formlayer-settings-section" id="formlayer-settings-integrations" style="display:none;">
							<div class="section-header">
								<h3>' . esc_html__('Integrations', 'formlayer') . '</h3>
								<p>' . esc_html__('Connect your form to 3rd party services.', 'formlayer') . '</p>
							</div>';

							if(defined("FORMLAYER_PRO_VERSION")){
								do_action("formlayer_render_form_integrations");
							} else {
								echo'<div class="formlayer-pro-lock-pane formlayer-pro-lock-pane-wrap">
									<div class="formlayer-pro-lock-icon-box">
										<span class="dashicons dashicons-lock" style="font-size:32px; height:32px; width:32px; color:var(--fl-primary);"></span>
									</div>
									<h4 class="formlayer-empty-title">' . esc_html__('Unlock Pro Integrations', 'formlayer') . '</h4>
									<p style="color:#64748b; margin:0 0 20px 0;">' . esc_html__('Connect to Slack, Mailchimp, Notion, Trello and more with FormLayer Pro.', 'formlayer') . '</p>
									<a href="#" class="formlayer-btn formlayer-btn-primary">' . esc_html__('Go Pro', 'formlayer') . '</a>
								</div>';
							}

							echo'</div>
						<!-- Custom CSS Section -->
						<div class="formlayer-settings-section" id="formlayer-settings-custom_css" style="display:none;">
							<div class="section-header">
								<h3>' . esc_html__('Custom CSS', 'formlayer') . '</h3>
								<p>' . esc_html__('Add custom styles specifically for this form.', 'formlayer') . '</p>
							</div>
							<div class="formlayer-settings-field">
								<textarea id="form-setting-custom-css" class="formlayer-input" style="height:300px; font-family:monospace;" placeholder=".formlayer-form { /* Your styles here */ }"></textarea>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>';

		
		do_action('formlayer_render_modals');
	
	}
}