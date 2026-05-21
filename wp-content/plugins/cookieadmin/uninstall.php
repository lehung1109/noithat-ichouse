<?php

// if uninstall.php is not called by WordPress, die
if(!defined('WP_UNINSTALL_PLUGIN')){
    die;
}

// Deleting options
delete_option('cookieadmin_version');
delete_option('cookieadmin_law');
delete_option('cookieadmin_scan');
delete_option('cookieadmin_settings');
delete_option('cookieadmin_consent_settings');