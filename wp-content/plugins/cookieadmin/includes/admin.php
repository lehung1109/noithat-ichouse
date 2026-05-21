<?php

namespace CookieAdmin;

if(!defined('COOKIEADMIN_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class Admin{
	
	static function enqueue_scripts(){
		
		if(!is_admin()){
			return true;
		}
		
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		$admin_page = basename(parse_url($request_uri, PHP_URL_PATH));
		
		if($admin_page != 'admin.php'){
			return true;
		}
		
		$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		// List all page slugs where styles should be loaded
		$plugin_pages = [
			'cookieadmin',
			'cookieadmin-settings',
			'cookieadmin-scan-cookies',
			'cookieadmin-consent',
			'cookieadmin-consent-logs',
			'cookieadmin-license',
		];
		
		if(empty($current_page) || !in_array($current_page, $plugin_pages)){
			return true;
		}
		
		//Consent Page CSS
		wp_enqueue_style('cookieadmin-style', COOKIEADMIN_PLUGIN_URL . 'assets/css/cookie.css', [], COOKIEADMIN_VERSION);
		wp_enqueue_style('cookieadmin-user-style', COOKIEADMIN_PLUGIN_URL . 'assets/css/consent.css', [], COOKIEADMIN_VERSION);
		
		//WP Color picker
		wp_enqueue_style('wp-color-picker');
		
		$view = get_option('cookieadmin_law', 'cookieadmin_gdpr');	
		$policy = cookieadmin_load_policy();
		
		if(!empty($policy) && !empty($view)){
			
			wp_enqueue_script('cookieadmin_js', COOKIEADMIN_PLUGIN_URL . 'assets/js/cookie.js', [], COOKIEADMIN_VERSION);
			// wp_enqueue_script('cookieadmin_js', COOKIEADMIN_PLUGIN_URL . 'assets/js/consent.js', [], COOKIEADMIN_VERSION);
		
			$policy['set'] = $view;
			$policy['admin_url'] = admin_url('admin-ajax.php');
			$policy['cookieadmin_nonce'] = wp_create_nonce('cookieadmin_admin_js_nonce');
			//cookieadmin_r_print($policy);die();
			
		$policy['lang']['show_more'] = __('Show more', 'cookieadmin');
		$policy['lang']['show_less'] = __('Show less', 'cookieadmin');
		$policy['lang']['days'] = __('Day(s)', 'cookieadmin');
		$policy['lang']['session'] = __('Session', 'cookieadmin');
		$policy['lang']['scan_completed'] = __('Scan completed', 'cookieadmin');
		$policy['lang']['processing'] = __('Processing...', 'cookieadmin');
		$policy['lang']['active'] = __('Active', 'cookieadmin');
		$policy['lang']['install_failed'] = __('Installation failed. Please try again.', 'cookieadmin');
		$policy['lang']['error_occurred'] = __('An error occurred. Please try again.', 'cookieadmin');
			
			wp_localize_script('cookieadmin_js', 'cookieadmin_policy', $policy);
		}
		
		wp_enqueue_script('cookieadmin_js_footer', COOKIEADMIN_PLUGIN_URL . 'assets/js/footer.js', [], COOKIEADMIN_VERSION);
		wp_localize_script('cookieadmin_js_footer', 'cookieadmin_data', array('is_pro' => cookieadmin_is_pro()));

		// We only need to upload icon on consent form page
		if(!empty($_GET['page']) && $_GET['page'] == 'cookieadmin-consent'){
			//to upload icons
			wp_enqueue_media();
		}
	}
	
	//Add Main Menu
	static function cookieadmin_plugin_menu(){
		
		if(!empty($_REQUEST['cookieadmin_save_settings'])){
		
			if(!empty($_REQUEST['cookieadmin_consent_type'])){
				\CookieAdmin\Admin\Consent::save_consent_form();
			}else{
				\CookieAdmin\Admin\Settings::save_settings();
			}
		}
		
		$capability = 'activate_plugins';
		$logo = defined('SITEPAD') ? 'cookieadmin_icon_20-black.svg' : 'cookieadmin_icon_20.svg';
		
		add_menu_page(__('CookieAdmin', 'cookieadmin'), __('CookieAdmin', 'cookieadmin'), $capability, 'cookieadmin', '\CookieAdmin\Admin\Dashboard::dashboard', COOKIEADMIN_PLUGIN_URL .'assets/images/'.$logo);
		
		add_submenu_page('cookieadmin', __('Dashboard', 'cookieadmin'), __('Dashboard', 'cookieadmin'), $capability, 'cookieadmin', '\CookieAdmin\Admin\Dashboard::dashboard');
		
		add_submenu_page('cookieadmin', __('Consent Form', 'cookieadmin'), __('Consent Form', 'cookieadmin'), $capability, 'cookieadmin-consent', '\CookieAdmin\Admin\Consent::consent_form');
		
		add_submenu_page('cookieadmin', __('Settings', 'cookieadmin'), __('Settings', 'cookieadmin'), $capability, 'cookieadmin-settings', '\CookieAdmin\Admin\Settings::settings');
		
		add_submenu_page('cookieadmin', __('Scan Cookies', 'cookieadmin'), __('Scan Cookies', 'cookieadmin'), $capability, 'cookieadmin-scan-cookies', '\CookieAdmin\Admin\Scan::show_cookies');
		
		if(defined('COOKIEADMIN_PREMIUM')){
			add_submenu_page('cookieadmin', __('Consent Logs', 'cookieadmin'), __('Consent Logs', 'cookieadmin'), $capability, 'cookieadmin-consent-logs', '\CookieAdminPro\Admin::show_consent_logs');

			if(!defined('SITEPAD')){
				add_submenu_page('cookieadmin', __('License', 'cookieadmin'), __('License', 'cookieadmin'), $capability, 'cookieadmin-license', '\CookieAdminPro\License::cookieadmin_show_license');
			}
		}else if(!defined('SITEPAD')){
			// Go Pro link
			add_submenu_page('cookieadmin', __('CookieAdmin Go Pro', 'cookieadmin'), __('Go Pro', 'cookieadmin'), $capability, COOKIEADMIN_PRO_URL);
		}
	}

	// cookieadmin header
	static function header_theme($title = 'Dashboard'){
		
		global $cookieadmin_lang, $cookieadmin_error, $cookieadmin_msg;

		self::conflict_notice();

		echo '
		<div class="cookieadmin-metabox-holder">
			<div class="cookieadmin-header">
				<div class="cookieadmin-header-left">
					<div class="cookieadmin-icon">
						<img class="cookieadmin-logo" src="'.esc_attr(COOKIEADMIN_PLUGIN_URL).'assets/images/cookieadmin-logo.png" alt="CookieAdmin Logo"> 
					</div>
					<span class="cookieadmin-header-version">v'.esc_html(COOKIEADMIN_VERSION).'</span>
				</div>
				<div class="cookieadmin-header-right">
					<a href="https://cookieadmin.net/docs" class="cookieadmin-header-link" target="_blank"><span class="dashicons dashicons-book-alt"></span> ' . esc_html__('Documentation', 'cookieadmin') . '</a>
					<span class="cookieadmin-header-separator"></span>
					<a href="https://softaculous.deskuss.com/open.php?topicId=26" class="cookieadmin-header-link" target="_blank"><span class="dashicons dashicons-sos"></span> ' . esc_html__('Support', 'cookieadmin') . '</a>
				</div>
			</div>
			<h1 class="cookieadmin-page-title">'.esc_html($title).'</h1>';

		if(!empty($cookieadmin_error)){
			echo '<div class="cookieadmin-notice"><div id="cookieadmin_message" class="error"><p>'.esc_html($cookieadmin_error).'</p></div></div>';
		}
		
		if(!empty($cookieadmin_msg)){
			echo '<div class="cookieadmin-notice"><div id="cookieadmin_message" class="updated"><p>'.esc_html($cookieadmin_msg).'</p></div></div>';
		}

		echo '<div class="cookieadmin-postbox-container">';
	}

	// cookieadmin footer
	static function footer_theme($no_twitter = 0){
		global $cookieadmin_lang, $cookieadmin_error, $cookieadmin_msg;

		echo '</div>';

		if(!defined('SITEPAD')){
			echo '<div class="cookieadmin-footer">
				<a href="'.esc_url(COOKIEADMIN_WWW_URL).'" target="_blank">CookieAdmin</a> v'.esc_html(COOKIEADMIN_VERSION).' &middot; ' . esc_html__('Report bugs', 'cookieadmin') . ' <a href="https://softaculous.deskuss.com/open.php?topicId=26" target="_blank">'.esc_html__('here', 'cookieadmin').'</a>';

			if(defined('COOKIEADMIN_PREMIUM')){
				echo ' &middot; <a href="mailto:support@cookieadmin.net">'.esc_html__('Email support', 'cookieadmin').'</a>';
			}

			echo '</div>';
		}

		echo '</div>';
	}

	static function cookieadmin_table_exists($table_name) {
		global $wpdb;
		
		$query = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
		
		return $wpdb->get_var($query) === $table_name;
	}
	
	static function scan_notice(){
		
		$cookieadmin_auto_scan = get_option('cookieadmin_scan');
		$current_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
		
		if(empty($cookieadmin_auto_scan['status']) || (strpos($current_page, 'cookieadmin') !== 0)){
			return false;
		}
			
		$msg = '';
		
		if($cookieadmin_auto_scan['status'] === 2){
			
			$count = !empty($cookieadmin_auto_scan['count']) ? $cookieadmin_auto_scan['count'] : 0;
			/* translators: %1$s is replaced with a "string" of name of plugins, and %2$s is replaced with "string" which can be "is" or "are" based on the count of the plugin */
			$msg = sprintf(__('<br><strong>Cookie Scan is running:</strong> %1$d Cookies found. Please wait for cookies to appear on <a href="%2$s">Scan Cookies Page</a>', 'cookieadmin'), $count, esc_url(admin_url('admin.php?page=cookieadmin-scan-cookies')));
		}
		
		if($cookieadmin_auto_scan['status'] === 3 && isset($cookieadmin_auto_scan['success'])){
				
			if($cookieadmin_auto_scan['success'] === true){
				/* translators: %1$s is replaced with a "string" of name of plugins, and %2$s is replaced with "string" which can be "is" or "are" based on the count of the plugin */
				$msg = sprintf(__('<br><strong>Cookie Scan completed successfully:</strong> Please visit <a href="%1$s">Scan Cookies</a> to review scan results.', 'cookieadmin'), esc_url(admin_url('admin.php?page=cookieadmin-scan-cookies')));
			}
			
			if($cookieadmin_auto_scan['success'] === false){
				/* translators: %1$s is replaced with a "string" of name of plugins, and %2$s is replaced with "string" which can be "is" or "are" based on the count of the plugin */
				$msg = sprintf(__('<br><strong>Cookie Scan failed:</strong> Please <a href="%1$s">Scan Cookies</a> again for compliance. %2$s', 'cookieadmin'), esc_url(admin_url('admin.php?page=cookieadmin-scan-cookies')), esc_html(!empty($cookieadmin_auto_scan['message']) ? $cookieadmin_auto_scan['message'] : ''));
			}
		}
		
		if(empty($msg)){
			return;
		}
		
		// cookieadmin_logo_svg fn is Internal static SVG. if allowing user input or filters, escape it.
		echo '			
		<div class="notice notice-info is-dismissible" id="cookieadmin-auto-scan-notice">
			<p>'.cookieadmin_logo_svg().wp_kses_post($msg). '</p>
		</div>';
		
		wp_register_script('cookieadmin-scan-notice', '', ['jquery'], '', true);
		wp_enqueue_script('cookieadmin-scan-notice');
		wp_add_inline_script('cookieadmin-scan-notice', 'jQuery("#cookieadmin-auto-scan-notice").on("click",
		function(e){
			
			let target = jQuery(e.target);
			if(!target.hasClass("notice-dismiss")){
				return;
			}
			
			var data;

			// Hide it
			jQuery("#cookieadmin-auto-scan-notice").remove();

			// Save this preference
			jQuery.post("'.admin_url('admin-ajax.php?action=cookieadmin_ajax_handler&cookieadmin_act=close-notice&notice=cookieadmin-scan-notice').'&cookieadmin_security='.wp_create_nonce('cookieadmin_admin_js_nonce').'", data, function(response) {
			});
			
		});');
		
	}
	static function consent_log_purge_notice(){
		
		$cookieadmin_consent_purge = get_option('cookieadmin_consent_purge', ['status' => 0, 'count' => 0]);
				
		if(empty($cookieadmin_consent_purge['status'])){
			return false;
		}
			
		$msg = '';
		$count = empty($cookieadmin_consent_purge['count']) ? 0 : $cookieadmin_consent_purge['count'];
		
		if($cookieadmin_consent_purge['status'] === 2){
			/* translators: %1$d: number of consent logs deleted */
			$msg = 	sprintf(__('<br><strong>Consent logs are being deleted :</strong> %1$d logs deleted. Please wait for deletion process to complete.', 'cookieadmin'), $count);
		}
		
		if($cookieadmin_consent_purge['status'] === 3){
			if(isset($cookieadmin_consent_purge['success'])){
				if($cookieadmin_consent_purge['success'] === true){
					/* translators: %1$d: number of consent logs deleted */
					$msg = sprintf(__('<br><strong>Consent logs deleted successfully:</strong> %1$d logs deleted.', 'cookieadmin'), $count);
				}
				
				if($cookieadmin_consent_purge['success'] === false){
					/* translators: %1$d: number of consent logs deleted, %2$s: error message*/
					$msg = sprintf(__('<br><strong>Consent logs deletion failed:</strong> %1$d logs deleted. Error : %2$s', 'cookieadmin'), $count, esc_html($cookieadmin_consent_purge['message']));
				}
			}
		}
		
		if(empty($msg)){
			return;
		}
		
		// cookieadmin_logo_svg fn is Internal static SVG. if allowing user input or filters, escape it.
		echo '			
		<div class="notice notice-info is-dismissible" id="cookieadmin-consent-purge-notice">
			<p>'.cookieadmin_logo_svg().wp_kses_post($msg). '</p>
		</div>';
		
		wp_register_script('cookieadmin-consent-purge-notice', '', ['jquery'], '', true);
		wp_enqueue_script('cookieadmin-consent-purge-notice');
		wp_add_inline_script('cookieadmin-consent-purge-notice', 'jQuery("#cookieadmin-consent-purge-notice").on("click",
		function(e){
			
			let target = jQuery(e.target);
			if(!target.hasClass("notice-dismiss")){
				return;
			}
			
			var data;

			// Hide it
			jQuery("#cookieadmin-consent-purge-notice").remove();

			// Save this preference
			jQuery.post("'.admin_url('admin-ajax.php?action=cookieadmin_ajax_handler&cookieadmin_act=close-notice&notice=cookieadmin-consent-purge-notice').'&cookieadmin_security='.wp_create_nonce('cookieadmin_admin_js_nonce').'", data, function(response) {
			});
			
		});');
		
	}
	
	static function conflict_notice(){

		$conflicting = [
			'complianz-gdpr/complianz-gdpr.php' => 'Complianz',
			'cookie-law-info/cookie-law-info.php' => 'CookieYes',
			'cookiebot/cookiebot.php' => 'Cookiebot',
			'cookie-notice/cookie-notice.php' => 'Cookie Notice',
			'gdpr-cookie-compliance/gdpr-cookie-compliance.php' => 'GDPR Cookie Compliance',
			'borlabs-cookie/borlabs-cookie.php' => 'Borlabs Cookie',
			'wp-gdpr-compliance/wp-gdpr-compliance.php' => 'Cookie Information',
		];

		if(!function_exists('is_plugin_active')){
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$active = [];
		foreach($conflicting as $slug => $name){
			if(is_plugin_active($slug)){
				$active[] = $name;
			}
		}

		if(empty($active)){
			return;
		}

		$names = implode(', ', $active);

		echo '<div class="cookieadmin-conflict-notice">
			<span class="dashicons dashicons-warning"></span>
			<span>'.sprintf(__('Conflicting plugin(s) detected: <b>%s</b>. Please deactivate them to avoid issues with CookieAdmin.', 'cookieadmin'), esc_html($names)).'</span>
		</div>';
	}

	static function close_notices(){
		
		if(empty($_REQUEST['notice'])){
			return;
		}
		
		$notice = sanitize_text_field(wp_unslash($_REQUEST['notice']));
		$notice = str_replace('-notice', '', $notice);
		$notice = str_replace('-', '_', $notice);
		
		update_option($notice, array());
	}
	
	static function plugin_update_notice(){
		if(defined('SOFTACULOUS_PLUGIN_UPDATE_NOTICE')){
			return;
		}

		$to_update_plugins = apply_filters('softaculous_plugin_update_notice', []);

		if(empty($to_update_plugins)){
			return;
		}

		/* translators: %1$s is replaced with a "string" of name of plugins, and %2$s is replaced with "string" which can be "is" or "are" based on the count of the plugin */
		$msg = sprintf(__('New versions of %1$s %2$s available. Updating ensures better performance, security, and access to the latest features.', 'cookieadmin'), '<b>'.esc_html(implode(', ', $to_update_plugins)).'</b>', (count($to_update_plugins) > 1 ? 'are' : 'is')) . ' <a class="button button-primary" href='.esc_url(admin_url('plugins.php?plugin_status=upgrade')).'>Update Now</a>';

		define('SOFTACULOUS_PLUGIN_UPDATE_NOTICE', true); // To make sure other plugins don't return a Notice
		echo '<div class="notice notice-info is-dismissible" id="cookieadmin-plugin-update-notice">
			<p>'.wp_kses_post($msg). '</p>
		</div>';

		wp_register_script('cookieadmin-update-notice', '', ['jquery'], '', true);
		wp_enqueue_script('cookieadmin-update-notice');
		wp_add_inline_script('cookieadmin-update-notice', 'jQuery("#cookieadmin-plugin-update-notice").on("click", function(e){
			let target = jQuery(e.target);

			if(!target.hasClass("notice-dismiss")){
				return;
			}

			var data;
			
			// Hide it
			jQuery("#cookieadmin-plugin-update-notice").hide();
			
			// Save this preference
			jQuery.post("'.admin_url('admin-ajax.php?action=cookieadmin_ajax_handler&cookieadmin_act=close-update-notice').'&cookieadmin_security='.wp_create_nonce('cookieadmin_admin_js_nonce').'", data, function(response) {
				//alert(response);
			});
		});');
	}

	static function plugin_update_notice_filter($plugins = []){
		$plugins['cookieadmin/cookieadmin.php'] = 'CookieAdmin';
		return $plugins;
	}

	static function close_plugin_update_notice(){
		$plugin_update_notice = get_option('softaculous_plugin_update_notice', []);
		$available_update_list = get_site_transient('update_plugins');
		$to_update_plugins = apply_filters('softaculous_plugin_update_notice', []);

		if(empty($available_update_list) || empty($available_update_list->response)){
			return;
		}

		foreach($to_update_plugins as $plugin_path => $plugin_name){
			if(isset($available_update_list->response[$plugin_path])){
				$plugin_update_notice[$plugin_path] = $available_update_list->response[$plugin_path]->new_version;
			}
		}

		update_option('softaculous_plugin_update_notice', $plugin_update_notice);
	}

	static function is_feature_available($return = 0){
		
		if(cookieadmin_is_pro()){
			return '';
		}
		
		$msg = ' <a href="'.esc_url(COOKIEADMIN_PRO_URL).'" target="_blank" class="cookieadmin-pro-badge">'.esc_html__('Pro', 'cookieadmin').'</a>';
		
		if(!empty($return)){
			return $msg;
		}else{
			echo wp_kses_post($msg);
		}

	}
}

