<?php
/*
* SiteSEO
* https://siteseo.io
* (c) SiteSEO Team
*/

namespace SiteSEOPro;

if(!defined('ABSPATH')){
	die('HACKING ATTEMPT!');
}

class Alerts{

	static function setup_alerts_scheduled(){
		global $siteseo;

		if(empty($siteseo->pro['toggle_state_seo_alerts'])){
			
			if(wp_next_scheduled('siteseo_check_seo_alerts')){
				wp_clear_scheduled_hook('siteseo_check_seo_alerts');
			}
			return;
		}

		if(!wp_next_scheduled('siteseo_check_seo_alerts')){
			wp_schedule_event(time(), 'daily', 'siteseo_check_seo_alerts');
		}
	}

	static function seo_alerts(){
		global $siteseo;

		$settings = $siteseo->pro;

		if(empty($settings['toggle_state_seo_alerts'])){
		    return;
		}

		$alerts = [];

		// Noindex alert
		if(!empty($settings['noindex_alert'])){
			$noindex_status = self::check_homepage_noindex();
			if($noindex_status['alert']){
				$alerts[] = $noindex_status['message'];
			}
		}

		// Sitemap alert
		if(!empty($settings['sitemap_alert'])){
			$sitemap_status = self::check_sitemap();
			if($sitemap_status['alert']){
				$alerts[] = $sitemap_status['message'];
			}
		}

		// robots.txt alert
		if(!empty($settings['robots_txt_alert'])){
			$robots_status = self::check_robots_txt();
			if($robots_status['alert']){
				$alerts[] = $robots_status['message'];
			}
		}

		// Send alerts, issues
		if(!empty($alerts)){
			self::send_alerts($alerts, $settings);
		}
	}

	static function check_homepage_noindex(){
		global $siteseo;

		$result = [
			'alert' => false,
			'message' => ''
		];

		// global Noindex
		if(!empty($siteseo->titles_settings['titles_noindex'])){
			$result['alert'] = true;
			$result['message'] = __(
				'Global noindex is enabled. This prevents search engines from indexing your entire site, including the homepage.',
				'siteseo-pro'
			);

			return $result;
		}

		$front_page_id = get_option('page_on_front');

		if(!empty($front_page_id)){
			$noindex_meta = get_post_meta($front_page_id, '_siteseo_robots_index', true);

			if(!empty($noindex_meta) && $noindex_meta === 'yes'){
				$result['alert'] = true;
				$result['message'] = sprintf(
					/* translators: %d: Post ID */
					__('Your homepage (ID: %d) is set to noindex. This prevents search engines from indexing your site.', 'siteseo-pro'),
					$front_page_id
				);
			}
		}

		return $result;
	}

	static function check_sitemap(){
		$result = [
			'alert' => false,
			'message' => ''
		];

		$sitemap_url = home_url('/sitemaps.xml');
		$response = wp_remote_get($sitemap_url, [
			'timeout' => 10,
			'sslverify' => false
		]);

		if(is_wp_error($response)){
			$result['alert'] = true;
			$result['message'] = sprintf(
				/* translators: %s: status code */
				__('Sitemap error: %s', 'siteseo-pro'),
				$response->get_error_message()
			);

			return $result;
		}

		$status_code = wp_remote_retrieve_response_code($response);

		if($status_code >= 400){
			$result['alert'] = true;
			$result['message'] = sprintf(
				/* translators: %1$d: HTTP status code, %2$s: HTTP response message */
				__('Sitemap returned error %1$d: %2$s', 'siteseo-pro'),
				$status_code,
				wp_remote_retrieve_response_message($response)
			);
		}

		return $result;
	}

	static function check_robots_txt(){
		$result = [
			'alert' => false,
			'message' => ''
		];

		$robots_url = home_url('/robots.txt');
		$response = wp_remote_get($robots_url, [
			'timeout' => 10,
			'sslverify' => false
		]);

		if(is_wp_error($response)){
			$result['alert'] = true;
			$result['message'] = sprintf(
				/* translators: %s: status code */
				__('robots.txt error: %s', 'siteseo-pro'),
				$response->get_error_message()
			);

			return $result;
		}

		$status_code = wp_remote_retrieve_response_code($response);

		if($status_code >= 400){
			$result['alert'] = true;
			$result['message'] = sprintf(
				/* translators: %1$d: HTTP status code, %2$s: HTTP response message */
				__('robots.txt returned error %1$d: %2$s', 'siteseo-pro'),
				$status_code,
				wp_remote_retrieve_response_message($response)
			);
		}

		return $result;
	}

	static function send_alerts($alerts, $settings){
		global $siteseo;

		$recipients = !empty($settings['alert_recipients']) ? $settings['alert_recipients'] : '';

		/* translators: %s: status code */
		$subject = sprintf(__('⚠️ SEO Alert: Issues Found on %s', 'siteseo-pro'), get_bloginfo('name'));

		$message  = '<p>'. __('Hello,', 'siteseo-pro').'</p>';

		$message .= '<p>'. __('SiteSEO has detected important SEO issues on your website.', 'siteseo-pro').'</p>';

		$message .= '<p><strong>'. __('Detected issues:', 'siteseo-pro').'</strong></p><ul>';

		foreach($alerts as $alert){
			$message .= '<li>'.esc_html($alert).'</li>';
		}

		$message .= '</ul>';

		$message .= '<p>'. __('Please review and resolve these issues as soon as possible.', 'siteseo-pro').'</p>';

		$message .= '<p>'. __('Need help?', 'siteseo-pro').' ';
		$message .= '<a href="https://softaculous.deskuss.com/open.php?topicId=22" target="_blank">'.esc_html__('Contact support.', 'siteseo-pro').'</a></p>';

		$message .= '<p>— '. __('SiteSEO', 'siteseo-pro').'</p>';

		$headers = ['Content-Type: text/html; charset=UTF-8'];

		// Email
		if(!empty($recipients)){
			wp_mail($recipients, $subject, $message, $headers);
		}

		// Slack
		if(!empty($settings['webhook_url'])){
			self::slack_notification($alerts, $settings['webhook_url']);
		}
	}

	static function slack_notification($alerts, $webhook_url){
		$attachments = [];
		foreach($alerts as $alert){
			$attachments[] = [
				'color' => '#f44336',
				'text' => $alert
			];
		}

		$body = [
			/* translators: %s: status code */
			'text' => sprintf(__('⚠️ SEO Alert: Issues Found on [%s]', 'siteseo-pro'), get_bloginfo('name')),
			'attachments' => $attachments
		];

		wp_remote_post($webhook_url, [
			'body' => json_encode($body),
			'headers' => ['Content-Type' => 'application/json']
		]);
	}
}