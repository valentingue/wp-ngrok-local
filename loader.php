<?php 
/*
Plugin Name: WP Ngrok Local
Description: COnfigure local website to work with ngrok tunnels
Version: 0.0.1
Author: Valentin Guerchet
Copyright 2024 Valentin Guerchet
Text Domain: wp_ngrok_local
Domain Path: /languages
*/

define('LOCALTUNNEL_ACTIVE', true);

if(!empty($_SERVER['HTTP_X_ORIGINAL_HOST']) && strpos($_SERVER['HTTP_X_ORIGINAL_HOST'], 'ngrok') !== FALSE) {
	if(
		isset($_SERVER['HTTP_X_ORIGINAL_HOST']) && 
		$_SERVER['HTTP_X_ORIGINAL_HOST'] === "https"
	) {
		$server_proto = 'https://';
	} else {
		$server_proto = 'http://';
	}
	
	$url = $server_proto . $_SERVER['HTTP_HOST'];
	if(!defined('WP_SITEURL') && !defined('WP_HOME')){
		define('WP_SITEURL', $url);
		define('WP_HOME', $url);
	}
	else{
		update_option('WP_SITEURL', $url);
		update_option('WP_HOME', $url);
	}
	
}


function change_urls($page_html) {
	if(defined('LOCALTUNNEL_ACTIVE') && LOCALTUNNEL_ACTIVE === true) {

	$wp_home_url = esc_url(home_url('/'));
	$rel_home_url = wp_make_link_relative($wp_home_url);

	$esc_home_url = str_replace('/', '\/', $wp_home_url);
	$rel_esc_home_url = str_replace('/', '\/', $rel_home_url);

	$rel_page_html = str_replace($wp_home_url, $rel_home_url, $page_html);
	$esc_page_html = str_replace($esc_home_url, $rel_esc_home_url, $rel_page_html);

	return $esc_page_html;
	}
}

function buffer_start_relative_url() { 
	if(defined('LOCALTUNNEL_ACTIVE') && LOCALTUNNEL_ACTIVE === true) {
	ob_start('change_urls'); 
	}
}
function buffer_end_relative_url() { 
	if(defined('LOCALTUNNEL_ACTIVE') && LOCALTUNNEL_ACTIVE === true) {
	@ob_end_flush(); 
	}
}

add_action('registered_taxonomy', 'buffer_start_relative_url');
add_action('shutdown', 'buffer_end_relative_url');