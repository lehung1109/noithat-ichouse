<?php

namespace FormLayer;

if(!defined('ABSPATH')){
	exit;
}

class Frontend{

	static function init(){
		add_shortcode('formlayer', '\FormLayer\Frontend::render_shortcode');
		add_action('wp_enqueue_scripts', '\FormLayer\Frontend::enqueue_assets');
	}

	static function enqueue_assets(){
		wp_enqueue_style('formlayer-frontend', FORMLAYER_PLUGIN_URL . 'assets/css/frontend.css', [], FORMLAYER_VERSION);
		wp_enqueue_script('formlayer-frontend', FORMLAYER_PLUGIN_URL . 'assets/js/frontend.js', ['jquery'], FORMLAYER_VERSION, true);

		wp_localize_script('formlayer-frontend', 'formlayer_data', [
			'ajax_url' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('formlayer-frontend'),
			'messages' => get_option('formlayer_settings', []),
			'is_pro' => defined('FORMLAYER_PRO_VERSION') ? true : false,
		]);
	}

	static function render_shortcode($atts){
		$atts = shortcode_atts([
			'id' => 0
		], $atts);

		if(empty($atts['id'])){
			return '<div class="formlayer-error">' . esc_html__('Please specify form ID: [formlayer id="123"]', 'formlayer') . '</div>';
		}

		$id = intval($atts['id']);
		$form = get_post($id);

		if(!$form || 'formlayer_form' !== $form->post_type){
			// Try finding by display ID
			$forms = get_posts([
				'post_type' => 'formlayer_form',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query' => [
					[
						'key' => '_formlayer_display_id',
						'value' => $id,
						'compare' => '='
					]
				],
				'posts_per_page' => 1
			]);
			
			if(!empty($forms)){
				$form = $forms[0];
			}
		}

		if(empty($form)){
			/* translators: %s: Form ID */
			return '<div class="formlayer-error">' . esc_html(sprintf(__('Form not found (ID: %s)', 'formlayer'), $atts['id'])) . '</div>';
		}
		
		if('formlayer_form' !== $form->post_type){
			return '<div class="formlayer-error">' . esc_html__('Invalid post type. Expected formlayer_form.', 'formlayer') . '</div>';
		}
		
		if('publish' !== $form->post_status && 'draft' !== $form->post_status){
			return '<div class="formlayer-error">' . esc_html__('Form is not published', 'formlayer') . '</div>';
		}

		self::enqueue_captcha_scripts();

		return self::render_form($form);
	}

	static function render_form($form){
		$form_id = $form->ID;
		$content = $form->post_content;
		
		$is_json = false;
		$data = json_decode($content, true);
		if (json_last_error() === JSON_ERROR_NONE && is_array($data) && isset($data['fields'])) {
			$is_json = true;
		}

		// Inject Custom CSS
		if($is_json && ! empty( $data['settings']['custom_css'])){
			$handle = 'formlayer-custom-' . intval( $form_id );

			wp_register_style( $handle, false, [], FORMLAYER_VERSION );
			wp_enqueue_style( $handle );

			$custom_css = $data['settings']['custom_css'];

			// Allow only safe CSS (basic hardening)
			$custom_css = wp_kses( $custom_css, [] ); // removes HTML tags

			wp_add_inline_style( $handle, $custom_css );
		}

		$has_submit = false;
		if ($is_json) {
			$html = self::render_json_form($data);
			foreach ($data['fields'] as $f){
				if($f['type'] === 'submit'){
					$has_submit = true;
					break;
				}
			}
		} else {
			//apply kses for HTML sanitization
			$html = do_blocks($content);
			
			// Sanitize HTML from do_blocks() - allow only safe HTML/CSS
			$allowed_html = wp_kses_allowed_html('post');
			// Add extra allowed attributes if needed for forms
			$allowed_html['div']['data-*'] = true;
			$allowed_html['form']['data-*'] = true;
			$allowed_html['input']['data-*'] = true;
			$allowed_html['button']['data-*'] = true;
			
			$html = wp_kses($html, $allowed_html);
			
			if(empty(trim(wp_strip_all_tags($html)))){
				$html = self::render_form_fallback($form);
				$html = wp_kses($html, $allowed_html);
			}
		}

		$output = '<div class="formlayer-form-wrapper" id="formlayer-form-' . esc_attr($form_id) . '">
			<form class="formlayer-form" data-form-id="' . esc_attr($form_id) . '">
				<div class="formlayer-form-status"></div>
				<div class="formlayer-form-fields-wrapper">
				' .$html; // sanitized above

		if(!$has_submit){
			$output .= '<div class="formlayer-submit-wrap">
					<button type="submit" class="formlayer-submit-btn">' . esc_html__('Submit Form', 'formlayer') . '</button>
				</div>';
		}

		$output .= '</div>
			</form>
		</div>';
		
		return $output;
	}

	static function render_json_form($data) {
		$html = '';
		$fields = isset($data['fields']) ? $data['fields'] : [];
		
		foreach ($fields as $field) {
			$html .= self::render_field_html($field);
		}
		
		return $html;
	}

	static function render_field_html($field) {
		$type = isset($field['type']) ? $field['type'] : 'text';
		$label = isset($field['label']) ? $field['label'] : '';
		$placeholder = isset($field['placeholder']) ? $field['placeholder'] : '';
		$required = !empty($field['required']) ? 'required' : '';
		
		// Advanced Props
		$label_placement = isset($field['label_placement']) ? $field['label_placement'] : 'top';
		$container_class = isset($field['container_class']) ? $field['container_class'] : '';
		$element_class = isset($field['element_class']) ? $field['element_class'] : '';
		$help_text = isset($field['help_text']) ? $field['help_text'] : '';
		$default_value = isset($field['default_value']) ? $field['default_value'] : (isset($field['value']) ? $field['value'] : '');
		$label_color = isset($field['style_label_color']) ? $field['style_label_color'] : '';
		$border_radius = isset($field['style_border_radius']) ? $field['style_border_radius'] : '';

		$name = \FormLayer\Util::get_field_name($field);
		$req_mark = $required ? '<span class="required-indicator">*</span>' : '';

		// Styles
		$label_style = !empty($label_color) ? 'style="color:' . esc_attr($label_color) . ';"' : '';
		$input_style = !empty($border_radius) ? 'style="border-radius:' . intval($border_radius) . 'px;"' : '';

		// Allow Pro to override rendering
		$pro_html = apply_filters('formlayer_render_field_html', '', $field);
		if (!empty($pro_html)) {
			return self::sanitize_field_html($pro_html);
		}

		$html = '<div class="formlayer-field-wrap label-' . esc_attr($label_placement) . ' ' . esc_attr($container_class) . '">';

		if($label && !in_array($type, ['submit', 'section', 'hidden', 'terms', 'gdpr']) && 'label-hidden' !== $label_placement && 'hidden' !== $label_placement){
			$html .= '<label class="formlayer-label" ' . $label_style . '>' . esc_html($label) . $req_mark . '</label>';
		}

		$input_class = 'formlayer-input ' . esc_attr($element_class);

		switch ($type) {
			case 'textarea':
				$rows = isset($field['rows']) ? intval($field['rows']) : 4;
				$cols = isset($field['cols']) ? intval($field['cols']) : '';
				$style = !empty($cols) ? 'width:' . intval($cols) . 'px !important;' : 'width:100%;';
				if (!empty($border_radius)) $style .= 'border-radius:' . intval($border_radius) . 'px;';
				$html .= '<textarea name="' . esc_attr($name) . '" class="' . $input_class . '" placeholder="' . esc_attr($placeholder) . '" ' . $required . ' style="' . esc_attr($style) . '" rows="' . $rows . '" cols="' . esc_attr($cols) . '">' . esc_textarea($default_value) . '</textarea>';
				break;
			case 'dropdown':
				$options = isset($field['options']) && !empty($field['options']) ? $field['options'] : ['Option 1', 'Option 2', 'Option 3'];
				if (is_string($options)) $options = explode("\n", $options);
				
				$html .= '<select name="' . esc_attr($name) . '" class="' . $input_class . '" ' . $required . ' ' . $input_style . ' style="width:100%;">';
				$html .= '<option value="">' . esc_html($placeholder ? $placeholder : 'Select Option') . '</option>';
				foreach($options as $opt) {
					$o = is_array($opt) ? $opt : ['label' => $opt, 'value' => $opt, 'default' => false];
					$selected = ($default_value === $o['value'] || (empty($default_value) && !empty($o['default']))) ? 'selected' : '';
					$html .= '<option value="' . esc_attr($o['value']) . '" ' . $selected . '>' . esc_html($o['label']) . '</option>';
				}
				$html .= '</select>';
				break;
			case 'radio':
			case 'checkbox':
			case 'multiple':
				$options = isset($field['options']) && !empty($field['options']) ? $field['options'] : ['Option 1', 'Option 2', 'Option 3'];
				if (is_string($options)) $options = explode("\n", $options);

				$html .= '<div class="formlayer-options-list">';
				foreach($options as $opt) {
					$o = is_array($opt) ? $opt : ['label' => $opt, 'value' => $opt, 'default' => false];
					$input_type = ($type === 'radio') ? 'radio' : 'checkbox';
					$field_name = ($type === 'radio') ? $name : $name . '[]';
					
					$checked = '';
					if ($type === 'radio') {
						$checked = ($default_value === $o['value'] || (empty($default_value) && !empty($o['default']))) ? 'checked' : '';
					} else {
						// Checkbox/Multiple
						if (is_array($default_value)) {
							$checked = in_array($o['value'], $default_value) ? 'checked' : '';
						} elseif (!empty($default_value)) {
							$checked = ($default_value === $o['value']) ? 'checked' : '';
						} else {
							$checked = !empty($o['default']) ? 'checked' : '';
						}
					}
					
					$html .= '
						<label class="formlayer-option-label">
							<input type="' . $input_type . '" name="' . esc_attr($field_name) . '" value="' . esc_attr($o['value']) . '" ' . $checked . '>
							<span>' . esc_html($o['label']) . '</span>
						</label>';
				}
				$html .= '</div>';
				break;
			case 'email':
				$html .= '<input type="email" name="' . esc_attr($name) . '" class="' . $input_class . '" placeholder="' . esc_attr($placeholder) . '" value="' . esc_attr($default_value) . '" ' . $required . ' ' . $input_style . '>';
				break;
			case 'name':
				$show_first = !isset($field['enable_first_name']) || $field['enable_first_name'] !== false;
				$show_middle = !empty($field['enable_middle_name']);
				$show_last = !isset($field['enable_last_name']) || $field['enable_last_name'] !== false;

				$p1 = isset($field['placeholder_first']) && $field['placeholder_first'] !== '' ? $field['placeholder_first'] : 'First Name';
				$p2 = isset($field['placeholder_middle']) && $field['placeholder_middle'] !== '' ? $field['placeholder_middle'] : 'Middle Name';
				$p3 = isset($field['placeholder_last']) && $field['placeholder_last'] !== '' ? $field['placeholder_last'] : 'Last Name';

				$l1 = isset($field['label_first']) && $field['label_first'] !== '' ? $field['label_first'] : __('First Name', 'formlayer');
				$l2 = isset($field['label_middle']) && $field['label_middle'] !== '' ? $field['label_middle'] : __('Middle Name', 'formlayer');
				$l3 = isset($field['label_last']) && $field['label_last'] !== '' ? $field['label_last'] : __('Last Name', 'formlayer');

				$html .= '<div class="formlayer-name-fields-wrap">';
				if($show_first) {
					$html .= '<div class="formlayer-sub-field">
						<input type="text" name="' . esc_attr($name) . '[first]" class="' . $input_class . '" placeholder="' . esc_attr($p1) . '" ' . $required . '>
						<span class="formlayer-sub-label">' . esc_html($l1) . '</span>
					</div>';
				}
				if($show_middle) {
					$html .= '<div class="formlayer-sub-field">
						<input type="text" name="' . esc_attr($name) . '[middle]" class="' . $input_class . '" placeholder="' . esc_attr($p2) . '">
						<span class="formlayer-sub-label">' . esc_html($l2) . '</span>
					</div>';
				}
				if($show_last) {
					$html .= '<div class="formlayer-sub-field">
						<input type="text" name="' . esc_attr($name) . '[last]" class="' . $input_class . '" placeholder="' . esc_attr($p3) . '" ' . $required . '>
						<span class="formlayer-sub-label">' . esc_html($l3) . '</span>
					</div>';
				}
				$html .= '</div>';
				break;
			case 'country':
				$html .= '<div class="formlayer-select-wrap">
					<span class="dashicons dashicons-admin-site"></span>
					<select name="' . esc_attr($name) . '" class="' . $input_class . '" ' . $required . '>';
				$html .= '<option value="">' . esc_html($placeholder ? $placeholder : 'Select Country') . '</option>';
				foreach(self::get_countries() as $code => $label) {
					$selected = ($default_value === $code || $default_value === $label) ? 'selected' : '';
					$html .= '<option value="' . esc_attr($code) . '" ' . $selected . '>' . esc_html($label) . '</option>';
				}
				$html .= '</select>
				</div>';
				break;
			case 'mask':
				$html .= '<input type="text" name="' . esc_attr($name) . '" class="' . $input_class . ' formlayer-masked-input" placeholder="' . esc_attr($placeholder ? $placeholder : '(+1) 000-0000') . '" value="' . esc_attr($default_value) . '" ' . $required . '>';
				break;
			case 'rating':
				$html .= '<div class="formlayer-rating" data-name="' . esc_attr($name) . '">';
				for($i=1; $i<=5; $i++){
					$html .= '<span class="dashicons dashicons-star-filled" data-value="' . $i . '"></span>';
				}
				$html .= '<input type="hidden" name="' . esc_attr($name) . '" value="' . esc_attr($default_value) . '">';
				$html .= '</div>';
				break;
			case 'section':
				return '<div class="formlayer-section-break"><h3>' . esc_html($label) . '</h3>' . ($help_text ? '<p>' . esc_html($help_text) . '</p>' : '') . '</div>';
			case 'number':
				$min = isset($field['min']) && $field['min'] !== '' ? 'min="' . esc_attr($field['min']) . '"' : '';
				$max = isset($field['max']) && $field['max'] !== '' ? 'max="' . esc_attr($field['max']) . '"' : '';
				$digit_limit = isset($field['digit_limit']) && $field['digit_limit'] !== '' ? 'maxlength="' . esc_attr($field['digit_limit']) . '"' : '';
				$html .= '<input type="number" name="' . esc_attr($name) . '" class="' . $input_class . '" placeholder="' . esc_attr($placeholder) . '" value="' . esc_attr($default_value) . '" ' . $required . ' ' . $input_style . ' ' . $min . ' ' . $max . ' ' . $digit_limit . '>';
				break;
			case 'terms':
				// Use terms_label if set, else fall back to the admin label field
				$raw_terms_label = isset($field['terms_label']) && $field['terms_label'] !== '' ? $field['terms_label'] : ($label ? $label : __('I agree to the Terms &amp; Conditions', 'formlayer'));
				$html .= '
					<div class="formlayer-terms-wrap">
						<input type="checkbox" name="' . esc_attr($name) . '" ' . $required . '>
						<span class="formlayer-terms-label">' . wp_kses_post($raw_terms_label) . '</span>
					</div>';
				break;
			case 'gdpr':
				$gdpr_label = isset($field['gdpr_label']) ? $field['gdpr_label'] : 'Accept GDPR Policy';
				$gdpr_desc = isset($field['gdpr_description']) ? $field['gdpr_description'] : '';
				$html .= '
				<div class="formlayer-gdpr-wrap">
					<input type="checkbox" name="' . esc_attr($name) . '" ' . $required . '>
					<div class="formlayer-gdpr-info">
						<div class="formlayer-gdpr-label">' . wp_kses_post($gdpr_label) . '</div>
						' . ($gdpr_desc ? '<div class="formlayer-gdpr-desc">' . wp_kses_post($gdpr_desc) . '</div>' : '') . '
					</div>
				</div>';
				break;
			case 'captcha':
				$settings = get_option('formlayer_settings', []);
				$provider = isset($field['captcha_provider']) ? $field['captcha_provider'] : 'hcaptcha';
				
				// Get global default theme based on provider
				$global_theme = 'light';
				if($provider === 'hcaptcha') $global_theme = isset($settings['captcha_h_theme']) ? $settings['captcha_h_theme'] : 'light';
				// Pro captcha providers - delegate to Pro plugin via filter
				$global_theme = apply_filters('formlayer_pro_captcha_theme', $global_theme, $provider);

				$theme = isset($field['captcha_theme']) ? $field['captcha_theme'] : $global_theme;
				$site_key = '';
				
				if($provider === 'hcaptcha') $site_key = isset($settings['captcha_h_site_key']) ? $settings['captcha_h_site_key'] : '';
				// Pro captcha providers - delegate to Pro plugin via filter
				$site_key = apply_filters('formlayer_pro_captcha_sitekey', $site_key, $provider);

				if(empty($site_key)) return '';

				$html .= '<div class="formlayer-field-item formlayer-captcha-container" data-provider="'.esc_attr($provider).'" data-sitekey="'.esc_attr($site_key).'" data-theme="'.esc_attr($theme).'">';
				if($provider === 'hcaptcha') {
					$html .= '<div class="h-captcha" data-sitekey="'.esc_attr($site_key).'" data-theme="'.esc_attr($theme).'"></div>';
				} else {
					// Pro captcha providers - delegate to Pro plugin via filter
					$pro_html = apply_filters('formlayer_pro_captcha_html', '', $provider, $field);
					$html .= wp_kses_post($pro_html);
				}
				$html .= '</div>';
				break;
			case 'submit':
				$align = isset($field['btn_align']) ? $field['btn_align'] : 'left';
				$size = isset($field['btn_size']) ? $field['btn_size'] : 'md';
				$bg = isset($field['btn_bg_color']) ? $field['btn_bg_color'] : '';
				$txt = isset($field['btn_text_color']) ? $field['btn_text_color'] : '';
				$bg_h = isset($field['btn_bg_hover']) ? $field['btn_bg_hover'] : '';
				$txt_h = isset($field['btn_text_hover']) ? $field['btn_text_hover'] : '';
				$rad = isset($field['style_border_radius']) ? intval($field['style_border_radius']) . 'px' : '';
				
				$btn_id = 'fl-btn-' . (isset($field['id']) ? $field['id'] : uniqid());
				$btn_style = '';
				if($bg) $btn_style .= 'background-color:' . esc_attr($bg) . ' !important;';
				if($txt) $btn_style .= 'color:' . esc_attr($txt) . ' !important;';
				if($rad) $btn_style .= 'border-radius:' . esc_attr($rad) . ' !important;';

				$size_class = 'btn-' . esc_attr($size);
				$align_class = 'align-' . esc_attr($align);

				if ($bg_h || $txt_h) {
					$hover_css = "#" . esc_attr($btn_id) . ":hover {";
					if($bg_h) $hover_css .= "background-color: " . esc_attr($bg_h) . " !important;";
					if($txt_h) $hover_css .= "color: " . esc_attr($txt_h) . " !important;";
					$hover_css .= "}";
					wp_add_inline_style('formlayer-frontend', wp_strip_all_tags($hover_css));
				}

				return '<div class="formlayer-submit-wrap ' . esc_attr($align_class) . '">
					<button type="submit" id="' . esc_attr($btn_id) . '" class="formlayer-submit-btn ' . esc_attr($size_class) . ' ' . esc_attr($element_class) . '" style="' . $btn_style . '">' . esc_html($label ? $label : 'Submit Form') . '</button>
				</div>';
			default:
				$extra_attrs = '';
				if ($type === 'url') {
					if (!empty($field['url_validation'])) $extra_attrs .= ' data-validate-url="1"';
					if (!empty($field['url_https_only'])) $extra_attrs .= ' data-https-only="1"';
				}
				$input_type = in_array($type, ['password', 'url', 'tel', 'hidden']) ? $type : 'text';
				$html .= '<input type="' . esc_attr($input_type) . '" name="' . esc_attr($name) . '" class="' . $input_class . '" placeholder="' . esc_attr($placeholder) . '" value="' . esc_attr($default_value) . '" ' . $required . ' ' . $input_style . $extra_attrs . '>';
				break;
		}

		if (!empty($help_text)) {
			$html .= '<div class="formlayer-help-text">' . esc_html($help_text) . '</div>';
		}

		$html .= '</div>';
		return $html;
	}

	// Sanitizes field HTML to allow standard form tags while preventing XSS
	static function sanitize_field_html($html){
		$allowed = wp_kses_allowed_html('post');
		
		$allowed['input'] = [
			'type' => true,
			'name' => true,
			'value' => true,
			'class' => true,
			'placeholder' => true,
			'required' => true,
			'checked' => true,
			'style' => true,
			'id' => true,
			'data-*' => true,
			'disabled' => true,
			'readonly' => true,
			'min' => true,
			'max' => true,
			'maxlength' => true,
		];
		$allowed['select'] = [
			'name' => true,
			'class' => true,
			'required' => true,
			'style' => true,
			'id' => true,
			'data-*' => true,
			'disabled' => true,
		];
		$allowed['option'] = [
			'value' => true,
			'selected' => true,
			'disabled' => true,
		];
		$allowed['textarea'] = [
			'name' => true,
			'class' => true,
			'placeholder' => true,
			'required' => true,
			'style' => true,
			'id' => true,
			'rows' => true,
			'cols' => true,
			'data-*' => true,
			'disabled' => true,
			'readonly' => true,
		];
		$allowed['label'] = [
			'class' => true,
			'style' => true,
			'for'   => true,
		];
		$allowed['button'] = [
			'type' => true,
			'class' => true,
			'style' => true,
			'id' => true,
			'data-*' => true,
			'disabled' => true,
		];
		$allowed['div']['data-*'] = true;
		$allowed['div']['class'] = true;
		$allowed['div']['style']  = true;
		$allowed['span']['data-*'] = true;
		$allowed['span']['class']  = true;
		$allowed['span']['style']  = true;
		$allowed['p']['class'] = true;
		$allowed['p']['style'] = true;
		
		return wp_kses($html, $allowed);
	}

	static function render_form_fallback($form){
		$form_id = $form->ID;
		$blocks = parse_blocks($form->post_content);
		
		$html = '';
		foreach($blocks as $block){
			$html .= self::render_block_fallback($block);
		}
		return $html;
	}

	static function render_block_fallback($block){
		$attrs = isset($block['attrs']) ? $block['attrs'] : [];
		$inner_html = isset($block['innerHTML']) ? $block['innerHTML'] : '';
		
		switch($block['blockName']){
			case 'formlayer/text-field':
				$label = isset($attrs['label']) ? $attrs['label'] : 'Text Field';
				$placeholder = isset($attrs['placeholder']) ? $attrs['placeholder'] : '';
				$required = !empty($attrs['required']) ? ' required' : '';
				return '<div class="formlayer-field-wrap">
					<label class="formlayer-label">' . esc_html($label) . ($required ? '<span class="required-indicator">*</span>' : '') . '</label>
					<input type="text" name="field_text" class="formlayer-input" placeholder="' . esc_attr($placeholder) . '"' . $required . '>
				</div>';
				
			case 'formlayer/email-field':
				$label = isset($attrs['label']) ? $attrs['label'] : 'Email';
				$placeholder = isset($attrs['placeholder']) ? $attrs['placeholder'] : '';
				$required = !empty($attrs['required']) ? ' required' : '';
				return '<div class="formlayer-field-wrap">
					<label class="formlayer-label">' . esc_html($label) . ($required ? '<span class="required-indicator">*</span>' : '') . '</label>
					<input type="email" name="field_email" class="formlayer-input" placeholder="' . esc_attr($placeholder) . '"' . $required . '>
				</div>';
				
			case 'formlayer/textarea-field':
				$label = isset($attrs['label']) ? $attrs['label'] : 'Message';
				$placeholder = isset($attrs['placeholder']) ? $attrs['placeholder'] : '';
				$required = !empty($attrs['required']) ? ' required' : '';
				return '<div class="formlayer-field-wrap">
					<label class="formlayer-label">' . esc_html($label) . ($required ? '<span class="required-indicator">*</span>' : '') . '</label>
					<textarea name="field_textarea" class="formlayer-input" placeholder="' . esc_attr($placeholder) . '"' . $required . '></textarea>
				</div>';
				
			case 'formlayer/submit-button':
				$text = isset($attrs['text']) ? $attrs['text'] : 'Submit';
				return '<div class="formlayer-submit-wrap">
					<button type="submit" class="formlayer-submit-btn">' . esc_html($text) . '</button>
				</div>';
				
			default:
				return $inner_html;
		}
	}

	static function enqueue_captcha_scripts(){
		global $post;

		if(is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'formlayer')){
			$settings = get_option('formlayer_settings', []);
			if (!empty($settings['captcha_h_site_key'])) {
				wp_enqueue_script('hcaptcha', 'https://js.hcaptcha.com/1/api.js', [], FORMLAYER_VERSION, true);
			}

			// Pro captcha providers - delegate to Pro plugin via action
			do_action('formlayer_enqueue_captcha_scripts');
	
		}
	}
	
	static function get_countries() {
		return [
			'AF' => 'Afghanistan',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AU' => 'Australia',
			'AT' => 'Austria',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CX' => 'Christmas Island',
			'CC' => 'Cocos (Keeling) Islands',
			'CO' => 'Colombia',
			'KM' => 'Comoros',
			'CG' => 'Congo',
			'CD' => 'Congo, the Democratic Republic of the',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Cote d\'Ivoire',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El Salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands (Malvinas)',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'TF' => 'French Southern Territories',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GN' => 'Guinea',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'HM' => 'Heard Island and McDonald Islands',
			'VA' => 'Holy See (Vatican City State)',
			'HN' => 'Honduras',
			'HK' => 'Hong Kong',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran, Islamic Republic of',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JO' => 'Jordan',
			'KZ' => 'Kazakhstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KP' => 'Korea, Democratic People\'s Republic of',
			'KR' => 'Korea, Republic of',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgyzstan',
			'LA' => 'Lao People\'s Democratic Republic',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LS' => 'Lesotho',
			'LR' => 'Liberia',
			'LY' => 'Libyan Arab Jamahiriya',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macao',
			'MK' => 'Macedonia, the former Yugoslav Republic of',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MH' => 'Marshall Islands',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia, Federated States of',
			'MD' => 'Moldova, Republic of',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'AN' => 'Netherlands Antilles',
			'NC' => 'New Caledonia',
			'NZ' => 'New Zealand',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'MP' => 'Northern Mariana Islands',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PS' => 'Palestinian Territory, Occupied',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PN' => 'Pitcairn',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RU' => 'Russian Federation',
			'RW' => 'Rwanda',
			'SH' => 'Saint Helena',
			'KN' => 'Saint Kitts and Nevis',
			'LC' => 'Saint Lucia',
			'PM' => 'Saint Pierre and Miquelon',
			'VC' => 'Saint Vincent and the Grenadines',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'ST' => 'Sao Tome and Principe',
			'SA' => 'Saudi Arabia',
			'SN' => 'Senegal',
			'CS' => 'Serbia and Montenegro',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'ZA' => 'South Africa',
			'GS' => 'South Georgia and the South Sandwich Islands',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard and Jan Mayen',
			'SZ' => 'Swaziland',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syrian Arab Republic',
			'TW' => 'Taiwan, Province of China',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania, United Republic of',
			'TH' => 'Thailand',
			'TL' => 'Timor-Leste',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TC' => 'Turks and Caicos Islands',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'US' => 'United States',
			'UM' => 'United States Minor Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Viet Nam',
			'VG' => 'Virgin Islands, British',
			'VI' => 'Virgin Islands, U.S.',
			'WF' => 'Wallis and Futuna',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe'
		];
	}
}