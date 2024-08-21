<?php
namespace uncl_unwantedcleaner;

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

        // Hook to register scripts
        add_action('admin_enqueue_scripts', array($this, 'uncl_enqueue_admin_scripts'));
    }

    public function uncl_enqueue_admin_scripts($hook) {
        // Überprüfen, ob wir uns auf der Einstellungsseite des Plugins befinden
        if ($hook !== 'toplevel_page_unwanted-cleaner') {
            return;
        }
        // Bootstrap CSS einbinden        
        wp_enqueue_style('bootstrap', UNCL_PLUGIN_URL . '/includes/assets/css/bootstrap.min.css');
        wp_enqueue_style('uncl_style', UNCL_PLUGIN_URL . '/includes/assets/css/style.css', true,UNCL_PLUGIN_VERSION);
        // Bootstrap JavaScript einbinden    
        wp_enqueue_script('bootstrap', UNCL_PLUGIN_URL . '/includes/assets/js/bootstrap.bundle.min.js', array('jquery'), null, true);

    }

    public function uncl_save_unwanted_list($purpose , $list) {
        error_log('uncl_save_unwanted_list: '.$list);
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
        //$plugin_list = count($this->uncl_unwanted_plugins) == 1 ? explode( ",", $this->uncl_unwanted_plugins[0] ) : $this->uncl_unwanted_plugins;
        $plugin_list = str_replace( '\\', "",  $this->uncl_unwanted_plugins );
        $slugs = [];
        $plugins = $plugin_list[0];
        $plugins =json_decode($plugins, true);
       foreach ($plugins as $plugin) {
                $slug =  $plugin['slug'];
                array_push($slugs, $slug);          
        }
        $this->uncl_delete_unwanted_delete_hello_php_above_plugin();
        error_log('slugs: '.json_encode($slugs));
        // Go through all of the installed plugins so if a plugin is active, it can be deactivated first
        // foreach ($installed_plugins as $plugin_file => $plugin_data) {
        foreach ($installed_plugins as $plugin_file => $_) {    // Deconstruction of $plugin_data because we don't use it

            if (in_array(dirname($plugin_file), $slugs)) {

                $this->uncl_deactivate_plugin( $plugin_file );
                $deleted = delete_plugins(array($plugin_file));

                if ($deleted) $plugins_to_delete[] = $plugin_file;
            }
        }

        return !empty($plugins_to_delete);
    }

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
        error_log('uncl_load_unwanted_plugins');
        error_log($this->uncl_unwanted_options);
        
        $this->uncl_unwanted_plugins = get_option($this->uncl_unwanted_options, array());
    }

    public function add_admin_menu() {
        //add_options_page('Unwanted Cleaner', 'Unwanted Cleaner', 'manage_options', 'unwanted-cleaner', array($this, 'uncl_init_admin_page'));
        add_menu_page('Unwanted Cleaner', 'Unwanted Cleaner', 'manage_options', 'unwanted-cleaner', array($this, 'uncl_init_admin_page'), ''.UNCL_PLUGIN_URL . '/includes/assets/img/icon.png', 100);
    }

    public function uncl_register_settings() {
        register_setting('unwanted_cleaner_group', $this->uncl_unwanted_options, array($this, 'uncl_sanitize_plugins_list'));
    }

	public function uncl_sanitize_plugins_list($input) {
        return explode("\n", sanitize_textarea_field($input));
    }

	public function uncl_init_admin_page() {
        ?><div id="main_unwanted_cleaner" class="container"></div><?php
		$this->uncl_load_unwanted_plugins();
	
        // $pro = 0;    // for future use
        $lang = [
            "Unwanted_Cleaner_Settings" => esc_html__('Unwanted Cleaner Settings', 'unwanted-cleaner'),
            "Plugins_can_be_manually_deleted" => sprintf( esc_html__('Plugins shown below will %1$snot%2$s be automatically deleted, unless you check the checkbox below.', 'unwanted-cleaner'), '<b>', '</b>' ),
            "Plugins_will_be_automatically_deleted" => sprintf( esc_html__('Plugins shown below will be %1$sautomatically%2$s deleted as soon as a core upgrade has taken place.', 'unwanted-cleaner'), '<b>', '</b>' ),
            "Automatic_deletion_confirmation" => sprintf( esc_html__('If checked you give permission to Unwanted Cleaner to %1$sautomatically%2$s delete the plugins listed above, whenever a core update did run.', 'unwanted-cleaner'), '<b>', '</b>' ),
            "save_changes" => esc_html__('Save Changes', 'unwanted-cleaner'),
            "delete_now_hint" => esc_html__('If you want to delete the unwanted plugins right now, push the button below.', 'unwanted-cleaner'),
            "delete_unwanted_plugins" => esc_html__('Delete unwanted plugins now', 'unwanted-cleaner'),
            "saving" => esc_html__('Saving list...', 'unwanted-cleaner'),
            "please_enter_atleast_2chrs" => esc_html__('Please enter at least 2 characters', 'unwanted-cleaner'),
            "no_plugin_found" => esc_html__('No plugin found', 'unwanted-cleaner'),
            "loading" => esc_html__('Loading...', 'unwanted-cleaner'),
            "search" => esc_html__('Search', 'unwanted-cleaner'),
            "deleting" => esc_html__('Deleting plugins...', 'unwanted-cleaner'),
            "no_select_uc" => esc_html__('You are not allowed to select Unwanted Cleaner as an unwanted plugin!', 'unwanted-cleaner'),
            "error_load_fetch" => esc_html__('A network error occurred. Please reload the page and try again.', 'unwanted-cleaner'),
            "plugins" => esc_html__('Plugins', 'unwanted-cleaner'),
            "themes" => esc_html__('Themes', 'unwanted-cleaner')
            /* for future use
            "files" => esc_html__('Files', 'unwanted-cleaner'),
            "database" => esc_html__('Database', 'unwanted-cleaner')
            */
        ];
        
        $delete_ok = get_option('uncl_state_delete', false);

        wp_enqueue_script('uncl-main-js', UNCL_PLUGIN_URL . '/includes/assets/js/uncl_main.js', array('jquery'), UNCL_PLUGIN_VERSION, true);
        $images = UNCL_PLUGIN_URL . '/includes/assets/img/';
        $user_lang = get_user_locale(get_current_user_id());
        $plugins = str_replace( '\\', "",  $this->uncl_unwanted_plugins );
        wp_localize_script('uncl-main-js','uncl_var',array(
			'nonce' => wp_create_nonce("uncl-nonce"),
			'check' => 1,
			//'pro' => $pro,  // for future use
			'rtl' => is_rtl() ,
			'text' => $lang,
            'plugin_list' => $plugins,
            'ajaxurl' => admin_url('admin-ajax.php'),
            'delete_ok' => $delete_ok,
            'images' => $images,
            'user_lang'=>$user_lang
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
        error_log("STATE: " . $state);
        $plugin_list = sanitize_text_field($_POST['plugin_list']);
        error_log('uncl_unwanted_plugins_handler: '.json_encode($plugin_list));
        
        $delete_ok = sanitize_text_field($_POST['delete_ok']);
        $message = esc_html__('This message should not be here.', 'unwanted-cleaner');
        if( $state == 'save' ) {
            $this->uncl_save_unwanted_list('plugins',  $plugin_list);
            $message = esc_html__('List of plugins saved successfully.', 'unwanted-cleaner'); 
            update_option('uncl_unwanted_plugins_list', $plugin_list);
        } elseif( $state == 'delete' ) {
            $this->uncl_delete_unwanted_plugins();
            $message = esc_html__('Plugins deleted successfully.', 'unwanted-cleaner');
        } elseif ($state == 'auto') {
            error_log('uncl_unwanted_plugins_handler: auto==>'.$delete_ok);
        }
        // 2DO: good idea to do it in any case?
        update_option('uncl_state_delete', $delete_ok);
    
        $response = array( 'success' => true, 'm'=>$message );
        wp_send_json_success($response,200);
    }
}