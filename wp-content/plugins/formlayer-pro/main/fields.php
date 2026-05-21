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

class Fields{

	static function add_categories($categories){
		$categories[] = ['id' => 'pro', 'label' => __('Premium Fields', 'formlayer-pro'), 'open' => false];
		return $categories;
	}

	static function add_field_types($field_types){
		$pro_fields = [
			['type' => 'address', 'label' => 'Address Fields', 'icon' => 'dashicons-location', 'category' => 'pro'],
			['type' => 'date', 'label' => 'Date / Time Picker', 'icon' => 'dashicons-calendar-alt', 'category' => 'pro'],
			['type' => 'url', 'label' => 'Website / URL', 'icon' => 'dashicons-admin-site', 'category' => 'pro'],
			['type' => 'password', 'label' => 'Password', 'icon' => 'dashicons-lock', 'category' => 'pro'],
			['type' => 'hidden', 'label' => 'Hidden Field', 'icon' => 'dashicons-visibility-faint', 'category' => 'pro'],
			['type' => 'image', 'label' => 'Image Upload', 'icon' => 'dashicons-format-image', 'category' => 'pro'],
			['type' => 'file', 'label' => 'File Upload', 'icon' => 'dashicons-upload', 'category' => 'pro'],
			['type' => 'phone', 'label' => 'Phone Number', 'icon' => 'dashicons-phone', 'category' => 'pro'],
			['type' => 'camera', 'label' => 'Camera Field', 'icon' => 'dashicons-camera', 'category' => 'pro'],
			['type' => 'richtext', 'label' => 'Rich Text Editor', 'icon' => 'dashicons-editor-paragraph', 'category' => 'pro'],
		];

		return array_merge($field_types, $pro_fields);
	}

}