<?php
/*
* SITESEO
* https://siteseo.io
* (c) SiteSEO Team
*/

namespace SiteSEOPro;

if(!defined('ABSPATH')){
	die('Hacking Attempt !');
}

class LinksManage{

	static function init(){
		global $siteseo;

		if(empty($siteseo->pro['toggle_state_external_links'])){
			return;
		}
		
		add_filter('the_content', '\SiteSEOPro\LinksManage::manage_external_links', 999);
	}

	static function manage_external_links($content){
		if(empty($content)){
			return $content;
		}

		global $siteseo;

		$options = $siteseo->pro;

		$nofollow = !empty($options['external_links_nofollow']) ? $options['external_links_nofollow'] : '';
		$sponsored = !empty($options['external_links_sponsored']) ? $options['external_links_sponsored'] : '';
		$target_blank = !empty($options['external_links_target_blank']) ? $options['external_links_target_blank'] : '';
		$noreferrer = !empty($options['external_links_noreferrer']) ? $options['external_links_noreferrer'] : '';
		$exclude_links = !empty($options['external_links_exclude']) ? $options['external_links_exclude'] : '';

		// Convert exclude domains to array
		$exclude_domains = array_filter(array_map('trim', explode(',', $exclude_links)));
		// Get site host
		$site_host = parse_url(home_url(), PHP_URL_HOST);

		libxml_use_internal_errors(true);

		$dom = new \DOMDocument();

		$dom->loadHTML('<?xml encoding="utf-8" ?>' . $content);

		$links = $dom->getElementsByTagName('a');

		foreach($links as $link){

			$href = $link->getAttribute('href');

			if(empty($href)){
				continue;
			}

			// Skip anchors, mailto, tel
			if(strpos($href, '#') === 0 || strpos($href, 'mailto:') === 0 || strpos($href, 'tel:') === 0){
				continue;
			}

			$link_host = parse_url($href, PHP_URL_HOST);

			// If no host (relative link), skip
			if(empty($link_host)){
				continue;
			}

			// Skip internal links
			if($link_host === $site_host){
				continue;
			}

			// Check excluded domains
			$skip = false;
			foreach($exclude_domains as $domain){
				if(strpos($link_host, $domain) !== false){
					$skip = true;
					break;
				}
			}

			if($skip){
				continue;
			}

			// Get existing rel values
			$rel = $link->getAttribute('rel');
			$rel_values = array_filter(explode(' ', $rel));

			// Add rel attributes
			if(!empty($nofollow) && !in_array('nofollow', $rel_values)){
				$rel_values[] = 'nofollow';
			}

			if(!empty($sponsored) && !in_array('sponsored', $rel_values)){
				$rel_values[] = 'sponsored';
			}

			if(!empty($noreferrer)){
				if(!in_array('noopener', $rel_values)){
					$rel_values[] = 'noopener';
				}

				if(!in_array('noreferrer', $rel_values)){
					$rel_values[] = 'noreferrer';
				}
			}

			// Set rel attribute
			if(!empty($rel_values)){
				$link->setAttribute('rel', implode(' ', $rel_values));
			}

			// Target blank
			if(!empty($target_blank)){
				$link->setAttribute('target', '_blank');
			}
		}

		// Save HTML
		$html = $dom->saveHTML();

		// Remove unwanted tags added by DOMDocument
		$body = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $html);

		return $body;
	}
}