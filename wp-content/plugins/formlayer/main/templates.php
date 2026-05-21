<?php
namespace FormLayer;

if(!defined('ABSPATH')){
	exit;
}

class Templates{
	
	static function get_all(){
		$templates = [
			[ 'id' => 'scratch', 'title' => __('Start from Scratch', 'formlayer'), 'desc' => __('A clean slate for your custom form vision.', 'formlayer'), 'icon' => 'dashicons-plus', 'cat' => 'all' ],
			[ 'id' => 'contact', 'title' => __('Direct Contact Hub', 'formlayer'), 'desc' => __('Professional name, email, and message for inquiries.', 'formlayer'), 'icon' => 'dashicons-email', 'cat' => 'general', 'fields' => [
				[ 'id' => 'f1', 'type' => 'name', 'label' => 'Full Identity', 'placeholder' => 'Your complete name', 'required' => true ],
				[ 'id' => 'f2', 'type' => 'email', 'label' => 'Contact Mail', 'placeholder' => 'best@email.com', 'required' => true ],
				[ 'id' => 'f3', 'type' => 'text', 'label' => 'Subject', 'placeholder' => 'How can we help?', 'required' => true ],
				[ 'id' => 'f4', 'type' => 'textarea', 'label' => 'Message Body', 'placeholder' => 'Tell us how we can help...', 'required' => true ],
				[ 'id' => 'f5', 'type' => 'gdpr', 'label' => 'I agree to the privacy policy', 'required' => true ],
				[ 'id' => 'f6', 'type' => 'submit', 'label' => 'Send Message' ]
			]],
			[ 'id' => 'lead_gen', 'title' => __('Growth Lead Capturer', 'formlayer'), 'desc' => __('Optimized for high-conversion B2B marketing.', 'formlayer'), 'icon' => 'dashicons-megaphone', 'cat' => 'marketing', 'fields' => [
				[ 'id' => 'f1', 'type' => 'name', 'label' => 'Prospect Name', 'required' => true ],
				[ 'id' => 'f2', 'type' => 'email', 'label' => 'Professional Business Email', 'required' => true ],
				[ 'id' => 'f3', 'type' => 'text', 'label' => 'Company Name', 'required' => true ],
				[ 'id' => 'f4', 'type' => 'dropdown', 'label' => 'Industry Sector', 'options' => ['Tech', 'Health', 'Finance', 'Education'], 'required' => true ],
				[ 'id' => 'f5', 'type' => 'url', 'label' => 'Company Website', 'required' => false ],
				[ 'id' => 'f6', 'type' => 'submit', 'label' => 'Get Started' ]
			]],
			[ 'id' => 'service', 'title' => __('Service Request Desk', 'formlayer'), 'desc' => __('Efficient ticket generation for technical issues.', 'formlayer'), 'icon' => 'dashicons-sos', 'cat' => 'crm', 'fields' => [
				[ 'id' => 'f1', 'type' => 'email', 'label' => 'Requester Email', 'required' => true ],
				[ 'id' => 'f2', 'type' => 'dropdown', 'label' => 'Issue Category', 'options' => ['Hardware', 'Software', 'Network', 'Account'], 'required' => true ],
				[ 'id' => 'f3', 'type' => 'radio', 'label' => 'Issue Priority', 'options' => ['P1 - Critical', 'P2 - High', 'P3 - Normal'], 'required' => true ],
				[ 'id' => 'f4', 'type' => 'text', 'label' => 'Summary Subject', 'required' => true ],
				[ 'id' => 'f5', 'type' => 'textarea', 'label' => 'Detailed Problem Description', 'placeholder' => 'Describe the technical issue...', 'required' => true ],
				[ 'id' => 'f6', 'type' => 'submit', 'label' => 'Open Support Ticket' ]
			]],
			[ 'id' => 'feedback', 'title' => __('Product Insight Survey', 'formlayer'), 'desc' => __('Listen to your users with quantitative ratings.', 'formlayer'), 'icon' => 'dashicons-awards', 'cat' => 'feedback', 'fields' => [
				[ 'id' => 'f1', 'type' => 'name', 'label' => 'Reviewer Name', 'required' => false ],
				[ 'id' => 'f2', 'type' => 'rating', 'label' => 'Overall Satisfaction Score', 'required' => true ],
				[ 'id' => 'f3', 'type' => 'textarea', 'label' => 'What is our best feature?', 'required' => false ],
				[ 'id' => 'f4', 'type' => 'textarea', 'label' => 'Areas for Improvement', 'required' => false ],
				[ 'id' => 'f5', 'type' => 'checkbox', 'label' => 'Would you recommend us?', 'options' => ['Yes', 'Maybe', 'No'], 'required' => true ],
				[ 'id' => 'f6', 'type' => 'submit', 'label' => 'Send Feedback' ]
			]],
			// Pro Teaser Templates
			[ 'id' => 'course_reg', 'title' => __('Course Enrollment (Pro)', 'formlayer'), 'desc' => __('Secure student details and course selections.', 'formlayer'), 'icon' => 'dashicons-welcome-learn-more', 'cat' => 'education', 'is_pro' => true ],
			[ 'id' => 'it_asset', 'title' => __('IT Asset Request (Pro)', 'formlayer'), 'desc' => __('Workflow for internal hardware and software requests.', 'formlayer'), 'icon' => 'dashicons-laptop', 'cat' => 'it', 'is_pro' => true ],
			[ 'id' => 'expense_claim', 'title' => __('Expense Reimbursement (Pro)', 'formlayer'), 'desc' => __('Processing finance claims for team mileage and costs.', 'formlayer'), 'icon' => 'dashicons-money-alt', 'cat' => 'finance', 'is_pro' => true ],
			[ 'id' => 'onboarding', 'title' => __('Member Onboarding (Pro)', 'formlayer'), 'desc' => __('User registration for your digital community.', 'formlayer'), 'icon' => 'dashicons-admin-users', 'cat' => 'crm', 'is_pro' => true ],
			[ 'id' => 'talent', 'title' => __('Talent Discovery Portal (Pro)', 'formlayer'), 'desc' => __('Modern job application flow with portfolio support.', 'formlayer'), 'icon' => 'dashicons-id', 'cat' => 'hr', 'is_pro' => true ],
			[ 'id' => 'marketing_survey', 'title' => __('Market Trends Survey (Pro)', 'formlayer'), 'desc' => __('Analyze industry shifts with comprehensive data.', 'formlayer'), 'icon' => 'dashicons-chart-area', 'cat' => 'marketing', 'is_pro' => true ]
		];

		return apply_filters('formlayer_templates', $templates);
	}
	
	static function js_templates(){
		return [

			'empty_state' => '
				<div class="formlayer-empty-dropzone">
					<div class="formlayer-empty-icon">
						<span class="dashicons dashicons-plus"></span>
					</div>
					<p>' . esc_html__('Click on sidebar elements to add them to your form.', 'formlayer' ) . '</p>
				</div>',

			'field_instance' => '
				<div class="formlayer-field-instance {{active_class}} label-{{label_placement}} {{container_class}}" data-id="{{id}}">
					{{label_html}}
					<div class="formlayer-field-input" {{input_style}}>
						{{input_html}}
					</div>
					{{help_html}}
					{{actions_html}}
				</div>',

			'field_label' => '
				<div class="formlayer-field-label">
					<label {{label_style}}>{{label}} {{required_mark}}</label>
				</div>',

			'field_actions' => '
				<div class="formlayer-field-actions">
					<button class="formlayer-field-move-up" title="' . esc_attr__( 'Move Up', 'formlayer' ) . '" {{move_up_disabled}}>
						<span class="dashicons dashicons-arrow-up-alt2"></span>
					</button>
					<button class="formlayer-field-move-down" title="' . esc_attr__( 'Move Down', 'formlayer' ) . '" {{move_down_disabled}}>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</button>
					<button class="formlayer-field-clone" title="' . esc_attr__( 'Clone', 'formlayer' ) . '">
						<span class="dashicons dashicons-admin-page"></span>
					</button>
					<button class="formlayer-field-delete" title="' . esc_attr__( 'Delete', 'formlayer' ) . '">
						<span class="dashicons dashicons-trash"></span>
					</button>
				</div>',

			'hidden_field_preview' => '
				<div class="formlayer-field-hidden-preview">
					<span class="dashicons dashicons-visibility-faint"></span>
					' . esc_html__( 'Hidden:', 'formlayer' ) . ' {{value}}
				</div>',

			'captcha_preview' => '
				<div class="formlayer-captcha-preview fl-style-box">
					<span class="dashicons dashicons-shield fl-icon"></span>
					<div class="fl-title">{{provider}}</div>
					<p class="fl-desc">' . esc_html__( '(Using global credentials from settings)', 'formlayer' ) . '</p>
				</div>',

			'richtext_preview' => '
				<div class="formlayer-richtext-preview fl-style-box">
					<div class="fl-toolbar">
						<span class="dashicons dashicons-editor-bold"></span>
						<span class="dashicons dashicons-editor-italic"></span>
						<span class="dashicons dashicons-editor-ul"></span>
						<span class="dashicons dashicons-editor-ol"></span>
					</div>
					<div class="fl-placeholder">' . esc_html__( 'Rich Text Editor Placeholder', 'formlayer' ) . '</div>
				</div>',

			'template_card' => '
				<div class="formlayer-template-card {{locked_class}}" data-id="{{id}}">
					{{badge_html}}
					<div class="template-icon">
						<span class="dashicons {{icon}}"></span>
					</div>
					<div class="template-info">
						<h4>{{title}}</h4>
						<p>{{desc}}</p>
					</div>
					<div class="template-footer">
						{{button_html}}
					</div>
				</div>',

			'no_templates_found' => '
				<div class="formlayer-empty-search">
					<span class="dashicons dashicons-search"></span>
					<h3>' . esc_html__( 'No templates found', 'formlayer' ) . '</h3>
					<p>' . esc_html__( 'Try a different search term or category.', 'formlayer' ) . '</p>
				</div>',

			'file_upload_box' => '
				<div class="formlayer-file-upload-box">
					<div class="formlayer-file-fake-btn" {{btn_style}}>{{btn_text}}</div>
					<span class="formlayer-file-chosen-name">{{chosen_text}}</span>
				</div>',

			'name_fields_preview' => '
				<div class="formlayer-name-preview-container">
					{{sub_fields}}
				</div>',

			'address_grid_preview' => '
				<div class="formlayer-grid formlayer-address-grid">
					{{sub_fields}}
				</div>',

			'sub_field_preview' => '
				<div class="formlayer-sub-field {{full_width_class}}">
					<input type="{{type}}" placeholder="{{placeholder}}" disabled>
					<div class="formlayer-sub-label">{{label}}</div>
				</div>',

			'sidebar_category' => '
				<div class="formlayer-category {{open_class}}" data-cat="{{id}}">
					<div class="formlayer-category-header">
						<span>
							<span class="dashicons dashicons-admin-generic"></span> {{label}}
						</span>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="formlayer-category-content">
						{{fields_html}}
					</div>
				</div>',

			'palette_field' => '
				<div class="formlayer-palette-field" data-type="{{type}}">
					<span class="dashicons {{icon}}"></span>
					<span>{{label}}</span>
				</div>',

			'sidebar_accordion' => '
				<div class="formlayer-accordion {{open_class}}" data-accordion="{{id}}">
					<div class="formlayer-accordion-header">
						<h4>{{title}}</h4>
						<span class="dashicons dashicons-arrow-down-alt2"></span>
					</div>
					<div class="formlayer-accordion-content">
						{{content_html}}
					</div>
				</div>',

			'control_group' => '
				<div class="formlayer-control-group">
					<label class="formlayer-control-label">{{label}} {{info_html}}</label>
					{{input_html}}
				</div>',

			'info_icon' => '
				<span class="formlayer-info-icon" title="{{title}}">i</span>',

			'option_edit_row' => '
				<div class="formlayer-option-edit-row" data-index="{{index}}">
					<input type="checkbox" class="formlayer-option-default" {{default_checked}} title="' . esc_attr__( 'Default selected', 'formlayer' ) . '">
					<input type="text" class="formlayer-option-label" value="{{label}}" placeholder="' . esc_attr__( 'Label', 'formlayer' ) . '">
					<input type="text" class="formlayer-option-value" value="{{value}}" placeholder="' . esc_attr__( 'Value', 'formlayer' ) . '">
					<button class="formlayer-btn-remove-option" title="' . esc_attr__( 'Remove', 'formlayer' ) . '">
						<span class="dashicons dashicons-no-alt"></span>
					</button>
				</div>'

		];
	}
}
