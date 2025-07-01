<?php
/**
 * Plugin Name: ACF Custom Blocks
 * Description: Register ACF Blocks from the admin UI.
 * Version: 0.1a
 * Author: JM Web Dev
 */

if (!defined('ABSPATH')) exit;

// Load core
require_once plugin_dir_path(__FILE__) . 'includes/admin-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/block-generator.php';

add_action('acf/init', function() {
    $block_dir = get_stylesheet_directory() . '/blocks';
    if (!file_exists($block_dir)) return;

    foreach (glob($block_dir . '/*/block.json') as $block_file) {
        $block_data = json_decode(file_get_contents($block_file), true);
        $path = dirname($block_file);

        if ($block_data) {
			$template = isset($block_data['acf']['renderTemplate'])
				? $path . '/' . basename($block_data['acf']['renderTemplate'])
				: $path . '/template.php'; // fallback

			acf_register_block_type(array_merge($block_data, [
				'render_template' => $template,
				'name'            => $block_data['name'],
			]));
		}
    }
});

add_filter('block_categories_all', function ($categories, $post) {
    return array_merge(
        [['slug' => 'custom-blocks', 'title' => __('Custom Blocks', 'custom-blocks')]],
        $categories
    );
}, 10, 2);

add_action('wp_enqueue_scripts', function () {
    $theme_uri = get_stylesheet_directory_uri();
    $theme_dir = get_stylesheet_directory();

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
    $blocks_path = get_stylesheet_directory() . '/blocks';
    $blocks_url  = get_stylesheet_directory_uri() . '/blocks';

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
