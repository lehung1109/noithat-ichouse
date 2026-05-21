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

class Integrations{

	static function init(){
		add_action('formlayer_after_submission', '\FormLayerPro\Integrations::trigger_integrations', 10, 3);
	}

	static function trigger_integrations($form_id, $entry_data, $entry_id){
		$form = get_post($form_id);
		$form_data = json_decode($form->post_content, true);
		$form_settings = isset($form_data['settings']) ? $form_data['settings'] : [];
		$form_ints = isset($form_settings['integrations']) ? $form_settings['integrations'] : [];
		
		$global_settings = get_option('formlayer_integration_settings', []);
		$platforms = ['slack', 'mailchimp', 'sheets', 'notion', 'trello', 'discord'];

		// Get actual field labels for better integration output
		$field_labels = \FormLayer\Util::get_form_field_labels($form_id);

		foreach($platforms as $p){
			$form_p = isset($form_ints[$p]) ? $form_ints[$p] : [];
			$global_p = isset($global_settings[$p]) ? $global_settings[$p] : [];

			$global_enabled = !empty($global_p['enabled']);
			$form_enabled = !empty($form_p['enabled']);

			if(!$form_enabled && !$global_enabled) continue;

			// Merge settings: Form settings override global settings
			$settings = isset($global_p['settings']) ? $global_p['settings'] : [];
			foreach($form_p as $key => $val){
				if($key !== 'enabled' && !empty($val)){
					// Map some keys for consistency
					$target_key = $key;
					
					$settings[$target_key] = $val;
				}
			}

			$method = "send_to_" . ($p === 'sheets' ? 'google_sheets' : $p);
			if(method_exists(__CLASS__, $method)){
				self::$method($form_id, $entry_data, $settings, $field_labels);
			}
		}
	}

	static function send_to_discord($form_id, $entry_data, $discord_settings, $field_labels = []){
		$webhook_url = isset($discord_settings['webhook']) ? $discord_settings['webhook'] : '';
		if(empty($webhook_url)) return;

		$content = "**New Form Submission (ID: $form_id)**\n";
		foreach($entry_data as $key => $value){
			if ($key === '__source_url') continue;
			
			$label = isset($field_labels[$key]) ? $field_labels[$key] : ucfirst(str_replace(['field_', '_'], ['', ' '], $key));
			if(is_array($value)) $value = implode(', ', $value);

			$content .= "**" . $label . "**: $value\n";
		}

		if (!empty($entry_data['__source_url'])) {
			$content .= "\n*Source URL: " . $entry_data['__source_url'] . "*";
		}

		wp_remote_post($webhook_url, [
			'body' => json_encode(['content' => $content]),
			'headers' => ['Content-Type' => 'application/json']
		]);
	}

	static function send_to_slack($form_id, $entry_data, $slack_settings, $field_labels = []){
		$webhook_url = isset($slack_settings['webhook']) ? $slack_settings['webhook'] : '';
		if(empty($webhook_url)){ 
			return;
		}

		$message = "New Form Submission (ID: $form_id):\n";
		foreach($entry_data as $key => $value){
			if ($key === '__source_url') continue;

			$label = isset($field_labels[$key]) ? $field_labels[$key] : ucfirst(str_replace(['field_', '_'], ['', ' '], $key));
			if(is_array($value)) $value = implode(', ', $value);

			$message .= "*$label*: $value\n";
		}

		if (!empty($entry_data['__source_url'])) {
			$message .= "\nSource URL: " . $entry_data['__source_url'];
		}

		wp_remote_post($webhook_url, [
			'body' => json_encode(['text' => $message]),
			'headers' => ['Content-Type' => 'application/json']
		]);
	}

	static function send_to_mailchimp($form_id, $entry_data, $mc_settings, $field_labels = []){
		$api_key = isset($mc_settings['api_key']) ? $mc_settings['api_key'] : '';
		$list_id = isset($mc_settings['list_id']) ? $mc_settings['list_id'] : '';
		
		if(empty($api_key) || empty($list_id)){ 
			return;
		}

		// Find email field
		$email = '';
		$form = get_post($form_id);
		$form_data = json_decode($form->post_content, true);
		if (isset($form_data['fields'])) {
			foreach ($form_data['fields'] as $f) {
				if ($f['type'] === 'email') {
					$key = !empty($f['name_attr']) ? $f['name_attr'] : 'field_' . $f['id'];
					if (isset($entry_data[$key])) {
						$email = $entry_data[$key];
						break;
					}
				}
			}
		}

		// Fallback to searching keys if not found via structure
		if (empty($email)) {
			foreach ($entry_data as $k => $v) {
				if (stripos($k, 'email') !== false && !is_array($v) && is_email($v)) {
					$email = $v;
					break;
				}
			}
		}

		if(empty($email)) return;

		$dc = substr($api_key, strpos($api_key, '-') + 1);
		$url = "https://$dc.api.mailchimp.com/3.0/lists/$list_id/members/";

		wp_remote_post($url, [
			'method' => 'POST',
			'headers' => [
				'Authorization' => 'Basic ' . base64_encode('user:' . $api_key),
				'Content-Type' => 'application/json'
			],
			'body' => json_encode([
				'email_address' => $email,
				'status' => 'subscribed'
			])
		]);
	}

	static function send_to_notion($form_id, $entry_data, $notion_settings, $field_labels = []){
		$api_key = isset($notion_settings['api_key']) ? $notion_settings['api_key'] : '';
		$database_id = isset($notion_settings['database_id']) ? $notion_settings['database_id'] : '';

		if(empty($api_key) || empty($database_id)){
			return;
		}

		$properties = [
			'Form ID' => [
				'rich_text' => [
					[
						'type' => 'text',
						'text' => ['content' => (string) $form_id],
					],
				],
			],
		];

		foreach($entry_data as $key => $value){
			if ($key === '__source_url') continue;

			$label = isset($field_labels[$key]) ? $field_labels[$key] : ucfirst(str_replace(['field_', '_'], ['', ' '], $key));
			$clean_val = is_array($value) ? implode(', ', $value) : (string)$value;

			if(stripos($label, 'email') !== false){
				$properties[$label] = [
					'email' => $clean_val,
				];
			} else {
				$properties[$label] = [
					'rich_text' => [
						[
							'type' => 'text',
							'text' => ['content' => $clean_val],
						],
					],
				];
			}
		}

		$title_value = "Form #$form_id";
		if(!empty($entry_data)){
			// Try to find a sensible title
			foreach($entry_data as $k => $v) {
				if ($k !== '__source_url' && !is_array($v)) {
					$title_value = mb_strimwidth($v, 0, 50, '...');
					break;
				}
			}
		}

		$properties['Form ID'] = [
			'title' => [
				[
					'type' => 'text',
					'text' => ['content' => $title_value],
				],
			],
		];

		$body = json_encode([
			'parent' => ['database_id' => $database_id],
			'properties' => $properties,
		]);

		wp_remote_post('https://api.notion.com/v1/pages', [
			'method'  => 'POST',
			'headers' => [
				'Authorization' => 'Bearer ' . $api_key,
				'Content-Type' => 'application/json',
				'Notion-Version' => '2022-06-28',
			],
			'body' => $body,
		]);
	}

	static function send_to_trello($form_id, $entry_data, $trello_settings, $field_labels = []){
		$api_key = isset($trello_settings['api_key']) ? $trello_settings['api_key'] : '';
		$token = isset($trello_settings['token']) ? $trello_settings['token'] : '';
		$list_id = isset($trello_settings['list_id']) ? $trello_settings['list_id'] : '';

		if(empty($api_key) || empty($token) || empty($list_id)){
			return;
		}

		$desc_lines = ["**Form ID:** $form_id", "---"];
		foreach($entry_data as $key => $value){
			if ($key === '__source_url') continue;

			$label = isset($field_labels[$key]) ? $field_labels[$key] : ucfirst(str_replace(['field_', '_'], ['', ' '], $key));
			$clean_val = is_array($value) ? implode(', ', $value) : (string)$value;

			$desc_lines[] = "**" . $label . ":** " . $clean_val;
		}

		if (!empty($entry_data['__source_url'])) {
			$desc_lines[] = "---";
			$desc_lines[] = "**Source URL:** " . $entry_data['__source_url'];
		}

		$description = implode("\n", $desc_lines);

		$card_name = "New Submission – Form #$form_id";
		if(!empty($entry_data)){
			foreach($entry_data as $k => $v) {
				if ($k !== '__source_url' && !is_array($v) && !empty($v)) {
					$card_name = mb_strimwidth($v, 0, 50, '...') . " (Form #$form_id)";
					break;
				}
			}
		}

		$payload = [
			'name' => $card_name,
			'desc' => $description,
			'idList' => $list_id,
			'key' => $api_key,
			'token' => $token,
			'pos' => 'top',
		];

		wp_remote_post('https://api.trello.com/1/cards', [
			'method'  => 'POST',
			'headers' => ['Content-Type' => 'application/json'],
			'body'    => json_encode($payload),
		]);
	}

	static function get_google_access_token($json_config){
		$config = json_decode($json_config, true);
		if(empty($config['private_key']) || empty($config['client_email'])){
			return false;
		}

		$header = self::base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
		$now = time();
		$payload = self::base64url_encode(json_encode([
			'iss' => $config['client_email'],
			'scope' => 'https://www.googleapis.com/auth/spreadsheets',
			'aud' => 'https://oauth2.googleapis.com/token',
			'exp' => $now + 3600,
			'iat' => $now
		]));

		$signature = '';
		if(!openssl_sign($header . '.' . $payload, $signature, $config['private_key'], OPENSSL_ALGO_SHA256)){
			return false;
		}

		$jwt = $header . '.' . $payload . '.' . self::base64url_encode($signature);

		$response = wp_remote_post('https://oauth2.googleapis.com/token', [
			'body' => [
				'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
				'assertion' => $jwt
			]
		]);

		if(is_wp_error($response)){
			return false;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		return isset($body['access_token']) ? $body['access_token'] : false;
	}

	static function base64url_encode($data){
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
	}

	static function send_to_google_sheets($form_id, $entry_data, $gs_settings, $field_labels = []){
		$service_account_json = isset($gs_settings['service_account_json']) ? $gs_settings['service_account_json'] : '';
		$spreadsheet_id = isset($gs_settings['spreadsheet_id']) ? $gs_settings['spreadsheet_id'] : '';
		$sheet = isset($gs_settings['sheet_name']) ? $gs_settings['sheet_name'] : (isset($gs_settings['sheet']) ? $gs_settings['sheet'] : 'Sheet1');
		
		// URL encode the sheet name to handle spaces and special characters
		$encoded_sheet = urlencode($sheet);
		
		if(empty($service_account_json) || empty($spreadsheet_id)){
			return;
		}

		$access_token = self::get_google_access_token($service_account_json);
		if(!$access_token) return;

		// Get form fields to maintain order
		$form = get_post($form_id);
		if (!$form) return;
		$form_data = json_decode($form->post_content, true);
		$fields = isset($form_data['fields']) ? $form_data['fields'] : [];

		// Prepare row values in field order
		$values = [];
		foreach($fields as $field){
			$key = !empty($field['name_attr']) ? $field['name_attr'] : 'field_' . $field['id'];
			$value = isset($entry_data[$key]) ? $entry_data[$key] : '';
			if(is_array($value)) $value = implode(', ', $value);
			$values[] = $value;
		}

		// Check if sheet exists and get headers
		$sheet_exists = self::check_sheet_exists($spreadsheet_id, $sheet, $access_token);
		
		// If sheet doesn't exist, create it
		if(!$sheet_exists){
			$created = self::create_sheet($spreadsheet_id, $sheet, $access_token);
			if(!$created){
				return;
			}
		}
		
		// Build the range with proper encoding
		$range = $encoded_sheet . '!A1';
		$url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id/values/$range:append?valueInputOption=RAW";

		$response = wp_remote_post($url, [
			'method' => 'POST',
			'headers' => [
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type' => 'application/json'
			],
			'body' => json_encode([
				'values' => [$values]
			])
		]);
		
		// Check for errors in response
		$body = json_decode(wp_remote_retrieve_body($response), true);
		
		return $response;
	}

	// Helper function to check if sheet exists
	static function check_sheet_exists($spreadsheet_id, $sheet_name, $access_token){
		$url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id?fields=sheets.properties";
		
		$response = wp_remote_get($url, [
			'headers' => [
				'Authorization' => 'Bearer ' . $access_token
			]
		]);
		
		if(is_wp_error($response)){
			return false;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		
		if(!isset($body['sheets'])){
			return false;
		}
		
		foreach($body['sheets'] as $sheet){
			if($sheet['properties']['title'] === $sheet_name){
				return true;
			}
		}
		
		return false;
	}

	static function create_sheet($spreadsheet_id, $sheet_name, $access_token){
		$url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id:batchUpdate";
		
		$body = [
			'requests' => [
				[
					'addSheet' => [
						'properties' => [
							'title' => $sheet_name
						]
					]
				]
			]
		];
		
		$response = wp_remote_post($url, [
			'method' => 'POST',
			'headers' => [
				'Authorization' => 'Bearer ' . $access_token,
				'Content-Type' => 'application/json'
			],
			'body' => json_encode($body)
		]);
		
		if(is_wp_error($response)){
			return false;
		}
		
		$response_body = json_decode(wp_remote_retrieve_body($response), true);
		
		return !isset($response_body['error']);
	}
}
