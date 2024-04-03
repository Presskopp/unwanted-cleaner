<?php
/*
Plugin Name: Unwanted Cleaner
Plugin URI: https://wordpress.org/plugins/unwanted-cleaner/
Description: This plugin removes unwanted plugins during the WordPress core upgrade process. You can manage the list of unwanted plugins from the settings page.
Version: 1.0
Author: Presskopp
Author URI: https://profiles.wordpress.org/presskopp/
License: GPL2
*/

class Unwanted_Cleaner {
    private static $initialized = false;
    private $unwanted_plugins_option;
    private $unwanted_plugins = array();

    public function __construct() {
        error_log('constructor called');

        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        
        $this->unwanted_plugins_option = 'unwanted_plugins_list';

        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

		// Delete unwanted plugins before updating core
        // add_filter('upgrader_source_selection', array($this, 'unwanted_cleaner'), 10, 4);

        add_action('upgrader_process_complete', array($this, 'delete_unwanted_plugins_after_core_upgrade'), 10, 2);

		add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('activated_plugin', array($this, 'on_plugin_activation'));
        add_action('init', array($this, 'init'));

        // für Nonce
        add_action('admin_post_delete_unwanted_plugins', 'process_delete_unwanted_plugins_form');

        // Trigger on plugins page load
        // add_action('admin_post_delete_unwanted_plugins', array($this, 'trigger_on_plugins_page'));
    }

    public function delete_unwanted_plugins_after_core_upgrade($upgrader_object, $options) {
        error_log('delete_unwanted_plugins_after_core_upgrade');
        // Überprüfen, ob es sich um ein Core-Upgrade handelt
        if ($options['action'] === 'update' && $options['type'] === 'core') {
            // Laden der Liste der unerwünschten Plugins
            $unwanted_plugins = get_option('unwanted_plugins_list', array());
    
            // Überprüfen und Löschen der unerwünschten Plugins
            foreach ($unwanted_plugins as $plugin) {
                $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin;
                error_log("delete_plugin_folder: " . $plugin_dir);
                if (is_dir($plugin_dir)) {
                    $deleted = $this->delete($plugin_dir, true); // Rekursives Löschen des Plugin-Ordners
                    if ($deleted) {
                        error_log('Deleted unwanted plugin folder: ' . $plugin);
                    } else {
                        error_log('Failed to delete unwanted plugin folder: ' . $plugin);
                    }
                }
            }

            // Löschen der hello.php-Datei, wenn hello-dolly in der Liste der unerwünschten Plugins enthalten ist
            if (in_array('hello-dolly', $unwanted_plugins)) {
                $this->delete_hello_php_above_plugin();
            }
        }
    }

    // Löschen der hello.php-Datei oberhalb des Plugin-Verzeichnisses
    public function delete_hello_php_above_plugin() {
        $hello_php_file = WP_PLUGIN_DIR . '/hello.php';
        error_log("hello_php_file: " . $hello_php_file);
        if (file_exists($hello_php_file)) {
            deactivate_plugins($hello_php_file);
            $file_exists_before_deletion = true;
            
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
            if ($file_still_exists_after_deletion) {
                error_log('Failed to delete hello.php file above plugin directory');
            } else {
                error_log('Deleted hello.php file above plugin directory');
            }
        }
    }

    // Funktion zum rekursiven Löschen des Plugin-Ordners definieren
    // Verwendung der delete() Methode des WordPress-Dateisystems
    public function delete( $file, $recursive = false, $type = false ) {
        global $wp_filesystem;

        if ( empty( $file ) ) {
            // Some filesystems report this as /, which can cause non-expected recursive deletion of all files in the filesystem.
            return false;
        }

        // Verwendung des WP_Filesystem für das Löschen
        WP_Filesystem();

        // Verwenden der WordPress-Dateisystemfunktionen zum Löschen von Dateien und Ordnern
        return $wp_filesystem->delete($file, $recursive, $type);
    }

    public function on_plugin_activation($plugin) {
        if ($plugin === plugin_basename(__FILE__)) {
            $this->activate();
        }
    }

    public function activate() {
        $default_options = array('akismet', 'hello-dolly');
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
        add_options_page('Unwanted Cleaner', 'Unwanted Cleaner', 'manage_options', 'unwanted-cleaner', array($this, 'render_admin_page'));
    }

    public function register_settings() {
        register_setting('unwanted_plugins_group', $this->unwanted_plugins_option, array($this, 'sanitize_plugins_list'));
		add_action('admin_init', array($this, 'check_and_delete_unwanted_plugins'));
    }

	public function sanitize_plugins_list($input) {
        return explode("\n", sanitize_textarea_field($input));
    }

	public function render_admin_page() {
        error_log('render_admin_page!');
		$this->load_unwanted_plugins();
		$status = isset($_GET['status']) ? $_GET['status'] : '';

		if ($status === 'error') {
			echo '<div class="error"><p>' . esc_html__('Error: No Plugins have been deleted.', 'unwanted-cleaner') . '</p></div>';
		}
		
		if ($status === 'success') {
			echo '<div class="updated"><p>' . esc_html__('Unwanted plugins have been successfully deleted.', 'unwanted-cleaner') . '</p></div>';
		}

        error_log(plugin_dir_path(__FILE__));
        wp_register_script('main-js', plugins_url('main.js',__FILE__), array('jquery'), '', true);
        wp_enqueue_script('main-js');
		include plugin_dir_path(__FILE__) . 'settings-form.php';
	}

    public function unwanted_cleaner($plugins) {
        error_log('unwanted_cleaner');
        $this->load_unwanted_plugins();
        $installed_plugins = get_plugins();
        $plugins_to_delete = array();

		// Do we have a "hello.php" in the plugins directory?
        /*$hello_php_plugin_file = WP_PLUGIN_DIR . '/hello.php';
        if (file_exists($hello_php_plugin_file)) {
            deactivate_plugins($hello_php_plugin_file);
            $deleted = wp_delete_file($hello_php_plugin_file);
            unset($plugins[$hello_php_plugin_file]);
        }*/
        $this->delete_hello_php_above_plugin();
		
        foreach ($installed_plugins as $plugin_file => $plugin_data) {
            error_log('run foreach');
            if (in_array(dirname($plugin_file), $this->unwanted_plugins)) {
                $deleted = false;
                deactivate_plugins($plugin_file);
                $deleted = delete_plugins(array($plugin_file));

                // Debugging output
                if ($deleted) {
					echo '<p>' . esc_html__('Deleted plugin: ', 'unwanted-cleaner') . esc_html($plugin_file) . '</p>';
				} else {
					echo '<p>' . esc_html__('Failed to delete plugin: ', 'unwanted-cleaner') . esc_html($plugin_file) . '</p>';
				}

                $plugins_to_delete[] = $plugin_file;
            }
        }

        return empty($plugins_to_delete);
    }

	public function check_and_delete_unwanted_plugins() {
		$this->load_unwanted_plugins();
		
		if (!empty($this->unwanted_plugins)) {

			$result = $this->delete_unwanted_plugins_now();
			
			if ($result) {
				wp_redirect(admin_url('options-general.php?page=unwanted-cleaner&status=success'));
			} else {
				wp_redirect(admin_url('options-general.php?page=unwanted-cleaner&status=error'));
			}
			
			exit;
		}
	}
	
    public function delete_unwanted_plugins_now() {
        error_log("in delete_unwanted_plugins_now");

        // Überprüfen des Nonce-Felds mit check_admin_referer()
        if ( ! check_admin_referer( 'delete_unwanted_plugins_action', 'delete_unwanted_plugins_nonce' ) ) {
            // Nonce ist ungültig oder nicht vorhanden, Formulardaten nicht verarbeiten
            wp_die('Nonce verification failed. Form submission aborted.');
        }

        // Nonce ist gültig, Formulardaten können sicher verarbeitet werden
        $this->load_unwanted_plugins();
        $installed_plugins = get_plugins();
        $plugins_to_delete = array();

		// Do we have a "hello.php" in the plugins directory?
        $this->delete_hello_php_above_plugin();
        /*
        $hello_php_plugin_file = WP_PLUGIN_DIR . '/hello.php';
        if (file_exists($hello_php_plugin_file)) {
            deactivate_plugins($hello_php_plugin_file);
            $deleted = wp_delete_file($hello_php_plugin_file);
            unset($plugins[$hello_php_plugin_file]);
        }*/

        foreach ($installed_plugins as $plugin_file => $plugin_data) {
            error_log("unwanted plugins / now: " . $this->unwanted_plugins);
            if (in_array(dirname($plugin_file), $this->unwanted_plugins)) {
                $deleted = false;
                deactivate_plugins($plugin_file);
                $deleted = delete_plugins(array($plugin_file));

                if ($deleted) {
                    $plugins_to_delete[] = $plugin_file;
                }
            }
        }

        return !empty($plugins_to_delete);
    }
}

$unwanted_cleaner = new Unwanted_Cleaner();
