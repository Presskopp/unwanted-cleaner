<?php
namespace uncl_unwantedcleaner;

// 2do 
// include_once ABSPATH . 'wp-admin/includes/plugin.php';

class uncl_unwanted_cleaner {
    private static $initialized = false;
    private $uncl_unwanted_options;
    private $uncl_unwanted_plugins = array();

    public function __construct() {

        if (self::$initialized) return;
        self::$initialized = true;

        $this->uncl_unwanted_options = 'uncl_unwanted_plugins_list';

        register_activation_hook(UNCL_PLUGIN_FILE, array($this, 'uncl_activate'));
        register_deactivation_hook(UNCL_PLUGIN_FILE, array($this, 'uncl_deactivate'));

        // Initialisation
        add_action('init', array($this, 'uncl_init'));

        // fires after upgrade of core, themes or plugins
        add_action('upgrader_process_complete', array($this, 'uncl_delete_unwanted_plugins_after_core_upgrade'), 10, 2);

        if ( !is_admin() ) return;

        // add entries to admin menu
		add_action('admin_menu', array($this, 'add_admin_menu'));
        // fires when admin panel is initialised
        add_action('admin_init', array($this, 'uncl_register_settings'));
        
        // fires on plugin activation
        add_action('activated_plugin', array($this, 'uncl_on_plugin_activation'));

        // Ajax handler
        add_action('wp_ajax_uncl_handler', array( $this,'uncl_unwanted_plugins_handler'));

        ///$this->fun_show_noti_update_happen();
    }

    public function uncl_save_unwanted_list($purpose , $list) {
        if (isset($list)) {
            $n = "uncl_unwanted_".$purpose."_list";
            update_option($n, $list);
        }
    }
//$option_value = get_option('uncl_last_wp_version');
    // main function to delete the unwanted plugins
    public function uncl_delete_unwanted_plugins() {
        
        $installed_plugins = get_plugins();
        $plugins_to_delete = array();
        $plugin_list = count($this->uncl_unwanted_plugins) == 1 ? explode( ",", $this->uncl_unwanted_plugins[0] ) : $this->uncl_unwanted_plugins;

        $this->uncl_delete_unwanted_delete_hello_php_above_plugin();

        // Go through all of the installed plugins so if a plugin is active, it can be deactivated first
        // foreach ($installed_plugins as $plugin_file => $plugin_data) {
        foreach ($installed_plugins as $plugin_file => $_) {    // Deconstruction of $plugin_data because we don't use it

            if (in_array(dirname($plugin_file), $plugin_list)) {

                $this->uncl_deactivate_plugin( $plugin_file );
                $deleted = delete_plugins(array($plugin_file));

                if ($deleted) $plugins_to_delete[] = $plugin_file;
            }
        }

        return !empty($plugins_to_delete);
    }

    /*
    public fun_show_noti_update_happen(){

        if( $option_hod_yesno==1 && !isset($option_wp) && ($option_wp <$wp_version) ) return ;
        //show message notifaction to user (yes / no)
        //return
        
        $option_hod_yesno=1;
        $option_wp= $wp_version;
        if (return_noti === true){// call function for remove plugins 
        }
    }*/

    public function uncl_delete_unwanted_plugins_after_core_upgrade($upgrader_object, $options) {

        $option_delete = get_option('uncl_state_delete') ?? false;
        if( $option_delete==false ) return ;

        ///auto
        if ($options['action'] === 'update' && $options['type'] === 'core') {
            
            // Load the list of unwanted plugins
            $uncl_unwanted_plugins = get_option('uncl_unwanted_plugins_list', array());

            if (empty($uncl_unwanted_plugins)) return;
            $plugin_list =  count($uncl_unwanted_plugins)==1 ? explode( ",", $uncl_unwanted_plugins[0] ) : $uncl_unwanted_plugins;

            // Check and delete unwanted plugins
            foreach ($plugin_list as $plugin) {

                if (isset($plugin)) {
                    $plugin_deactivated = $this->uncl_deactivate_plugin( $plugin );

                    if($plugin_deactivated){
                        $plugin_dir = UNCL_PLUGIN_DIR . '/' . $plugin;
                        // recursively delete plugin folder
                        $this->uncl_delete_file($plugin_dir, true); 
                    }
                }
            }

            // Delete hello.php file, if hello-dolly is in list of unwanted plugins
            if (in_array('hello-dolly', $plugin_list))  $this->uncl_delete_unwanted_delete_hello_php_above_plugin();

        }
    }

    // Delete hello.php file if it exists above the plugin folder
    public function uncl_delete_unwanted_delete_hello_php_above_plugin() {

        $hello_php_file = UNCL_PLUGIN_DIR . '/hello.php';

        if (file_exists($hello_php_file)) {
            deactivate_plugins($hello_php_file);
            wp_delete_file($hello_php_file);
        }
    }

    private function uncl_deactivate_plugin( $plugin_slug ) {
        // see https://core.trac.wordpress.org/ticket/26735
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $plugin_file = '';
        $plugins = get_plugins();
        
        foreach ( $plugins as $file => $info ) {
            if (strpos($file, $plugin_slug) === 0) {
                $plugin_file = $file;
                break;
            }
        }

        if ($plugin_file) { 
            deactivate_plugins( $plugin_file );
            return true;
        }
        return false;
    }

    // function to recursively delete plugin folder
    // use delete() method of WP filesystem
    public function uncl_delete_file( $file, $recursive = false, $type = false ) {
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

    public function uncl_on_plugin_activation($plugin) {
        if ($plugin === plugin_basename(UNCL_PLUGIN_FILE)) $this->uncl_activate();
    }

    public function uncl_activate() {
        //$default_options = array('akismet','hello-dolly');
        $default_options = array('hello-dolly');
        update_option($this->uncl_unwanted_options, $default_options);
        update_option('uncl_unwanted_cleaner_active', true);
    }

    public function uncl_deactivate() {
        delete_option($this->uncl_unwanted_options);
        delete_option('uncl_unwanted_cleaner_active');
    }

    public function uncl_init() {
        load_plugin_textdomain(
            UNCL_PLUGIN_TEXTDOMAIN,
            false,
            UNCL_PLUGIN_URL . "/languages"
        );
        if ( is_admin() && current_user_can('manage_options') && get_option('uncl_unwanted_cleaner_active') ) {
            $this->uncl_load_unwanted_plugins();
        }
    }

    public function uncl_load_unwanted_plugins() {
        $this->uncl_unwanted_plugins = get_option($this->uncl_unwanted_options, array());
    }

    public function add_admin_menu() {
        add_options_page('Unwanted Cleaner', 'Unwanted Cleaner', 'manage_options', 'unwanted-cleaner', array($this, 'uncl_init_admin_page'));
    }

    public function uncl_register_settings() {
        register_setting('unwanted_cleaner_group', $this->uncl_unwanted_options, array($this, 'uncl_sanitize_plugins_list'));
    }

	public function uncl_sanitize_plugins_list($input) {
        return explode("\n", sanitize_textarea_field($input));
    }

	public function uncl_init_admin_page() {
        ?><div id="main_unwanted_cleaner"></div> <?php
		$this->uncl_load_unwanted_plugins();
	
        // $pro = 0;    // for future use
        $lang = [
            "Unwanted_Cleaner_Settings" => esc_html__('Unwanted Cleaner Settings', 'unwanted-cleaner'),
            "List_of_unwanted_plugins" => esc_html__('List of unwanted plugins', 'unwanted-cleaner'),
            "Enter_the_slugs_of_unwanted_plugins" => esc_html__('Enter the <b>slugs</b> of your unwanted plugins, <b>each on a new line</b>.', 'unwanted-cleaner'),
            "They_will_be_automatically_deleted" => esc_html__('They are automatically deleted as soon as a core upgrade has taken place.', 'unwanted-cleaner'),
            "save_changes" => esc_html__('Save Changes', 'unwanted-cleaner'),
            "delete_now_hint" => esc_html__('If you want to delete the unwanted plugins right now, push the button below.', 'unwanted-cleaner'),
            "delete_unwanted_plugins" => esc_html__('Delete unwanted plugins now', 'unwanted-cleaner'),
            "saving" => esc_html__('Saving list...', 'unwanted-cleaner'),
            "deleting" => esc_html__('Deleting plugins...', 'unwanted-cleaner')
        ];
      
        $delete_ok= get_option('uncl_state_delete');
        $delete_ok = !empty($delete_ok) ?  $delete_ok : false;
        wp_enqueue_script('uncl-main-js', UNCL_PLUGIN_URL . '/includes/assets/js/uncl_main.js', array('jquery'), '1.0.0', true);
        wp_localize_script('uncl-main-js','uncl_var',array(
			'nonce' => wp_create_nonce("uncl-nonce"),
			'check' => 1,
			//'pro' => $pro,  // for future use
			'rtl' => is_rtl() ,
			'text' => $lang,
            'plugin_list' => $this->uncl_unwanted_plugins,
            'ajaxurl' => admin_url('admin-ajax.php'),
            'delete_ok' => $delete_ok
		));
	}

    public function uncl_unwanted_plugins_handler() {

        if ( !check_ajax_referer( 'uncl-nonce', 'nonce' ) ) {
            wp_send_json_success(array('success' => false, 'm' => esc_html__('Nonce verification failed', 'unwanted-cleaner')), 401);
        }

        if ( !current_user_can('manage_options') ) {
            wp_send_json_success(array('success' => false, 'm' => esc_html__('Insufficient permissions', 'unwanted-cleaner')), 401);
        }

        $state = sanitize_text_field($_POST['state']);
        $plugin_list = sanitize_text_field($_POST['plugin_list']);
        $plugin_list = str_replace(' ', ',',  $plugin_list);

        $delete_ok =sanitize_text_field($_POST['delete_ok']);
        $message = esc_html__('Plugins deleted successfully.', 'unwanted-cleaner');
        if( $state == 'save' ) {
            $this->uncl_save_unwanted_list('plugins',  $plugin_list);
            $message = esc_html__('List of plugins saved successfully.', 'unwanted-cleaner'); 
            update_option('uncl_state_delete', $delete_ok);
        } else {
           $this->uncl_delete_unwanted_plugins();
        }
        
        $response = array( 'success' => true, 'm'=>$message );
        wp_send_json_success($response,200);
    }
}