<?php
/*
Plugin Name: Unwanted Cleaner
Plugin URI: https://presskopp.com/
Description: This plugin removes unwanted plugins during the WordPress core upgrade process. You can manage the list of unwanted plugins from the settings page.
Version: 1.0.0
Author: Presskopp
Author URI: https://presskopp.com/
License: GPL2
Text Domain: unwanted-cleaner
Domain Path: /languages
*/

/** Prevent this file from being accessed directly */
if (!defined('ABSPATH')) {
    die("Direct access of plugin files is not allowed.");
}

/** Define UWP_PLUGIN_FILE */
if (!defined('UWP_PLUGIN_FILE')) {
    define('UWP_PLUGIN_FILE', __FILE__);
}

/** Constant pointing to the root directory path of the plugin */
if (!defined("UWP_PLUGIN_DIRECTORY")) {
    define("UWP_PLUGIN_DIRECTORY", plugin_dir_path(__FILE__));
}

if (!defined("UWP_PLUGIN_VERSION")) {
    define("UWP_PLUGIN_VERSION", "1.0.0");
}

/** Constant pointing to the root directory URL of the plugin */
if (!defined("UWP_PLUGIN_URL")) {
    define("UWP_PLUGIN_URL", plugin_dir_url(__FILE__));
}

/** Constant defining the textdomain for localization */
if (!defined("UWP_PLUGIN_TEXTDOMAIN")) {
    define("UWP_PLUGIN_TEXTDOMAIN", "unwanted-cleaner");
}

if (!defined("WP_PLUGIN_DIR")) {
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '\plugins' );
}

// Include the main plugin class file
require_once plugin_dir_path(__FILE__) . 'includes/class-unwanted-cleaner.php';

// Instantiate the main plugin class
$unwanted_cleaner = new unwantedcleaner\unwanted_cleaner();