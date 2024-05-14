<?php
namespace UnwantedCleaner;

class Unwanted_Cleaner {
    private static $initialized = false;
    private $unwanted_plugins_option;
    private $unwanted_plugins = array();

    public function __construct() {
        error_log('constructor was called');

        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
        
        $this->unwanted_plugins_option = 'unwanted_plugins_list';

        register_activation_hook(UWP_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(UWP_PLUGIN_FILE, array($this, 'deactivate'));
        
        // Initialisation
        add_action('init', array($this, 'init'));

        // fires after upgrade of core, themes or plugins
        add_action('upgrader_process_complete', array($this, 'delete_unwanted_plugins_after_core_upgrade'), 10, 2);

        if ( !is_admin() ) return;

        // add entries to admin menu
		add_action('admin_menu', array($this, 'add_admin_menu'));
        // fires when admin panel is initialised
        add_action('admin_init', array($this, 'register_settings'));
        // fires on plugin activation
        add_action('activated_plugin', array($this, 'on_plugin_activation'));

        // Ajax handler
        add_action('wp_ajax_handler_uwp', array( $this,'unwanted_plugins_handler'));
    }

    // main function to delete the unwanted plugins
    public function delete_unwanted_plugins() {
        error_log("delete_unwanted_plugins was called");
    
        $installed_plugins = get_plugins();
        $plugins_to_delete = array();
        $plugin_list =  count($this->unwanted_plugins)==1 ? explode( ",", $this->unwanted_plugins[0] ) : $this->unwanted_plugins;

        $this->delete_hello_php_above_plugin();

        // Go through all of the installed plugins so if a plugin is active, it can be deactivated first
        // foreach ($installed_plugins as $plugin_file => $plugin_data) {
        foreach ($installed_plugins as $plugin_file => $_) {    // Deconstruction of $plugin_data because we don't use it
            
            error_log("now checking: " . $plugin_file);
            //error_log(in_array(dirname($plugin_file), $plugin_list));
            error_log('dirname: ' . dirname($plugin_file));
            //error_log(wp_json_encode($plugin_list));

            if (in_array(dirname($plugin_file), $plugin_list)) {

                uc_deactivate_plugin( $plugin_file );

                $deleted = delete_plugins(array($plugin_file));

                if ($deleted) {
                    $plugins_to_delete[] = $plugin_file;
                    error_log($plugin_file . " deleted successfully.");
                }
                else {
                    error_log("Failed deleting " . $plugin_file);
                    
                }
            }
        }

        return !empty($plugins_to_delete);
    }

    public function save_unwanted_plugins_list() {
        error_log("function save_unwanted_plugins_list() was called");

        // check nonce
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'uwp-nonce' ) ) {
            error_log('No permission to save list');
            echo __("You don't have permission to do so", 'unwanted-cleaner');
            return;
        }

        if (isset($_POST['plugin_list'])) {
            update_option('unwanted_plugins_list', $_POST['plugin_list']);
            $unwanted_plugins = get_option('unwanted_plugins_list', array());
            error_log(__LINE__ . ' ' . json_encode($unwanted_plugins));
            error_log('Plugin list saved successfully!');
        }
    }

    public function delete_unwanted_plugins_after_core_upgrade($upgrader_object, $options) {
        error_log('delete_unwanted_plugins_after_core_upgrade');

        // Check if a core upgrade was done
        if ($options['action'] === 'update' && $options['type'] === 'core') {
            
            // Load the list of unwanted plugins
            $unwanted_plugins = get_option('unwanted_plugins_list', array());

            if (empty($unwanted_plugins)) return;

            // Check and delete unwanted plugins
            foreach ($unwanted_plugins as $plugin) {
                $plugin_dir = WP_PLUGIN_DIR . '/' . $plugin;
                // error_log("delete_plugin_folder: " . $plugin_dir);
                
                if (is_dir($plugin_dir)) {
                    
                    uc_deactivate_plugin( $plugin_file );

                    $deleted = $this->delete($plugin_dir, true); // recursively delete plugin folder
                    if ($deleted) {
                        error_log('Deleted unwanted plugin folder: ' . $plugin);
                    } else {
                        error_log('Failed deleting unwanted plugin folder: ' . $plugin);
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

            $file_still_exists_after_deletion = file_exists($hello_php_file);
            // unset($plugins[$hello_php_file]);
            // 2DO user response
            if ($file_still_exists_after_deletion) {
                error_log('Failed deleting hello.php file above plugin directory');
            } else {
                error_log('Successfully deleted hello.php file above plugin directory');
            }
        }
    }

    private function uc_deactivate_plugin( $plugin ) {
        // see https://core.trac.wordpress.org/ticket/26735
        if ( is_plugin_active( $plugin ) ) {
            deactivate_plugins( $plugin );
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
        load_plugin_textdomain(
            UWP_PLUGIN_TEXTDOMAIN,
            false,
            UWP_PLUGIN_URL . "/languages"
        );
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
		//add_action('admin_init', array($this, 'check_and_delete_unwanted_plugins'));
    }

	public function sanitize_plugins_list($input) {
        return explode("\n", sanitize_textarea_field($input));
    }

	public function init_admin_page() {
        error_log('init_admin_page!');
		$this->load_unwanted_plugins();
	
        $pro = 0;    // for future use
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

        error_log(wp_json_encode($this->unwanted_plugins));

        wp_enqueue_script('uwp-main-js', UWP_PLUGIN_URL . '/includes/assets/js/uc_main.js', array('jquery'), '1.0.0', true);

        wp_localize_script('uwp-main-js','uwp_var',array(
			'nonce'=> wp_create_nonce("uwp-nonce"),
			'check' => 1,
			'pro' => $pro,  // for future use
			'rtl' => is_rtl() ,
			'text' => $lang,
            'plugin_list'=>$this->unwanted_plugins,
            'ajaxurl' => admin_url('admin-ajax.php')
			
		));

		include plugin_dir_path(__FILE__) . 'settings-form.php';
	}

    public function unwanted_plugins_handler() {
        error_log("unwanted_plugins_handler");

        if ( !check_ajax_referer( 'uwp-nonce', 'nonce' ) ) {
            $response = array( 'success' => false  , 'm'=>'Nonce verification failed' );
			//wp_send_json_success($response,200);
            wp_send_json_error($response, 401);
        }

        if ( !current_user_can('manage_options') ) {
            wp_send_json_error('Insufficient permissions');
        }

        $state = sanitize_text_field($_POST['state']);
        $plugin_list = sanitize_text_field($_POST['plugin_list']);
        $plugin_list = str_replace(' ', ',', $plugin_list);
        $message = $state == 'delete' ? __('Plugins deleted successfully.', 'unwanted-cleaner') : __('List of plugins saved successfully.', 'unwanted-cleaner');
        
        //update_option($this->unwanted_plugins_option, explode("\n", $plugin_list));

        if( $state == 'save' ) {
            // save options (list of unwanted plugins)
            error_log('save: ' . $plugin_list);
            //update_option( 'unwanted_plugins_list', explode( ",", $plugin_list ) );
            
            //update_option( 'unwanted_plugins_list', $plugin_list );
            $this->save_unwanted_plugins_list();

            //call function for save
            error_log("unwanted_plugins_handler() called save_unwanted_plugins_list()");    // we do now
        } else {    // delete
           // call function for delete
           $this->delete_unwanted_plugins();
           error_log("unwanted_plugins_handler() called delete_unwanted_plugins()");
        }
        error_log($message);
        $response = array( 'success' => true, 'm'=>$message ); 
        wp_send_json_success($response,200);
    }
}