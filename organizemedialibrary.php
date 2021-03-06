<?php
/*
Plugin Name: Organize Media Library
Plugin URI: http://wordpress.org/plugins/organize-media-library/
Version: 4.0
Description: Thumbnails rebuild and organize uploads into month- and year-based folders or specified folders. Move the upload folder, it is possible to move all uploaded files. URL in the content, replace with the new URL.
Author: Katsushi Kawamori
Author URI: http://riverforest-wp.info/
Text Domain: organizemedialibrary
Domain Path: /languages
*/

/*  Copyright (c) 2015- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

	load_plugin_textdomain('organizemedialibrary', false, basename( dirname( __FILE__ ) ) . '/languages' );

	define("ORGANIZEMEDIALIBRARY_PLUGIN_BASE_FILE", plugin_basename(__FILE__));
	define("ORGANIZEMEDIALIBRARY_PLUGIN_BASE_DIR", dirname(__FILE__));
	define("ORGANIZEMEDIALIBRARY_PLUGIN_URL", plugins_url($path='',$scheme=null).'/organize-media-library');

	include_once ORGANIZEMEDIALIBRARY_PLUGIN_BASE_DIR.'/inc/OrganizeMediaLibrary.php';
	$organizemedialibrary = new OrganizeMediaLibrary();
	list($upload_dir, $upload_url, $upload_path) = $organizemedialibrary->upload_dir_url_path();
	define("ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_DIR", $upload_dir);
	define("ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_URL", $upload_url);
	define("ORGANIZEMEDIALIBRARY_PLUGIN_UPLOAD_PATH", $upload_path);
	unset($organizemedialibrary ,$upload_dir, $upload_url, $upload_path);

	require_once( ORGANIZEMEDIALIBRARY_PLUGIN_BASE_DIR.'/req/OrganizeMediaLibraryRegist.php' );
	$organizemedialibraryregist = new OrganizeMediaLibraryRegist();
	add_action('admin_init', array($organizemedialibraryregist, 'register_settings'));
	unset($organizemedialibraryregist);

	require_once( ORGANIZEMEDIALIBRARY_PLUGIN_BASE_DIR.'/req/OrganizeMediaLibraryAdmin.php' );
	$organizemedialibraryadmin = new OrganizeMediaLibraryAdmin();
	add_filter( 'plugin_action_links', array($organizemedialibraryadmin, 'settings_link'), 10, 2 );
	add_action( 'admin_menu', array($organizemedialibraryadmin, 'add_pages'));
	add_action( 'admin_enqueue_scripts', array($organizemedialibraryadmin, 'load_custom_wp_admin_style') );
	$postcount = 0;
	$organizemedialibraryadmin->postcount = $postcount;
	add_action( 'admin_footer', array($organizemedialibraryadmin, 'load_custom_wp_admin_style2') );
	add_filter( 'wp_get_attachment_link', array($organizemedialibraryadmin, 'modify_attachment_link'), 10, 6 );
	unset($organizemedialibraryadmin);

?>