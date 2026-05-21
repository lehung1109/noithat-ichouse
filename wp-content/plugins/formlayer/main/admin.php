<?php

namespace FormLayer;

if(!defined('ABSPATH')){
	exit;
}

class Admin{

	static function init(){
		add_action('admin_menu', '\FormLayer\Admin::register_menu');
		// enqueue 
		add_action('admin_enqueue_scripts', '\FormLayer\Admin::enqueue_admin_assets');
	}

	static function register_menu(){
		if(defined('FORMLAYER_PRO_VERSION')){
			$unread = \FormLayerPro\Util::get_unread_count();
			$count_html = $unread > 0 ? ' <span class="update-plugins count-' . $unread . '"><span class="plugin-count">' . $unread . '</span></span>' : '';
		} else {
			$count_html = '';
		}
		
		add_menu_page(__('FormLayer', 'formlayer') ,__( 'FormLayer', 'formlayer' ) . $count_html, 'manage_options', 'formlayer', '\FormLayer\Admin::render_admin_page', FORMLAYER_ASSETS_URL.'/img/formlayer-logo-30.png');

		add_submenu_page('formlayer', __( 'Dashboard', 'formlayer' ), __( 'Dashboard', 'formlayer' ), 'manage_options', 'formlayer' ,'\FormLayer\Admin::render_admin_page');

	}

	static function render_admin_page(){
		\FormLayer\Settings\UI::header();
		
		echo '<div id="formlayer-tab-forms" class="formlayer-tab-content">';
		\FormLayer\Settings\UI::forms_tab();
		echo '</div>';

		// Form Builder tab
		echo '<div id="formlayer-tab-formbuilder" class="formlayer-tab-content" style="display:none;">';
		\FormLayer\Settings\UI::formbuilder_tab();
		echo '</div>';
		echo '<div id="formlayer-tab-entries" class="formlayer-tab-content" style="display:none;">';
		\FormLayer\Settings\UI::entries_tab();
		echo '</div>';

		echo '<div id="formlayer-tab-integrations" class="formlayer-tab-content" style="display:none;">';
		\FormLayer\Settings\UI::integrations_tab();
		echo '</div>';

		echo '<div id="formlayer-tab-settings" class="formlayer-tab-content" style="display:none;">';
		\FormLayer\Settings\UI::settings_tab();
		echo '</div>';
			
		echo'<div id="formlayer-tab-support" class="formlayer-tab-content" style="display:none;">';
		\FormLayer\Settings\UI::support_tab();
		echo'</div>';

		if(defined('FORMLAYER_PRO_VERSION')){
			echo '<div id="formlayer-tab-license" class="formlayer-tab-content" style="display:none;">';
			do_action('formlayer_render_license_page');
			echo '</div>';
		}

		\FormLayer\Settings\UI::footer();
	}

	static function enqueue_admin_assets($hook){
		if(strpos( $hook, 'formlayer' ) === false){
			return;
		}

		wp_enqueue_style('formlayer-admin-css', FORMLAYER_PLUGIN_URL . 'assets/css/admin.css', [],FORMLAYER_VERSION);

		wp_enqueue_script('formlayer-admin-js', FORMLAYER_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], FORMLAYER_VERSION, true);

		$categories = [
			['id' => 'general', 'label' => __('General Fields', 'formlayer'), 'open' => true],
			['id' => 'advanced', 'label' => __('Advanced Fields', 'formlayer'), 'open' => false]
		];

		$field_types = [
			['type' => 'name', 'label' => 'Name Fields', 'icon' => 'dashicons-admin-users', 'category' => 'general'],
			['type' => 'email', 'label' => 'Email', 'icon' => 'dashicons-email', 'category' => 'general'],
			['type' => 'text', 'label' => 'Simple Text', 'icon' => 'dashicons-edit', 'category' => 'general'],
			['type' => 'mask', 'label' => 'Mask Input', 'icon' => 'dashicons-shield', 'category' => 'general'],
			['type' => 'textarea', 'label' => 'Text Area', 'icon' => 'dashicons-editor-alignleft', 'category' => 'general'],
			['type' => 'country', 'label' => 'Country List', 'icon' => 'dashicons-flag', 'category' => 'general'],
			['type' => 'number', 'label' => 'Numeric Field', 'icon' => 'dashicons-editor-ol', 'category' => 'general'],
			['type' => 'dropdown', 'label' => 'Dropdown', 'icon' => 'dashicons-arrow-down-alt2', 'category' => 'general'],
			['type' => 'radio', 'label' => 'Radio Field', 'icon' => 'dashicons-marker', 'category' => 'general'],
			['type' => 'checkbox', 'label' => 'Checkbox', 'icon' => 'dashicons-yes', 'category' => 'general'],
			['type' => 'multiple', 'label' => 'Multiple Choice', 'icon' => 'dashicons-list-view', 'category' => 'general'],
			['type' => 'section', 'label' => 'Section Break', 'icon' => 'dashicons-minus', 'category' => 'general'],
			['type' => 'rating', 'label' => 'Ratings', 'icon' => 'dashicons-star-filled', 'category' => 'advanced'],
			['type' => 'terms', 'label' => 'Terms & Conditions', 'icon' => 'dashicons-media-text', 'category' => 'advanced'],
			['type' => 'gdpr', 'label' => 'GDPR Agreement', 'icon' => 'dashicons-shield', 'category' => 'advanced'],
			['type' => 'captcha', 'label' => 'Captcha Protection', 'icon' => 'dashicons-shield-alt', 'category' => 'advanced'],
			['type' => 'submit', 'label' => 'Submit Button', 'icon' => 'dashicons-plus-alt', 'category' => 'advanced']
		];

		wp_localize_script('formlayer-admin-js', 'formlayer_admin', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'process_url' => admin_url( 'admin.php?page=formlayer&action=process_choice' ),
			'nonce' => wp_create_nonce('formlayer_admin_nonce'),
			'form_id' => isset($_GET['form_id']) ? intval($_GET['form_id']) : 0,
			'categories' => apply_filters('formlayer_builder_categories', $categories),
			'fieldTypes' => apply_filters('formlayer_field_types', $field_types),
			'is_pro' => defined('FORMLAYER_PRO_VERSION'),
			'captcha_settings' => get_option('formlayer_settings', []),
			'templates' => \FormLayer\Templates::get_all(),
			'html_templates' => \FormLayer\Templates::js_templates()
		]);
	}
}