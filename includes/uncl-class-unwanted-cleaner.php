<?php
namespace uncl_unwantedcleaner;

class uncl_unwanted_cleaner {
    private static $initialized = false;
    private $uncl_unwanted_options;
    private $uncl_unwanted_plugins = array();
    private $uncl_unwanted_themes = array();

    public function __construct() {

        if (self::$initialized) return;
        self::$initialized = true;

        $this->uncl_unwanted_options = 'uncl_unwanted_plugins_list';

       /*  register_activation_hook(UNCL_PLUGIN_FILE, array($this, 'uncl_activate'));
        register_deactivation_hook(UNCL_PLUGIN_FILE, array($this, 'uncl_deactivate')); */

        // Initialisation
        add_action('init', array($this, 'uncl_init'));

        // fires after upgrade of core, themes or plugins
        //add_action('upgrader_process_complete', array($this, 'uncl_delete_unwanted_items_after_core_upgrade'), 10, 2);
        add_action('upgrader_process_complete', array($this, 'uncl_core_upgrade_flag'), 10, 2);
        add_action('shutdown',array($this, 'uncl_delete_unwanted_shutdown'), 10, 2);

        // Add admin-specific hooks only if in the admin area
        if ( is_admin() ) {
            // Add entries to admin menu
            add_action('admin_menu', array($this, 'add_admin_menu'));

            // Fires when admin panel is initialised
            //add_action('admin_init', array($this, 'uncl_register_settings'));

            // Fires on plugin activation
            // add_action('activated_plugin', array($this, 'uncl_on_plugin_activation'));

            // Ajax handler
            add_action('wp_ajax_uncl_handler', array($this, 'uncl_unwanted_plugins_handler'));

            // Hook to register scripts
            add_action('admin_enqueue_scripts', array($this, 'uncl_enqueue_admin_scripts'));
        }
    }

    public function uncl_enqueue_admin_scripts($hook) {
        // Check if we are on the settings page
        if ( $hook !== 'toplevel_page_unwanted-cleaner' ) {
            return;
        }
        wp_enqueue_style('bootstrap', UNCL_PLUGIN_URL . '/includes/assets/css/bootstrap.min.css', array(), UNCL_BOOTSTRAP_VERSION);
        wp_enqueue_style('uncl_style', UNCL_PLUGIN_URL . '/includes/assets/css/style.css', true, UNCL_PLUGIN_VERSION);
        wp_enqueue_script('bootstrap', UNCL_PLUGIN_URL . '/includes/assets/js/bootstrap.bundle.min.js', array('jquery'), UNCL_BOOTSTRAP_VERSION, true);

    }

    public function uncl_save_unwanted_list($purpose , $list) {
        if (isset($list)) {
            $n = "uncl_unwanted_".$purpose."_list";
            update_option($n, $list);
        }
    }

    // main function to delete the unwanted plugins
    public function uncl_delete_unwanted_plugins() {

        $installed_plugins = get_plugins();
        $plugins_to_delete = array();  
        $this->uncl_unwanted_plugins = get_option('uncl_unwanted_plugins_list', false);
        $plugin_list = str_replace( '\\', "",  $this->uncl_unwanted_plugins );
        $slugs = [];
        $plugins = json_decode($plugin_list[0]);

        foreach ($plugins as $plugin) {
            $slug =  $plugin->slug;
            array_push($slugs, $slug);
        }

        $this->uncl_delete_unwanted_delete_hello_php_above_plugin();

        // Go through all of the installed plugins so if a plugin is active, it can be deactivated first
        foreach ($installed_plugins as $plugin_file => $_) {    // Deconstruction of $plugin_data because we don't use it
            if (in_array(dirname($plugin_file), $slugs)) {
                $this->uncl_deactivate_plugin( $plugin_file );
                $deleted = delete_plugins(array($plugin_file));
                if ($deleted) $plugins_to_delete[] = $plugin_file;
            }
        }

        return !empty($plugins_to_delete);
    }
    public function uncl_delete_unwanted_themes() {

        $uncl_delete_unwanted_themes_list = get_option('uncl_unwanted_themes_list', false);

        if ( $uncl_delete_unwanted_themes_list == false ) return;
        
        $installed_themes = wp_get_themes();
        $themes_list = str_replace( '\\', "",  $uncl_delete_unwanted_themes_list );
        $slugs = [];        
        $themes = json_decode($themes_list, true);
        foreach ( $themes as $theme ) {
                $slug =  $theme['slug'];
                array_push($slugs, $slug);
        }

        // Go through all of the installed themes so if a theme is active, it won't be deleted
        $active_theme = wp_get_theme()->get_stylesheet();

        foreach ( $installed_themes as $themes_file => $_ ) {
            // Get the slug (directory name) of the theme
            $theme_slug = basename($themes_file);
            
            // Skip the active theme
            if ( $theme_slug === $active_theme ) {
                continue;
            }

            // Check if the theme is in the list of slugs to be deleted
            if ( in_array( $theme_slug, $slugs ) ) {

                // Try to delete the theme
                try {
                    $result = delete_theme($theme_slug);
                    
                    // Check if deleting of theme was successfull
                    if ( !$result ) {
                        throw new Exception("ERROR: The theme '$theme_slug' could not be deleted.");
                    }
                } catch ( Exception $e ) {
                    error_log("ERROR: Error deleting theme '$theme_slug': " . $e->getMessage());
                }
            }
        }

        return !empty($plugins_to_delete);
    }

    public function uncl_core_upgrade_flag($upgrader_object, $options){
        if ($options['action'] === 'update' && $options['type'] === 'core') {
            update_option('uncl_core_upgrade_flag', true);           
        }
    }

    public function uncl_delete_unwanted_shutdown(){

            if (get_option('uncl_core_upgrade_flag')) {
                delete_option('uncl_core_upgrade_flag');

                if ( get_option('uncl_state_delete_plugins') ) {
                    $this->uncl_delete_unwanted_plugins();
                }
    
                if ( get_option('uncl_state_delete_themes') ) {
                    $this->uncl_delete_unwanted_themes();
                }
        }
    }

    // Delete hello.php file if it exists above the plugin folder
    public function uncl_delete_unwanted_delete_hello_php_above_plugin() {

        $hello_php_file = UNCL_PLUGIN_DIR . '/hello.php';

        if ( file_exists( $hello_php_file ) ) {
            deactivate_plugins( $hello_php_file );
            wp_delete_file( $hello_php_file );
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
    // uses delete() method of WP filesystem
    public function uncl_delete_file( $file, $recursive = false, $type = false ) {
        global $wp_filesystem;

        if ( empty( $file ) ) {
            return false;
        }

        // Use WP_Filesystem for deleting files and folders
        WP_Filesystem();

        return $wp_filesystem->delete($file, $recursive, $type);
    }

    /* public function uncl_on_plugin_activation($plugin) {
        if ( $plugin === plugin_basename( UNCL_PLUGIN_FILE ) ) $this->uncl_activate();
    } */

 /*    public function uncl_activate() {
 
    }

    public function uncl_deactivate() {
       
    } */

    public function uncl_init() {
        load_plugin_textdomain(
            UNCL_PLUGIN_TEXTDOMAIN,
            false,
            UNCL_PLUGIN_URL . "/languages"
        );
        if ( is_admin() && current_user_can('manage_options') ) {
            $this->uncl_load_unwanted_plugins();
        }
    }

    public function uncl_load_unwanted_plugins() {
        $this->uncl_unwanted_plugins = get_option('uncl_unwanted_plugins_list', false);
        $this->uncl_unwanted_themes = get_option('uncl_unwanted_themes_list', false);
    }

    public function add_admin_menu() {
        add_menu_page('Unwanted Cleaner', 'Unwanted Cleaner', 'manage_options', 'unwanted-cleaner', array($this, 'uncl_init_admin_page'), ''.UNCL_PLUGIN_URL . '/includes/assets/img/icon.png', 100);
    }

    /* public function uncl_register_settings() {
        //register_setting('unwanted_cleaner_group', $this->uncl_unwanted_options, array($this, 'uncl_sanitize_plugins_list'));
    } */

	public function uncl_sanitize_plugins_list($input) {
        return explode("\n", sanitize_textarea_field($input));
    }

	public function uncl_init_admin_page() {
        ?><div id="main_unwanted_cleaner" class="container"></div><?php
		$this->uncl_load_unwanted_plugins();
	
        $lang = [
            "can_be_manually_deleted" => sprintf(
                /* translators: %1$s will be replaced by either 'Themes' or 'Plugins', %2$s and %3$s are HTML tags for bold text. */
                esc_html__('%1$s shown below will %2$snot%3$s be automatically deleted, unless you check the checkbox below.', 'unwanted-cleaner'), 
                esc_html__('%s', 'unwanted-cleaner'),
                '<b>',
                '</b>'
            ),
            "will_be_automatically_deleted" => sprintf(
                /* translators: %1$s will be replaced by either 'Themes' or 'Plugins', %2$s and %3$s are HTML tags for bold text. */
                esc_html__('%1$s shown below will be %2$sautomatically%3$s deleted as soon as a core upgrade has taken place.', 'unwanted-cleaner'),
                esc_html__('%s', 'unwanted-cleaner'),
                '<b>',
                '</b>'
            ),
            "Automatic_deletion_confirmation" => sprintf(
                /* translators: %1$s and %2$s are HTML tags for bold text, %3$s will be replaced by either themes or plugins. */
                esc_html__('If checked you give permission to Unwanted Cleaner to %1$sautomatically%2$s delete the %3$s listed above, whenever a core update did run.', 'unwanted-cleaner'),
                '<b>',
                '</b>',
                '%s'
            ),
            "save_changes" => esc_html__('Save Changes', 'unwanted-cleaner'),
            
            /* translators: %s will be replaced by either themes or plugins. */
            "delete_now_hint" => esc_html__('If you want to delete the unwanted %s right now, push the button below.', 'unwanted-cleaner'),
            
            /* translators: %s will be replaced by either themes or plugins. */
            "delete_unwanted_plugins" => esc_html__('Delete unwanted %s now', 'unwanted-cleaner'),

            "saving" => esc_html__('Saving list...', 'unwanted-cleaner'),
            "please_enter_atleast_2chrs" => esc_html__('Enter 2 or more characters to start your search', 'unwanted-cleaner'),
            
            /* translators: %s will be replaced by either themes or plugins. */
            "no_plugin_found" => esc_html__('No %s found', 'unwanted-cleaner'),
            
            "loading" => esc_html__('Loading...', 'unwanted-cleaner'),
            "search" => esc_html__('Search', 'unwanted-cleaner'),

            /* translators: %s will be replaced by either themes or plugins. */
            "deleting" => esc_html__('Deleting %s ...', 'unwanted-cleaner'),

            "no_select_uc" => esc_html__('You are not allowed to select Unwanted Cleaner as an unwanted plugin!', 'unwanted-cleaner'),
            "already_added" => esc_html__('The selected item has already been added!', 'unwanted-cleaner'),
            "error_load_fetch" => esc_html__('A network error occurred. Please reload the page and try again.', 'unwanted-cleaner'),
            "plugins" => esc_html__('Plugins', 'unwanted-cleaner'),
            "themes" => esc_html__('Themes', 'unwanted-cleaner'),
            'close' => esc_html__('Close', 'unwanted-cleaner'),

            /* translators: %1$s and %2$s are HTML tags for opening and closing tag A and %3$s and %4$s are HTML tags for opening and closing tag A  */
            'sponsor_context' => esc_html__('Thank you for using %1$sUnwanted Cleaner%2$s. If you like it, you may consider %3$s buying me a coffee%4$s', 'unwanted-cleaner'),
            
            /* for future use */
            "files" => esc_html__('Files', 'unwanted-cleaner'),
            "database" => esc_html__('Database', 'unwanted-cleaner')
        ];
        
        $delete_ok_plugins = get_option('uncl_state_delete_plugins', false);
        $delete_ok_themes = get_option('uncl_state_delete_themes', false);

        wp_enqueue_script( 'uncl-main-js', UNCL_PLUGIN_URL . '/includes/assets/js/uncl_main.js', array('jquery'), UNCL_PLUGIN_VERSION, true );
        $images = UNCL_PLUGIN_URL . '/includes/assets/img/';
        $user_lang = get_user_locale( get_current_user_id() );
        $plugins = str_replace( '\\', "",  $this->uncl_unwanted_plugins );
        $themes = str_replace( '\\', "",  $this->uncl_unwanted_themes );
        wp_localize_script( 'uncl-main-js', 'uncl_var', array(
            'nonce' => wp_create_nonce("uncl-nonce"),
			'check' => 1,
			'rtl' => is_rtl() ,
			'text' => $lang,
            'plugin_list' => $plugins,
            'theme_list' => $themes,
            'ajaxurl' => admin_url('admin-ajax.php'),
            'delete_ok_plugins' => $delete_ok_plugins,
            'delete_ok_themes' => $delete_ok_themes,
            'images' => $images,
            'user_lang'=>$user_lang
		) );
	}

    public function uncl_unwanted_plugins_handler() {

        if ( !check_ajax_referer( 'uncl-nonce', 'nonce' ) ) {
            wp_send_json_success(array('success' => false, 'm' => esc_html__('Nonce verification failed', 'unwanted-cleaner')), 401);
        }
        if ( !current_user_can('manage_options') ) {
            wp_send_json_success(array('success' => false, 'm' => esc_html__('Insufficient permissions', 'unwanted-cleaner')), 401);
        }

        $state = !empty($_POST['state']) ? sanitize_text_field($_POST['state']) : '';
        $plugin_list = !empty($_POST['plugin_list']) ? sanitize_text_field($_POST['plugin_list']) : '';
        $theme_list = !empty($_POST['theme_list']) ? sanitize_text_field($_POST['theme_list']) : '';
        $delete_ok_plugins = !empty($_POST['delete_ok_plugins']) ? sanitize_text_field($_POST['delete_ok_plugins']) : '';
        $delete_ok_themes = !empty($_POST['delete_ok_themes']) ? sanitize_text_field($_POST['delete_ok_themes']) : '';
        $message = esc_html__('Settings saved successfully.', 'unwanted-cleaner');

        if( $state == 'save' ) {
            $this->uncl_save_unwanted_list('plugins',  $plugin_list);
            $this->uncl_save_unwanted_list('themes',  $theme_list);
            $message = esc_html__('List of plugins saved successfully.', 'unwanted-cleaner');
        } elseif( $state == 'delete_plugins' ) {
            $this->uncl_delete_unwanted_plugins();
            $message = esc_html__('Plugins deleted successfully.', 'unwanted-cleaner');
        } elseif ( $state == 'delete_themes' ) {
            $this->uncl_delete_unwanted_themes();
            $message = esc_html__('Themes deleted successfully.', 'unwanted-cleaner');
        }

        update_option( 'uncl_state_delete_plugins', $delete_ok_plugins );
        update_option( 'uncl_state_delete_themes', $delete_ok_themes );

        $response = array( 'success' => true, 'm' => $message );
        wp_send_json_success( $response, 200 );
    }
}