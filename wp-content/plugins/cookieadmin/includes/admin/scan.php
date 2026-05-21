<?php

namespace CookieAdmin\Admin;

if(!defined('COOKIEADMIN_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class Scan{
	
	static function show_cookies(){
		global $cookieadmin_lang, $cookieadmin_error, $cookieadmin_msg, $wpdb;
		
		\CookieAdmin\Admin::header_theme(__('Manage Cookies', 'cookieadmin'));
		
		$cookieadmin_requires_pro = \CookieAdmin\Admin::is_feature_available(1);
		
		$table_name = esc_sql($wpdb->prefix . 'cookieadmin_cookies');
		
		$categorized = [];
		$categorized_cookies = [];
		

		$scanned_cookies = $wpdb->get_results("SELECT * FROM {$table_name}");
		
		foreach($scanned_cookies as $row => $data){
			
			$expires = 0;
			
			if(!empty($data->expires) && !empty($data->scan_timestamp)){
				$expires = strtotime($data->expires);
				$timestamp = $data->scan_timestamp;
				
				if(!empty($expires) && ($expires > 0) && !empty($timestamp)){
					$expires = round(($expires - $timestamp) / 86400);
				}else{
					$expires = 0;
				}
			}

			if($expires < 1){
				$exp = __('Session', 'cookieadmin');
			}else{
				$exp = $expires .  ' '.__('Day(s)', 'cookieadmin');
			}
			
			if(empty($data->category)){
				$data->category = 'Unknown';
			}
			
			if(!isset($categorized[$data->category])){
				$categorized[$data->category] = '';
			}
			
			if(empty($data->description)){
				$data->description = 'Not Available';
			}
			
			$categorized[$data->category] .= '<tr><td>'.esc_html($data->cookie_name).'</td><td>'.esc_html($data->description).'</td><td>'.esc_html($exp).'</td><td> <span class="dashicons dashicons-edit cookieadmin_edit_icon" id="edit_'.esc_attr($data->id).'"></span> <span class="dashicons dashicons-trash cookieadmin_delete_icon" id="delete_'.esc_attr($data->id).'"></span> </td></tr>';

			$categorized_cookies[$data->id]['id'] = $data->id;
			$categorized_cookies[$data->id]['cookie_name'] = $data->cookie_name;
			$categorized_cookies[$data->id]['description'] = $data->description;
			$categorized_cookies[$data->id]['category'] = $data->category;
			$categorized_cookies[$data->id]['expires'] = $expires;

		}
		
		wp_register_script('cookieadmin_categorized_cookies', '', array('jquery'), COOKIEADMIN_VERSION, true);
		wp_enqueue_script('cookieadmin_categorized_cookies');
		wp_localize_script('cookieadmin_categorized_cookies', 'categorized_cookies', $categorized_cookies);
		
		$no_cookies = '<tr class="cookieadmin-empty-row"><td colspan=4>'.esc_html__('No Cookies Found!', 'cookieadmin').'</td></tr>';
		$no_cookies_hidden = '<tr class="cookieadmin-empty-row" hidden><td colspan=4>'.esc_html__('No Cookies Found!', 'cookieadmin').'</td></tr>';
		
		echo '
		<div class="cookieadmin_consent-wrap">
			<form action="" method="post">';
			do_action('cookieadmin_before_scan_results');
			
			echo '<div class="cookieadmin-card">
				<div class="cookieadmin-card-header">
					<div>
					<span class="cookieadmin-card-title"><span class="dashicons dashicons-list-view"></span> '.esc_html__('Scanned Cookies', 'cookieadmin').'</span>
					<p class="cookieadmin-desc">'.esc_html__('Scanned cookies will be automatically categorised and displayed here. You can add, edit and delete cookies as per your needs.', 'cookieadmin').'</p>
					</div>
					<div class="cookieadmin-toolbar-actions">
							<input type="button" class="cookieadmin-btn cookieadmin-btn-secondary cookieadmin-add-cookie" value="'.esc_html__('Add Cookie', 'cookieadmin').'" cookieadmin-pro-only="1"></input>
							'.wp_kses_post($cookieadmin_requires_pro).
							( !empty($cookieadmin_requires_pro) ? '
							<input type="button" class="cookieadmin-btn cookieadmin-btn-primary cookieadmin-scan" value="'.esc_html__('Scan', 'cookieadmin').'"></input> ' : '').'
							<input type="button" class="cookieadmin-btn cookieadmin-btn-primary cookieadmin-scan" value="'.esc_html__('Full Scan', 'cookieadmin').'" cookieadmin-pro-only="1">
							'.wp_kses_post($cookieadmin_requires_pro).'
					</div>
				</div>
				<div class="cookieadmin-card-body">
					<div class="cookieadmin-manager-result">
						<table class="cookieadmin-table cookieadmin-cookie-categorized">
							<thead>
								<tr>
									<th width="30%">'.esc_html__('Name', 'cookieadmin').'</th>
									<th width="50%">'.esc_html__('Description', 'cookieadmin').'</th>
									<th width="10%">'.esc_html__('Expiry', 'cookieadmin').'</th>
									<th width="10%">'.esc_html__('Action', 'cookieadmin').'</th>
								</tr>
							</thead>
							<tbody id="necessary_tbody">
								<tr><td colspan=4>'.esc_html__('Necessary Cookies', 'cookieadmin').'</td></tr>
								'.( !empty($categorized['Necessary']) ? $no_cookies_hidden . wp_kses_post($categorized['Necessary']) : $no_cookies ).'
							</tbody>
							<tbody id="functional_tbody">
								<tr><td colspan=4>'.esc_html__('Functional Cookies', 'cookieadmin').'</td></tr>
								'.( !empty($categorized['Functional']) ? $no_cookies_hidden . wp_kses_post($categorized['Functional']) : $no_cookies ).'
							</tbody>
							<tbody id="analytics_tbody">
								<tr><td colspan=4>'.esc_html__('Analytical Cookies', 'cookieadmin').'</td></tr>
								'.( !empty($categorized['Analytics']) ? $no_cookies_hidden . wp_kses_post($categorized['Analytics']) :$no_cookies ).'
							</tbody>
							<tbody id="marketing_tbody">
								<tr><td colspan=4>'.esc_html__('Marketing Cookies', 'cookieadmin').'</td></tr>
								'.( !empty($categorized['Marketing']) ? $no_cookies_hidden . wp_kses_post($categorized['Marketing']) : $no_cookies ).'
							</tbody>
							<tbody id="unknown_tbody">
								<tr><td colspan=4>'.esc_html__('Unknown Cookies', 'cookieadmin').'</td></tr>
								'.( !empty($categorized['Unknown']) ? $no_cookies_hidden . wp_kses_post($categorized['Unknown']) : $no_cookies ).'
							</tbody>
						</table>
					</div>
				</div>
			</div>';
			
			wp_nonce_field('cookieadmin_admin_nonce', 'cookieadmin_security');
			
			echo '
		</div>
		</form>
		<br/>';
		
		\CookieAdmin\Admin::footer_theme();
		
		echo '
		<!-- Modal Overlay -->
		<div class="cookieadmin_modal-overlay" id="edit-cookie-modal" hidden>
			<div class="cookieadmin_modal-container">
				<div class="cookieadmin_modal-header">
					<h2>'.esc_html__('Edit Cookie', 'cookieadmin').'</h2>
					<button class="cookieadmin_dialog_modal_close_btn">&times;</button>
				</div>

				<div class="cookieadmin_modal-body">
					<div class="cookieadmin_form-group">
						<label for="cookieadmin-dialog-cookie-category">'.esc_html__('Category', 'cookieadmin').'</label>
						<select id="cookieadmin-dialog-cookie-category">
							<option value="" selected>'.esc_html__('Select a category', 'cookieadmin').'</option>
							<option value="Necessary">'.esc_html__('Necessary', 'cookieadmin').'</option>
							<option value="Functional">'.esc_html__('Functional', 'cookieadmin').'</option>
							<option value="Analytics">'.esc_html__('Analytical', 'cookieadmin').'</option>
							<option value="Marketing">'.esc_html__('Marketing', 'cookieadmin').'</option>
							<option value="Unknown">'.esc_html__('Unknown', 'cookieadmin').'</option>
						</select>
					</div>
					
					<div class="cookieadmin_form-group">
						<label for="cookie_id">'.esc_html__('Cookie Name/ID', 'cookieadmin').'</label>
						<input type="text" id="cookieadmin-dialog-cookie-name" Placeholder="'.esc_html__('Enter Cookie Name or id', 'cookieadmin').'">
					</div>

					<div class="cookieadmin_form-group">
						<label for="description">'.esc_html__('Description', 'cookieadmin').'</label>
						<textarea id="cookieadmin-dialog-cookie-desc" Placeholder="'.esc_html__('Enter Cookie description here', 'cookieadmin').'"></textarea>
					</div>

					<div class="cookieadmin_form-group">
						<label for="duration">'.esc_html__('Duration', 'cookieadmin').'</label>
						<input type="number" min=0 id="cookieadmin-dialog-cookie-duration" Placeholder="'.esc_html__('Set 0 for Session or expiry in days', 'cookieadmin').'">
					</div>
				</div>
				<div class="cookieadmin_modal-footer">					
					<span id="cookieadmin-message"></span>
					<button class="cookieadmin-btn cookieadmin-btn-primary" id="cookieadmin_dialog_save_btn" form="edit-cookie-form">'.esc_html__('Save', 'cookieadmin').'</button>					
				</div>
			</div>
		</div>';
	}
	
	
	
	static function scan_cookies_ajax(){
		global $cookieadmin_error;
		
		$urls = [];
		if(!empty($_REQUEST['urls'])){
			$urls = map_deep(wp_unslash($_REQUEST['urls']), 'sanitize_url');
		}
		
		if(cookieadmin_is_pro()){
			$scanner_info = get_option('cookieadmin_pro_scanner', []);
			
			if(!empty($scanner_info['last_scan']) && (time() < $scanner_info['last_scan'] + 3600)){
				wp_send_json([
					'success' => false,
					'message' => __('Cookie Scan can be triggered once an hour', 'cookieadmin')
				]);
			}
		}
		
		self::scan_cookies($urls);
		
		if(!empty($cookieadmin_error)){
			wp_send_json([
				'success' => false,
				'message' => $cookieadmin_error]
			);
		}		
		
		wp_send_json(['success' => true, 'data' => null]);
	}
	
	// Orchestrator function for scanning cookies
	static function scan_cookies($urls = []){
		global $cookieadmin_error;
		
		if(cookieadmin_is_pro()){
			
			if(!method_exists('\CookieAdminPro\Admin', 'cookieadmin_get_site_urls')){
				$urls = [home_url()];
			} else {
				$urls = \CookieAdminPro\Admin::cookieadmin_get_site_urls($urls);
			}

			$cookieData = apply_filters('cookieadmin_pro_scan_cookies', $urls);
			
			//Server side scann - skipped for now - need to discuss.
			// $cookieData2 = \CookieAdmin\Scanner::start_scan($urls);
			// $cookieData = array_replace_recursive($cookieData2, $cookieData1);
			
			if(!empty($cookieadmin_error)){
				update_option('cookieadmin_scan', [
						'status' => 3,
						'success' => false,
						'message' => $cookieadmin_error,
						'update' => time()
					]);
				return;
			}
			
			if(!empty($cookieData)){
				
				if(function_exists('cookieadmin_pro_get_remaining_urls')){
					// Check Remaining urls
					$remaining_urls = cookieadmin_pro_get_remaining_urls($urls);
					
					if(!empty($remaining_urls)){
						//send next batch for scan
						wp_schedule_single_event(time() + 5, 'cookieadmin_run_auto_scan_batch', [$remaining_urls]);
					}
				}
				
				$res = self::save_raw_scan_results($cookieData);
				if(function_exists('cookieadmin_pro_update_scan_count')){
					cookieadmin_pro_update_scan_count($res);
				}
				
				self::cookieadmin_auto_configure_cookies();
				return;
			}
			
		}else{
			$cookieData = \CookieAdmin\Scanner::start_scan();
			if(!empty($cookieData)){
				self::save_raw_scan_results($cookieData);
				return self::cookieadmin_auto_configure_cookies();
			}
		}
		// cookieadmin_r_print($cookieData);
		
		if(defined('DOING_CRON') && get_transient('cookieadmin_auto_scan_in_progress')){
			delete_transient('cookieadmin_auto_scan_in_progress');
		}
		
		update_option('cookieadmin_scan', [
			'status' => 3,
			'success' => false,
			'message' => __('No Cookies Found', 'cookieadmin'),
			'update' => time()
		]);
		
		return false;
	}
	
	static function save_raw_scan_results(array $found_cookies){

		global $wpdb;
		
		$table_name = esc_sql($wpdb->prefix . 'cookieadmin_cookies');

		if (empty($found_cookies)) {
			return ['inserted' => 0, 'updated' => 0];
		}

		// Step 1: Fetch all existing cookie names from our database in one efficient query.
		$existing_cookies_in_db = $wpdb->get_col("SELECT cookie_name FROM {$table_name}");
		// Use array_flip for very fast 'isset' lookups instead of slow 'in_array' in a loop.
		$existing_cookies_lookup = !empty($existing_cookies_in_db) ? array_flip($existing_cookies_in_db) : [];

		$results = ['inserted' => 0, 'updated' => 0];

		// Step 2: Loop through each cookie found by the scanner.
		foreach ($found_cookies as $cookie_name => $cookie_data) {

			// Step 3: Check if the cookie exists in our DB.
			if (isset($existing_cookies_lookup[$cookie_name])) {
				$wpdb->update(
					$table_name,
					[
						'domain' => sanitize_text_field($cookie_data['domain']),
						'path' => sanitize_text_field($cookie_data['path']),
						'expires' => !empty($cookie_data['session']) ? 0 : (!empty($cookie_data['expires']) ? $cookie_data['expires'] : null),
						'samesite' => !empty($cookie_data['samesite']) ? sanitize_text_field($cookie_data['samesite']) : null,
						'secure' => (int)($cookie_data['secure'] ?? 0),
						'httponly' => (int)($cookie_data['httponly'] ?? 0),
						'raw_name' => sanitize_text_field($cookie_name),
						'scan_timestamp' => time(),
					], // Data to update
					[ 'cookie_name' => $cookie_name ], // WHERE clause
					['%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d'], // Format for the data
					[ '%s' ]  // Format for the WHERE clause
				);
				$results['updated']++;

			} else {
				
				if($cookie_name == 'cookieadmin_consent' && empty($cookie_data['expires'])){
					$view = get_option('cookieadmin_law', 'cookieadmin_gdpr');		
					$policy = cookieadmin_load_policy();
					
					if(!empty($policy) && !empty($policy[$view])){
						$cookie_exp_days = (int) (!empty($policy[$view]['cookieadmin_days']) ? $policy[$view]['cookieadmin_days'] : 365);
						$utc_time = gmdate('Y-m-d H:i:s', strtotime('+'.$cookie_exp_days.' days'));
					} else {
						$utc_time = gmdate('Y-m-d H:i:s', strtotime('+365 days'));
					}
					
					$cookie_data['expires'] = $utc_time; 
				}

				// ------ INSERT a NEW cookie ------
				$data = [
					'cookie_name' => sanitize_text_field($cookie_name),
					'domain' => sanitize_text_field($cookie_data['domain']),
					'path' => sanitize_text_field($cookie_data['path']),
					'expires' => !empty($cookie_data['session']) ? 0 : (!empty($cookie_data['expires']) ? $cookie_data['expires'] : null),
					'samesite' => !empty($cookie_data['samesite']) ? sanitize_text_field($cookie_data['samesite']) : null,
					'secure' => (int)($cookie_data['secure'] ?? 0),
					'httponly' => (int)($cookie_data['httponly'] ?? 0),
					'raw_name' => sanitize_text_field($cookie_name),
					'scan_timestamp' => time(),
				];

				$formats = ['%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%d'];

				if ($wpdb->insert($table_name, $data, $formats)) {
					$results['inserted']++;
				} else {
					//error_log("CookieAdmin: Error inserting cookie data: " . $wpdb->last_error);
				}
			}
		}
		return $results;
	}
	
	static function cookieadmin_auto_configure_cookies(){
		global $wpdb, $cookieadmin_error;
		
		$table_name = $wpdb->prefix . 'cookieadmin_cookies';
		$categorized_cookies = [];
		$uncategorized_cookies = [];
		
		$all_cookies = $wpdb->get_results("SELECT id, cookie_name, category FROM {$table_name}");
		
		foreach($all_cookies as $cookie){
			
			if(!empty($cookie->category)){
				$categorized_cookies[$cookie->id] = $cookie->cookie_name;
			}else{
				$uncategorized_cookies[] = $cookie->cookie_name;
			}
		}
		
		if(!empty($uncategorized_cookies)){
			
			$uncategorized_cookies = array_flip($uncategorized_cookies);
			$categorized_cookies = array_flip($categorized_cookies);
			
			
			$categorizd_cookies = \CookieAdmin\CookieCategorizer::categorize_cookies($uncategorized_cookies, $categorized_cookies);
			
			$remove_cookies = !empty($categorizd_cookies['remove_cookies']) ? $categorizd_cookies['remove_cookies'] : [];
			
			unset($categorizd_cookies['remove_cookies']);
			if(!empty($remove_cookies)){
				$placeholders = implode(',', array_fill(0, count($remove_cookies), '%s'));
				$sql = $wpdb->prepare("DELETE FROM {$table_name} WHERE id IN ({$placeholders})", ...$remove_cookies);
				$wpdb->query($sql);
			}			
			
			foreach($categorizd_cookies as $cookie_data){
			
				$count = $wpdb->update(
					$table_name,
					[ 'cookie_name' => $cookie_data['cookie_name'], 'category' =>  $cookie_data['category'], 'description' =>  $cookie_data['description'], 'edited' =>  1, 'patterns' =>  $cookie_data['patterns'] ], // Data to update
					[ 'raw_name' => $cookie_data['raw_name'] ], // WHERE 
					[ '%s', '%s', '%s', '%d', '%s' ], // Format for the data
					[ '%s' ]  // Format for the WHERE clause
				);
				
			}
			
			update_option('cookieadmin_scan', [
					'status' => 3,
					'update' => time(),
					'success' => true,
					'count' => $count
				]);
			
			$categorized_cookies = $wpdb->get_results("SELECT id, cookie_name, category, expires, scan_timestamp, description FROM {$table_name}");
			
			delete_option('cookieadmin_first_scan');
			
			return $categorized_cookies;
		}
		
        $cookieadmin_error = $cookieadmin_error . ' ' . __('No new cookies Found!', 'cookieadmin');
		
		return false;
	}
	
	
	static function edit_cookies(){
		global $wpdb;
		
		$table_name = esc_sql($wpdb->prefix . 'cookieadmin_cookies');
		$data = null;
		
		if(empty($_REQUEST['cookie_info'])){
			wp_send_json(['success' => false,
				'data'    => null,
				'message'   => __('Error : Cookie details missing', 'cookieadmin')]
			);
		}
			
		$cookie_info = map_deep(wp_unslash($_REQUEST['cookie_info']), 'sanitize_text_field');
		
		$scan_timestamp = $wpdb->get_col($wpdb->prepare("SELECT scan_timestamp FROM {$table_name} WHERE id = %d", $cookie_info['id']));
		
		if(empty($scan_timestamp)){
			wp_send_json(['success' => false,
				'data'    => null,
				'message'   => __('Error : Invalid cookie record', 'cookieadmin')]
			);
		}
		
		$calculated_expiry_seconds = ($cookie_info['duration'] * 86400) + $scan_timestamp[0];
		$calculated_expiry = date('Y-m-d H:i:s', $calculated_expiry_seconds);
		
		$resp = $wpdb->update(
			$table_name,
			[ 'cookie_name' => $cookie_info['name'], 'description' =>  $cookie_info['description'], 'expires' =>  $calculated_expiry, 'category' =>  $cookie_info['type'], 'edited' => 1], // Data to update
			[ 'id' => $cookie_info['id'] ], // WHERE 
			[ '%s', '%s', '%s', '%s', '%d' ], // Format for the data
			[ '%d' ]  // Format for the WHERE clause
		);
		
		if ($wpdb->last_error || $resp === false) {
			//error_log('DB Error: ' . $wpdb->last_error); // Log it
			wp_send_json(['success' => false,
				'data'    => null,
				'message'   => __('Cookie updation Failed, Error: ', 'cookieadmin') . esc_html($wpdb->last_error)]);
		}
		
		wp_send_json(['success' => true,
				'data'    => $data,
				'message'   => __('Cookie updation successful', 'cookieadmin')]);
		
	}
	
	static function delete_cookies(){
		global $wpdb;
		
		$table_name = esc_sql($wpdb->prefix . 'cookieadmin_cookies');
		
		if(empty($_REQUEST['cookie_raw_id'])){
			wp_send_json(['success' => false,
				'data'    => null,
				'message'   => __('Error : Cookie Id missing', 'cookieadmin')]);
		}
			
		$cookie_id = (int) sanitize_text_field(wp_unslash($_REQUEST['cookie_raw_id']));
		
		$resp = $wpdb->delete( $table_name, ['id' => $cookie_id], [ '%s' ] );
		
		if ($wpdb->last_error || $resp === false) {
			//error_log('DB Error: ' . $wpdb->last_error); //Log it
			wp_send_json(['success' => false,
				'data'    => null,
				'message'   => __('Cookie deletion Failed, Error: ', 'cookieadmin') . esc_html($wpdb->last_error)]);
		}
		
		wp_send_json(['success' => true,
				'message'   => __('Cookie deletion successful', 'cookieadmin')]);
	}
	
}
