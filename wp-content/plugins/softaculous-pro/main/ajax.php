<?php

	if(!defined('ABSPATH')){
		die('HACKING ATTEMPT');
	}

	// Ajax Hooks
	add_action('wp_ajax_softaculous_pro_wp_ajax', 'softaculous_pro_wp_ajax');
	add_action('wp_ajax_softaculous_pro_dismiss_expired_licenses', 'softaculous_pro_dismiss_expiry_notice');
	add_action('wp_ajax_softaculous_pro_close_update_notice', 'softaculous_pro_close_update_notice');

	// Template Installation related ajax calls
	add_action('wp_ajax_softaculous_pro_template_info', 'softaculous_pro_ajax_template_info');
	add_action('wp_ajax_softaculous_pro_start_install_template', 'softaculous_pro_ajax_start_install_template');
	add_action('wp_ajax_softaculous_pro_selected_plugin_install', 'softaculous_pro_ajax_selected_plugin');
	add_action('wp_ajax_softaculous_pro_download_template', 'softaculous_pro_ajax_download_template');
	add_action('wp_ajax_softaculous_pro_import_template', 'softaculous_pro_ajax_import_template');

	add_action('wp_ajax_softaculous_pro_generate_post', 'softaculous_pro_ajax_generate_post');
	add_action('wp_ajax_softaculous_switch_template_mode', 'softaculous_pro_switch_templated_mode');
	add_action('wp_ajax_softaculous_pro_ai_autocomplete', 'softaculous_pro_ai_autocomplete');
	add_action('wp_ajax_softaculous_pro_search_images', 'softaculous_pro_search_images');
	add_action('wp_ajax_softaculous_pro_cache_iframe_urls', 'softaculous_pro_cache_iframe_urls');
	add_action('wp_ajax_softaculous_pro_stream_iframes', 'softaculous_pro_stream_iframes');

	// Setup information
	add_action('wp_ajax_softaculous_pro_setup_info', 'softaculous_pro_save_setup_info');
	add_action('wp_ajax_softaculous_pro_ai_description', 'softaculous_pro_ai_description');
	add_action('wp_ajax_softaculous_pro_get_setup_info', 'softaculous_pro_get_setup_info');

	// dismiss
	add_action('wp_ajax_softaculous_pro_onboarding_dismiss', 'softaculous_pro_onboarding_dismiss');
	add_action('wp_ajax_softaculous_pro_get_progress', 'softaculous_pro_get_progress');
	
// Get the template info from our servers
function softaculous_pro_ajax_template_info(){

	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	include_once(dirname(__FILE__).'/onboarding.php');
	$data = [];

	if(isset($_REQUEST['slug'])){		
		$resp = wp_remote_get(softaculous_pro_pfx_api_url().'template-info.php?slug='.$_REQUEST['slug'], array('timeout' => 90));
	
		// Is the response valid ?
		if ( !is_wp_error( $resp ) && ( $resp['response']['code'] == 200 ) ){		
			$data = json_decode($resp['body'], true);
		}
	}
	
	$setup_info = softaculous_pro_get_option_setup_info();
	$setup_info = !empty($setup_info) ? $setup_info : array();
	$setup_info['theme_slug'] = $_REQUEST['slug'];

	update_option('softaculous_pro_setup_info',$setup_info);
	
	softaculous_pro_ajax_output($data);
	
}

// Start the installation of the template
function softaculous_pro_ajax_start_install_template(){
	
	global $softaculous_pro;
	
	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	include_once(dirname(__FILE__).'/onboarding.php');
	softaculous_pro_reset_progress();
	set_time_limit(300);
	
	// Handling Access through FTP
	ob_start();
	// Check if FTP is required
	$have_credentials = request_filesystem_credentials('');

	softaculous_pro_set_progress(__('Checking file directory...'), 5 , ['success' => true]);
	
	if(false === $have_credentials){
		$form_html = ob_get_clean();
		$ftp_modal = '<div id="request-filesystem-credentials-dialog" class="notification-dialog-wrap request-filesystem-credentials-dialog">
		<div class="notification-dialog-background"></div>
		<div class="notification-dialog" role="dialog" aria-labelledby="request-filesystem-credentials-title" tabindex="0">
		<div class="request-filesystem-credentials-dialog-content">'. $form_html . '</div></div></div>';

		wp_send_json_error(['form' => $ftp_modal]);
	}

	ob_end_clean(); // Just in case there was any output till now it will be cleaned.

	$data = [];
	
	//pagelayer_print($_POST);die();
	$license = softaculous_pro_optPOST('softaculous_pro_license');
	
	// Check if its a valid license
	if(!empty($license)){
	
		$resp = wp_remote_get(softaculous_pro_api_url().'license.php?license='.$license.'&url='.rawurlencode(site_url()), array('timeout' => 30));
	
		if(is_array($resp)){
			$json = json_decode($resp['body'], true);
			//print_r($json);
		}else{
		
			$data['error']['resp_invalid'] = __('The response from the server was malformed. Please try again in sometime !', 'softaculous-pro').var_export($resp, true);
			softaculous_pro_ajax_output_xmlwrap($data);
			
		}
	
		// Save the License
		if(empty($json['license'])){
		
			$data['error']['lic_invalid'] = __('The license key is invalid', 'softaculous-pro');
			softaculous_pro_ajax_output_xmlwrap($data);
			
		}else{
			
			update_option('softaculous_pro_license', $json);
	
			// Load license
			spro_load_license();
			
		}
		
	}
	
	// Load templates
	$softaculous_pro['templates'] = softaculous_pro_get_templates_list();
	
	$slug = softaculous_pro_optPOST('theme');
	
	if(!defined('PAGELAYER_VERSION')){
		
		$res = spro_install_required_plugin('pagelayer', array('plugin_init' => 'pagelayer/pagelayer.php'));
		
		if(empty($res['success'])){
			$data['error']['pl_req'] = __('Pagelayer is required to use the templates !', 'softaculous-pro');
			softaculous_pro_ajax_output_xmlwrap($data);
		}
	}
	
	if(!defined('PFX_VERSION')){
		
		$res = spro_install_required_plugin('popularfx-templates', array('plugin_init' => 'popularfx-templates/popularfx-templates.php', 'plugin_download_url' => softaculous_pro_api_url(0, 'popularfx').'update2.php?give=1'));
		
		if(empty($res['success'])){
			$data['error']['pl_req'] = __('PopularFX plugin is required to use the templates !', 'softaculous-pro');
			softaculous_pro_ajax_output_xmlwrap($data);
		}
	}
	
	if(!function_exists('popularfx_templates_dir')){

		$res = spro_install_required_theme('popularfx');
		
		if(empty($res['success'])){
			$data['error']['pfx_req'] = __('PopularFX theme is required to use the templates !', 'softaculous-pro');
			softaculous_pro_ajax_output_xmlwrap($data);
		}
		
	}
	
	if(empty($softaculous_pro['templates']['list'][$slug])){
		$data['error']['template_invalid'] = __('The template you submitted is invalid !', 'softaculous-pro');
		softaculous_pro_ajax_output_xmlwrap($data);
	}
	
	$template = $softaculous_pro['templates']['list'][$slug];
	
	// Do we have the req PL version ?
	if(!empty($template['pl_ver']) && version_compare(PAGELAYER_VERSION, $template['pl_ver'], '<')){
		$data['error']['pl_ver'] = sprintf(__('Your Pagelayer version is %1$s while the template requires Pagelayer version higher than or equal to %2$s ', 'softaculous-pro'), PAGELAYER_VERSION, $template['pl_ver']);
		softaculous_pro_ajax_output_xmlwrap($data);
	}
	
	// Do we have the req PL version ?
	if(version_compare(PAGELAYER_VERSION, '1.8.9', '<')){
		$data['error']['pl_ver'] = sprintf(__('Your Pagelayer version is %1$s while the onboarding requires Pagelayer version higher than or equal to 1.8.9', 'softaculous-pro'), PAGELAYER_VERSION);
		softaculous_pro_ajax_output_xmlwrap($data);
	}
	
	// Do we have the req PFX Plugin version ?
	if(!empty($template['pfx_ver']) && version_compare(PFX_VERSION, $template['pfx_ver'], '<')){
		$data['error']['pfx_ver'] = sprintf(__('Your PopularFX Plugin version is %1$s while the template requires PopularFX version higher than or equal to %2$s', 'softaculous-pro'), PFX_VERSION, $template['pfx_ver']);
		softaculous_pro_ajax_output_xmlwrap($data);
	}
	
	// Is it a pro template ?
	if($template['type'] > 1 && empty($softaculous_pro['license']['active'])){
		$data['error']['template_pro'] = sprintf(__('The selected template is a Pro template and you have a free or expired license. Please enter your license key %1$shere%2$s.', 'softaculous-pro'), 
			'<a href="'.admin_url('admin.php?page=assistant&act=license').'" target="_blank" style="color:blue;">',
			'</a>'
			);
		softaculous_pro_ajax_output_xmlwrap($data);
	}
	
	$do_we_have_pro = defined('PAGELAYER_PREMIUM');
	
	// Do we need to install Pagelayer or Pagelayer PRO ?
	if(!function_exists('pagelayer_theme_import_notices') || (empty($do_we_have_pro) && $template['type'] > 1)){
		if($template['type'] > 1){
			$download_url = SOFTACULOUS_PRO_PAGELAYER_API.'download.php?version=latest&license='.$softaculous_pro['license']['license'].'&url='.rawurlencode(site_url());
			$installed = spro_install_required_plugin('pagelayer-pro', array('plugin_init' => 'pagelayer-pro/pagelayer-pro.php', 'plugin_download_url' => $download_url));
		}else{
			$installed = spro_install_required_plugin('pagelayer', array('plugin_init' => 'pagelayer/pagelayer.php'));
		}
		
		// Did we fail to install ?
		if(is_wp_error($installed) || empty($installed)){
			$install_url = admin_url('admin.php?page=softaculous_pro_install_pagelayer&license=').@$softaculous_pro['license']['license'];
			$data['error']['pagelayer'] = sprintf(__('There was an error in installing Pagelayer which is required by this template. Please install Pagelayer manually by clicking %1$shere%2$s and then install the template !', 'softaculous-pro'), '<a href="%1$s" target="_blank">'.$install_url, '</a>');
			if(!empty($installed->errors)){
				$data['error']['pagelayer_logs'] = var_export($installed->errors, true);
			}
			softaculous_pro_ajax_output_xmlwrap($data);
		}
		
	}
	
	// Lets notify to download
	// $data['download'] = 1;
	$data['sel_plugin'] = 1;
	
	softaculous_pro_ajax_output_xmlwrap($data);
	
}


function softaculous_pro_ajax_selected_plugin(){
	
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	include_once(dirname(__FILE__).'/onboarding.php');
	if ( ! current_user_can( 'edit_posts' ) ) {	
		wp_send_json_error();
	}
	
	$results = array();
	$options = softaculous_pro_get_option_setup_info();
	$sel_features = $options['features'];

	// Dynamic progress calculation for each plugin 
	$min_progress = 11;
	$max_progress = 45;
	$current_progress = $min_progress;
	if(!empty($sel_features)){
		$feature_list = spro_get_features_list();

		// Count total plugins to be installed
		$total_plugins = 0;
		foreach ($feature_list as $slug => $features) {
			if (in_array($slug, $sel_features)) {
				$total_plugins += count($features['plugin']);
			}
		}
		
		// Avoid division by zero
		$progress_step = ($total_plugins > 0) ? floor(($max_progress - $min_progress) / $total_plugins) : 0;

		foreach($feature_list as $slug => $features){
			if (in_array($slug, $sel_features)) {
				foreach($features['plugin'] as $plugin_slug => $plugin_data){	
					softaculous_pro_set_progress(_('Enabling Feature').' : '.$features['name'], $current_progress, ['success' => true]);
					$res = spro_install_required_plugin($plugin_slug, $plugin_data);
					$results[] = array(
						'plugin_slug' => $plugin_slug,
						'status' => $res,
					);
					$current_progress += $progress_step;
					if ($current_progress > $max_progress) {
						$current_progress = $max_progress;
					}
					sleep(1);
				}
			}
		}
		foreach ($results as $item) {
			if (isset($item['status']['error'])) {
				$data['failed_plugin'][$item['plugin_slug']] = $item['status']['error'];
			}
		}
		$data['download'] = 1;
		softaculous_pro_set_progress(__('All features have been installed successfully.'), $max_progress, ['success' => true]);
		softaculous_pro_ajax_output($data);
	}
}

// Download template
function softaculous_pro_ajax_download_template(){
	
	global $softaculous_pro;
	
	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	include_once(dirname(__FILE__).'/onboarding.php');
	softaculous_pro_set_progress(__('Downloading the template...'), 50 , ['success' => true]);
	$slug = softaculous_pro_optPOST('theme');
	
	// Do the download
	$data = softaculous_pro_download_template($slug);
	
	// Any error ?
	if(!empty($data['error'])){
		softaculous_pro_ajax_output($data);
	}
	softaculous_pro_set_progress(__('Template downloaded successfully...'), 70 , ['success' => true]);
	
	// Lets import then
	$data['import'] = 1;
	
	softaculous_pro_ajax_output($data);
	
}


// Import template
function softaculous_pro_ajax_import_template(){ 
	
	global $softaculous_pro, $pl_error;

	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	include_once(dirname(__FILE__).'/onboarding.php');
	softaculous_pro_set_progress(__('Importing template content...'), 75 , ['success' => true]);
	$slug = softaculous_pro_optPOST('theme');
	$to_import = softaculous_pro_optPOST('to_import');
	$_POST['set_home_page'] = 1;
	
	if(!empty($to_import)){
		$to_import[] = 'blog';
		$items = ['page' => $to_import];
	}else{
		$items = [];
	}
	
	// Import the template
	$data = softaculous_pro_import_template($slug, $items);
	softaculous_pro_set_progress(__('Template installed successfully...'), 100, ['success' => true]);
	softaculous_pro_ajax_output($data);
	
}


// For ai onboarding
function softaculous_pro_save_setup_info(){
	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	include_once(dirname(__FILE__).'/onboarding.php');
	
	if(current_user_can('activate_theme')){
		echo json_encode(['error' => 'You are not allowed here!']);
		wp_die();
	}
	
	//$step = $_POST['step'];
	$post_data = wp_unslash($_POST['data']);
	$setup_info = softaculous_pro_get_option_setup_info();
	$load_templates = false;
	$cacheTemplates = false;
	
	$setup_info = !empty($setup_info) ? $setup_info : array();

	if(empty($post_data)){
		echo json_encode(['error' => 'Post data not found!']);
		wp_die();
	}
	
	// wp_send_json($post_data);
	if(isset($post_data['business_title'])){
		$business_title = sanitize_text_field($post_data['business_lang']);
		update_option('blogname', $post_data['business_title']);
	}

	// Choose the lang
	if(isset($post_data['business_lang'])){
		$business_lang = sanitize_text_field($post_data['business_lang']);
		// update_option('WPLANG', 'en');
		$setup_info['business_lang'] = $business_lang;
	}

	if(!empty($post_data['business_type'])){
		$setup_info['business_type'] = sanitize_text_field($post_data['business_type']);
	}

	if(isset($post_data['mode'])){
		if(in_array($post_data['mode'], ['ai', 'manual'])){
			$setup_info['mode'] = $post_data['mode'];
		}else{
			$setup_info['mode'] = 'manual';
		}
	}

	if (isset($setup_info['mode']) && $setup_info['mode'] == 'ai' && !empty($post_data['business_description'])) { 
		$business_description = $post_data['business_description']; // todo: senitize Array
		$load_templates = true;
		$cacheTemplates = ($setup_info['mode'] == 'ai' ? true : false);
		
		if(!is_array($business_description)) {
			echo json_encode(['error' => 'Description required.']);
			wp_die();
		}else{
			$setup_info['business_description'] = $business_description;
		
			// This will make sure that new added will be set to newly added index;
			$setup_info['active_desc'] = isset($post_data['active_desc']) ? $post_data['active_desc'] : count($setup_info['business_description']) - 1;
			
			// TODO check if provious description is same then not change the tags
			$images_suggestions = softaculous_pro_ai_image_tags_suggestions($business_description[$setup_info['active_desc']]);
		
			if(!empty($images_suggestions)){
				$setup_info['image_suggestions'] = $images_suggestions;
			}
		}
		
		if (strlen($setup_info['business_description'][$setup_info['active_desc']]) < 200) {
			echo json_encode(['error' => __('It seems that the description is insufficient. Please provide a brief overview of your site before proceeding further. You can write your own description, or let AI generate one for you.')]);
			wp_die();
		}
		
	}

	if(isset($post_data['business_email'])){
		update_option('pagelayer_cf_from_email', sanitize_email($post_data['business_email']));
	}
	if(isset($post_data['business_phone'])){
		update_option('pagelayer-phone', sanitize_text_field($post_data['business_phone']));
	}
	if(isset($post_data['business_address'])){
		update_option('pagelayer-address', sanitize_text_field($post_data['business_address']));
	}
	
	// Social Links
	if(isset($post_data['business_social_facebook'])){
		update_option('pagelayer-facebook-url', sanitize_url($post_data['business_social_facebook']));
	}
	if(isset($post_data['business_social_twitter'])){
		update_option('pagelayer-twitter-url', sanitize_url($post_data['business_social_twitter']));
	}
	if(isset($post_data['business_social_instagram'])){
		update_option('pagelayer-instagram-url', sanitize_url($post_data['business_social_instagram']));
	}
	if(isset($post_data['business_social_linkedin'])){
		update_option('pagelayer-linkedin-url', sanitize_url($post_data['business_social_linkedin']));
	}
	if(isset($post_data['business_social_youtube'])){
		update_option('pagelayer-youtube-url', sanitize_url($post_data['business_social_youtube']));
	}

	if(!empty($post_data['features'])){
		$setup_info['features'] = $post_data['features'];
	}
	
	// Pagelayer plugin is compulsory for import
	if(!empty($post_data['step']) && $post_data['step'] == 'features' && (empty($post_data['features']) || !in_array('pagelayer', $post_data['features']))){
		echo json_encode(['error' => 'The Page Builder plugin is required to import themes and content !']);
		wp_die();
	}

	if (isset($post_data['selected_images'])) {
		$load_templates = true;
		
		$setup_info['selected_images'] = is_array($post_data['selected_images']) ? $post_data['selected_images'] : array($post_data['selected_images']);
		
		if(count($setup_info['selected_images']) < 10){
			echo json_encode(['error' => __('Please select at least 10 images to help us design a better website.')]);
			wp_die();
		}
		
	}
	
	if($load_templates){
		$active_desc_id = $setup_info['active_desc'];
		$selected_desc = $setup_info['business_description'][$active_desc_id];
		
		// If an image is already selected we skip the images while caching templates
		$images =  $cacheTemplates ? [] : $setup_info['selected_images'];
		
		$generate_theme_pid = softaculous_pro_ai_save_data($selected_desc, $images);

		if(is_wp_error($generate_theme_pid)){
			echo json_encode(['error' => $generate_theme_pid->get_error_message()]);
			wp_die();
		}
		
		if(empty($generate_theme_pid['pid'])){
			echo json_encode(['error' => 'Unable to get preview Id!', 'response' => $generate_theme_pid]);
			wp_die();
		}
		
		$setup_info['theme_pid'] = $generate_theme_pid['pid'];
	}
	
	update_option('softaculous_pro_setup_info', $setup_info);
	
	// load first 12 Templates
	if($cacheTemplates && !empty($setup_info['theme_pid'])){
		$urls = softaculous_pro_cache_templates($setup_info['theme_pid'], 1);
		$setup_info['preview_urls'] = $urls;
	}
	
	echo json_encode([
		'success' => 'done',
		'setup_info' => $setup_info,
	]);
	
	wp_die();

}

function softaculous_pro_ajax_generate_post() {

	// Validate AJAX request
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	
	if(!current_user_can( 'edit_posts' )){
		wp_send_json_error(__('Permission denied.'));
	}
	
	// Get and sanitize input
	$data = isset($_REQUEST['data']) && is_array($_REQUEST['data']) ? $_REQUEST['data'] : [];
	$site_name = isset($data['site_name']) ? sanitize_text_field($data['site_name']) : '';
	$user_context = isset($data['description']) ? sanitize_textarea_field($data['description']) : $site_name;

	// Fallback if no context
	if (empty($site_name)) {
		wp_send_json_error(__('Site name is required to generate content.'));
	}

	$ai_data = [
		'request_type' => 'builder_create_post',
		'user_desc' => $user_context,
		'business_title' => $site_name
	];

	$res = softaculous_pro_generate_ai_content($ai_data);

	// Handle empty or invalid response
	if (empty($res) || !is_array($res)) {
		wp_send_json_error(__('Unable to generate post. Please try again later.'));
	}

	if (!empty($res['error'])) {
		wp_send_json_error($res['error']);
	}

	if (empty($res['title']) || empty($res['content'])) {
		wp_send_json_error(__('Generated content is incomplete. No title or content found.'));
	}

	// Prepare post array
	$post = [
		'post_title'   => wp_strip_all_tags($res['title']),
		'post_content' => wp_kses_post($res['content']),
		'post_status'  => 'publish'
	];

	$post_id = wp_insert_post($post);

	if (is_wp_error($post_id)) {
		wp_send_json_error(__('Failed to insert post into database.'));
	}

	wp_send_json_success(__('Post successfully created!'));
}

function softaculous_pro_switch_templated_mode(){

	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	
	$mode = isset($_POST['mode']) && $_POST['mode'] === 'ai' ? true : false;
	$ai_setup_info = softaculous_pro_get_option_setup_info();

	if(empty($ai_setup_info ) || !is_array($ai_setup_info)){
		$ai_setup_info = array();
	}

	// Update oposite value of ai
	$ai_setup_info['mode'] = !$mode;

	update_option('softaculous_pro_setup_info', $ai_setup_info);
	wp_die();
}

// Category Autocomplete
function softaculous_pro_ai_autocomplete(){
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');

	$string = !empty($_REQUEST['val']) ? sanitize_text_field($_REQUEST['val']) : '';

	$data = [
		'request_type' => 'builder_autocomplete',
		'string' => $string,
	];

	$res = softaculous_pro_generate_ai_content($data);
	echo json_encode($res);
	wp_die();
}

// For ai onboarding
function softaculous_pro_search_images(){
	global $softaculous_pro;
	
	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	
	$api_url = SOFTACULOUS_PRO_AI_DEMO . 'wp-json/softwpai/v1/get/images';
	$license = ( isset($softaculous_pro['license']) && isset($softaculous_pro['license']['license'] )
		? $softaculous_pro['license']['license'] : ''
	);
	
	$query       = isset( $_REQUEST['query'] ) ? sanitize_text_field( $_REQUEST['query'] ) : '';
	$per_page    = isset( $_REQUEST['per_page'] ) ? absint( $_REQUEST['per_page'] ) : 15;
	$page        = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
	
	$args = [
		'body' => [
			'query' => $query, 
			'per_page' => $per_page,
			'page' => $page,
			'license' => $license,
			'url' => site_url()
		],
		'timeout' => 30,
		'method'  => 'POST',
	];

	$response = wp_remote_post($api_url, $args);
	
	// Remote post failed?
	if (is_wp_error($response)) {
		
		// Try again
		$response = wp_remote_post($api_url, $args);
		
		// If still failed, give error
		if (is_wp_error($response)) {
			echo json_encode(['error' => 'Unable to get images for some reason', 'response' => $response]);
			wp_die();
		}
	}
	
	$body = wp_remote_retrieve_body($response);
	$images = json_decode($body, true);
	
	if(isset($images['code'])){
		echo json_encode([
			'error' => isset($images['message']) ? $images['message'] : 'Unknown error occurred',
			'code' => $images['code'],
			'data' => isset($images['data']) ? $images['data'] : array()
		]);
		wp_die();
	}
	
	echo json_encode([
		'success' => 'done',
		'images' => $images
	]);
		
	wp_die();
}

// This function will call AI BUILDER and actually cause AI Builder to cache it.
// This function itself will be called via AJAX for now
function softaculous_pro_cache_iframe_urls(){
	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	include_once(dirname(__FILE__).'/onboarding.php');
	if(current_user_can('activate_theme')){
		echo json_encode(['error' => 'You are not allowed here!']);
		wp_die();
	}
	
	/* Example, also support for pages
		$urls = array{
			'template_name/page' => 'template_name/URL'
		}
	*/
	$urls = isset($_REQUEST['urls']) && is_array($_REQUEST['urls']) ? $_REQUEST['urls'] : [];
	if(empty($urls)) {
		echo json_encode(['error' => 'No URLs provided']);
		wp_die();
	}
	
	$setup_info = softaculous_pro_get_option_setup_info();
	
	if(empty($setup_info['mode']) || $setup_info['mode'] !== 'ai'){
		echo json_encode(['error' => 'AI Mode is not enabled !']);
		wp_die();
	}

	ignore_user_abort(true);

	$timeout = 30;	
	$multi = curl_multi_init();
	$channels = [];

	foreach ($urls as $i => $url) {
		
		// If URL not provided
		// Also support for pages
		if(!wp_http_validate_url($url)){
			$page = ($url != $i) ? $i : 'home';
			$url = softaculous_pro_ai_demo_url($url, '', $page);
		}
		
		$url .= (strpos($url, '?') === false ? '?' : '&') . 'ignore_user_abort=1';
		
		$clean_url = esc_url_raw($url);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_multi_add_handle($multi, $ch);
		$channels[$i] = $ch;
	}

	$running = null;
	do {
		curl_multi_exec($multi, $running);
		curl_multi_select($multi);
	} while ($running > 0);

	// Optional: Collect results
	$responses = [];
	foreach ($channels as $i => $ch) {
		$responses[$i] = curl_multi_getcontent($ch);
		curl_multi_remove_handle($multi, $ch);
		curl_close($ch);
	}

	curl_multi_close($multi);
	
	echo json_encode($responses);
	wp_die();
}

// load demo blobs
function softaculous_pro_stream_iframes() {
	
	$setup_info = softaculous_pro_get_option_setup_info();
	include_once(dirname(__FILE__).'/onboarding.php');
	if(empty($setup_info['mode']) || $setup_info['mode'] !== 'ai'){
		echo json_encode(['error' => 'AI Mode is not enabled !']);
		wp_die();
	}
	
	set_time_limit(0);
	
	// Disable all output buffering that might break chunked streaming
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	ob_implicit_flush(true);

	// Security checks
	if (!isset($_POST['softaculous_pro_nonce']) || !wp_verify_nonce($_POST['softaculous_pro_nonce'], 'softaculous_pro_ajax')) {
		echo "DATA::" . json_encode(['error' => 'Invalid nonce']) . "\n";
		exit;
	}

	if (!current_user_can('activate_plugins')) {
		echo "DATA::" . json_encode(['error' => 'You are not allowed here!']) . "\n";
		exit;
	}
	
	/* Example
		$urls = array{
			'template_name/page' => 'template_name/URL'
		}
	*/
	// Parse and validate URLs
	$urls = isset($_POST['urls']) ? json_decode(stripslashes($_POST['urls']), true) : [];

	if (!is_array($urls) || empty($urls)) {
		echo "DATA::" . json_encode(['error' => 'No URLs provided']) . "\n";
		exit;
	}

	$timeout = 300;
	$multi = curl_multi_init();
	$handles = [];

	foreach ($urls as $index => $url) {
		
		// If URL not provided
		// Also support for page
		if(!wp_http_validate_url($url)){
			$page = ($url != $index) ? $index : 'home';
			$url = softaculous_pro_ai_demo_url($url, '', $page);
		}
		
		$clean_url = esc_url_raw($url);
		$ch = curl_init($clean_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_multi_add_handle($multi, $ch);
		$handles[$index] = $ch;
		$urls[$index] = $clean_url;
	}

	$running = null;

	do {
		curl_multi_exec($multi, $running);
		while ($info = curl_multi_info_read($multi)) {
			$handle = $info['handle'];
			$index = array_search($handle, $handles);
			$content = curl_multi_getcontent($handle);
			$error = curl_error($handle);
		
			$parts = parse_url($urls[$index]);
			parse_str($parts['query'], $query);
			$tpage = !empty($query['tpage']) ? $query['tpage'] : 'home';
			$slug = !empty($query['template_preview']) ? $query['template_preview'] : $index ;

			$result = [
				'index' => $index,
				'url' => $urls[$index],
				'page' => $tpage,
				'slug' => $slug,
				'success' => $error === '',
				'html' => $error ? "<h1>Failed to load iframe: $error</h1>" : $content
			];

			echo "DATA::" . json_encode($result) . "\n";
			flush();
			ob_flush();

			curl_multi_remove_handle($multi, $handle);
			curl_close($handle);
		}
		usleep(100000);
	} while ($running > 0);

	curl_multi_close($multi);
	exit;
}

// For generate description
function softaculous_pro_ai_description(){

	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	
	$data = isset($_REQUEST['data']) && is_array($_REQUEST['data']) ? $_REQUEST['data'] : [];
	$title = !empty($data['site_name']) ? sanitize_text_field($data['site_name']) : '';
	$category = !empty($data['site_category']) ? sanitize_text_field($data['site_category']) : '';
	$language = !empty($data['site_language']) ? sanitize_text_field($data['site_language']) : 'English';
	$description = !empty($data['description']) ? sanitize_textarea_field($data['description']) : $title;
	
	$ai_data = [
		'request_type' => 'builder_desc',
		'business_language' => $language,
		'user_desc' => $description,
		'business_category' => $category,
		'business_title' => $title,
	];

	$res = softaculous_pro_generate_ai_content($ai_data);
	
	echo json_encode($res);
	wp_die();
}

function softaculous_pro_get_setup_info(){

	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');

	$slug = isset($_POST['slug']) ? sanitize_text_field($_POST['slug']) : '';
	$setup_info = softaculous_pro_get_option_setup_info();

	if(isset($setup_info) && !empty($setup_info[$slug])){
		wp_send_json_success($setup_info[$slug]);
	} else {
		wp_send_json_error(__('Setup information not found.', 'softaculous-pro'));
	}
}

// Get the template info from our servers
function softaculous_pro_onboarding_dismiss(){

	// Some AJAX security
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');
	include_once(dirname(__FILE__).'/onboarding.php');
	
	if(isset($_REQUEST['dismiss'])){
		update_option('softaculous_pro_onboarding_dismiss', time());
	}
	
	$data['done'] = 1;

	softaculous_pro_ajax_output($data);
	
}

function softaculous_pro_get_progress() {
	check_ajax_referer('softaculous_pro_ajax', 'softaculous_pro_nonce');

	$progress = get_option('softaculous_pro_onboarding_progress', []);

	if (empty($progress)) {
		wp_send_json_error(['message' => 'No progress available']);
	}

	wp_send_json_success(['progress' => $progress]);
}


?>