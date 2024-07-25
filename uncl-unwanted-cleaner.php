<?php
/*
Plugin Name: Unwanted Cleaner
Plugin URI: https://presskopp.com/unwanted-cleaner
Description: This plugin removes unwanted plugins during the WordPress core upgrade process. You can manage the list of unwanted plugins from the settings page.
Version: 1.0.1
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

/** Define UNCL_PLUGIN_FILE */
if (!defined('UNCL_PLUGIN_FILE')) {
    define('UNCL_PLUGIN_FILE', __FILE__);
}

if (!defined("UNCL_PLUGIN_VERSION")) {
    define("UNCL_PLUGIN_VERSION", "1.0.1");
}

/** Constant pointing to the root directory URL of the plugin */
if (!defined("UNCL_PLUGIN_URL")) {
    define("UNCL_PLUGIN_URL", plugin_dir_url(__FILE__));
}

/** Constant defining the textdomain for localization */
if (!defined("UNCL_PLUGIN_TEXTDOMAIN")) {
    define("UNCL_PLUGIN_TEXTDOMAIN", "unwanted-cleaner");
}

if (!defined("UNCL_PLUGIN_DIR")) {
    define( 'UNCL_PLUGIN_DIR', WP_CONTENT_DIR . '\plugins' );
}

// Include the main plugin class file
require_once plugin_dir_path(__FILE__) . 'includes/uncl-class-unwanted-cleaner.php';

// Instantiate the main plugin class
// $uncl_unwanted_cleaner = new uncl_unwantedcleaner\uncl_unwanted_cleaner();
new uncl_unwantedcleaner\uncl_unwanted_cleaner();