<?php

namespace CookieAdminPro;

if(!defined('COOKIEADMIN_PRO_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class ContentBlock{
	
	static function init(){
		global $cookieadmin_settings;

		if(is_admin() || cookieadmin_is_editor_mode()){
			return;
		}
		
		if(empty($cookieadmin_settings['content_blocking'])){
			return;
		}
		
		if(defined('COOKIEADMIN_SCANNER')){
			return;
		}

		if(self::has_consent()){
			return;
		}

		add_action('wp_head', [__CLASS__, 'add_embed_block_css']);
		add_action('template_redirect', [__CLASS__, 'start_buffer']);
		add_action('shutdown', [__CLASS__, 'end_buffer']);
	}

	static function add_embed_block_css(){
		echo '<style>
		    .cookieadmin-content-blocker{
		        position: relative;
		    }
		    .wp-block-embed__wrapper .cookieadmin-content-blocker, 
		    .ep-embed-content-wraper .cookieadmin-content-blocker, 
		    .elementor-widget-audio .cookieadmin-content-blocker, 
		    .elementor-widget-video .cookieadmin-content-blocker,
			.pagelayer-video-holder .cookieadmin-content-blocker
		    {
		        position: unset;
		    }
			.wp-block-embed__wrapper.epyt-figure .cookieadmin-content-blocker{
				position: relative;
			}
			.cookieadmin-content-thumbnail{
				position:absolute;
				top:0;
				left:0;
				height:100%;
				width:100%;
				z-index:1;
				background-color:#999;
			}
			.cookieadmin-accept-content{
				position:absolute;
				top:0;
				left:0;
				right:0;
				bottom:0;
				z-index:2;
				display:flex;
				justify-content:center;
				align-items:center;
			}
			.cookieadmin-accept-content div{
				background:rgba(0,0,0,0.8);
				color:#fff;
				padding:10px 20px;
				border-radius:30px;
				font-size:14px;
			}
		</style>';
	}

	static function has_consent($preference = ''){
		if(empty($_COOKIE['cookieadmin_consent'])){
			return false;
		}

		$consent = json_decode(wp_unslash($_COOKIE['cookieadmin_consent']), true);
		if(empty($consent)){
			return false;
		}

		if(!empty($consent['reject']) && ($consent['reject'] === 'true' || $consent['reject'] === true)){
			return false;
		}

		if(!empty($consent['accept']) && ($consent['accept'] === 'true' || $consent['accept'] === true)){
			return true;
		}

		if(!empty($preference)){
			if(!empty($consent[$preference]) && ($consent[$preference] === 'true' || $consent[$preference] === true)){
				return true;
			}
		}

		return false;
	}

	static function start_buffer(){

		if(is_admin() || wp_doing_ajax()){
			return false;
		}

		// Don't load this for REST API requests.
		if(defined('REST_REQUEST') && REST_REQUEST){
			return;
		}

		// Start output buffer.
		ob_start([__CLASS__, 'process_html']);
	}

	static function process_html($html){
		global $cookieadmin_settings;
		
		if(empty($html) || !is_string($html)){
			return $html;
		}
		
		if(!empty($cookieadmin_settings['content_blocking'])){
			$html = self::block_embeds($html);
		}
		
		return $html;
	}

	static function end_buffer(){
		if(!empty(ob_get_length())){
			ob_end_flush();
		}
	}
	
	static function get_embed_services(){
		return [
			'youtube' => [
				'name' => 'YouTube',
				'category' => 'marketing',
				'patterns' => ['youtube.com/embed', 'youtu.be/', 'youtube-nocookie.com'],
			],
			'vimeo' => [
				'name' => 'Vimeo',
				'category' => 'marketing',
				'patterns' => ['vimeo.com/video', 'player.vimeo.com'],
			],
			'dailymotion' => [
				'name' => 'Dailymotion',
				'category' => 'marketing',
				'patterns' => ['www.dailymotion.com/embed', 'geo.dailymotion.com/player.html'],
			],
			'soundcloud' => [
				'name' => 'SoundCloud',
				'category' => 'marketing',
				'patterns' => ['soundcloud.com/player', 'w.soundcloud.com'],
			],
			'maps' => [
				'name' => 'Google Maps',
				'category' => 'marketing',
				'patterns' => ['maps.google', 'maps.googleapis.com', 'google.com/maps', 'goo.gl/maps'],
			],
		];
	}
	
	static function get_enabled_services(){
		global $cookieadmin_settings;
		
		$key = 'content_blocking_services';
		$enabled = !empty($cookieadmin_settings[$key]) ? $cookieadmin_settings[$key] : [];
		
		if(!is_array($enabled)){
			$enabled = array_filter(array_map('trim', explode(',', $enabled)));
		}
		
		return $enabled;
	}
	
	static function find_matching_service($content, $services){
		$content_lower = strtolower($content);
		
		foreach($services as $key => $service){
			foreach($service['patterns'] as $pattern){
				if(strpos($content_lower, strtolower($pattern)) !== false){
					return [
						'service_key' => $key,
						'service_name' => $service['name'],
						'category' => $service['category'],
					];
				}
			}
		}
		
		return null;
	}
	
	static function block_embeds($html){

		$enabled_services = self::get_enabled_services();
		$services = self::get_embed_services();

		if(!preg_match_all('/<iframe(\s+[^>]*)?>.*?<\/iframe>/is', $html, $matches)){
			return $html;
		}
		
		if(empty($matches[0]) || !is_array($matches[0])){
			return $html;
		}
		
		foreach($matches[0] as $iframe){

			$match = self::find_matching_service($iframe, $services);
			
			if(empty($match)){
				continue;
			}

			if(empty($enabled_services) || !in_array($match['service_key'], $enabled_services)){
				continue;
			}

			if(self::has_consent($match['category'])){
				continue;
			}
			
			// Extracting url of the content if any
			$url = '';
			if(preg_match('/(?:src|data|href)=["\']([^"\']+)["\']/i', $iframe, $m)){
				if(!empty($m[1])){
					$url = $m[1];
				}
			}

			$thumbnail = '';
			if(!empty($url)){
				$thumbnail = self::get_thumbnail_url($match['service_key'], $url);
			}
			
			$service_info = [
				'service' => $match['service_name'],
				'service_key' => $match['service_key'],
				'category' => $match['category'],
				'embed_type' => 'iframe',
				'thumbnail' => $thumbnail,
			];
			
			$placeholder = self::get_embed_placeholder($iframe, $service_info);
			$html = str_replace($iframe, $placeholder, $html);
		}
		
		return $html;
	}
	
	static function get_embed_placeholder($iframe, $service_info){

		$name = $service_info['service'];
		$category = $service_info['category'];
		$thumbnail = !empty($service_info['thumbnail']) ? $service_info['thumbnail'] : '';
		
		$html = '<div class="cookieadmin-content-blocker" data-cookieadmin-category="' . esc_attr($category) . '" data-cookieadmin-service="' . esc_attr($name).'">';
		
		$html .= '<div class="cookieadmin-content-thumbnail">';
		
		if($thumbnail){
			$html .= '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($name) . ' placeholder image" style="width:100%;height:100%;object-fit:cover;">';
		}

		$html .= '</div>';
		
		$html .= '<div class="cookieadmin-accept-content"><div>' . sprintf(esc_html__('Accept %s cookies to load content', 'cookieadmin'), esc_html($category)) . '</div></div>';

		// Replace src to data-cookieadmin-src
		$html .= str_replace('src=', 'data-cookieadmin-src=', $iframe);

		$html .= '</div>';
		
		return $html;
	}

	static function get_thumbnail_url($service_key, $url){
		if($service_key === 'vimeo'){
			$oembed = new \WP_oEmbed();
			$provider = @$oembed->get_provider($url);
			if(!empty($provider)){
				$response = @$oembed->fetch($provider, $url, []);
				if(!empty($response->thumbnail_url)){
					return $response->thumbnail_url;
				}
			}
		}
		if($service_key === 'youtube'){
			$pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';
			if(preg_match($pattern, $url, $y_matches)){
				if(!empty($y_matches[1])){
					return 'https://img.youtube.com/vi/'.$y_matches[1].'/maxresdefault.jpg';
				}
			}
		}
		if($service_key === 'dailymotion'){
			if(preg_match('/(?:video\/|video=)([a-zA-Z0-9]+)/', $url, $sc_matches)){
				if(!empty($sc_matches[1])){
					return 'https://www.dailymotion.com/thumbnail/video/' . $sc_matches[1];
				}
			}
		}
	}
}
