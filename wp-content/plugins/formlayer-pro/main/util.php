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

class Util{

    static function get_unread_count(){
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}formlayer_entries WHERE status = 'unread'");
    }

    static function get_browser_info($ua){
        $browser = "Unknown Browser";
        $os = "Unknown OS";

        if (preg_match('/MSIE/i', $ua) && !preg_match('/Opera/i', $ua)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Firefox/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Chrome/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/Opera/i', $ua)) {
            $browser = 'Opera';
        } elseif (preg_match('/Netscape/i', $ua)) {
            $browser = 'Netscape';
        }

        if (preg_match('/windows|win32/i', $ua)) {
            $os = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $os = 'Mac OS';
        } elseif (preg_match('/linux/i', $ua)) {
            $os = 'Linux';
        } elseif (preg_match('/iphone/i', $ua)) {
            $os = 'iPhone';
        } elseif (preg_match('/android/i', $ua)) {
            $os = 'Android';
        }

        return ['browser' => $browser, 'os' => $os];
    }
}