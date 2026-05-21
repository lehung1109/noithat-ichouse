<?php
namespace FormLayer;

if(!defined('ABSPATH')){
	exit;
}

class Util{

	
	static function get_form_field_labels($form_id) {
		$form = get_post($form_id);
		$field_labels = [];
		if ($form) {
			$form_data = json_decode($form->post_content, true);
			if (is_array($form_data) && !empty($form_data['fields'])) {
				foreach ($form_data['fields'] as $field) {
					$name = self::get_field_name($field);
					$field_labels[$name] = !empty($field['label']) ? $field['label'] : $name;
				}
			}
		}
		return $field_labels;
	}

	static function get_form_field_types($form_id){
		$form = get_post($form_id);
		$field_types = [];
		if(!empty($form)){
			$form_data = json_decode($form->post_content, true);
			if(is_array($form_data) && !empty($form_data['fields'])){
				foreach($form_data['fields'] as $field){
					$name = self::get_field_name($field);
					$field_types[$name] = isset($field['type']) ? $field['type'] : 'text';
				}
			}
		}

		return $field_types;
	}

	static function get_field_name($field) {
		if (!empty($field['name_attr'])) {
			return $field['name_attr'];
		}
		$id = isset($field['id']) ? $field['id'] : sanitize_title(isset($field['label']) ? $field['label'] : '');
		return 'field_' . $id;
	}

	static function get_post_id_by_display_id($display_id) {
		$posts = get_posts([
			'post_type' => 'formlayer_form',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'meta_query' => [
				[
					'key' => '_formlayer_display_id',
					'value' => $display_id,
					'compare' => '='
				]
			],
			'posts_per_page' => 1,
			'post_status' => 'any'
		]);
		return !empty($posts) ? $posts[0]->ID : 0;
	}
}