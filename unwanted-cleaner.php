<?php
/*
Plugin Name: Unwanted Cleaner
Plugin URI: https://wordpress.org/plugins/unwanted-cleaner/
Description: This plugin removes unwanted plugins during the WordPress core upgrade process. You can manage the list of unwanted plugins from the settings page.
Version: 1.0.0
Author: Presskopp
Author URI: https://profiles.wordpress.org/presskopp/
License: GPL2
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


class Unwanted_Cleaner {
    private static $initialized = false;
    private $unwanted_plugins_option;
    private $unwanted_plugins = array();
    

    public function __construct() {
        error_log('constructor called (our own code)');

        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        
        $this->unwanted_plugins_option = 'unwanted_plugins_list';

        register_activation_hook(UWP_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(UWP_PLUGIN_FILE, array($this, 'deactivate'));

        add_action('upgrader_process_complete', array($this, 'delete_unwanted_plugins_after_core_upgrade'), 10, 2);
		add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('activated_plugin', array($this, 'on_plugin_activation'));
        add_action('init', array($this, 'init'));

        // unused?
        // add_action('admin_post_delete_unwanted_plugins', 'process_delete_unwanted_plugins_form');

        // Ajax handler
        add_action('wp_ajax_handler_uwp', array( $this,'unwanted_plugins_handler'));
    }

    // main function to delete the unwanted plugins
    public function delete_unwanted_plugins() {
        error_log("delete_unwanted_plugins was called");
    
        $installed_plugins = get_plugins();
        $plugins_to_delete = array();
        $plugin_list =  count($this->unwanted_plugins)==1 ? explode( ",", $this->unwanted_plugins[0] ) : $this->unwanted_plugins;

		// Do we have a "hello.php" in the plugins directory?
        $this->delete_hello_php_above_plugin();
        error_log(gettype($plugin_list));

        // Go through all of the installed plugins so if a plugin is active, it can be deactivated first
        foreach ($installed_plugins as $plugin_file => $plugin_data) {
            error_log("now checking: " . $plugin_file);
            error_log(in_array(dirname($plugin_file), $plugin_list));
            error_log(dirname($plugin_file));
            error_log(json_encode($plugin_list));
            if (in_array(dirname($plugin_file), $plugin_list)) {

                if (is_plugin_active($plugin_file)) {
                    deactivate_plugins($plugin_file);
                }

                // call uninstall so uninstall routine can run before deletion (this only happens if the plugin was activated)
                // uninstall_plugins($plugin_file);
                // but what if a plugin asks the user anything on deinstallaion?
                // hmm..

                $deleted = delete_plugins(array($plugin_file));

                if ($deleted) {
                    $plugins_to_delete[] = $plugin_file;
                    error_log($plugin_file . " deleted successfully.");
                }
                else {
                    error_log("Failed to delete " . $plugin_file);
                }

            }
        }

        return !empty($plugins_to_delete);
    }

    public function save_unwanted_plugins_list() {
        if (!current_user_can('manage_options')) {
            return;
        }
        if (isset($_POST['save_plugin_list'])) {
            update_option('plugin_list_uwp', $_POST['plugin_list_uwp']);
            error_log('Plugin-Liste erfolgreich gespeichert!');
        }
    }

    public function delete_unwanted_plugins_after_core_upgrade($upgrader_object, $options) {
        error_log('delete_unwanted_plugins_after_core_upgrade');

        // Check if a core upgrade was done
        if ($options['action'] === 'update' && $options['type'] === 'core') {
            
            // Load the list of unwanted plugins
            $unwanted_plugins = get_option('unwanted_plugins_list', array());
    
            // undefined function - why??
            // delete_unwanted_plugins();

            // Überprüfen und Löschen der unerwünschten Plugins
            foreach ($unwanted_plugins as $plugin) {
                $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin;
                // error_log("delete_plugin_folder: " . $plugin_dir);
                if (is_dir($plugin_dir)) {
                    $deleted = $this->delete($plugin_dir, true); // recursively delete plugin folder
                    if ($deleted) {
                        error_log('Deleted unwanted plugin folder: ' . $plugin);
                    } else {
                        error_log('Failed to delete unwanted plugin folder: ' . $plugin);
                    }
                }
            }

            // Delete hello.php file, if hello-dolly is in list of unwanted plugins
            if (in_array('hello-dolly', $unwanted_plugins)) {
                $this->delete_hello_php_above_plugin();
            }
        }
    }

    // Delete hello.php file if it exists above the plugin folder
    public function delete_hello_php_above_plugin() {
        $hello_php_file = WP_PLUGIN_DIR . '/hello.php';
        // error_log("hello_php_file: " . $hello_php_file);
        if (file_exists($hello_php_file)) {
            deactivate_plugins($hello_php_file);
            // $file_exists_before_deletion = true;
            
            // = NULL for some reason
            // $deleted = wp_delete_file($hello_php_file);
            wp_delete_file($hello_php_file);

            /*
            ob_start();
            var_dump($deleted);
            $dumped_value = ob_get_clean();
            error_log("deleted: " . $dumped_value);
            */

            $file_still_exists_after_deletion = file_exists($hello_php_file);
            // unset($plugins[$hello_php_file]);
            // 2DO user response
            if ($file_still_exists_after_deletion) {
                error_log('Failed to delete hello.php file above plugin directory');
            } else {
                error_log('Deleted hello.php file above plugin directory');
            }
        }
    }

    // function to recursively delete plugin folder
    // use delete() method of WP filesystem
    public function delete( $file, $recursive = false, $type = false ) {
        global $wp_filesystem;

        if ( empty( $file ) ) {
            // Some filesystems report this as /, which can cause non-expected recursive deletion of all files in the filesystem.
            return false;
        }

        // Use WP_Filesystem for deleting
        WP_Filesystem();

        // Use WP_Filesystem for deleting files and folders
        return $wp_filesystem->delete($file, $recursive, $type);
    }

    public function on_plugin_activation($plugin) {
        if ($plugin === plugin_basename(UWP_PLUGIN_FILE)) {
            $this->activate();
        }
    }

    public function activate() {
        $default_options = array('akismet','hello-dolly');
        update_option($this->unwanted_plugins_option, $default_options);
        update_option('unwanted_cleaner_active', true);
    }

    public function deactivate() {
        delete_option($this->unwanted_plugins_option);
        delete_option('unwanted_cleaner_active');
    }

    public function init() {
        if (is_admin() && current_user_can('manage_options') && get_option('unwanted_cleaner_active')) {
            $this->load_unwanted_plugins();
        }
    }

    public function load_unwanted_plugins() {
        $this->unwanted_plugins = get_option($this->unwanted_plugins_option, array());
    }

    public function add_admin_menu() {
        add_options_page('Unwanted Cleaner', 'Unwanted Cleaner', 'manage_options', 'unwanted-cleaner', array($this, 'init_admin_page'));
    }

    public function register_settings() {
        register_setting('unwanted_plugins_group', $this->unwanted_plugins_option, array($this, 'sanitize_plugins_list'));
		add_action('admin_init', array($this, 'check_and_delete_unwanted_plugins'));
    }

	public function sanitize_plugins_list($input) {
        return explode("\n", sanitize_textarea_field($input));
    }

	public function init_admin_page() {
        error_log('init_admin_page!');
		$this->load_unwanted_plugins();
	
        $pro =0;
        $lang = [
            "Unwanted_Cleaner_Settings" => __('Unwanted Cleaner Settings', 'unwanted-cleaner'),
            "List_of_unwanted_plugins" => __('List of unwanted plugins', 'unwanted-cleaner'),
            "Enter_the_slugs_of_unwanted_plugins" => __('Enter the <b>slugs</b> of your unwanted plugins, <b>each on a new line</b>.', 'unwanted-cleaner'),
            "They_will_be_automatically_deleted" => __('They are automatically deleted as soon as a core upgrade has taken place.', 'unwanted-cleaner'),
            "save_changes" => __('Save Changes', 'unwanted-cleaner'),
            "delete_now_hint" => __('If you want to delete the unwanted plugins right now, push the button below.', 'unwanted-cleaner'),
            "delete_unwanted_plugins" => __('Delete unwanted plugins now', 'unwanted-cleaner'),
            "saving" => __('Saving list...', 'unwanted-cleaner'),
            "deleting" => __('Deleting plugins...', 'unwanted-cleaner')
        ];
        error_log(json_encode($this->unwanted_plugins));
        wp_enqueue_script('uwp-main-js',UWP_PLUGIN_URL.'main.js', array('jquery'), '', true);
        wp_localize_script('uwp-main-js','uwp_var',array(
			'nonce'=> wp_create_nonce("uwp-nonce"),
			'check' => 1,
			'pro' => $pro,
			'rtl' => is_rtl() ,
			'text' => $lang,
            'plugin_list'=>$this->unwanted_plugins,
            'ajaxurl' => admin_url('admin-ajax.php')
			
		));
		include plugin_dir_path(__FILE__) . 'settings-form.php';
	}

    // 2DO check if function is still needed
	public function check_and_delete_unwanted_plugins() {
		$this->load_unwanted_plugins();
		
		if (!empty($this->unwanted_plugins)) {

			$result = $this->delete_unwanted_plugins();
			
			if ($result) {
				wp_redirect(admin_url('options-general.php?page=unwanted-cleaner&status=success'));
			} else {
				wp_redirect(admin_url('options-general.php?page=unwanted-cleaner&status=error'));
			}

			exit;
		}
	}

    public function unwanted_plugins_handler() {
        error_log("unwanted_plugins_handler");
        if ( ! check_ajax_referer( 'uwp-nonce', 'nonce' ) ) {
            $response = array( 'success' => false  , 'm'=>'nonce failed' );
			//wp_send_json_success($response,200);
            wp_send_json_error($response, 401);
        }

        $state = sanitize_text_field($_POST['state']);
        $plugin_list = sanitize_text_field($_POST['plugin_list']);
        $plugin_list = str_replace(' ', ',', $plugin_list);
        $message = $state == 'delete' ? __('Plugins deleted successfully.', 'unwanted-cleaner') : __('List of plugins saved.', 'unwanted-cleaner');
        
        //update_option($this->unwanted_plugins_option, explode("\n", $plugin_list));

        if($state == 'save'){
            // save options (list of unwanted plugins)
            error_log($plugin_list);
            //update_option( 'unwanted_plugins_list', explode( ",", $plugin_list ) );
            update_option( 'unwanted_plugins_list', $plugin_list );
            //call function for save
            error_log("unwanted_plugins_handler() called save_unwanted_plugins_list()");
        }else{
           // call function for delete
           $this->delete_unwanted_plugins();
           error_log("unwanted_plugins_handler() called delete_unwanted_plugins()");
        }
        error_log($message);
        $response = array( 'success' => true, 'm'=>$message ); 
        wp_send_json_success($response,200);
    }
}

$unwanted_cleaner = new Unwanted_Cleaner();
