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
