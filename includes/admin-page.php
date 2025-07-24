<?php

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_acf-custom-blocks') return;

    wp_enqueue_style('dashicons'); // Load Dashicons icon font

    wp_enqueue_style(
        'acfcb-icons',
        plugin_dir_url(__FILE__) . '../assets/icon-picker.css',
        [],
        filemtime(plugin_dir_path(__FILE__) . '../assets/icon-picker.css')
    );

    wp_enqueue_script(
        'acfcb-icons',
        plugin_dir_url(__FILE__) . '../assets/icon-picker.js',
        [],
        filemtime(plugin_dir_path(__FILE__) . '../assets/icon-picker.js'),
        true
    );
});

add_action('admin_menu', function() {
    if (!current_user_can('manage_options')) {
        return; // block access for non-admins
    }

    add_menu_page(
        'ACF Custom Blocks',
        'ACF Blocks',
        'manage_options',
        'acf-custom-blocks',
        'acfcb_admin_page',
        'dashicons-screenoptions'
    );
});

function acfcb_admin_page() {
    ?>
    <div class="wrap">
        <h1>ACF Block Generator</h1>
        <form method="post" style="max-width: 800px;">
            <?php wp_nonce_field('acfcb_generate_block'); ?>
            <table class="form-table">
                <tr><th>Important</th><td><p>All Custom Blocks are added to the <em>CUSTOM BLOCKS</em> category for easy filtering in the page editor. Search by category or name to find them.</p></td></tr>
                <tr><th><label for="block_name">Block Name</label></th>
                    <td><input type="text" name="block_name" id="block_name" required style="width: 100%;"></td></tr>
                <tr><th><label for="block_description">Description</label></th>
                    <td><textarea name="block_description" id="block_description" rows="3" style="width: 100%;"></textarea></td></tr>
                <tr>
                    <th><label for="block_icon">Icon</label></th>
                    <td>
                        <input type="hidden" name="block_icon" id="block_icon" value="admin-generic">

                        <input type="text" id="acfcb-icon-search" placeholder="Search icons..." style="margin-bottom: 1em; width: 100%; padding: 6px;">
                        <div class="acfcb-icon-picker">
                            <?php
                            $icons = [
                                'admin-appearance', 'admin-collapse', 'admin-comments', 'admin-customizer', 'admin-generic', 'admin-home', 'admin-links',
                                'admin-media', 'admin-multisite', 'admin-network', 'admin-page', 'admin-plugins', 'admin-post', 'admin-settings',
                                'admin-site-alt', 'admin-site', 'admin-tools', 'admin-users', 'align-center', 'align-full-width', 'align-left',
                                'align-none', 'align-right', 'analytics', 'archive', 'arrow-down-alt', 'arrow-down', 'arrow-left-alt', 'arrow-left',
                                'arrow-right-alt', 'arrow-right', 'arrow-up-alt', 'arrow-up', 'art', 'awards', 'backup', 'book-alt', 'book',
                                'buddicons-activity', 'buddicons-bbpress-logo', 'buddicons-buddypress-logo', 'buddicons-community', 'buddicons-forums',
                                'buddicons-friends', 'buddicons-groups', 'buddicons-pm', 'buddicons-replies', 'buddicons-topics', 'buddicons-tracking',
                                'businessman', 'businessperson', 'businesswoman', 'calendar-alt', 'calendar', 'camera-alt', 'carrot', 'cart', 'category',
                                'chart-area', 'chart-bar', 'chart-line', 'chart-pie', 'clipboard', 'clock', 'cloud-saved', 'cloud-upload', 'cloud',
                                'columns', 'controls-back', 'controls-forward', 'controls-pause', 'controls-play', 'controls-repeat', 'controls-skipback',
                                'controls-skipforward', 'controls-volumeoff', 'controls-volumeon', 'cover-image', 'dashboard', 'database-add',
                                'database-export', 'database-import', 'database-remove', 'database-view', 'database', 'dismiss', 'download', 'drumstick',
                                'edit-large', 'edit-page', 'edit', 'editor-aligncenter', 'editor-alignleft', 'editor-alignright', 'editor-bold',
                                'editor-break', 'editor-code', 'editor-contract', 'editor-customchar', 'editor-expand', 'editor-help', 'editor-indent',
                                'editor-insertmore', 'editor-italic', 'editor-kitchensink', 'editor-ol', 'editor-outdent', 'editor-paragraph',
                                'editor-paste-text', 'editor-paste-word', 'editor-quote', 'editor-removeformatting', 'editor-rtl', 'editor-spellcheck',
                                'editor-strikethrough', 'editor-table', 'editor-textcolor', 'editor-ul', 'editor-underline', 'editor-unlink', 'editor-video',
                                'ellipsis', 'email-alt', 'email', 'embed-audio', 'embed-generic', 'embed-photo', 'embed-post', 'embed-video', 'excerpt-view',
                                'external', 'facebook-alt', 'facebook', 'feedback', 'filter', 'flag', 'format-aside', 'format-audio', 'format-chat',
                                'format-gallery', 'format-image', 'format-links', 'format-quote', 'format-standard', 'format-status', 'format-video',
                                'forms', 'fullscreen-alt', 'fullscreen-exit-alt', 'fullscreen-exit', 'fullscreen', 'games', 'groups', 'hammer',
                                'heading', 'heart', 'hidden', 'id-alt', 'id', 'image-crop', 'image-filter', 'image-flip-horizontal', 'image-flip-vertical',
                                'image-rotate-left', 'image-rotate-right', 'images-alt2', 'images-alt', 'index-card', 'info-outline', 'info',
                                'insert-after', 'insert-before', 'insert', 'layout', 'leftright', 'lightbulb', 'list-view', 'location-alt', 'location',
                                'lock', 'marker', 'media-archive', 'media-audio', 'media-code', 'media-default', 'media-document', 'media-interactive',
                                'media-spreadsheet', 'media-text', 'media-video', 'megaphone', 'menu-alt2', 'menu-alt3', 'menu-alt', 'menu', 'microphone',
                                'migrate', 'minus', 'money-alt', 'money', 'move', 'nametag', 'networking', 'no-alt', 'no', 'open-folder', 'palmtree',
                                'performance', 'pets', 'phone', 'playlist-audio', 'playlist-video', 'plus-alt2', 'plus-alt', 'plus', 'portfolio',
                                'post-status', 'pressthis', 'products', 'randomize', 'redo', 'remove', 'rest-api', 'rss', 'saved', 'schedule', 'screenoptions',
                                'search', 'share-alt2', 'share-alt', 'share', 'shield-alt', 'shield', 'shortcode', 'slides', 'smartphone', 'smiley',
                                'sort', 'sos', 'star-empty', 'star-filled', 'star-half', 'sticky', 'store', 'superhero-alt', 'superhero', 'tablet',
                                'tag', 'testimonial', 'text', 'tickets-alt', 'tickets', 'translation', 'trash', 'twitter', 'undo', 'universal-access-alt',
                                'universal-access', 'unlock', 'update', 'update-alt', 'upload', 'vault', 'video-alt2', 'video-alt3', 'video-alt',
                                'visibility', 'welcome-add-page', 'welcome-comments', 'welcome-learn-more', 'welcome-view-site', 'welcome-widgets-menus',
                                'welcome-write-blog', 'whatsapp', 'yes'
                            ];
                            foreach ($icons as $icon) {
                                echo '<div class="acfcb-icon dashicons dashicons-' . esc_attr($icon) . '" data-icon="' . esc_attr($icon) . '" title="' . esc_attr($icon) . '"></div>';
                            }
                            ?>
                        </div>
                        </td>
                </tr>
                <tr><th><label for="block_keywords">Keywords (comma separated)</label></th>
                    <td><input type="text" name="block_keywords" id="block_keywords" style="width: 100%;"></td></tr>
            </table>
            <?php submit_button('Generate Block'); ?>
        </form>
    </div>
    <?php
}

// Add a submenu page for ACF Custom Blocks
add_action('admin_menu', function () {
    if (!current_user_can('manage_options')) {
        return; // block access for non-admins
    }

    add_submenu_page(
        'options-general.php?page=acf-custom-blocks', // Or use your plugin's top-level menu slug
        'ACF Custom Blocks',
        'ACF Custom Blocks',
        'manage_options',
        'acf-custom-blocks',
        'acf_custom_blocks_admin_page'
    );
});

function acf_custom_blocks_admin_page() {
    // Handle individual field group generation
    if (
        isset($_POST['generate_field_group']) && // Check if form submitted
        isset($_GET['page']) &&
        $_GET['page'] === 'acf-custom-blocks' // Ensure it's only on this admin page
    ) {
        $block_to_generate = sanitize_title($_POST['generate_field_group']);

        if (check_admin_referer('acf_custom_blocks_generate_' . $block_to_generate)) {
            // Remove block from created groups so it can be regenerated
            $created_groups = get_option('acf_custom_blocks_created_groups', []);
            $created_groups = array_diff($created_groups, [$block_to_generate]);
            update_option('acf_custom_blocks_created_groups', $created_groups);

            // Generate field group again
            acf_custom_blocks_create_field_group_for_block($block_to_generate);

            echo '<div class="notice notice-success is-dismissible">
                <p>✅ Field group for <strong>' . esc_html($block_to_generate) . '</strong> has been (re)generated.</p>
            </div>';
        }
    }

    // Handle reset action
    if (isset($_POST['acf_custom_blocks_reset']) && check_admin_referer('acf_custom_blocks_reset_nonce')) {
        delete_option('acf_custom_blocks_created_groups');
        echo '<div class="notice notice-success is-dismissible">
            <p>✅ Block field groups reset. They will regenerate on next page load.</p>
        </div>';
    }

    // Get list of blocks
    $block_dir = get_template_directory() . '/blocks';
    $blocks = [];
    if (file_exists($block_dir)) {
        foreach (glob($block_dir . '/*/block.json') as $block_file) {
            $block_data = json_decode(file_get_contents($block_file), true);
            if ($block_data && isset($block_data['name'])) {
                $blocks[] = sanitize_title($block_data['name']);
            }
        }
    }
    ?>
    <div class="wrap">
        <h3>ACF Custom Blocks</h3>
        <p>Review block field groups and manage auto-generated groups.</p>

        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th>Block Name</th>
                    <th>Field Group Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blocks as $block_name) :
                    $field_group_title = 'Block: ' . ucfirst(str_replace('-', ' ', $block_name)) . ' Fields';
                    $field_group = null;

                    $query = new WP_Query(array(
                        'post_type'      => 'acf-field-group',
                        'title'          => $field_group_title,
                        'posts_per_page' => 1,
                        'post_status'    => 'publish',
                        'fields'         => 'ids',
                    ));

                    if (!empty($query->posts)) {
                        $field_group_id = $query->posts[0];
                        $field_group    = get_post($field_group_id);
                    }

                    wp_reset_postdata();
                    ?>
                    <tr>
                        <td><?php echo esc_html( ucwords( str_replace('-', ' ', $block_name) ) ); ?></td>
                        <td>
                            <?php if ($field_group) : ?>
                                ✅ Field Group Exists
                            <?php else : ?>
                                ❌ Missing Field Group
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($field_group) : ?>
                                <a href="<?php echo esc_url(get_edit_post_link($field_group->ID)); ?>" class="button button-primary" target="_blank">Edit</a>
                                <form method="post" style="display:inline-block;">
                                    <?php wp_nonce_field('acf_custom_blocks_generate_' . $block_name); ?>
                                    <input type="hidden" name="generate_field_group" value="<?php echo esc_attr($block_name); ?>">
                                    <button type="submit" class="button">🛠 Regenerate</button>
                                </form>
                            <?php else : ?>
                                <form method="post" style="display:inline-block;">
                                    <?php wp_nonce_field('acf_custom_blocks_generate_' . $block_name); ?>
                                    <input type="hidden" name="generate_field_group" value="<?php echo esc_attr($block_name); ?>">
                                    <button type="submit" class="button">➕ Generate Field Group</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <hr>

        <form method="post" onsubmit="return confirm('Are you sure you want to regenerate all block field groups? This won’t effect any other ACF field groups.');">
            <?php wp_nonce_field('acf_custom_blocks_reset_nonce'); ?>
            <p>
                <button type="submit" name="acf_custom_blocks_reset" class="button button-danger">
                    🔄 Regenerate ALL Block Field Groups
                </button>
            </p>
            <p class="description">This only regenerates field groups for blocks created by this plugin. Other ACF field groups won’t be effected.</p>
        </form>
    </div>
    <?php
}