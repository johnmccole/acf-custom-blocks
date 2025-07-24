<?php
/**
 * Plugin Name: ACF Custom Blocks
 * Description: Dynamically register ACF Blocks from the admin UI.
 * Version: 0.2a
 * Author: JM Web Dev
 */

if (!defined('ABSPATH')) exit;

// Load core
require_once plugin_dir_path(__FILE__) . 'includes/admin-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/block-generator.php';

// Load blocks and field groups early enough for ACF to pick them up
add_action('acf/init', function() {
    $block_dir = get_template_directory() . '/blocks';
    if (!file_exists($block_dir)) return;

    foreach (glob($block_dir . '/*/block.json') as $block_file) {
        $block_data = json_decode(file_get_contents($block_file), true);
        $path = dirname($block_file);

        if ($block_data) {
            $template = isset($block_data['acf']['renderTemplate'])
                ? $path . '/' . basename($block_data['acf']['renderTemplate'])
                : $path . '/template.php'; // fallback

            // Register block and create field group
            acf_custom_blocks_register_block_with_fields(array_merge($block_data, [
                'render_template' => $template,
                'name'            => $block_data['name'],
            ]));
        }
    }
}, 5); // Priority 5 to run early within acf/init

add_filter('block_categories_all', function ($categories, $post) {
    return array_merge(
        [['slug' => 'custom-blocks', 'title' => __('Custom Blocks', 'custom-blocks')]],
        $categories
    );
}, 10, 2);

add_action('wp_enqueue_scripts', function () {
    $theme_uri = get_template_directory_uri();
    $theme_dir = get_template_directory();

    // Load the same block styles on the frontend
    if (file_exists($theme_dir . '/blocks/editor-style.css')) {
        wp_enqueue_style(
            'acf-blocks-frontend-style',
            $theme_uri . '/blocks/editor-style.css',
            [],
            filemtime($theme_dir . '/blocks/editor-style.css')
        );
    }
});

add_action('enqueue_block_editor_assets', function () {
    $blocks_path = get_template_directory() . '/blocks';
    $blocks_url  = get_template_directory_uri() . '/blocks';

    if (!file_exists($blocks_path)) return;

    foreach (glob($blocks_path . '/*/style.css') as $css_file) {
        $slug = basename(dirname($css_file));
        wp_enqueue_style(
            "acf-block-editor-{$slug}",
            $blocks_url . '/' . $slug . '/style.css',
            [],
            filemtime($css_file)
        );
    }
});

/**
 * Registers a new ACF block type and automatically creates a matching field group.
 *
 * @param array $block_args Arguments for acf_register_block_type().
 */
function acf_custom_blocks_register_block_with_fields($block_args) {
    if (!function_exists('acf_register_block_type')) {
        error_log('ACF is not active. Cannot register block.');
        return;
    }

    // Sanitize block name
    $block_name = sanitize_title($block_args['name']);

    // Check if block already exists
    if (acf_custom_blocks_block_exists($block_name)) {
        error_log("Block '{$block_name}' already exists. Skipping registration.");
        return;
    }

    // Register block
    acf_register_block_type($block_args);

    // Immediately create field group if missing
    $field_group_title = 'Block: ' . ucfirst(str_replace('-', ' ', $block_name)) . ' Fields';
    $existing_group = get_posts(array(
        'post_type'      => 'acf-field-group',
        'posts_per_page' => 1,
        'title'          => $field_group_title,
        'fields'         => 'ids',
    ));

    if (empty($existing_group)) {
        add_action('admin_init', function () use ($block_name) {
            acf_custom_blocks_create_field_group_for_block($block_name);
        });
        error_log("Auto-created field group for '{$block_name}' on acf/init.");
    } else {
        error_log("Field group for '{$block_name}' already exists. No action taken.");
    }
}

/**
 * Checks if a block with the given name is already registered.
 */
function acf_custom_blocks_block_exists( $block_name ) {
    $registry = WP_Block_Type_Registry::get_instance();
    return $registry->is_registered( 'acf/' . $block_name );
}

/**
 * Adds a default ACF field group for a block if it doesn't already exist.
 */
function acf_custom_blocks_create_field_group_for_block($block_name) {
    if (!function_exists('acf_update_field_group')) {
        error_log("ACF not active. Cannot create field group for '{$block_name}'.");
        return;
    }

    // Track created field groups in WP options
    $created_groups = get_option('acf_custom_blocks_created_groups', []);

    if (in_array($block_name, $created_groups, true)) {
        error_log("Field group for '{$block_name}' already created. Skipping.");
        return;
    }

    $field_group_title = 'Block: ' . ucfirst(str_replace('-', ' ', $block_name)) . ' Fields';
    $field_group_key   = 'group_' . sanitize_title($block_name);

    // Check if a field group already exists in DB
    $existing_group = get_posts(array(
        'post_type'      => 'acf-field-group',
        'posts_per_page' => 1,
        'title'          => $field_group_title,
        'fields'         => 'ids',
    ));

    if (!empty($existing_group)) {
        error_log("Field group '{$field_group_title}' already exists. Skipping.");
        return;
    }

    // Create new field group
    $field_group = array(
        'title'    => $field_group_title,
        'key'      => $field_group_key,
        'fields'   => [], // Start blank
        'location' => array(
            array(
                array(
                    'param'    => 'block',
                    'operator' => '==',
                    'value'    => 'acf/' . $block_name,
                ),
            ),
        ),
        'menu_order' => 0,
        'position'   => 'normal',
        'style'      => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active'     => 1,
        'description'=> '',
        'local'      => 0, // Crucial: persist in DB
    );

    $field_group_id = acf_update_field_group($field_group);

    if ($field_group_id) {
        // Mark as created
        $created_groups[] = $block_name;
        update_option('acf_custom_blocks_created_groups', $created_groups);

        error_log("Created editable field group for '{$block_name}' in admin.");
    } else {
        error_log("Failed to create field group for '{$block_name}'.");
    }
}
