<?php
/*
* SITESEO
* https://siteseo.io
* (c) SITSEO Team
*/

namespace SiteSEOPro;

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

class QuickEdit{
	
	static function init(){
		add_action('admin_footer', '\SiteSEOPro\QuickEdit::admin_footer');
		add_filter('post_row_actions', '\SiteSEOPro\QuickEdit::inject_data_div', 10, 2);
		add_filter('page_row_actions', '\SiteSEOPro\QuickEdit::inject_data_div', 10, 2);
		add_action('save_post', '\SiteSEOPro\QuickEdit::save_custom_seo_fields');
		add_action('admin_enqueue_scripts', '\SiteSEOPro\QuickEdit::enqueue_scripts');
	}

	static function admin_footer(){
		global $pagenow;

		if($pagenow !== 'edit.php'){
			return;
		}

		$post_type = 'post';
		
		if(!empty($_GET['post_type'])){
			$post_type = sanitize_text_field(wp_unslash($_GET['post_type']));
		}

		if(empty($post_type) || !post_type_exists($post_type)){
			$post_type = 'post';
		}

		self::quick_edit_template($post_type);
	}

	static function inject_data_div($actions, $post){
		self::quick_edit_data($post->ID);
		return $actions;
	}

	static function quick_edit_template($post_type){

		echo '<div id="siteseo-pro-quick-edit-template" style="display:none;">
		<fieldset class="inline-edit-col-right inline-edit-siteseo-pro" style="clear:both; margin-top: 20px; width:100%; border-top: 1px solid #ddd; padding-top: 15px;">';
		// nonce
		echo wp_nonce_field('siteseo_quick_edit_nonce', 'siteseo_quick_edit_nonce_status', true, false);

		echo'<div style="display: flex; gap: 30px; margin-bottom: 20px;">
			<!-- Column 1 -->
			<div style="flex: 1;">
				<h4 style="margin:0 0 15px; padding:0; font-size:12px; font-weight:600; text-transform:uppercase; color:#646970;">'. esc_html__('SiteSEO Settings', 'siteseo-pro') .'</h4>
				
				<div style="margin-bottom: 15px;">
					<label style="display:block; margin-bottom:5px; color:#646970; font-size:12px; font-weight:600;">'. esc_html__('SEO Title', 'siteseo-pro').'</label>
					<input type="text" name="_siteseo_titles_title" value="" style="width:100%; box-sizing:border-box;">
				</div>
				
				<div style="margin-bottom: 15px;">
					<label style="display:block; margin-bottom:5px; color:#646970; font-size:12px; font-weight:600;">'. esc_html__('SEO Description', 'siteseo-pro') .'</label>
					<textarea name="_siteseo_titles_desc" rows="3" style="width:100%; resize:vertical; box-sizing:border-box;"></textarea>
				</div>
			</div>

			<!-- Column 2 -->
			<div style="flex: 1;">
				<h4 style="margin:0 0 15px; padding:0; font-size:12px; font-weight: normal; color:#646970;">'. esc_html__('Robots Meta', 'siteseo-pro') .'</h4>
				
				<div style="border: 1px solid #ddd; padding: 10px; background: #fff;">
					<label style="display:block; line-height:1.8;">
						<input type="checkbox" name="_siteseo_robots_index" value="yes" class="siteseo-robot-noindex">
						<span style="font-size:12px;">'. esc_html__('No Index', 'siteseo-pro') .'</span>
					</label>
					<label style="display:block; line-height:1.8;">
						<input type="checkbox" name="_siteseo_robots_follow" value="yes">
						<span style="font-size:12px;">'. esc_html__('No Follow', 'siteseo-pro') .'</span>
					</label>
					<label style="display:block; line-height:1.8;">
						<input type="checkbox" name="_siteseo_robots_archive" value="yes">
						<span style="font-size:12px;">'. esc_html__('No Archive', 'siteseo-pro') .'</span>
					</label>
					<label style="display:block; line-height:1.8;">
						<input type="checkbox" name="_siteseo_robots_imageindex" value="yes">
						<span style="font-size:12px;">'. esc_html__('No Image Index', 'siteseo-pro') .'</span>
					</label>
					<label style="display:block; line-height:1.8;">
						<input type="checkbox" name="_siteseo_robots_snippet" value="yes">
						<span style="font-size:12px;">'. esc_html__('No Snippet', 'siteseo-pro') .'</span>
					</label>
				</div>
			</div>

			<!-- Column 3 -->
			<div style="flex: 1;">
				<div style="margin-bottom: 15px;">
					<label style="display:block; margin-bottom:5px; color:#646970; font-size:12px; font-weight:600;">'. esc_html__('Primary Focus Keyword', 'siteseo-pro') .'</label>
					<input type="text" name="_siteseo_analysis_target_kw" value="" style="width:100%; box-sizing:border-box;">
				</div>
				
				<div style="margin-bottom: 15px;">
					<label style="display:block; margin-bottom:5px; color:#646970; font-size:12px; font-weight:600;">'. esc_html__('Canonical URL', 'siteseo-pro') .'</label>
					<input type="text" name="_siteseo_robots_canonical" value="" style="width:100%; box-sizing:border-box;">
				</div>';

		// category condition
		if($post_type === 'post' || is_object_in_taxonomy($post_type, 'category')){
			
			echo '<div style="margin-bottom: 15px;">
				<label style="display:block; margin-bottom:5px; color:#646970; font-size:12px; font-weight:600;">'. esc_html__('Primary Category', 'siteseo-pro') .'</label>
				<select name="_siteseo_robots_primary_cat" style="width:100%; box-sizing:border-box;">
					<option value="none">&mdash; '.esc_html__('Not Selected', 'siteseo-pro').' &mdash;</option>';

			$categories = get_categories(['hide_empty' => 0]);
			foreach($categories as $category){
				echo '<option value="'. esc_attr($category->term_id) .'">'. esc_html($category->name) .'</option>';
			}

			echo '</select>
			</div>';
		}

		echo '</div>
			</div>
			</fieldset>
		</div>';
	}

	static function quick_edit_data($post_id){
		$title = !empty(get_post_meta($post_id, '_siteseo_titles_title', true)) ? get_post_meta($post_id, '_siteseo_titles_title', true) : '';
		$desc = !empty(get_post_meta($post_id, '_siteseo_titles_desc', true)) ? get_post_meta($post_id, '_siteseo_titles_desc', true) : '';
		$canonical = !empty(get_post_meta($post_id, '_siteseo_robots_canonical', true)) ? get_post_meta($post_id, '_siteseo_robots_canonical', true) : '';
		$index = !empty(get_post_meta($post_id, '_siteseo_robots_index', true)) ? get_post_meta($post_id, '_siteseo_robots_index', true) : '';
		$follow = !empty(get_post_meta($post_id, '_siteseo_robots_follow', true)) ? get_post_meta($post_id, '_siteseo_robots_follow', true) : '';
		$imageindex = !empty(get_post_meta($post_id, '_siteseo_robots_imageindex', true)) ? get_post_meta($post_id, '_siteseo_robots_imageindex', true) : '';
		$archive = !empty(get_post_meta($post_id, '_siteseo_robots_archive', true)) ? get_post_meta($post_id, '_siteseo_robots_archive', true) : '';
		$snippet = !empty(get_post_meta($post_id, '_siteseo_robots_snippet', true)) ? get_post_meta($post_id, '_siteseo_robots_snippet', true) : '';
		$primary_cat = !empty(get_post_meta($post_id, '_siteseo_robots_primary_cat', true)) ? get_post_meta($post_id, '_siteseo_robots_primary_cat', true) : '';
		$target_kw = !empty(get_post_meta($post_id, '_siteseo_analysis_target_kw', true)) ? get_post_meta($post_id, '_siteseo_analysis_target_kw', true) : '' ;
		$permalink = !empty(get_permalink($post_id)) ? get_permalink($post_id) : '';

		echo '<div class="siteseo-quickedit-data" id="siteseo-quickedit-'.esc_attr($post_id).'" 
			data-title="'.esc_attr($title).'" 
			data-desc="'.esc_attr($desc).'" 
			data-canonical="'.esc_attr($canonical).'" 
			data-index="'.esc_attr($index).'" 
			data-follow="'.esc_attr($follow).'" 
			data-imageindex="'.esc_attr($imageindex).'" 
			data-archive="'.esc_attr($archive).'" 
			data-snippet="'.esc_attr($snippet).'" 
			data-target-kw="'.esc_attr($target_kw).'" 
			data-permalink="'.esc_attr($permalink).'" 
			data-primary-cat="'.esc_attr($primary_cat).'" style="display:none;"></div>';
	}

	static function enqueue_scripts($hook){
		if($hook !== 'edit.php'){
			return;
		}

		wp_enqueue_script('siteseo-pro-quick-edit', SITESEO_PRO_URL . 'assets/js/quickedit.js', ['jquery', 'inline-edit-post'], SITESEO_PRO_VERSION, true);
	}

	static function save_custom_seo_fields($post_id){
		if(!isset($_POST['siteseo_quick_edit_nonce_status']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['siteseo_quick_edit_nonce_status'])), 'siteseo_quick_edit_nonce')){
			return;
		}

		if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
			return;
		}

		if(!current_user_can('edit_post', $post_id)){
			return;
		}

		$fields = [
			'_siteseo_titles_title',
			'_siteseo_titles_desc',
			'_siteseo_robots_canonical',
			'_siteseo_robots_primary_cat',
			'_siteseo_analysis_target_kw'
		];

		foreach($fields as $field){
			if(isset($_POST[$field])){
				update_post_meta($post_id, $field, sanitize_text_field(wp_unslash($_POST[$field])));
			}
		}

		$checkbox_fields = [
			'_siteseo_robots_index',
			'_siteseo_robots_follow',
			'_siteseo_robots_imageindex',
			'_siteseo_robots_archive',
			'_siteseo_robots_snippet',
		];

		foreach($checkbox_fields as $c_field){
			if(isset($_POST[$c_field])){
				update_post_meta($post_id, $c_field, 'yes');
			} else {
				update_post_meta($post_id, $c_field, '');
			}
		}
	}
}