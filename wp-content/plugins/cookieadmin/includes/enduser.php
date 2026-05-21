<?php

namespace CookieAdmin;

if(!defined('COOKIEADMIN_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class Enduser{
	
	static $http_cookies = array();
	static $categorized_cookies = array();
	
	static function enqueue_scripts(){
		global $wpdb;
		
		$view = get_option('cookieadmin_law', 'cookieadmin_gdpr');	
		$policy = cookieadmin_load_policy();
		$table_name = esc_sql($wpdb->prefix . 'cookieadmin_cookies');
		//cookieadmin_r_print($view);
		//cookieadmin_r_print($policy);
		
		if(!empty($policy) && !empty($view) && !cookieadmin_is_editor_mode()){
		
			wp_enqueue_style('cookieadmin-style', COOKIEADMIN_PLUGIN_URL . 'assets/css/consent.css', [], COOKIEADMIN_VERSION);
			
			$js_deps = [];
			// Free consent.js is the base script from where the functionality gets triggered
			// So we need to make sure the dependencies of free script gets loaded first
			// Like the pro/consent.js is a dependency of the free one.
			if(defined('COOKIEADMIN_PREMIUM')){
				$js_deps[] = 'cookieadmin_pro_js';
			}
			
			wp_enqueue_script('cookieadmin_js', COOKIEADMIN_PLUGIN_URL . 'assets/js/consent.js', $js_deps, COOKIEADMIN_VERSION);
		
			$policy[$view]['ajax_url'] = admin_url('admin-ajax.php');
			$policy[$view]['nonce'] = wp_create_nonce('cookieadmin_js_nonce');
			$policy[$view]['http_cookies'] = self::$http_cookies;
			$policy[$view]['home_url'] = home_url();
			$policy[$view]['plugin_url'] = COOKIEADMIN_URL;
			$policy[$view]['is_pro'] = (defined('COOKIEADMIN_PREMIUM') ? COOKIEADMIN_PREMIUM : 0);
			$policy[$view]['ssl'] = is_ssl();
			
			$base_path = parse_url(home_url(), PHP_URL_PATH) ?: '/';
			$base_path = ($base_path !== '/') ? rtrim($base_path, '/') . '/' : '/';
			
			// Used for setting cookie
			$policy[$view]['base_path'] = $base_path;
			
			$policy[$view]['lang']['show_less'] = __('Show less', 'cookieadmin');
			$policy[$view]['lang']['duration'] = __('Duration', 'cookieadmin');
			$policy[$view]['lang']['session'] = __('Session', 'cookieadmin');
			$policy[$view]['lang']['days'] = __('Days', 'cookieadmin');
			
			// cookieadmin_r_print($policy);die();
			
			$rows = $wpdb->get_results("SELECT cookie_name, category, expires, description, patterns FROM {$table_name}");
			$cookie_data = array();

			foreach ($rows as $row) {
				$cookie_data[$row->cookie_name] = $row;
			}
			
			$policy[$view]['categorized_cookies'] = self::$categorized_cookies = $cookie_data;
			
			wp_localize_script('cookieadmin_js', 'cookieadmin_policy', $policy[$view]);
			
		}
	}

	/* static function cookieadmin_block_cookie_init_php(){
		
		//New - To catch, remove and send cookies in WP enqueue
		$http_cookies = array();
		$headers = headers_list();

		foreach($headers as $header) {
			
			if (stripos(trim($header), 'Set-Cookie:') === 0) {
				$header = trim(substr($header, strlen('Set-Cookie:')));
				$name = trim(explode('=', $header)[0]);
				$http_cookies[$name]['string'] = trim($header);
				setcookie($name, '', time() - 999999, '/');
			}
		}

		$http_cookies['cookieadmin_consent'] = ["string" => "cookieadmin_consent=CookieAdmin Cookie Initialization"];
		
		self::$http_cookies = $http_cookies;
	} */
	
	static function block_scripts(){

		if(wp_doing_ajax() || is_admin() || defined('REST_REQUEST') || defined('COOKIEADMIN_SCANNER') || cookieadmin_is_editor_mode()){
			return;
		}
		
		$settings = get_option('cookieadmin_settings');
		
		// If block scripts is disabled, we don't need to make any changes
		if(empty($settings) || empty($settings['block_scripts'])){
			return;
		}

		$view = get_option('cookieadmin_law', 'cookieadmin_gdpr');
		$policy = cookieadmin_load_policy();
		if(empty($policy) || empty($view)){
			return;
		}

		ob_start([__CLASS__, 'update_tracking_scripts']);
	}

	static function update_tracking_scripts($html){

		if(stripos($html, '<script') === false){
			return $html;
		}

		if(empty(self::$categorized_cookies)){
			return $html;
		}

		$cookieadmin_consent = isset($_COOKIE['cookieadmin_consent'])
							? json_decode(wp_unslash($_COOKIE['cookieadmin_consent']), true)
							: [];

		// Sanitizing cookies
		array_walk( $cookieadmin_consent, function( $value, $key ) use ( &$cookieadmin_consent ) {
			$sanitized_key = sanitize_key( $key );
			$cookieadmin_consent[ $sanitized_key ] = sanitize_text_field($value);
		} );

		$html = preg_replace_callback(
			'/<script\b([^>]*)>([\s\S]*?)<\/script>/i',
			function($match) use ($cookieadmin_consent){
				$attrs = $match[1];
				$content = $match[2];
				$full_tag = $match[0];

				if(preg_match('/\btype\s*=\s*["\']text\/plain["\']/i', $attrs)){
					return $full_tag;
				}

				if(preg_match('/\b(id|src)\s*=\s*["\'][^"\']*cookieadmin[^"\']*["\']/i', $attrs)){
					return $full_tag;
				}

				if(preg_match('/\btype\s*=\s*["\']([^"\']+)["\']/i', $attrs, $type_match)){
					$type = strtolower(trim($type_match[1]));
					if($type !== 'text/javascript' && $type !== 'module'){
						return $full_tag;
					}
				}

				$src = '';
				if(preg_match('/\bsrc\s*=\s*["\']([^"\']*)["\']/i', $attrs, $src_match)){
					$src = $src_match[1];
				}

				$match_against = !empty($src) ? $src : trim($attrs . ' ' . $content);

				if(empty($match_against)){
					return $full_tag;
				}

				foreach (self::$categorized_cookies as $item) {
					$category = !empty($item->category) ? strtolower($item->category) : '';
					$patterns = !empty($item->patterns) ? json_decode($item->patterns, true) : '';

					if(empty($patterns) || empty($category)){
						continue;
					}

					foreach ($patterns as $pattern) {
						if(strpos($match_against, $pattern) !== false){
							if($category !== 'necessary' && 
								(empty($cookieadmin_consent) || 
									(!empty($cookieadmin_consent[$category]) && $cookieadmin_consent[$category] == 'false') || 
									(!empty($cookieadmin_consent['reject']) && $cookieadmin_consent['reject'] == 'true')
								)
							){
								if($attrs === ''){
									return '<script type="text/plain" data-cookieadmin-category="' . esc_attr($category) . '">' . $content . '</script>';
								}
								return '<script type="text/plain" data-cookieadmin-category="' . esc_attr($category) . '"' . $attrs . '>' . $content . '</script>';
							}
						}
					}
				}

				return $full_tag;
			},
			$html
		);

		return $html;
	}
	
	static function cookieadmin_show_banner(){
		
		$view = get_option('cookieadmin_law', 'cookieadmin_gdpr');	
		$policy = cookieadmin_load_policy();
		
		$raw_template = cookieadmin_load_consent_template($policy[$view], $view);
		
		if(!is_array($raw_template) || empty($raw_template)){
			return false;
		}

		$templates = implode('', $raw_template);
		
		$allowed_tags = cookieadmin_kses_allowed_html();
		
		$templates = apply_filters('cookieadmin_after_banner', $templates);
		
		// var_dump($policy[$view]);
		echo wp_kses($templates, $allowed_tags);
	}
	
	static function cookieadmin_table_exists($table_name) {
		global $wpdb;
		
		$query = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
		
		return $wpdb->get_var($query) === $table_name;
	}
}

