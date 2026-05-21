<?php
namespace CookieAdminPro;

if(!defined('COOKIEADMIN_PRO_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class Subdomain{

	static function get_base_domain_name($host) {

		$host = strtolower($host);
		if(filter_var($host, FILTER_VALIDATE_IP) || $host === ''){
			return $host;
		}

		if(($host === 'localhost') || (preg_match('/\.local$/', $host))){
			return $host;
		}
		
		$parts = explode('.', $host);
		$count = count($parts);
		
		if ($count < 2){
			return $host;
		}
		
		$multilevel_tlds_list = self::get_multilevel_tlds_list();

		// If the host itself is a suffix
		if(in_array($host, $multilevel_tlds_list)){
			return $host;
		}

		// 1. Check for 3-segment matches (e.g., k12.ca.us)
		if ($count >= 4){
			$lastThree = implode('.', array_slice($parts, -3));
			if(in_array($lastThree, $multilevel_tlds_list)){
				return implode('.', array_slice($parts, -4));
			}
		}

		// 2. Check for 2-segment matches (e.g., co.uk OR tokyo.jp via wildcard)
		if ($count >= 3){
			$lastTwo = implode('.', array_slice($parts, -2));
			if(in_array($lastTwo, $multilevel_tlds_list)){
				return implode('.', array_slice($parts, -3));
			}
		}

		// 3. Fallback for 1-segment TLDs (com, net, org)
		return implode('.', array_slice($parts, -2));
	}

	static function get_multilevel_tlds_list(){
		$multilevel_tlds_list = [

			// --- Europe ---
			'co.uk', 'org.uk', 'me.uk', 'ltd.uk', 'plc.uk', 'net.uk', 'sch.uk', 'ac.uk', 'gov.uk',
			'com.es', 'nom.es', 'org.es', 'gob.es', 'edu.es',
			'com.fr', 'tm.fr', 'asso.fr', 'nom.fr', 'gouv.fr',
			'gov.it', 'edu.it',
			'com.tr', 'net.tr', 'org.tr', 'edu.tr', 'gov.tr', 'bel.tr', 'gen.tr',
			'com.ua', 'edu.ua', 'gov.ua', 'in.ua', 'net.ua', 'org.ua',

			// --- Americas ---
			'com.br', 'net.br', 'org.br', 'edu.br', 'gov.br', 'adm.br', 'adv.br', 'nom.br',
			'com.mx', 'net.mx', 'org.mx', 'edu.mx', 'gob.mx',
			'com.ar', 'net.ar', 'org.ar', 'gob.ar', 'edu.ar',
			'com.co', 'net.co', 'org.co', 'edu.co', 'gov.co',
			'ab.ca', 'bc.ca', 'mb.ca', 'nb.ca', 'nf.ca', 'nl.ca', 'ns.ca', 'on.ca', 'qc.ca', 'sk.ca',
			'fed.us', 'isa.us', 'kids.us', 'k12.ca.us', 'k12.ny.us',

			// --- Asia / Pacific ---
			'com.au', 'net.au', 'org.au', 'edu.au', 'gov.au', 'asn.au', 'id.au',
			'co.nz', 'net.nz', 'org.nz', 'ac.nz', 'govt.nz', 'kiwi.nz',
			'com.in', 'net.in', 'org.in', 'ind.in', 'gen.in', 'ac.in', 'edu.in', 'gov.in', 'co.in', 'firm.in', 'info.in',
			'com.cn', 'net.cn', 'org.cn', 'gov.cn', 'edu.cn',
			'com.sg', 'net.sg', 'org.sg', 'edu.sg', 'gov.sg',
			'com.my', 'net.my', 'org.my', 'edu.my', 'gov.my',
			'co.id', 'net.id', 'org.id', 'web.id', 'ac.id', 'go.id',
			'co.kr', 'ne.kr', 'or.kr', 'go.kr', 'ac.kr',
			'com.tw', 'net.tw', 'org.tw', 'edu.tw', 'gov.tw',
			'com.vn', 'net.vn', 'org.vn', 'edu.vn', 'gov.vn',
			'ac.th', 'co.th', 'go.th', 'in.th',
			'com.ph', 'net.ph', 'org.ph', 'gov.ph',

			// --- Africa / Middle East ---
			'co.za', 'org.za', 'net.za', 'edu.za', 'gov.za',
			'co.il', 'org.il', 'net.il', 'ac.il', 'gov.il',
			'co.ae', 'net.ae', 'org.ae', 'gov.ae',
			'com.eg', 'edu.eg', 'gov.eg', 'org.eg',
		];

		return $multilevel_tlds_list;
	}
}