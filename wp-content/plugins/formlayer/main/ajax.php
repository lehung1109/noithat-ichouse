<?php
namespace FormLayer;

if(!defined('ABSPATH')){
	exit;
}

class Ajax{

	static function hooks(){
		add_action('wp_ajax_formlayer_submit_form', '\FormLayer\Ajax::handle_form_submit');
		add_action('wp_ajax_nopriv_formlayer_submit_form', '\FormLayer\Ajax::handle_form_submit');
		add_action('wp_ajax_formlayer_delete_form', '\FormLayer\Ajax::delete_form');
		add_action('wp_ajax_formlayer_save_settings', '\FormLayer\Ajax::save_settings');
		add_action('wp_ajax_formlayer_save_form', '\FormLayer\Ajax::save_form');
		add_action('wp_ajax_formlayer_load_form', '\FormLayer\Ajax::load_form');
	}

	static function delete_form(){
		check_ajax_referer('formlayer_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(['message' => esc_html__('Insufficient permissions', 'formlayer')]);
		}
		
		$display_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
		if(empty($display_id)){
			wp_send_json_error(['message' => 'Invalid form ID']);
		}
		
		$form_id = \FormLayer\Util::get_post_id_by_display_id($display_id);
		if(empty($form_id)){
			wp_send_json_error(['message' => 'Form mapped ID not found']);
		}

		$form = get_post($form_id);
		if(!$form || 'formlayer_form' !== $form->post_type){
			wp_send_json_error(['message' => 'Form not found']);
		}
		
		wp_delete_post($form_id, true);
		
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$remaining = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'formlayer_form' AND post_status NOT IN ('trash', 'auto-draft')");
		if (intval($remaining) === 0) {
			update_option('formlayer_id_counter', 0);
		}

		wp_send_json_success(['message' => 'Form deleted']);
	}

	

	static function save_settings(){
		check_ajax_referer('formlayer_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(['message' => esc_html__('Insufficient permissions', 'formlayer')]);
		}

		// $_POST['settings'] is sent as an array from JS — do NOT pass through
		// sanitize_text_field() which converts arrays to an empty string.
		// Each key/value is sanitized individually in the loop below.
		$raw_settings = isset($_POST['settings']) && is_array($_POST['settings']) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			? wp_unslash($_POST['settings']) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			: [];
		$to_save = [];

		foreach( (array) $raw_settings as $key => $value ){
			$to_save[ sanitize_key( $key ) ] = sanitize_text_field( (string) $value );
		}

		update_option('formlayer_settings', $to_save);

		wp_send_json_success(['message' => esc_html__('Settings saved successfully!', 'formlayer')]);
	}

	static function handle_form_submit(){
	
		check_ajax_referer('formlayer-frontend', 'nonce');

		$display_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
		$form_id = \FormLayer\Util::get_post_id_by_display_id($display_id);
		
		if(empty($form_id)){
			// Try as raw post ID fallback
			$check_post = get_post($display_id);
			if($check_post && 'formlayer_form' === $check_post->post_type){
				$form_id = $display_id;
			}
		}

		if(empty($form_id)){
			wp_send_json_error(['message' => 'Form not found.']);
		}

		$form = get_post($form_id);

		// Check if form has captcha field & verify
		$form_data = json_decode($form->post_content, true);
		if (!is_array($form_data)) {
			$form_data = ['fields' => []];
		}
		
		$has_captcha = false;
		$captcha_provider = 'none';
		$fields = isset($form_data['fields']) ? (array)$form_data['fields'] : [];
		
		foreach($fields as $f){
			if(isset($f['type']) && $f['type'] === 'captcha'){
				$has_captcha = true;
				$captcha_provider = isset($f['captcha_provider']) ? $f['captcha_provider'] : 'hcaptcha';
				break;
			}
		}
		
		if($has_captcha && $captcha_provider !== 'none'){
			$global_settings = get_option('formlayer_settings', []);
			
			if($captcha_provider !== 'none'){
				$captcha_verified = false;
				$captcha_token = '';
				$secret_key = '';
				$verify_url = '';

				if($captcha_provider === 'hcaptcha'){
					$captcha_token = isset($_POST['h-captcha-response']) ? sanitize_text_field(wp_unslash($_POST['h-captcha-response'])) : '';
					$secret_key = isset($global_settings['captcha_h_secret_key']) ? $global_settings['captcha_h_secret_key'] : '';
					$verify_url = 'https://hcaptcha.com/siteverify';
				} else {
					// Pro captcha providers - delegate to Pro plugin via action
					$sanitized_post = array_map('sanitize_text_field', wp_unslash($_POST));
					$captcha_verified = apply_filters('formlayer_verify_pro_captcha', false, $captcha_provider, $sanitized_post, $global_settings);
				}

				if(!empty($captcha_token) && !empty($secret_key) && $captcha_provider === 'hcaptcha'){
					$response = wp_remote_post($verify_url, [
						'body' => [
							'secret' => $secret_key,
							'response' => $captcha_token
						]
					]);
					
					if(!is_wp_error($response)) {
						$body = json_decode(wp_remote_retrieve_body($response), true);
						if($body && !empty($body['success'])){
							$captcha_verified = true;
						}
					}
				}

				if(!$captcha_verified){
					wp_send_json_error(['message' => esc_html__('Captcha verification failed. Please try again.', 'formlayer')]);
				}
			}
		}

		$submitted_data = [];
		$submitted_data['__source_url'] = isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : '';
		$excluded_keys = ['action', 'nonce', 'form_id', 'h-captcha-response', 'cf-turnstile-response', 'g-recaptcha-response'];
		
		foreach($_POST as $key => $value){
			if(!in_array($key, $excluded_keys)){
				if(is_array($value)){
					$submitted_data[$key] = array_map('sanitize_text_field', $value);
				} else {
					$submitted_data[$key] = sanitize_text_field(wp_unslash($value));
				}
			}
		}

		// --- Field Validation ---
		$global_settings = get_option('formlayer_settings', []);
		$fields = isset($form_data['fields']) ? $form_data['fields'] : [];
		
		foreach($fields as $field){
			$input_name = \FormLayer\Util::get_field_name($field);
			$value = isset($submitted_data[$input_name]) ? $submitted_data[$input_name] : '';
			
			// Required check
			$is_empty = false;
			if (is_array($value)) {
				$is_empty = true;
				foreach ($value as $v) {
					if (!empty($v)) {
						$is_empty = false;
						break;
					}
				}
			} else {
				$is_empty = empty($value);
			}

			if(!empty($field['required']) && $is_empty){
				wp_send_json_error(['message' => !empty($global_settings['msg_required']) ? $global_settings['msg_required'] : __('This field is required.', 'formlayer')]);
			}
			
			if(!$is_empty){
				// Email check
				if($field['type'] === 'email' && !is_array($value)){
					if(!is_email($value)){
						wp_send_json_error(['message' => !empty($global_settings['msg_email']) ? $global_settings['msg_email'] : __('Please enter a valid email address.', 'formlayer')]);
					}
				}
				// URL check
				if($field['type'] === 'url' && !is_array($value) && !filter_var($value, FILTER_VALIDATE_URL)){
					wp_send_json_error(['message' => !empty($global_settings['msg_url']) ? $global_settings['msg_url'] : __('Please enter a valid URL.', 'formlayer')]);
				}
				// Number check
				if($field['type'] === 'number' && !is_array($value)){
					if(!is_numeric($value)){
						wp_send_json_error(['message' => !empty($global_settings['msg_number']) ? $global_settings['msg_number'] : __('Please enter a valid number.', 'formlayer')]);
					}
					
					// Min/Max check
					if(isset($field['min']) && $field['min'] !== '' && $value < $field['min']){
						/* translators: %s: minimum value */
						wp_send_json_error(['message' => sprintf(__('Minimum value is %s.', 'formlayer'), $field['min'])]);
					}
					if(isset($field['max']) && $field['max'] !== '' && $value > $field['max']){
						/* translators: %s: maximum value */
						wp_send_json_error(['message' => sprintf(__('Maximum value is %s.', 'formlayer'), $field['max'])]);
					}
					
					// Digit limit
					if(!empty($field['digit_limit']) && strlen((string)$value) > intval($field['digit_limit'])){
						/* translators: %d: maximum digit limit */
						wp_send_json_error(['message' => sprintf(__('Maximum digit limit is %d.', 'formlayer'), $field['digit_limit'])]);
					}
				}
				// Terms check
				if(($field['type'] === 'terms' || $field['type'] === 'gdpr') && $is_empty){
					wp_send_json_error(['message' => !empty($global_settings['msg_terms']) ? $global_settings['msg_terms'] : __('You must accept the terms.', 'formlayer')]);
				}
			}

			if($field['type'] === 'password' && !$is_empty){
				$submitted_data[$input_name] = wp_hash_password($submitted_data[$input_name]);
			}
		}

		$form_data = json_decode($form->post_content, true);
		$settings = isset($form_data['settings']) ? $form_data['settings'] : [];
		
		// Email Notifications
		$mail_sent = false;
		if (!empty($settings['notifications']['enabled'])) {
			$to = !empty($settings['notifications']['to_email']) && strpos($settings['notifications']['to_email'], '{admin_email}') === false ? $settings['notifications']['to_email'] : get_option('admin_email');
			$subject = !empty($settings['notifications']['subject']) ? $settings['notifications']['subject'] : 'New Form Submission';
			$message_body = !empty($settings['notifications']['message']) ? $settings['notifications']['message'] : "You have a new submission:\n\n{all_fields}";
			
			$field_labels = \FormLayer\Util::get_form_field_labels($form_id);
			$field_types = \FormLayer\Util::get_form_field_types($form_id);
			$fields_text = "";
			foreach($submitted_data as $key => $val) {
				if ($key === '__source_url') continue;

				$label = isset($field_labels[$key]) ? $field_labels[$key] : ucfirst(str_replace(['field_', '_'], ['', ' '], $key));
				$type = isset($field_types[$key]) ? $field_types[$key] : '';
				
				if(is_array($val)){
					$val = implode(', ', $val);
				}

				// Mask password in email
				if($type === 'password'){
					$val = '******';
				}

				$fields_text .= $label . ": " . $val . "\n";
			}

			if (!empty($submitted_data['__source_url'])) {
				$fields_text .= "\nSource URL: " . $submitted_data['__source_url'] . "\n";
			}
			
			// Replace Merge Tags
			$merge_tags = [
				'{all_fields}' => $fields_text,
				'{admin_email}' => get_option('admin_email'),
				'{form_title}' => $form->post_title,
				'{site_title}' => get_bloginfo('name'),
				'{site_url}' => get_site_url()
			];
			
			// Also support individual fields
			foreach($submitted_data as $key => $val){
				if(is_array($val)){
					$val = implode(', ', $val);
				}
				$merge_tags['{'.$key.'}'] = $val;
			}

			$to = strtr($to, $merge_tags);
			$subject = strtr($subject, $merge_tags);
			$message_body = strtr($message_body, $merge_tags);

			$headers = [];
			$from_name = !empty($settings['notifications']['from_name']) ? strtr($settings['notifications']['from_name'], $merge_tags) : get_bloginfo('name');
			$from_email = !empty($settings['notifications']['from_email']) ? strtr($settings['notifications']['from_email'], $merge_tags) : get_option('admin_email');
			
			if($from_email === '{admin_email}'){ 
				$from_email = get_option('admin_email');
			}

			$headers[] = "From: $from_name <$from_email>";
			
			if(!empty($settings['notifications']['reply_to'])){
				$headers[] = "Reply-To: " . strtr($settings['notifications']['reply_to'], $merge_tags);
			}
			
			if(!empty($settings['notifications']['bcc'])){
				$headers[] = "Bcc: " . strtr($settings['notifications']['bcc'], $merge_tags);
			}

			$mail_sent = @wp_mail($to, $subject, $message_body, $headers);
		}

		// Trigger integrations via action hook
		$entry_id = apply_filters('formlayer_before_submission_response', 0, $form_id, $submitted_data);
		do_action('formlayer_after_submission', $form_id, $submitted_data, $entry_id);

		// Ensure clean buffer before sending JSON
		if (ob_get_length()) {
			ob_clean();
		}

		wp_send_json_success([
			'message'=> !empty($settings['confirmations']['message']) ? $settings['confirmations']['message'] : 'Thank you! Your form has been submitted successfully.',
			'settings'=> $settings,
			'mail_sent' => $mail_sent,
			'entry_id' => $entry_id,
		]);
	}

	static function save_form(){
		check_ajax_referer('formlayer_admin_nonce', 'nonce');

		if(!current_user_can('manage_options')){
			wp_send_json_error(['message' => esc_html__('Insufficient permissions', 'formlayer')]);
		}

		$display_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
		$form_id = !empty($display_id) ? \FormLayer\Util::get_post_id_by_display_id($display_id) : 0;

		$title = isset($_POST['title']) ? sanitize_text_field(wp_unslash($_POST['title'])) : 'Untitled Form';
		
		if (isset($_POST['fields']) && isset($_POST['settings'])) {
			$form_data = [
				'title' => $title,
				'fields' => json_decode(sanitize_text_field(wp_unslash($_POST['fields'])), true),
				'settings' => json_decode(sanitize_text_field(wp_unslash($_POST['settings'])), true)
			];
			$form_json = json_encode($form_data);
		} else {
			$form_json = isset($_POST['form_data']) ? sanitize_text_field(wp_unslash($_POST['form_data'])) : '';
		}

		if (!$form_json) {
			wp_send_json_error(['message' => 'No form data provided.']);
		}

		if(!empty($form_id)){
			// Update existing form
			wp_update_post([
				'ID' => $form_id,
				'post_title' => $title,
				'post_content' => $form_json,
			]);
		} else {
			// Create new form
			$form_id = wp_insert_post([
				'post_title' => $title,
				'post_content' => $form_json,
				'post_type' => 'formlayer_form',
				'post_status' => 'publish',
			]);

			if ($form_id) {
				$counter = get_option('formlayer_id_counter', 0);
				$counter++;
				update_option('formlayer_id_counter', $counter);
				update_post_meta($form_id, '_formlayer_display_id', $counter);
			}
		}

		$display_id = get_post_meta($form_id, '_formlayer_display_id', true);

		ob_start();
		\FormLayer\Settings\UI::render_form_row(get_post($form_id));
		$row_html = ob_get_clean();

		wp_send_json_success([
			'form_id' => $display_id ? $display_id : $form_id,
			'display_id' => $display_id ? $display_id : $form_id,
			'row_html' => $row_html
		]);
	}

	static function load_form(){
		check_ajax_referer('formlayer_admin_nonce', 'nonce');

		$display_id = isset($_GET['form_id']) ? intval($_GET['form_id']) : 0;
		if (!$display_id) {
			wp_send_json_error(['message' => 'Invalid form ID.']);
		}

		$form_id = \FormLayer\Util::get_post_id_by_display_id($display_id);
		
		// Fallback for cases where shortcode hasn't kicked counter initially yet
		if(!$form_id) {
		    $form_id = $display_id;
		}

		$form = get_post($form_id);
		if (!$form || $form->post_type !== 'formlayer_form') {
			wp_send_json_error(['message' => 'Form not found.']);
		}

		$form_data = json_decode($form->post_content, true);
		$display_id = get_post_meta($form_id, '_formlayer_display_id', true);
		wp_send_json_success([
			'form_data' => $form_data,
			'display_id' => $display_id ? $display_id : $form_id
		]);
	}

	
}