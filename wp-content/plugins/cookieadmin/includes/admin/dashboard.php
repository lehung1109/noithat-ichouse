<?php

namespace CookieAdmin\Admin;

if(!defined('COOKIEADMIN_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class Dashboard{
	
	static function dashboard(){
		
		global $cookieadmin_lang, $cookieadmin_error, $cookieadmin_msg, $cookieadmin_settings;
		
		\CookieAdmin\Admin::header_theme(__('Dashboard', 'cookieadmin'));
		
		$view = get_option('cookieadmin_law', 'cookieadmin_gdpr');
		
		$stats = array();
		
		$stats['banner'] = array(
			'icon' => 'yes-alt',
			'icon_color' => 'green',
			'label' => __('Consent Banner', 'cookieadmin'),
			'value' => esc_html__('Enabled', 'cookieadmin'),
			'value_class' => 'cookieadmin-green',
		);
		
		$stats['consent_type'] = array(
			'icon' => 'admin-site',
			'icon_color' => 'blue',
			'label' => __('Consent Type', 'cookieadmin'),
			'value' => ($view == 'cookieadmin_us' ? esc_html__('US State Laws', 'cookieadmin') : esc_html__('GDPR', 'cookieadmin')),
			'value_class' => 'cookieadmin-uppercase',
			'edit_url' => admin_url('admin.php?page=cookieadmin-consent'),
		);
		
		$stats['gcm'] = array(
			'icon' => 'google',
			'icon_color' => 'navy',
			'label' => __('Google Consent Mode v2', 'cookieadmin'),
			'value' => (!empty($cookieadmin_settings['google_consent_mode_v2']) ? '<span class="cookieadmin-green">'.esc_html__('Enabled', 'cookieadmin').'</span>' : esc_html__('Disabled', 'cookieadmin')),
			'edit_url' => admin_url('admin.php?page=cookieadmin-settings'),
		);
		
		$stats['auto_scan'] = array(
			'icon' => 'update',
			'icon_color' => 'amber',
			'label' => __('Auto Scan', 'cookieadmin'),
			'value' => (!empty($cookieadmin_settings['cookieadmin_auto_scan']) ? '<span class="cookieadmin-green">'.esc_html__('Enabled', 'cookieadmin').'</span>' : esc_html__('Disabled', 'cookieadmin')),
			'edit_url' => admin_url('admin.php?page=cookieadmin-settings'),
		);
		
		$stats = apply_filters('cookieadmin_dashboard_stats', $stats);
		
		// Stat cards
		echo '<div class="cookieadmin-stats-grid">';
		foreach($stats as $stat){
			echo '<div class="cookieadmin-stat-card">
				<div class="cookieadmin-stat-icon cookieadmin-stat-icon--'.esc_attr($stat['icon_color']).'"><span class="dashicons dashicons-'.esc_attr($stat['icon']).'"></span></div>
				<div class="cookieadmin-stat-label">'.esc_html($stat['label']);
			if(!empty($stat['edit_url'])){
				echo ' <a class="cookieadmin-stat-edit" href="'.esc_url($stat['edit_url']).'">'.esc_html__('Edit', 'cookieadmin').'</a>';
			}
			echo '</div>
				<div class="cookieadmin-stat-value '.(!empty($stat['value_class']) ? esc_attr($stat['value_class']) : '').'">'.wp_kses_post($stat['value']).'</div>
			</div>';
		}
		echo '</div>';
		
		// ====== DASHBOARD LAYOUT: MAIN 70% + SIDEBAR 30% ======
		$checklist = self::get_setup_checklist();
		$inventory = self::get_cookie_inventory();
		
		echo '<div class="cookieadmin-dashboard-grid">';
		
		// MAIN AREA (~70%)
		echo '<div class="cookieadmin-dashboard-main">';
		
		// Setup Checklist
		echo '<div class="cookieadmin-card cookieadmin-mb-16">
			<div class="cookieadmin-card-header">
				<span class="cookieadmin-card-title"><span class="dashicons dashicons-performance"></span>'.esc_html__('Setup Checklist', 'cookieadmin').'</span>
			</div>
			<div class="cookieadmin-card-body">
				<ol class="cookieadmin-checklist">';
		foreach($checklist as $item){
			// We should not show the checklist of Pro features if Pro is disabled.
			if(isset($item['pro']) && !defined('COOKIEADMIN_PRO_VERSION')){
				continue;
			}

			$done_class = $item['done'] ? 'cookieadmin-checklist-item--done' : 'cookieadmin-checklist-item--pending';
			$icon = $item['done'] ? 'yes' : 'marker';
			echo '<li><a href="'.esc_url($item['url']).'" class="'.esc_attr($done_class).'"><span class="dashicons dashicons-'.esc_attr($icon).'"></span> '.esc_html($item['label']).'</a></li>';
		}
		echo '</ol>
			</div>
		</div>';
		
		// Cookie Inventory
		echo '<div class="cookieadmin-card cookieadmin-mb-16">
			<div class="cookieadmin-card-header">
				<span class="cookieadmin-card-title"><span class="dashicons dashicons-chart-pie"></span>'.esc_html__('Cookie Inventory', 'cookieadmin').'</span>
				<a href="'.esc_url(admin_url('admin.php?page=cookieadmin-scan-cookies')).'" class="cookieadmin-btn cookieadmin-btn-secondary cookieadmin-btn-sm">'.esc_html__('View All Cookies', 'cookieadmin').'</a>
			</div>
			<div class="cookieadmin-card-body">';
		if(!empty($inventory['total'])){
			echo '<div class="cookieadmin-inventory-total"><span class="cookieadmin-inventory-number">'.esc_html($inventory['total']).'</span> <span class="cookieadmin-inventory-label">'.esc_html__('Cookies discovered', 'cookieadmin').'</span></div>';
			
			$seg_colors = array('necessary' => 'var(--cookieadmin-success)', 'functional' => 'var(--cookieadmin-info)', 'analytics' => 'var(--cookieadmin-warning)', 'marketing' => 'var(--cookieadmin-danger)', 'unknown' => 'var(--cookieadmin-text-soft)');
			
			echo '<div class="cookieadmin-segment-bar">';
			foreach($inventory['categories'] as $cat => $count){
				if($count > 0 && !empty($inventory['total'])){
					$pct = round(($count / $inventory['total']) * 100, 1);
					echo '<div class="cookieadmin-segment" style="width:'.esc_attr($pct).'%;background:'.esc_attr($seg_colors[$cat]).';" title="'.esc_attr(ucfirst($cat)).': '.esc_attr($count).'"></div>';
				}
			}
			echo '</div>';
			
			echo '<div class="cookieadmin-segment-legend">';
			foreach($inventory['categories'] as $cat => $count){
				echo '<span class="cookieadmin-legend-item"><span class="cookieadmin-legend-dot" style="background:'.esc_attr($seg_colors[$cat]).';"></span> '.esc_html(ucfirst($cat)).': <strong>'.esc_html($count).'</strong></span>';
			}
			echo '</div>';
		}else{
			echo '<p class="cookieadmin-text-muted">'.esc_html__('No cookies discovered yet. Run a scan to get started.', 'cookieadmin').'</p>';
		}
		if(!empty($inventory['unknown'])){
			echo '<div class="cookieadmin-inventory-warning"><span class="dashicons dashicons-warning"></span> '.esc_html__('Some cookies are uncategorized. Run a scan for better compliance.', 'cookieadmin').'</div>';
		}
		echo '</div>
		</div>';
		
		// Pro main-area widgets (appears inside main, below checklist + inventory)
		do_action('cookieadmin_dashboard_main_widgets');
		
		echo '</div>'; // end main
		
		// SIDEBAR (~30%)
		echo '<div class="cookieadmin-dashboard-sidebar">';
		
		// Recommended Plugins (free)
		self::render_recommended_plugins();
		
		// Pro sidebar widgets (if any)
		do_action('cookieadmin_dashboard_sidebar_widgets');
		
		echo '</div>'; // end sidebar
		
		echo '</div>'; // end grid
		
		\CookieAdmin\Admin::footer_theme();
	}
	
	static function get_setup_checklist(){
		global $cookieadmin_settings;
		
		$policy = cookieadmin_load_policy();
		$law = get_option('cookieadmin_law', '');
		$scan = get_option('cookieadmin_scan', array());
		$cur = !empty($policy[$law]) ? $policy[$law] : array();
		
		$checklist = array();
		
		$checklist[] = array(
			'label' => __('Run Cookie Scan', 'cookieadmin'),
			'done' => (!empty($scan['success']) && $scan['success'] === true),
			'url' => admin_url('admin.php?page=cookieadmin-scan-cookies'),
		);
		
		$checklist[] = array(
			'label' => __('Configure Consent Banner', 'cookieadmin'),
			'done' => !empty($cur),
			'url' => admin_url('admin.php?page=cookieadmin-consent'),
		);
		
		$checklist[] = array(
			'label' => __('Set Consent Type', 'cookieadmin'),
			'done' => (!empty($law) && in_array($law, array('cookieadmin_gdpr', 'cookieadmin_us'), true)),
			'url' => admin_url('admin.php?page=cookieadmin-consent'),
		);
		
		$checklist[] = array(
			'label' => __('Enable Google Consent Mode v2', 'cookieadmin'),
			'done' => !empty($cookieadmin_settings['google_consent_mode_v2']),
			'url' => admin_url('admin.php?page=cookieadmin-settings'),
			'pro' => true,
		);
		
		$has_links = false;
		if(!empty($cur['cookieadmin_privacy_policy']) || !empty($cur['cookieadmin_cookie_policy'])){
			$has_links = true;
		}
		$checklist[] = array(
			'label' => __('Set Privacy / Cookie Policy Links', 'cookieadmin'),
			'done' => $has_links,
			'url' => admin_url('admin.php?page=cookieadmin-consent'),
			'pro' => true,
		);
		
		$checklist[] = array(
			'label' => __('Configure Auto Scan', 'cookieadmin'),
			'done' => !empty($cookieadmin_settings['cookieadmin_auto_scan']),
			'url' => admin_url('admin.php?page=cookieadmin-settings'),
			'pro' => true,
		);
		
		return $checklist;
	}
	
	static function get_cookie_inventory(){
		global $wpdb;
		
		$table_name = esc_sql($wpdb->prefix . 'cookieadmin_cookies');
		
		if(!\CookieAdmin\Admin::cookieadmin_table_exists($table_name)){
			return array('total' => 0, 'categories' => array('necessary' => 0, 'functional' => 0, 'analytics' => 0, 'marketing' => 0, 'unknown' => 0), 'unknown' => 0);
		}
		
		$total = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
		
		$categories = array('necessary' => 0, 'functional' => 0, 'analytics' => 0, 'marketing' => 0, 'unknown' => 0);
		
		$rows = $wpdb->get_results("SELECT category, COUNT(*) AS cnt FROM $table_name GROUP BY category");
		
		if(!empty($rows)){
			foreach($rows as $row){
				$cat = strtolower($row->category);
				if(array_key_exists($cat, $categories)){
					$categories[$cat] = (int) $row->cnt;
				}else{
					$categories['unknown'] += (int) $row->cnt;
				}
			}
		}
		
		return array(
			'total' => $total,
			'categories' => $categories,
			'unknown' => $categories['unknown'],
		);
	}
	
	static function render_recommended_plugins(){
		$plugins = array(
			'loginizer' => array(
				'name' => 'Loginizer',
				'desc' => __('Brute Force Protection & Login Security', 'cookieadmin'),
				'icon' => 'dashicons-shield',
				'wporg_url' => 'https://wordpress.org/plugins/loginizer/',
			),
			'gosmtp' => array(
				'name' => 'GoSMTP',
				'desc' => __('SMTP Mailer for WordPress', 'cookieadmin'),
				'icon' => 'dashicons-email-alt',
				'wporg_url' => 'https://wordpress.org/plugins/gosmtp/',
			),
			'backuply' => array(
				'name' => 'Backuply',
				'desc' => __('Backup & Restore made easy', 'cookieadmin'),
				'icon' => 'dashicons-backup',
				'wporg_url' => 'https://wordpress.org/plugins/backuply/',
			),
			'siteseo' => array(
				'name' => 'SiteSEO',
				'desc' => __('SEO Optimization for WordPress', 'cookieadmin'),
				'icon' => 'dashicons-search',
				'wporg_url' => 'https://wordpress.org/plugins/siteseo/',
			),
		);
		
		echo '<div class="cookieadmin-card cookieadmin-recommended-plugins">
			<div class="cookieadmin-card-header">
				<span class="cookieadmin-card-title"><span class="dashicons dashicons-admin-plugins"></span>'.esc_html__('Recommended Plugins', 'cookieadmin').'</span>
			</div>
			<div class="cookieadmin-card-body">';
		
		foreach($plugins as $slug => $plugin){
			$status = self::get_plugin_status($slug);
			
			echo '<div class="cookieadmin-recommended-plugin" data-slug="'.esc_attr($slug).'">
				<div class="cookieadmin-recommended-plugin-info">
					<div class="cookieadmin-recommended-plugin-icon"><span class="dashicons '.esc_attr($plugin['icon']).'"></span></div>
					<div class="cookieadmin-recommended-plugin-details">
						<div class="cookieadmin-recommended-plugin-name">'.esc_html($plugin['name']).'</div>
						<div class="cookieadmin-recommended-plugin-desc">'.esc_html($plugin['desc']).'</div>
					</div>
				</div>
				<div class="cookieadmin-recommended-plugin-action">
				<a href="'.esc_url($plugin['wporg_url']).'" target="_blank" class="cookieadmin-recommended-plugin-link">'.esc_html__('Learn more', 'cookieadmin').'&nbsp;</a>
				';
			
			if($status === 'active'){
				echo '<span class="cookieadmin-badge cookieadmin-success">'.esc_html__('Active', 'cookieadmin').'</span>';
			}elseif($status === 'installed'){
				echo '<button type="button" class="cookieadmin-btn cookieadmin-btn-secondary cookieadmin-btn-sm cookieadmin-plugin-activate-btn" data-slug="'.esc_attr($slug).'">'.esc_html__('Activate', 'cookieadmin').'</button>';
			}else{
				echo '<button type="button" class="cookieadmin-btn cookieadmin-btn-primary cookieadmin-btn-sm cookieadmin-plugin-install-btn" data-slug="'.esc_attr($slug).'">'.esc_html__('Install', 'cookieadmin').'</button>';
			}
			
			echo '</div>
			</div>';
		}
		
		echo '</div>
		</div>';
	}
	
	static function get_plugin_status($slug){
		if(!function_exists('get_plugins')){
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		$all_plugins = get_plugins();
		
		foreach($all_plugins as $plugin_path => $plugin_data){
			if(strpos($plugin_path, $slug . '/') === 0){
				if(is_plugin_active($plugin_path)){
					return 'active';
				}
				return 'installed';
			}
		}
		
		return 'not_installed';
	}
	
	static function install_recommended_plugin(){
		check_ajax_referer('cookieadmin_admin_js_nonce', 'cookieadmin_security');
		
		if(!current_user_can('install_plugins')){
			wp_send_json_error(array('message' => __('You do not have permission to install plugins.', 'cookieadmin')));
		}
		
		$slug = !empty($_REQUEST['plugin']) ? sanitize_text_field(wp_unslash($_REQUEST['plugin'])) : '';
		if(empty($slug)){
			wp_send_json_error(array('message' => __('Plugin slug is required.', 'cookieadmin')));
		}
		
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		
		$api = plugins_api('plugin_information', array('slug' => $slug, 'fields' => array('sections' => false)));
		if(is_wp_error($api)){
			wp_send_json_error(array('message' => $api->get_error_message()));
		}
		
		$skin_class = class_exists('\Automatic_Upgrader_Skin') ? '\Automatic_Upgrader_Skin' : '\WP_Upgrader_Skin';
		$skin = new $skin_class();
		$upgrader = new \Plugin_Upgrader($skin);
		$result = $upgrader->install($api->download_link);
		
		if(is_wp_error($result)){
			wp_send_json_error(array('message' => $result->get_error_message()));
		}
		if(!$result){
			wp_send_json_error(array('message' => __('Plugin installation failed.', 'cookieadmin')));
		}
		
		$all_plugins = get_plugins();
		$installed_plugin = '';
		foreach($all_plugins as $path => $data){
			if(strpos($path, $slug . '/') === 0){
				$installed_plugin = $path;
				break;
			}
		}
		
		if(empty($installed_plugin)){
			wp_send_json_error(array('message' => __('Plugin installed but could not be located for activation.', 'cookieadmin')));
		}
		
		$result = activate_plugin($installed_plugin);
		if(is_wp_error($result)){
			wp_send_json_error(array('message' => $result->get_error_message()));
		}
		
		wp_send_json_success(array('message' => __('Plugin installed and activated successfully.', 'cookieadmin')));
	}
	
	static function activate_recommended_plugin(){
		check_ajax_referer('cookieadmin_admin_js_nonce', 'cookieadmin_security');
		
		if(!current_user_can('activate_plugins')){
			wp_send_json_error(array('message' => __('You do not have permission to activate plugins.', 'cookieadmin')));
		}
		
		$slug = !empty($_REQUEST['plugin']) ? sanitize_text_field(wp_unslash($_REQUEST['plugin'])) : '';
		if(empty($slug)){
			wp_send_json_error(array('message' => __('Plugin slug is required.', 'cookieadmin')));
		}
		
		if(!function_exists('get_plugins')){
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		
		$all_plugins = get_plugins();
		$plugin_path = '';
		foreach($all_plugins as $path => $data){
			if(strpos($path, $slug . '/') === 0){
				$plugin_path = $path;
				break;
			}
		}
		
		if(empty($plugin_path)){
			wp_send_json_error(array('message' => __('Plugin not found.', 'cookieadmin')));
		}
		
		$result = activate_plugin($plugin_path);
		if(is_wp_error($result)){
			wp_send_json_error(array('message' => $result->get_error_message()));
		}
		
		wp_send_json_success(array('message' => __('Plugin activated successfully.', 'cookieadmin')));
	}
}
