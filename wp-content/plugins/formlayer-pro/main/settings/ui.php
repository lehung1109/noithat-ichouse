<?php
/*
* FormLayer Pro
* https://formlayer.net
* (c) FormLayer Team
*/

namespace FormLayerPro\Settings;

if(!defined('ABSPATH')){
	exit;
}

class UI{

	static function render_int_card( $name, $desc, $logo, $slug, $tag = '', $connected = false){

		echo '<div class="formlayer-integration-card" data-slug="' . esc_attr($slug) . '">
			<div class="formlayer-card-header">
				<div class="formlayer-int-icon" style="background:none; padding:0;">
					<img src="'.esc_url(FORMLAYER_PRO_ASSETS_URL . '/img/' . $logo ).'" alt="'.esc_attr($name).'" 
						 style="width:48px; height:48px; object-fit:contain;">
				</div>';

		// Tag
		if(!empty($tag)){
			echo '<span class="formlayer-int-tag">'.esc_html($tag).'</span>';
		}

		echo '</div>
			<h3 class="formlayer-int-title">'.esc_html($name).'</h3>
			<p class="formlayer-int-desc">'.esc_html($desc ).'</p>

			<div class="formlayer-int-footer">';

		// Button
		$all_settings = get_option('formlayer_integration_settings', []);
		$connected = !empty($all_settings[$slug]['enabled']);
		if($connected){
			echo '<button class="formlayer-btn formlayer-btn-outline formlayer-configure-int" data-name="'.esc_attr($name).'" data-slug="'.esc_attr($slug).'">'.esc_html__('Connected', 'formlayer-pro').'</button>';
		} else {
			echo '<button class="formlayer-btn formlayer-btn-action formlayer-configure-int" style="width: 100%;" data-name="'.esc_attr($name).'" data-slug="'.esc_attr($slug).'">'.esc_html__('Configure', 'formlayer-pro').'</button>';
		}

		// Documentation link
		echo '<a href="#" class="formlayer-btn-link">'.esc_html__('Documentation', 'formlayer-pro').'</a>';

		echo '</div>
		</div>';
	}

	static function integrations(){
		// Integration cards
		echo'<div class="formlayer-integrations-grid">';
		self::render_int_card('Slack', __('Receive real-time notifications in your Slack channels whenever a form is submitted.', 'formlayer-pro'), 'slack-logo.png', 'slack', 'MOST POPULAR');

		self::render_int_card('Google Sheets', __('Automatically sync every new entry to a specific spreadsheet. Perfect for reporting.', 'formlayer-pro'), 'sheets-logo.png', 'sheets');

		self::render_int_card('Notion', __('Push form data into your Notion databases or create new pages automatically.', 'formlayer-pro'), 'notion-logo.png', 'notion');

		self::render_int_card('Trello', __('Turn submissions into Trello cards. Ideal for lead management and support.', 'formlayer-pro'), 'trello-logo.png', 'trello');

		self::render_int_card('Mailchimp', __('Subscribe users to your audience lists based on their form inputs.', 'formlayer-pro'), 'mailchimp-logo.png', 'mailchimp');
		
		self::render_int_card('Discord', __('notifications Discord', 'formlayer-pro'), 'discord-logo.png', 'discord');
		
		echo'</div>';
	}

	static function render_form_integrations(){
		echo '<div class="formlayer-pro-form-settings">
			<div class="formlayer-settings-field">
				<label>' . esc_html__('Active Integrations', 'formlayer-pro') . '</label>
				<p class="description" style="margin-bottom: 20px;">' . esc_html__('Enable and configure integrations for this specific form. Form settings override global settings.', 'formlayer-pro') . '</p>
				
				<div id="formlayer-active-integrations-container">
					<!-- Slack -->
					<div class="formlayer-integration-setting-row" style="background:#f8fafc; border:1px solid #f1f5f9; border-radius:12px; padding:20px; margin-bottom:15px;">
						<div style="display:flex; justify-content:space-between; align-items:center;">
							<div style="display:flex; align-items:center; gap:12px;">
								<img src="'.esc_url(FORMLAYER_PRO_ASSETS_URL . '/img/slack-logo.png').'" style="width:32px; height:32px;">
								<span style="font-weight:700; font-size:16px;">Slack</span>
							</div>
							<label class="formlayer-switch">
								<input type="checkbox" id="form-setting-int-slack-enabled">
								<span class="slider round"></span>
							</label>
						</div>
						<div class="integration-row-content" id="slack-integration-fields" style="margin-top:20px; padding-top:20px; border-top:1px solid #edf2f7; display:none;">
							<div class="formlayer-settings-field">
								<label>' . esc_html__('Override Webhook URL', 'formlayer-pro') . '</label>
								<input type="text" id="form-setting-int-slack-webhook" class="formlayer-input" placeholder="Leave empty to use global webhook">
							</div>
						</div>
					</div>

					<!-- Mailchimp -->
					<div class="formlayer-integration-setting-row" style="background:#f8fafc; border:1px solid #f1f5f9; border-radius:12px; padding:20px; margin-bottom:15px;">
						<div style="display:flex; justify-content:space-between; align-items:center;">
							<div style="display:flex; align-items:center; gap:12px;">
								<img src="'.esc_url(FORMLAYER_PRO_ASSETS_URL . '/img/mailchimp-logo.png').'" style="width:32px; height:32px;">
								<span style="font-weight:700; font-size:16px;">Mailchimp</span>
							</div>
							<label class="formlayer-switch">
								<input type="checkbox" id="form-setting-int-mailchimp-enabled">
								<span class="slider round"></span>
							</label>
						</div>
						<div class="integration-row-content" id="mailchimp-integration-fields" style="margin-top:20px; padding-top:20px; border-top:1px solid #edf2f7; display:none;">
							<div class="formlayer-settings-field">
								<label>' . esc_html__('Override Audience ID', 'formlayer-pro') . '</label>
								<input type="text" id="form-setting-int-mailchimp-list" class="formlayer-input" placeholder="Leave empty to use global list">
							</div>
						</div>
					</div>

					<!-- Google Sheets -->
					<div class="formlayer-integration-setting-row" style="background:#f8fafc; border:1px solid #f1f5f9; border-radius:12px; padding:20px; margin-bottom:15px;">
						<div style="display:flex; justify-content:space-between; align-items:center;">
							<div style="display:flex; align-items:center; gap:12px;">
								<img src="'.esc_url(FORMLAYER_PRO_ASSETS_URL . '/img/sheets-logo.png').'" style="width:32px; height:32px;">
								<span style="font-weight:700; font-size:16px;">Google Sheets</span>
							</div>
							<label class="formlayer-switch">
								<input type="checkbox" id="form-setting-int-sheets-enabled">
								<span class="slider round"></span>
							</label>
						</div>
						<div class="integration-row-content" id="sheets-integration-fields" style="margin-top:20px; padding-top:20px; border-top:1px solid #edf2f7; display:none;">
							<div class="formlayer-settings-field">
								<label>' . esc_html__('Spreadsheet ID', 'formlayer-pro') . '</label>
								<input type="text" id="form-setting-int-sheets-id" class="formlayer-input" placeholder="e.g. 1a2b3c4d...">
							</div>
							<div class="formlayer-settings-field" style="margin-top:15px;">
								<label>' . esc_html__('Sheet Name', 'formlayer-pro') . '</label>
								<input type="text" id="form-setting-int-sheets-name" class="formlayer-input" placeholder="e.g. Sheet1">
							</div>
						</div>
					</div>

					<!-- Notion -->
					<div class="formlayer-integration-setting-row" style="background:#f8fafc; border:1px solid #f1f5f9; border-radius:12px; padding:20px; margin-bottom:15px;">
						<div style="display:flex; justify-content:space-between; align-items:center;">
							<div style="display:flex; align-items:center; gap:12px;">
								<img src="'.esc_url(FORMLAYER_PRO_ASSETS_URL . '/img/notion-logo.png').'" style="width:32px; height:32px;">
								<span style="font-weight:700; font-size:16px;">Notion</span>
							</div>
							<label class="formlayer-switch">
								<input type="checkbox" id="form-setting-int-notion-enabled">
								<span class="slider round"></span>
							</label>
						</div>
						<div class="integration-row-content" id="notion-integration-fields" style="margin-top:20px; padding-top:20px; border-top:1px solid #edf2f7; display:none;">
							<div class="formlayer-settings-field">
								<label>' . esc_html__('Override Database ID', 'formlayer-pro') . '</label>
								<input type="text" id="form-setting-int-notion-db" class="formlayer-input" placeholder="Leave empty to use global database">
							</div>
						</div>
					</div>

					<!-- Trello -->
					<div class="formlayer-integration-setting-row" style="background:#f8fafc; border:1px solid #f1f5f9; border-radius:12px; padding:20px; margin-bottom:15px;">
						<div style="display:flex; justify-content:space-between; align-items:center;">
							<div style="display:flex; align-items:center; gap:12px;">
								<img src="'.esc_url(FORMLAYER_PRO_ASSETS_URL . '/img/trello-logo.png').'" style="width:32px; height:32px;">
								<span style="font-weight:700; font-size:16px;">Trello</span>
							</div>
							<label class="formlayer-switch">
								<input type="checkbox" id="form-setting-int-trello-enabled">
								<span class="slider round"></span>
							</label>
						</div>
						<div class="integration-row-content" id="trello-integration-fields" style="margin-top:20px; padding-top:20px; border-top:1px solid #edf2f7; display:none;">
							<div class="formlayer-settings-field">
								<label>' . esc_html__('List ID', 'formlayer-pro') . '</label>
								<input type="text" id="form-setting-int-trello-list" class="formlayer-input" placeholder="Enter Trello List ID">
							</div>
						</div>
					</div>

					<!-- Discord -->
					<div class="formlayer-integration-setting-row" style="background:#f8fafc; border:1px solid #f1f5f9; border-radius:12px; padding:20px; margin-bottom:15px;">
						<div style="display:flex; justify-content:space-between; align-items:center;">
							<div style="display:flex; align-items:center; gap:12px;">
								<img src="'.esc_url(FORMLAYER_PRO_ASSETS_URL . '/img/discord-logo.png').'" style="width:32px; height:32px;">
								<span style="font-weight:700; font-size:16px;">Discord</span>
							</div>
							<label class="formlayer-switch">
								<input type="checkbox" id="form-setting-int-discord-enabled">
								<span class="slider round"></span>
							</label>
						</div>
						<div class="integration-row-content" id="discord-integration-fields" style="margin-top:20px; padding-top:20px; border-top:1px solid #edf2f7; display:none;">
							<div class="formlayer-settings-field">
								<label>' . esc_html__('Override Webhook URL', 'formlayer-pro') . '</label>
								<input type="text" id="form-setting-int-discord-webhook" class="formlayer-input" placeholder="Leave empty to use global webhook">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>';
	}

	static function render_modal(){
		echo '<div id="formlayer-integration-modal" class="formlayer-modal-overlay" style="display:none;">
			<div class="formlayer-modal-content" style="max-width: 600px;">
				<div class="formlayer-modal-header" style="border-bottom: 1px solid #f1f5f9; padding-bottom: 20px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
					<div>
						<h3 id="int-modal-title" style="margin: 0; font-size: 22px; font-weight: 700; color: #1e293b;">' . esc_html__('Configure Integration', 'formlayer-pro') . '</h3>
						<p id="int-modal-desc" style="margin: 6px 0 0 0; font-size: 14px; color: #64748b;">' . esc_html__('Enter your credentials to connect.', 'formlayer-pro') . '</p>
					</div>
				</div>
				
				<div id="int-settings-fields" class="formlayer-settings-body">
					<!-- Fields will be injected here via JS -->
				</div>

				<div class="formlayer-modal-footer" style="margin-top: 30px; padding-top: 25px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 12px;">
					<button class="formlayer-btn formlayer-btn-outline" id="close-int-modal">' . esc_html__('Cancel', 'formlayer-pro') . '</button>
					<button class="formlayer-btn formlayer-btn-primary" id="save-int-settings">' . esc_html__('Save & Connect', 'formlayer-pro') . '</button>
				</div>
			</div>
		</div>';
	}

	static function turnstile($settings){

		echo'<div class="pane-header">
			<h4 style="margin: 0; font-size: 15px;">Cloudflare Turnstile Configuration</h4>
			<p>' . esc_html__('Cloudflare Turnstile provides a seamless, non-intrusive CAPTCHA experience. Get your keys from the Cloudflare Dashboard.', 'formlayer-pro') . '</p>
		</div>
		<div class="formlayer-setting-row-v">
			<label>' . esc_html__('Site Key', 'formlayer-pro') . '</label>
			<input type="text" name="captcha_t_site_key" class="formlayer-input" value="' . esc_attr($settings['captcha_t_site_key']) . '" placeholder="Enter your Turnstile site key">
			</div>
			<div class="formlayer-setting-row-v">
			<label>' . esc_html__('Secret Key', 'formlayer-pro') . '</label>
			<input type="password" name="captcha_t_secret_key" class="formlayer-input" value="' . esc_attr($settings['captcha_t_secret_key']) . '" placeholder="••••••••••••••••">
			</div>
			<div class="formlayer-setting-row-v">
			<label>' . esc_html__('Default Theme', 'formlayer-pro') . '</label>
			<select name="captcha_t_theme" class="formlayer-select">
				<option value="light" ' . selected($settings['captcha_t_theme'], 'light', false) . '>Light</option>
				<option value="dark" ' . selected($settings['captcha_t_theme'], 'dark', false) . '>Dark</option>
			</select>
		</div>';
	}

	static function recaptcha($settings){
		echo'<div class="pane-header">
			<h4 style="margin: 0; font-size: 15px;">Google reCAPTCHA Configuration</h4>
			<p>' . esc_html__('Google reCAPTCHA is a widely used spam protection service. Use either v2 keys here.', 'formlayer-pro') . '</p>
		</div>
		<div class="formlayer-setting-row-v">
			<label>' . esc_html__('reCAPTCHA Site Key', 'formlayer-pro') . '</label>
			<input type="text" name="captcha_r_site_key" class="formlayer-input" value="' . esc_attr($settings['captcha_r_site_key']) . '">
		</div>
		<div class="formlayer-setting-row-v">
			<label>' . esc_html__('reCAPTCHA Secret Key', 'formlayer-pro') . '</label>
			<input type="password" name="captcha_r_secret_key" class="formlayer-input" value="' . esc_attr($settings['captcha_r_secret_key']) . '">
		</div>
		<div class="formlayer-setting-row-v">
			<label>' . esc_html__('Default Theme', 'formlayer-pro') . '</label>
			<select name="captcha_r_theme" class="formlayer-select">
				<option value="light" ' . selected($settings['captcha_r_theme'], 'light', false) . '>Light</option>
				<option value="dark" ' . selected($settings['captcha_r_theme'], 'dark', false) . '>Dark</option>
			</select>
		</div>';
	}

	static function entries(){
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total_entries = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}formlayer_entries");
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$today_entries = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}formlayer_entries WHERE DATE(created_at) = CURDATE()");

		$forms = get_posts([
			'post_type' => 'formlayer_form',
			'posts_per_page' => -1,
			'post_status' => 'any'
		]);

		$page = 1;
		$per_page = 10;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total_entries = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}formlayer_entries");
		$total_pages = ceil($total_entries / $per_page);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$recent_entries = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}formlayer_entries ORDER BY created_at DESC LIMIT 10");

		echo '<div class="formlayer-entries-view">
			<div class="formlayer-entries-header" style="margin-bottom: 24px;">
				<div class="formlayer-entries-filter-bar">
					<div class="formlayer-entries-filter-left">
						<div class="formlayer-search-wrap formlayer-entries-search-wrapper">
							<span class="dashicons dashicons-search formlayer-entries-search-icon"></span>
							<input type="text" id="formlayer-entries-search" class="formlayer-entries-search-input-el" placeholder="' . esc_attr__('Search entries...', 'formlayer-pro') . '">
						</div>
						<select id="formlayer-entries-filter-form" class="formlayer-select formlayer-select-compact">
							<option value="">' . esc_html__('All Forms', 'formlayer-pro') . '</option>';
								foreach($forms as $form){
									echo '<option value="' . esc_attr($form->ID) . '">' . esc_html(get_the_title($form->ID)) . '</option>';
								}

						echo'</select>
						<select id="formlayer-entries-filter-status" class="formlayer-select formlayer-select-compact">
							<option value="">' . esc_html__('All Status', 'formlayer-pro') . '</option>
							<option value="read">' . esc_html__('Read', 'formlayer-pro') . '</option>
							<option value="unread">' . esc_html__('Unread', 'formlayer-pro') . '</option>
						</select>
					</div>
					<div style="display: flex; gap: 12px;">
						<button id="formlayer-btn-export-entries" class="formlayer-btn formlayer-btn-outline formlayer-export-btn">
							<span class="dashicons dashicons-migrate" style="font-size: 16px; margin-top: 2px;"></span>
							' . esc_html__('Export (CSV)', 'formlayer-pro') . '
						</button>
					</div>
				</div>
			</div>';

		echo '<div class="formlayer-table-card formlayer-entries-table-card">
				<div class="formlayer-entries-table-actions">
					<select id="formlayer-entries-bulk-action" class="formlayer-select formlayer-select-compact" style="min-width: 140px;">
						<option value="">' . esc_html__('Bulk Actions', 'formlayer-pro') . '</option>
						<option value="mark_read">' . esc_html__('Mark as Read', 'formlayer-pro') . '</option>
						<option value="mark_unread">' . esc_html__('Mark as Unread', 'formlayer-pro') . '</option>
						<option value="delete">' . esc_html__('Delete Permanently', 'formlayer-pro') . '</option>
					</select>
					<button id="formlayer-entries-apply-bulk" class="formlayer-btn formlayer-btn-outline" style="height: 34px; padding: 0 15px; font-size: 13px;">' . esc_html__('Apply', 'formlayer-pro') . '</button>
				</div>
				<div style="padding: 30px;">
					<table class="formlayer-table">
						<thead>
							<tr>
								<th style="width: 40px;"><input type="checkbox" id="formlayer-entries-select-all"></th>
								<th>' . esc_html__('ID', 'formlayer-pro') . '</th>
								<th>' . esc_html__('Status', 'formlayer-pro') . '</th>
								<th>' . esc_html__('Form Name', 'formlayer-pro') . '</th>
								<th>' . esc_html__('Submited At', 'formlayer-pro') . '</th>
								<th style="text-align: right; padding-right: 32px;">' . esc_html__('Actions', 'formlayer-pro') . '</th>
							</tr>
						</thead>
						<tbody id="formlayer-entries-tbody">';

		if (empty($recent_entries)) {
			echo '<tr><td colspan="6" style="text-align:center; padding:80px 0; color:var(--formlayer-text-muted);">
					<div style="font-size: 40px; margin-bottom: 20px;">📂</div>
					<div style="font-weight: 600; font-size: 16px;">' . esc_html__('No submissions found', 'formlayer-pro') . '</div>
					<p>' . esc_html__('Wait for your first form submission to see the data here.', 'formlayer-pro') . '</p>
				</td></tr>';
		} else {
			foreach ($recent_entries as $entry) {
				$form = get_post($entry->form_id);
				$form_title = $form ? get_the_title($form->ID) : 'Unknown Form';
				$data_raw = $entry->data;
				$data = json_decode($data_raw, true);

				if ($data === null && !empty($data_raw)) {
					$data = json_decode(stripslashes($data_raw), true);
				}

				$field_labels = \FormLayer\Util::get_form_field_labels($entry->form_id);
				$summary = [];
				if (is_array($data)) {
					foreach (array_slice($data, 0, 6) as $k => $v) {

						if (empty($v) || $k === '__source_url') {
							continue;
						}

						$label = isset($field_labels[$k]) ? $field_labels[$k] : ucfirst(str_replace(['field_', '_'], ['', ' '], $k));

						if(is_array($v)){
							$v = implode(', ', array_map('strval', $v));
						}

						$summary[] = '<strong>' . esc_html($label) . ':</strong> ' . esc_html(mb_strimwidth(wp_strip_all_tags($v), 0, 40, '...'));
					}
				}

				if(empty($summary)){
					$data_html = '<span style="color:#94a3b8; font-style:italic;">' . esc_html__('No displayable data', 'formlayer-pro') . '</span>';
					if (!empty($data_raw)) {
						$data_html = esc_html(mb_strimwidth(wp_strip_all_tags($data_raw), 0, 100, '...'));
					}
				} else {
					$data_html = implode(' <span style="color:#e2e8f0; margin:0 4px;">|</span> ', $summary);
				}

				$status_class = !empty($entry->status) ? $entry->status : 'unread';
				$status_label = ucfirst($status_class);

				echo'<tr data-entry-id="' . esc_attr($entry->id) . '" class="entry-row-' . esc_attr($status_class) . '">
						<td><input type="checkbox" class="entry-cb" value="' . esc_attr($entry->id) . '"></td>
						<td style="color: #64748b; font-weight: 500;">#' . esc_html($entry->id) . '</td>
						<td><span class="formlayer-badge status-' . esc_attr($status_class) . '">' . esc_html($status_label) . '</span></td>
						<td style="font-weight: 600;">' . esc_html($form_title) . '</td>
						<td style="color: #64748b; font-size: 13px;">' . esc_html(gmdate('M j, Y h:i A', strtotime($entry->created_at))) . '</td>
						<td style="text-align: right; padding-right: 32px;">
							<div class="formlayer-entry-row-actions">
								<button class="formlayer-toggle-status" data-entry-id="' . esc_attr($entry->id) . '" data-status="' . ($status_class === 'read' ? 'unread' : 'read') . '" title="Mark as ' . ($status_class === 'read' ? 'Unread' : 'Read') . '" style="background: none; border: none; color: ' . ($status_class === 'read' ? '#94a3b8' : 'var(--fl-primary)') . '; cursor: pointer; transition: color 0.2s;">
									<span class="dashicons dashicons-' . ($status_class === 'read' ? 'marker' : 'email-alt') . '"></span>
								</button>
								<button class="formlayer-view-entry" data-entry-id="' . esc_attr($entry->id) . '" title="View Details" style="background: none; border: none; color: #94a3b8; cursor: pointer; transition: color 0.2s;">
									<span class="dashicons dashicons-visibility"></span>
								</button>
								<button class="formlayer-delete-entry" data-entry-id="' . esc_attr($entry->id) . '" title="Delete" style="background: none; border: none; color: #94a3b8; cursor: pointer; transition: color 0.2s;">
									<span class="dashicons dashicons-trash"></span>
								</button>
							</div>
						</td>
					</tr>';
			}
		}
		echo '</tbody>
					</table>
				</div>
				<div class="formlayer-table-footer formlayer-table-footer-wrap">
					<div class="formlayer-pagination-info" style="color: #64748b; font-size: 13px;">
						Showing <span id="formlayer-entries-count-current">' . count($recent_entries) . '</span> of <span id="formlayer-entries-total">' . intval($total_entries) . '</span> entries
					</div>
					<div class="formlayer-pagination-controls" data-total-pages="'.esc_html($total_pages).'" style="display: flex; gap: 8px;">
						<button id="formlayer-pagination-prev" class="formlayer-btn formlayer-btn-outline" style="padding: 0 12px; height: 32px;" disabled><span class="dashicons dashicons-arrow-left-alt2"></span> Prev</button>
						<button id="formlayer-pagination-next" class="formlayer-btn formlayer-btn-outline" style="padding: 0 12px; height: 32px;" ' . ($total_pages <= 1 ? 'disabled' : '') . '>Next <span class="dashicons dashicons-arrow-right-alt2"></span></button>
					</div>
				</div>
			</div>
		</div>';
	}
}