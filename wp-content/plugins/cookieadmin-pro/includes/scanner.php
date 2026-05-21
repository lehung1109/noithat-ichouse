<?php

namespace CookieAdminPro;

if(!defined('COOKIEADMIN_PRO_VERSION') || !defined('ABSPATH')){
	die('Hacking Attempt');
}

class Scanner {

    const SCAN_TIMEOUT     = 55; // Seconds

    /**
     * The main public method to start the scan.
     *
     * @param string $urls_to_scan The URLs of the site to scan.
     * @return array|\WP_Error The formatted array of cookies on success, or a WP_Error object on failure.
     */
    public static function start_scan($urls_to_scan) {
        
		global $cookieadmin, $cookieadmin_error;
		
		$args = [
			'method'  => 'POST',
			'timeout' => self::SCAN_TIMEOUT,
			'body'    => [
				'urls'    => $urls_to_scan
			]
		];

		$api_url = cookieadmin_pro_api_url(-1);
		
		$url = $api_url.'scanner.php?scanner=1&license='.$cookieadmin['license']['license'].'&url='.rawurlencode(site_url());

		// Use the built-in WordPress HTTP API to call your PHP endpoint.
		$response = wp_remote_post($url, $args);

		// --- Handle the response from the scanner server ---

		if (is_wp_error($response)) {
			// This catches network-level errors (e.g., DNS lookup failure, cURL error, timeout)
			$cookieadmin_error = $cookieadmin_error . ' ' . __('Network error: ' . $response->get_error_message(), 'cookieadmin');
			return false;
		}

		$status_code = wp_remote_retrieve_response_code($response);
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);

			// Handle specific errors from our endpoint
		if ($status_code !== 200) {
			$error_message = $data['error'] ?? 'An unknown error occurred on the scanner server.';

			$cookieadmin_error = $cookieadmin_error . ' ' . __('Status Code: '.$status_code. ' ' . $error_message, 'cookieadmin');
			return false;
		}

		if (json_last_error() !== JSON_ERROR_NONE) {
			
			$cookieadmin_error = $cookieadmin_error . ' ' . __('The scanner server returned an unreadable response.', 'cookieadmin');
			return false;
		}
			
		update_option('cookieadmin_pro_scanner', array('last_scan' => time()));
			
		if(!empty($data['cookies'])){
			
			// Success! The data is valid. Now format it for our internal use.
			return self::format_scan_results($data['cookies']);
			
		}
		
		return array();
    }
    
    /**
     * Converts the cookie array from the API into our internal format.
     * This is the same formatting function you had before.
     *
     * @param array $scan_results The array of cookies from the scanner.
     * @return array The formatted array ready for the database.
     */
    private static function format_scan_results(array $scan_results) {
        $formatted = [];
        foreach ($scan_results as $cookie) {
            $expires_datetime = (!empty($cookie['expires']) && $cookie['expires'] != -1) 
                ? gmdate("Y-m-d H:i:s", (int)$cookie['expires']) 
                : null;

            $formatted[$cookie['name']] = [
                'name'      => $cookie['name'],
                'value'     => !empty($cookie['value']) ? $cookie['value'] : '',
                'expires'   => $expires_datetime,
                'path'      => !empty($cookie['path']) ? $cookie['path'] : '',
                'domain'    => !empty($cookie['domain']) ? $cookie['domain'] : '',
                'secure'    => (bool) (isset($cookie['secure']) ? $cookie['secure'] : false),
                'httponly'  => (bool) (isset($cookie['httpOnly']) ? $cookie['httpOnly'] : false),
                'Max-Age'   => null,
                'samesite'  => $cookie['sameSite'] ?? null,
            ];
        }
        return $formatted;
    }
}