<?php

namespace CookieAdminPro;

if(!defined('COOKIEADMIN_PRO_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class Admin{
	
	static function enqueue_scripts(){
		
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
		
		$is_admin_page = basename(parse_url($request_uri, PHP_URL_PATH));
		
		$current_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';
		
		if(!is_admin() || empty($current_page) ||  $is_admin_page != 'admin.php'){
			return false;
		}

		// Add condition to load only on our settings page
		//Consent Page Css
		wp_enqueue_style('cookieadmin-pro-style', COOKIEADMIN_PRO_PLUGIN_URL . 'assets/css/cookie.css', [], COOKIEADMIN_PRO_VERSION);
		
		wp_enqueue_script('cookieadmin_pro_js', COOKIEADMIN_PRO_PLUGIN_URL . 'assets/js/cookie.js', [], COOKIEADMIN_PRO_VERSION);
	
		$policy['admin_url'] = admin_url('admin-ajax.php');
		$policy['cookieadmin_nonce'] = wp_create_nonce('cookieadmin_pro_admin_js_nonce');
		$policy['lang']['background_scan'] = __('Scan has started in background. You will be notified upon completion', 'cookieadmin');

		if(strpos($current_page, 'cookieadmin-scan-cookies') !== false){
			$policy['sp_preloaded_pages'] = self::get_preloaded_pages();
			$policy['sp_has_woocommerce'] = post_type_exists('product');
			$policy['sp_home_url'] = untrailingslashit(home_url('/'));
			$policy['lang']['sp_saved'] = __('Saved', 'cookieadmin');
			$policy['lang']['sp_save_error'] = __('Failed to save. Please try again.', 'cookieadmin');
			$policy['lang']['sp_max_reached'] = __('Maximum pages reached (4/4)', 'cookieadmin');
			$policy['lang']['sp_reset_confirm'] = __('This will remove all selected pages except Home. Continue?', 'cookieadmin');
			$policy['lang']['sp_no_results'] = __('No matching pages found.', 'cookieadmin');
			$policy['lang']['sp_search_placeholder'] = __('Search and add pages...', 'cookieadmin');
			$policy['lang']['sp_single_post'] = __('Single Post (Most Recent)', 'cookieadmin');
			$policy['lang']['sp_single_product'] = __('Single Product (Most Recent)', 'cookieadmin');
			$policy['lang']['sp_already_selected'] = __('Replace by removing current selection first', 'cookieadmin');
			$policy['lang']['sp_post_badge'] = __('Post', 'cookieadmin');
			$policy['lang']['sp_product_badge'] = __('Product', 'cookieadmin');
			$policy['lang']['sp_page_badge'] = __('Page', 'cookieadmin');
			$policy['lang']['sp_home_label'] = __('Home', 'cookieadmin');
			$policy['lang']['sp_unavailable'] = __('No longer available', 'cookieadmin');
		}
		//cookieadmin_r_print($policy);die();
		
		wp_localize_script('cookieadmin_pro_js', 'cookieadmin_pro_policy', $policy);
	}
	
	//Re-Consent Icons
	static function reconsent_icons($icons_grid = '', $policy = []){
		
		$icon_files = glob(COOKIEADMIN_PRO_DIR . 'assets/images/re-consent-icons/*.svg', GLOB_BRACE);

		if(empty($icon_files)){
			return $icons_grid;
		}

		$default_icon = 'cookieadmin.svg';
		$selected_icon = !empty($policy['cookieadmin_reconsent_icon']) ? basename($policy['cookieadmin_reconsent_icon']) : $default_icon;
		$bg_color = !empty($policy['cookieadmin_re_consent_bg_color']) ? 'background-color:'.$policy['cookieadmin_re_consent_bg_color'] : '';

		foreach($icon_files as $icon_file){
			$icon_name = basename($icon_file);
			$is_selected = ($icon_name === $selected_icon) ? 'checked' : '';
			$icon_ext_dashed = str_replace('.', '-', $icon_name);
			
			$icons_grid .= '<label class="cookieadmin-reconsent-icon" style="'.esc_attr($bg_color).'" for="cookieadmin-reconsent-'.esc_attr($icon_ext_dashed).'"><input id="cookieadmin-reconsent-'.esc_attr($icon_ext_dashed).'" name="cookieadmin_reconsent_icon" type="radio" value="'.esc_attr($icon_name).'" '.$is_selected.'> <img src="'.esc_attr(COOKIEADMIN_PRO_PLUGIN_URL.'/assets/images/re-consent-icons/'.$icon_name).'"></label>';
		}
		
		return $icons_grid;
	}
	
	// Admin notice to create consent table if missing when consent logs are enabled
	static function table_missing_notice(){
		if(!is_admin()) return;

		// Only show on our plugin admin pages
		$current_page = isset($_GET['page']) ? sanitize_text_field(wp_unslash($_GET['page'])) : '';
		if(strpos($current_page, 'cookieadmin') === FALSE) {
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'cookieadmin_consents';
		$exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
		if($exists === $table_name){
			return;
		}

		echo '<div class="notice notice-error" id="cookieadmin-pro-table-missing-notice">';
		echo '<p>'.esc_html__('The Consent table is missing. Consent Logs and Consent data cannot be saved.', 'cookieadmin').'</p>';
		echo '<p><button id="cookieadmin-pro-create-consent-table" class="button button-primary">'.esc_html__('Create Consent Table', 'cookieadmin').'</button></p>';
		echo '</div>';    
	}
	
	//Add Main Menu
	static function plugin_menu(){
		
	}

	static function show_settings($title = 'CookieAdmin Pro'){
		
	}

	static function cookieadmin_pro_table_exists($table_name) {
		global $wpdb;
		
		$query = $wpdb->prepare("SHOW TABLES LIKE %s", $table_name);
		
		return $wpdb->get_var($query) === $table_name;
	}

	// Create Consent table via AJAX
	static function create_consent_table() {
		\CookieAdminPro\Database::activate();
		wp_send_json_success(esc_html__('Consent table created', 'cookieadmin'));
	}

	static function show_consent_logs(){
		
		global $cookieadmin_lang, $cookieadmin_error, $cookieadmin_msg;
		
		\CookieAdmin\Admin::header_theme(__('Consent Logs', 'cookieadmin'));
		
		$log_data  = \CookieAdminPro\Admin::get_consent_logs();
		$consent_logs = (!empty($log_data['logs']) ? $log_data['logs'] : array());
		
		echo '
			
		<div class="cookieadmin_pro_consent-wrap" style="max-width: 85vw;">
			<form action="" method="post">

			<div class="cookieadmin_consent-contents">
				<div class="cookieadmin_consent">
					
					<div class="contents cookieadmin_manager">
						
						<div class="cookieadmin-setting cookieadmin-manager-consent-logs">
							<label class="cookieadmin-title"></label>
							<div class="cookieadmin-setting-contents cookieadmin-consent-logs">
								<input type="button" class="cookieadmin-btn cookieadmin-btn-primary cookieadmin-consent-logs-export" value="'.esc_html__('Export CSV', 'cookieadmin').'">
							</div>
							
							<div class="cookieadmin-manager-scan-result">
								<table class="cookieadmin-table cookieadmin-consent-logs-result">
								<thead>
									<tr>
										<th width="30%">'.esc_html__('Consent Id', 'cookieadmin').'</th>
										<th width="20%">'.esc_html__('Status', 'cookieadmin').'</th>
										<th>'.esc_html__('Country', 'cookieadmin').'</th>
										<th>'.esc_html__('User IP (Anonymized)', 'cookieadmin').'</th>
										<th>'.esc_html__('Time', 'cookieadmin').'</th>
									</tr>
								</thead>
								<tbody>';
									
									if(!empty($consent_logs)){
										foreach ($consent_logs as $log){
											
											$status_badge = 'warning';
											if(strtolower($log['consent_status_raw']) == 'accept'){
												$status_badge = 'success';
											}elseif(strtolower($log['consent_status_raw']) == 'reject'){
												$status_badge = 'danger';
											}
											
											echo '
											<tr>
												<td>'.esc_html($log['consent_id']).'</td>
												<td><span class="cookieadmin-badge cookieadmin-'.esc_attr($status_badge).'">'.esc_html($log['consent_status']).'</span></td>
												<td>'.(!empty($log['country']) ? esc_html($log['country']) : '—').'</td>
												<td>'.esc_html($log['user_ip']).'</td>
												<td>'.esc_html($log['consent_time']).'</td>
											</tr>';
										}
									}else{
										echo '
										<tr>
											<td colspan="4">'.esc_html__('No consent logs recorded yet!', 'cookieadmin').'</td>
										</tr>';
									}
									
									echo '
								</tbody>
								</table>
							</div>';
							
							if(!empty($consent_logs)){
								echo '
								<div class="cookieadmin-consent-logs-pagination" style="text-align:right;">
										'.esc_html__('Displaying', 'cookieadmin').' <span class="displaying-num">'.esc_html($log_data['min_items'].' - '.$log_data['max_items']).'</span> '.esc_html__('of', 'cookieadmin').' <span class="max-num">'.esc_html($log_data['total_logs']).'</span> '.esc_html__('item(s)', 'cookieadmin').'
										&nbsp;
										<!-- First Page Consent logs -->
										<a class="first-page cookieadmin-logs-paginate" id="cookieadmin-first-consent-logs" href="javascript:void(0)">
										<span aria-hidden="true">«</span>
										</a>
										&nbsp;
										<!-- Previous Page Consent logs -->
										<a class="prev-page cookieadmin-logs-paginate" id="cookieadmin-previous-consent-logs" href="javascript:void(0)">
										<span aria-hidden="true">‹</span>
										</a>
										&nbsp;
										<!-- Current Page logs -->
										<span class="paging-input">
											<label for="current-page-selector" class="screen-reader-text">Current Page</label>
											<input class="current-page" id="current-page-selector" name="current-page-selector" value="'.(!empty($log_data['current_page']) ? esc_attr($log_data['current_page']) : '').'" size="3"  aria-describedby="table-paging" type="text" style="text-align: center;">
											<span class="tablenav-paging-text"> of 
												<span class="total-pages">'.esc_html($log_data['total_pages']).'</span>
											</span>
										</span>
										&nbsp;
										<!-- Next Page Consent Logs -->
										<a class="next-page cookieadmin-logs-paginate"  id="cookieadmin-next-consent-logs" href="javascript:void(0)">
											<span aria-hidden="true">›</span>
										</a>
										&nbsp;
										<!-- Last Page Consent logs -->
										<a class="last-page cookieadmin-logs-paginate" 
										id="cookieadmin-last-consent-logs" href="javascript:void(0)">
											<span aria-hidden="true">»</span>
										</a>
										&nbsp;
								</div>';
							}
						echo '
						</div>
					</div>
				</div>';
				
				wp_nonce_field('cookieadmin_pro_admin_nonce', 'cookieadmin_pro_security');
				
				echo '<br/>
				<br/>
			</div>
			</form>
		</div>';
		
		\CookieAdmin\Admin::footer_theme();
	
	}

	//Load Consent logs data from the database
	static function get_consent_logs(){
		
		global $wpdb;
		
		if($_POST && count($_POST) > 0){
			$nonce_slug = (wp_doing_ajax() ? 'cookieadmin_pro_admin_js_nonce' : 'cookieadmin_pro_admin_nonce');
			check_admin_referer($nonce_slug, 'cookieadmin_pro_security');
		}
	 
		if(!current_user_can('administrator')){
			wp_send_json_error(array('message' => 'Sorry, but you do not have permissions to perform this action'));
		}
		
		$num_items = 0;
		$table_name = esc_sql($wpdb->prefix . 'cookieadmin_consents');
		$current_page = isset($_POST['current_page']) ? intval($_POST['current_page']) : 1;

		if (!\CookieAdminPro\Admin::cookieadmin_pro_table_exists($table_name)) {
			// wp_send_json_error(['message' => 'Table does not exist']);
			return array();
		}
		
		// Get total number of logs
		$total_consent_logs = (int) $wpdb->get_var("SELECT COUNT(*) FROM $table_name");	
		$logs_per_page = 25;

		// Calculate max pages
		$max_page = ceil($total_consent_logs / $logs_per_page);
		$max_page = max(1, $max_page);
		
		// Ensure current page is within valid range
		if ($current_page > $max_page) {
			$current_page = $max_page;
		} elseif ($current_page < 1) {
			$current_page = 1;
		}

		// Calculate pagination offset
		$offset = ($current_page - 1) * $logs_per_page;
		
		// Fetch paginated logs
		$consent_logs = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $table_name ORDER BY id DESC LIMIT %d OFFSET %d",
				$logs_per_page,
				$offset
			),
			ARRAY_A
		);
		
		if(!empty($consent_logs)){
			
			foreach($consent_logs as $lk => $log){
				
				if(!empty($log['consent_status'])){
					$_consent_status = json_decode($log['consent_status'], true)[0];
					
					if($_consent_status == 'accept'){
						$consent_logs[$lk]['consent_status_raw'] = 'accept';
						$consent_logs[$lk]['consent_status'] = __('Accepted', 'cookieadmin');
					}elseif($_consent_status == 'reject'){
						$consent_logs[$lk]['consent_status_raw'] = 'reject';
						$consent_logs[$lk]['consent_status'] = __('Rejected', 'cookieadmin');
					}else{
						$consent_logs[$lk]['consent_status_raw'] = 'partially_accepted';
						$consent_logs[$lk]['consent_status'] = __('Partially Accepted', 'cookieadmin');
					}
				}
				
				if(!empty($log['consent_time'])){
					$consent_logs[$lk]['consent_time'] = cookieadmin_pro_human_readable_time($log['consent_time']);
				}
			
				if(!empty($log['user_ip'])){
					$consent_logs[$lk]['user_ip'] = inet_ntop($log['user_ip']);
				}
			}
			
			$num_items = count($consent_logs);
		}
		
		$min_items = $offset + 1;
		$max_items = $min_items + ($num_items - 1);
		
		$return = [
				'logs' => $consent_logs,
				'total_logs' => $total_consent_logs,
				'logs_per_page' => $logs_per_page,
				'current_page' => $current_page,
				'total_pages' => $max_page,
				'min_items' => $min_items,
				'max_items' => $max_items
			];
		
		// Return logs as JSON response
		if (defined('DOING_AJAX') && DOING_AJAX) {
			wp_send_json_success($return);
		}
		
		// Return paginated data
		return $return;
	}

	// Export Consent Logs from the Database
	static function export_logs() {
		global $wpdb;
		
		$cookieadmin_export_type = !empty($_REQUEST['cookieadmin_export_type']) ? sanitize_text_field(wp_unslash($_REQUEST['cookieadmin_export_type'])) : '';
		
		if(!empty($cookieadmin_export_type)){
			if($cookieadmin_export_type == 'consent_logs'){

				$table_name = esc_sql($wpdb->prefix . 'cookieadmin_consents');
				
				//First will check if the table in the database exists or not?
				if(!self::cookieadmin_pro_table_exists($table_name)){
					echo -1;
					echo esc_html__('Table does not exists', 'cookieadmin');
					wp_die();
				}
				
				$logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY id DESC", ARRAY_A);
				$filename = 'cookieadmin-consent-logs';
			}
		}
		
		if(empty($logs)){
			echo -1;
			echo esc_html__('No data to export', 'cookieadmin');
			wp_die();
		}
		
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename='.$filename.'.csv');
		
		$allowed_fields = array('consent_id' => 'Consent Id', 'consent_status' => 'Consent Status', 'country' => 'Country', 'user_ip' => 'User IP (Anonymized)', 'consent_time' => 'Consent Time');

		$file = fopen("php://output","w");
		
		fputcsv($file, array_values($allowed_fields));
		
		foreach($logs as $ik => $log){
				
			if(!empty($log['consent_status'])){
				$_consent_status = json_decode($log['consent_status'], true)[0];
				if($_consent_status == 'accept'){
					$log['consent_status'] = __('Accepted', 'cookieadmin');
				}elseif($_consent_status == 'reject'){
					$log['consent_status'] = __('Rejected', 'cookieadmin');
				}else{
					$log['consent_status'] = __('Partially Accepted', 'cookieadmin');
				}
			}
			
			if(!empty($log['consent_time'])){
				$log['consent_time'] = wp_date('M j Y g:i A T', $log['consent_time']);
			}
			
			if(!empty($log['user_ip'])){
				$log['user_ip'] = inet_ntop($log['user_ip']);
			}
			
			$log['country'] = (!empty($log['country']) ? $log['country'] : '—');
			
			$row = array();
			foreach($allowed_fields as $ak => $av){
				$row[$ak] = $log[$ak];
			}
			
			fputcsv($file, $row);
		}

		fclose($file);
		
		wp_die();
		
	}

	static function version_notice(){
		
		$type = '';
		if(!empty($_REQUEST['type'])){
			$type = sanitize_text_field(wp_unslash($_REQUEST['type']));
		}

		if(empty($type)){
			wp_send_json_error(__('Unknow version difference type', 'cookieadmin'));
		}
		
		update_option('cookieadmin_version_'. $type .'_nag', time() + WEEK_IN_SECONDS);
		wp_send_json_success();
	}

	static function dismiss_expired_licenses(){

		update_option('softaculous_expired_licenses', time());
		wp_send_json_success();
	}
	
	// Manual Add cookie feature for Admin
	static function add_cookie(){
		global $wpdb;
		
		$table_name = esc_sql($wpdb->prefix . 'cookieadmin_cookies');
		$data = null;
		
		if(empty($_REQUEST['cookie_info'])){
			wp_send_json([
				'success' => false,
				'data'    => null,
				'message'   => __('Error : Cookie details missing', 'cookieadmin')]);
		}
			
		$cookie_info = map_deep(wp_unslash($_REQUEST['cookie_info']), 'sanitize_text_field' );
		
		$scan_timestamp = time();
		if(empty($cookie_info['duration'])){
			$calculated_expiry = 0;
		}else{
			$calculated_expiry_seconds = ($cookie_info['duration'] * 86400) + $scan_timestamp;
			$calculated_expiry = date('Y-m-d H:i:s', $calculated_expiry_seconds);
		}
		
		$data = [
			'cookie_name' => $cookie_info['name'],
			'category' => $cookie_info['type'],
			'description' => $cookie_info['description'],
			'domain' => wp_parse_url( home_url(), PHP_URL_HOST ),
			'expires' => $calculated_expiry,
			'scan_timestamp' => $scan_timestamp,
			'edited' => 1
		];

		$formats = ['%s', '%s', '%s', '%s', '%s', '%d', '%d'];

		$wpdb->insert($table_name, $data, $formats);
		
		$data = $wpdb->insert_id;
		
		if ($wpdb->last_error || $data === false) {
			//error_log('DB Error: ' . $wpdb->last_error); // Log it
			wp_send_json([
				'success' => false,
				'data'    => null,
				'message'   => __('Cookie addition Failed, Error: ', 'cookieadmin') . esc_html($wpdb->last_error)
			]);
		}
		
		wp_send_json([
			'success' => true,
			'data' => $data,
			'message' => __('Cookie addition successful', 'cookieadmin')
		]);
		
	}
	
	//Starts the consent logs deletion for ajax
	static function consent_log_pruning_ajax(){
		
		if(get_transient('cookieadmin_pruning_in_progress')){
			wp_send_json([
				'success' => false,
				'message' => __('Deletion is already running in the background. You will be notified on completion.', 'cookieadmin')
			]);
		}
		
		$consent_logs_expiry = !empty($_REQUEST['consent_logs_expiry']) ? absint(wp_unslash($_REQUEST['consent_logs_expiry'])) : 0;
		

		if($consent_logs_expiry <= 0){
			wp_send_json([
				'success' => false,
				'message' => __('Please set a valid logs expiry day(s) limit to start deleting.', 'cookieadmin')
			]);
		}
		
		$retention_limit = time() - ($consent_logs_expiry * DAY_IN_SECONDS);
		
		set_transient('cookieadmin_pruning_in_progress', 'true', 3600);
		
		\CookieAdminPro\Cron::consent_log_pruning_batch($retention_limit);
		
		wp_send_json([
			'success' => false,
			'message' => __('Deletion has started in the background. You will be notified on completion.', 'cookieadmin')
		]);
		
	}
	
	static function get_preloaded_pages(){
		$home_url = untrailingslashit(home_url('/'));
		$page_ids = get_posts([
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 20,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'fields'         => 'ids',
		]);

		$pages = [];
		foreach($page_ids as $id){
			$url = untrailingslashit(get_permalink($id));
			if($url === $home_url){
				continue;
			}
			$pages[] = [
				'id'    => $id,
				'title' => get_the_title($id),
				'url'   => $url,
				'type'  => 'page',
			];
		}
		return $pages;
	}

	static function resolve_entry($val){

		if($val === 'home'){
			return ['type' => 'home', 'id' => 0, 'title' => __('Home', 'cookieadmin'), 'icon' => 'dashicons-admin-home', 'valid' => true];
		}

		if($val === 'latest_post'){
			$latest = get_posts(['post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids']);
			if(!empty($latest)){
				return ['type' => 'latest_post', 'id' => $latest[0], 'title' => __('Single Post', 'cookieadmin'), 'url' => untrailingslashit(get_permalink($latest[0])), 'icon' => 'dashicons-admin-post', 'valid' => true];
			}
			return ['type' => 'latest_post', 'id' => 0, 'title' => __('Single Post', 'cookieadmin'), 'url' => '', 'icon' => 'dashicons-admin-post', 'valid' => false];
		}

		if($val === 'latest_product'){
			if(!post_type_exists('product')){
				return ['type' => 'latest_product', 'id' => 0, 'title' => __('Single Product', 'cookieadmin'), 'url' => '', 'icon' => 'dashicons-products', 'valid' => false];
			}
			$latest = get_posts(['post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids']);
			if(!empty($latest)){
				return ['type' => 'latest_product', 'id' => $latest[0], 'title' => __('Single Product', 'cookieadmin'), 'url' => untrailingslashit(get_permalink($latest[0])), 'icon' => 'dashicons-products', 'valid' => true];
			}
			return ['type' => 'latest_product', 'id' => 0, 'title' => __('Single Product', 'cookieadmin'), 'url' => '', 'icon' => 'dashicons-products', 'valid' => false];
		}

		if(is_numeric($val) && $val > 0){
			if(get_post_status($val) === 'publish' && get_post_type($val) === 'page'){
				$url = untrailingslashit(get_permalink($val));
				return ['type' => 'page', 'id' => $val, 'title' => get_the_title($val), 'url' => $url, 'icon' => 'dashicons-admin-page', 'valid' => true];
			}
			return ['type' => 'page', 'id' => $val, 'title' => '', 'url' => '', 'icon' => 'dashicons-admin-page', 'valid' => false];
		}

		return null;
	}

	static function render_scan_pages_ui(){
		$saved = get_option('cookieadmin_scan_pages', []);
		$home_url = untrailingslashit(home_url('/'));
		$home_resolved = self::resolve_entry('home');

		echo '<div class="cookieadmin-card">
		<div class="cookieadmin-card-header">
			<span class="cookieadmin-card-title">'.esc_html__('Pages to Scan', 'cookieadmin').'&nbsp;<span class="dashicons dashicons-info cookieadmin-tooltip-box" data-tip="'.esc_attr__('Select pages for cookie scanning. Homepage is always included. Max 4 additional pages.', 'cookieadmin').'"></span></span>
		
		</div>
		<div class="cookieadmin-card-body">
		
		<div class="cookieadmin-sp-wrapper">
		<div style="width:50%">
			<div class="cookieadmin-sp-search-wrap" style="width:100%">
				<input type="text" class="cookieadmin-sp-search" id="cookieadmin_sp_search" placeholder="'.esc_attr__('Search and add pages...', 'cookieadmin').'" autocomplete="off">
				<div class="cookieadmin-sp-dropdown" id="cookieadmin_sp_dropdown"></div>
			</div>
			<div class="cookieadmin-sp-footer">';

		$user_count = 0;
		if(!empty($saved)){
			foreach($saved as $e){
				if($e !== 'home') $user_count++;
			}
		}

		echo '<span class="cookieadmin-sp-count">'.sprintf(esc_html__('%d page(s) selected', 'cookieadmin'), ($user_count + 1)).'</span>
			<span class="cookieadmin-sp-saved-text" id="cookieadmin_sp_saved_text"></span>
			<div class="cookieadmin-sp-footer-actions">
			<input type="button" class="cookieadmin-btn cookieadmin-btn-secondary cookieadmin-sp-reset" id="cookieadmin_sp_reset" value="'.esc_attr__('Reset', 'cookieadmin').'">
			</div>
			</div>
		</div>
		
		<div class="cookieadmin-sp-cards" id="cookieadmin_sp_cards" style="width:40%">';

		echo '<div class="cookieadmin-sp-card cookieadmin-sp-card--home" data-val="home">
		<div class="cookieadmin-sp-card-title">
		<span class="dashicons dashicons-admin-home"></span>
		<span class="cookieadmin-sp-card-name">'.esc_html($home_resolved['title']).'</span>
		<span class="dashicons dashicons-lock cookieadmin-sp-card-lock"></span>
		</div>
		</div>';

		if(!empty($saved)){
			foreach($saved as $entry){
				if($entry === 'home') continue;

				$resolved = self::resolve_entry($entry);
				if(empty($resolved)) continue;

				$type = $resolved['type'];
				$valid = $resolved['valid'];
				$title = $resolved['title'];
				$url = $resolved['url'];
				$icon = $resolved['icon'];

				$type_class = 'cookieadmin-sp-card--'.sanitize_html_class($type);
				$warning_class = !$valid ? ' cookieadmin-sp-card--warning' : '';

				$path = '';
				if($type === 'page' && !empty($url)){
					$path = str_replace($home_url, '', $url);
					if(empty($path)) $path = '/';
				}

				$data_val = esc_attr($entry);

				echo '<div class="cookieadmin-sp-card '.$type_class.$warning_class.'" data-val="'.$data_val.'">
				<div class="cookieadmin-sp-card-title">
				<span class="dashicons '.esc_attr($icon).'"></span>
				<span class="cookieadmin-sp-card-name">'.esc_html($title).'</span>';

				if(!$valid){
					echo '<span class="cookieadmin-sp-card-unavailable">'.esc_html__('No longer available', 'cookieadmin').'</span>';
				}

				echo '<span class="cookieadmin-sp-card-remove" title="'.esc_attr__('Remove', 'cookieadmin').'">&times;</span>
				</div>';

				if(!empty($path) && $path !== '/'){
					echo '<div class="cookieadmin-sp-card-url">'.esc_html($path).'</div>';
				}

				echo '</div>';
			}
		}

		echo '</div>
		</div>

		</div>
		</div>';
	}

	static function ajax_search_scan_content(){
		check_ajax_referer('cookieadmin_pro_admin_js_nonce', 'cookieadmin_pro_security');

		if(!current_user_can('administrator')){
			wp_send_json_error(['message' => __('Unauthorized', 'cookieadmin')]);
		}

		$search = !empty($_REQUEST['s']) ? sanitize_text_field(wp_unslash($_REQUEST['s'])) : '';
		$selected_ids = !empty($_REQUEST['selected_ids']) ? array_map('absint', (array)$_REQUEST['selected_ids']) : [];
		$home_url = untrailingslashit(home_url('/'));

		$results = [];

		if(strlen($search) >= 2){
			$page_ids = get_posts([
				'post_type'      => 'page',
				'post_status'    => 'publish',
				's'              => $search,
				'posts_per_page' => 20,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'fields'         => 'ids',
				'exclude'        => $selected_ids,
			]);

			foreach($page_ids as $id){
				$url = untrailingslashit(get_permalink($id));
				if($url === $home_url) continue;
				$results[] = [
					'id'    => $id,
					'title' => get_the_title($id),
					'url'   => $url,
					'type'  => 'page',
				];
			}
		}

		wp_send_json_success($results);
	}

	static function ajax_save_scan_pages(){
		check_ajax_referer('cookieadmin_pro_admin_js_nonce', 'cookieadmin_pro_security');

		if(!current_user_can('administrator')){
			wp_send_json_error(['message' => __('Unauthorized', 'cookieadmin')]);
		}

		$selections = !empty($_REQUEST['selections']) ? wp_unslash($_REQUEST['selections']) : [];
		if(!is_array($selections)) $selections = [];

		$saved = ['home'];
		$post_added = false;
		$product_added = false;
		$page_count = 0;

		foreach($selections as $sel){
			$val = sanitize_text_field($sel);

			if(is_numeric($val) && absint($val) > 0){
				$id = absint($val);
				if(get_post_status($id) === 'publish' && get_post_type($id) === 'page' && $page_count < 4){
					$saved[] = $id;
					$page_count++;
				}
			}elseif($val === 'latest_post' && !$post_added){
				$saved[] = 'latest_post';
				$post_added = true;
			}elseif($val === 'latest_product' && !$product_added && post_type_exists('product')){
				$saved[] = 'latest_product';
				$product_added = true;
			}

			if(($page_count + ($post_added ? 1 : 0) + ($product_added ? 1 : 0)) >= 4) break;
		}

		$user_count = count($saved) - 1;
		if($user_count > 4){
			$saved = array_slice($saved, 0, 5);
		}

		update_option('cookieadmin_scan_pages', $saved, true);

		wp_send_json_success([
			'message' => __('Scan pages saved', 'cookieadmin'),
			'data'    => $saved,
		]);
	}

	static function cookieadmin_get_site_urls($urls = [], $limit = 5){

		$saved = get_option('cookieadmin_scan_pages', []);
		$home_url = home_url('/');

		if(!empty($saved) && is_array($saved)){
			$urls[] = $home_url;

			foreach($saved as $val){
				if($val === 'home') continue; // Need to make sure home URL is always at first
				$resolved = self::resolve_entry($val);
				if(empty($resolved) || !$resolved['valid']) continue;
				$urls[] = $resolved['url'];
			}
		}

		$limit = max(1, (int) $limit);

		if(empty($urls)){
			$urls[] = $home_url;
		}

		// Normalize, dedupe, limit
		$urls = array_map('untrailingslashit', $urls);
		$urls = array_values(array_unique($urls));
		$urls = array_slice($urls, 0, $limit);
		
		// Persist canonical scan set
		update_option('cookieadmin_to_scan_urls', $urls);

		return apply_filters('cookieadmin_scan_urls', $urls);
	}
	
	// Saves cookieadmin_settings settings for the Pro features.
	static function save_settings($value, $old_value, $option){
		global $cookieadmin, $cookieadmin_settings;
		
		// If we are not saving any settings we simply return
		if(empty($_REQUEST['cookieadmin_save_settings'])){
			return $value;
		}
		
		check_admin_referer('cookieadmin_admin_nonce', 'cookieadmin_security');
	 
		if(!current_user_can('administrator')){
			wp_die(__('Sorry, but you do not have permissions to perform this action', 'cookieadmin'));
		}

		if(empty($value)){
			$value = [];
		}
		
		$cookieadmin_settings = $value;

		$cookieadmin_settings['respect_gpc'] = !empty($_REQUEST['cookieadmin_respect_gpc']);
		$cookieadmin_settings['gpc_message'] = !empty($_REQUEST['cookieadmin_gpc_message']) ? trim(sanitize_textarea_field(wp_unslash($_REQUEST['cookieadmin_gpc_message']))) : '';
		$cookieadmin_settings['gpc_override_warning'] = !empty($_REQUEST['cookieadmin_gpc_override_warning']) ? trim(sanitize_textarea_field(wp_unslash($_REQUEST['cookieadmin_gpc_override_warning']))) : '';
		
		if($cookieadmin_settings['gpc_message'] === $cookieadmin['gpc_message_default']){
			unset($cookieadmin_settings['gpc_message']);
		}
		
		if($cookieadmin_settings['gpc_override_warning'] === $cookieadmin['gpc_override_warning_default']){
			unset($cookieadmin_settings['gpc_override_warning']);
		}

		// Creating gpc.json if respect gpc was enabled.
		if(!empty($cookieadmin_settings['respect_gpc']) && (empty($old_value) || empty($old_value['respect_gpc']))){
			\CookieAdminPro\GPC::create_gpc_json_file();
		}
		
		// If GPC has been disable we will need to delete the gpc.json file
		if(empty($cookieadmin_settings['respect_gpc']) && !empty($old_value) && !empty($old_value['respect_gpc'])){
			$gpc_path = ABSPATH . '.well-known/gpc.json';

			if(file_exists($gpc_path)){
				unlink($gpc_path);
			}
		}

		return $cookieadmin_settings;
	}
	
	// Dashboard widget: Recent Consent Logs
	static function dashboard_consent_logs_widget(){
		global $wpdb;
		
		$table_name = esc_sql($wpdb->prefix . 'cookieadmin_consents');
		
		if(!\CookieAdminPro\Admin::cookieadmin_pro_table_exists($table_name)){
			return;
		}
		
		$logs = $wpdb->get_results(
			"SELECT consent_id, consent_status, country, consent_time, user_ip
			FROM {$table_name}
			ORDER BY id DESC
			LIMIT 10",
			ARRAY_A
		);
		
		if(empty($logs)){
			return;
		}
		
		echo '<div class="cookieadmin-card cookieadmin-mb-16">
			<div class="cookieadmin-card-header">
				<span class="cookieadmin-card-title"><span class="dashicons dashicons-visibility"></span>'.esc_html__('Recent Consent Logs', 'cookieadmin').'</span>
				<a class="cookieadmin-btn cookieadmin-btn-secondary cookieadmin-btn-sm" href="'.esc_url(admin_url('admin.php?page=cookieadmin-consent-logs')).'">'.esc_html__('View All Logs', 'cookieadmin').'</a>
			</div>
			<div class="cookieadmin-card-body">
			<table class="cookieadmin-consent-log-table">
			<thead>
				<tr>
					<th>'.esc_html__('Status', 'cookieadmin').'</th>
					<th>'.esc_html__('Country', 'cookieadmin').'</th>
					<th>'.esc_html__('Time', 'cookieadmin').'</th>
				</tr>
			</thead>
			<tbody>';
		
		foreach($logs as $log){
			$status_raw = '';
			$status_label = '';
			$status_class = '';
			
			if(!empty($log['consent_status'])){
				$_status = json_decode($log['consent_status'], true);
				if(is_array($_status) && !empty($_status[0])){
					$_status = $_status[0];
				}else{
					$_status = $log['consent_status'];
				}
				
				if($_status === 'accept'){
					$status_raw = 'accept';
					$status_label = __('Accepted', 'cookieadmin');
					$status_class = 'cookieadmin-success';
				}elseif($_status === 'reject'){
					$status_raw = 'reject';
					$status_label = __('Rejected', 'cookieadmin');
					$status_class = 'cookieadmin-danger';
				}else{
					$status_raw = 'partial';
					$status_label = __('Partial', 'cookieadmin');
					$status_class = 'cookieadmin-warning';
				}
			}
			
			$consent_time = !empty($log['consent_time']) ? human_time_diff($log['consent_time'], time()) . ' ' . __('ago', 'cookieadmin') : '-';
			$country = !empty($log['country']) ? esc_html($log['country']) : '-';
			
			echo '<tr>
				<td><span class="cookieadmin-badge '.esc_attr($status_class).'">'.esc_html($status_label).'</span></td>
				<td>'.esc_html($country).'</td>
				<td>'.esc_html($consent_time).'</td>
			</tr>';
		}
		
		echo '</tbody>
			</table>
			</div>
		</div>';
	}
}

