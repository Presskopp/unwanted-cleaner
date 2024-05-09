<div id="main_unwanted_cleaner">

</div>


<!-- 
<div class="wrap">
    <h2>< ?php echo esc_html__('Unwanted Cleaner Settings', 'unwanted-cleaner'); ?></h2>
    <form method="post" action="options.php">
    < ?php settings_fields('unwanted_plugins_group'); ?>
        <h3>< ?php echo esc_html__('List of unwanted plugins', 'unwanted-cleaner'); ?></h3>
        <p>< ?php echo esc_html__('Enter the slugs of unwanted plugins, each on a new line.', 'unwanted-cleaner'); ?></p>
        <textarea name="< php echo esc_attr($this->unwanted_plugins_option); ?>" rows="5" cols="50">< ?php echo esc_textarea(implode("\n", $this->unwanted_plugins)); ?></textarea>
        < ?php submit_button(__('Save Changes', 'unwanted-cleaner')); ?>
    </form>
    <form method="post" action="< ?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="delete_unwanted_plugins_now">
        < ?php wp_nonce_field('delete_unwanted_plugins_action', 'delete_unwanted_plugins_nonce'); ?>
        < ?php submit_button(__('Delete unwanted plugins now', 'unwanted-cleaner'), 'secondary'); ?>
    </form>
</div>

 -->