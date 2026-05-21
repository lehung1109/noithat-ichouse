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

class Templates {
	
	static function init() {
		add_filter('formlayer_templates', '\FormLayerPro\Templates::add_pro_templates');
	}

	static function add_pro_templates($templates) {
		$pro_templates = [
			[ 'id' => 'course_reg', 'title' => __('Course Enrollment', 'formlayer-pro'), 'desc' => __('Secure student details and course selections.', 'formlayer-pro'), 'icon' => 'dashicons-welcome-learn-more', 'cat' => 'education', 'is_pro' => true, 'fields' => [
				[ 'id' => 'f1', 'type' => 'section', 'label' => 'Student Information' ],
				[ 'id' => 'f2', 'type' => 'name', 'label' => 'Student Name', 'required' => true ],
				[ 'id' => 'f3', 'type' => 'email', 'label' => 'Student Email', 'required' => true ],
				[ 'id' => 'f4', 'type' => 'section', 'label' => 'Academic selection' ],
				[ 'id' => 'f5', 'type' => 'dropdown', 'label' => 'Select Preferred Course', 'options' => ['Beginner PHP', 'Advanced JS', 'Design Mastery'], 'required' => true ],
				[ 'id' => 'f6', 'type' => 'date', 'label' => 'Preferred Start Date', 'required' => true ],
				[ 'id' => 'f7', 'type' => 'terms', 'label' => 'Accept Enrollment Terms', 'required' => true ],
				[ 'id' => 'f8', 'type' => 'submit', 'label' => 'Enroll Now' ]
			]],
			[ 'id' => 'it_asset', 'title' => __('IT Asset Request', 'formlayer-pro'), 'desc' => __('Workflow for internal hardware and software requests.', 'formlayer-pro'), 'icon' => 'dashicons-laptop', 'cat' => 'it', 'is_pro' => true, 'fields' => [
				[ 'id' => 'f1', 'type' => 'name', 'label' => 'Employee Name', 'required' => true ],
				[ 'id' => 'f2', 'type' => 'text', 'label' => 'Department / Team', 'required' => true ],
				[ 'id' => 'f3', 'type' => 'dropdown', 'label' => 'Urgency Level', 'options' => ['Low', 'Medium', 'High', 'Critical'], 'required' => true ],
				[ 'id' => 'f4', 'type' => 'checkbox', 'label' => 'Requested Assets', 'options' => ['Laptop', 'Dual Monitor', 'Keyboard/Mouse', 'Software License'], 'required' => true ],
				[ 'id' => 'f5', 'type' => 'textarea', 'label' => 'Business Justification', 'required' => true ],
				[ 'id' => 'f6', 'type' => 'date', 'label' => 'Needed By Date', 'required' => true ],
				[ 'id' => 'f7', 'type' => 'submit', 'label' => 'Submit Asset Request' ]
			]],
			[ 'id' => 'expense_claim', 'title' => __('Expense Reimbursement', 'formlayer-pro'), 'desc' => __('Processing finance claims for team mileage and costs.', 'formlayer-pro'), 'icon' => 'dashicons-money-alt', 'cat' => 'finance', 'is_pro' => true, 'fields' => [
				[ 'id' => 'f1', 'type' => 'name', 'label' => 'Claimant Name', 'required' => true ],
				[ 'id' => 'f2', 'type' => 'email', 'label' => 'Finance Contact Email', 'required' => true ],
				[ 'id' => 'f3', 'type' => 'date', 'label' => 'Expense Date', 'required' => true ],
				[ 'id' => 'f4', 'type' => 'number', 'label' => 'Total Amount to Reimburse', 'required' => true ],
				[ 'id' => 'f5', 'type' => 'textarea', 'label' => 'Description of Costs', 'required' => true ],
				[ 'id' => 'f6', 'type' => 'file', 'label' => 'Upload Receipt Image', 'required' => true ],
				[ 'id' => 'f7', 'type' => 'submit', 'label' => 'Process Claim' ]
			]],
			[ 'id' => 'onboarding', 'title' => __('Member Onboarding', 'formlayer-pro'), 'desc' => __('User registration for your digital community.', 'formlayer-pro'), 'icon' => 'dashicons-admin-users', 'cat' => 'crm', 'is_pro' => true, 'fields' => [
				[ 'id' => 'f1', 'type' => 'name', 'label' => 'Full Legal Name', 'required' => true ],
				[ 'id' => 'f2', 'type' => 'email', 'label' => 'Primary Account Mail', 'required' => true ],
				[ 'id' => 'f3', 'type' => 'text', 'label' => 'Desired Username', 'placeholder' => 'Unique ID', 'required' => true ],
				[ 'id' => 'f4', 'type' => 'password', 'label' => 'Account Password', 'required' => true ],
				[ 'id' => 'f5', 'type' => 'textarea', 'label' => 'Short Professional Bio', 'required' => false ],
				[ 'id' => 'f6', 'type' => 'terms', 'label' => 'Accept Platform Terms', 'required' => true ],
				[ 'id' => 'f7', 'type' => 'submit', 'label' => 'Complete Registration' ]
			]],
			[ 'id' => 'talent', 'title' => __('Talent Discovery Portal', 'formlayer-pro'), 'desc' => __('Modern job application flow with portfolio support.', 'formlayer-pro'), 'icon' => 'dashicons-id', 'cat' => 'hr', 'is_pro' => true, 'fields' => [
				[ 'id' => 'f1', 'type' => 'name', 'label' => 'Applicant Full Name', 'required' => true ],
				[ 'id' => 'f2', 'type' => 'email', 'label' => 'Primary Contact Mail', 'required' => true ],
				[ 'id' => 'f3', 'type' => 'dropdown', 'label' => 'Education Level', 'options' => ['High School', 'Bachelors', 'Masters', 'PhD'], 'required' => true ],
				[ 'id' => 'f4', 'type' => 'textarea', 'label' => 'Relevant Work Experience', 'required' => true ],
				[ 'id' => 'f5', 'type' => 'url', 'label' => 'Portfolio / LinkedIn URL', 'required' => false ],
				[ 'id' => 'f6', 'type' => 'file', 'label' => 'Upload CV (PDF)', 'required' => true ],
				[ 'id' => 'f7', 'type' => 'submit', 'label' => 'Apply for Position' ]
			]],
			[ 'id' => 'marketing_survey', 'title' => __('Market Trends Survey', 'formlayer-pro'), 'desc' => __('Analyze industry shifts with comprehensive data.', 'formlayer-pro'), 'icon' => 'dashicons-chart-area', 'cat' => 'marketing', 'is_pro' => true, 'fields' => [
				[ 'id' => 'f1', 'type' => 'dropdown', 'label' => 'Market Segment', 'options' => ['B2B', 'B2C', 'G2C', 'Enterprise'], 'required' => true ],
				[ 'id' => 'f2', 'type' => 'rating', 'label' => 'Current Market Sentiment', 'required' => true ],
				[ 'id' => 'f3', 'type' => 'text', 'label' => 'Primary Competitor Name', 'required' => true ],
				[ 'id' => 'f4', 'type' => 'textarea', 'label' => 'Predicted Future Industry Trends', 'required' => false ],
				[ 'id' => 'f5', 'type' => 'gdpr', 'label' => 'Agree to survey storage', 'required' => true ],
				[ 'id' => 'f6', 'type' => 'submit', 'label' => 'Submit Analysis' ]
			]]
		];

		// Merge and ensure pro versions override teasers
		$merged = [];
		$pro_ids = array_column($pro_templates, 'id');
		
		foreach ($templates as $t) {
			if (in_array($t['id'], $pro_ids)) {
				// Find the pro version
				foreach ($pro_templates as $pt) {
					if ($pt['id'] === $t['id']) {
						$merged[] = $pt;
						break;
					}
				}
			} else {
				$merged[] = $t;
			}
		}
		
		// Add any pro templates that aren't in free at all (unlikely but good for future)
		$free_ids = array_column($templates, 'id');
		foreach ($pro_templates as $pt) {
			if (!in_array($pt['id'], $free_ids)) {
				$merged[] = $pt;
			}
		}

		return $merged;
	}

	static function js_templates(){
		return [
			'integration_field' => '
				<div class="formlayer-setting-row" style="flex-direction: column; align-items: flex-start; gap: 8px;">
					<div class="formlayer-setting-info">
						<label>{{label}}</label>
					</div>
					<input type="text" name="{{name}}" class="formlayer-input int-setting-input" value="{{value}}" placeholder="{{placeholder}}" style="max-width: 100%;">
				</div>',
			'integration_textarea' => '
				<div class="formlayer-setting-row" style="flex-direction: column; align-items: flex-start; gap: 8px;">
					<div class="formlayer-setting-info">
						<label>{{label}}</label>
					</div>
					<textarea name="{{name}}" class="formlayer-input int-setting-input" placeholder="{{placeholder}}" style="max-width: 100%; min-height: 150px; font-family: monospace; font-size: 12px;">{{value}}</textarea>
				</div>'
		];
	}
}
