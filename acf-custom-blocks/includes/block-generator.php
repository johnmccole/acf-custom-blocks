<?php

add_action('admin_init', function() {
    if (isset($_POST['block_name']) && check_admin_referer('acfcb_generate_block')) {
        acfcb_generate_block($_POST);
    }
});

function acfcb_generate_block($data) {
    $theme_dir = get_template_directory();
    $acf_json = $theme_dir . '/acf-json';
    $blocks_dir = $theme_dir . '/blocks';

    // Create directories if they don’t exist
    if (!file_exists($acf_json)) mkdir($acf_json, 0755, true);
    if (!file_exists($blocks_dir)) mkdir($blocks_dir, 0755, true);

    $block_name = sanitize_title($data['block_name']);
    $label = sanitize_text_field($data['block_name']);
    $desc = sanitize_text_field($data['block_description']);
    $icon = sanitize_text_field($data['block_icon']);
    $keywords = array_map('trim', explode(',', $data['block_keywords'] ?? ''));

    $block_slug = strtolower($block_name);
    $block_path = $blocks_dir . '/' . $block_slug;

    if (!file_exists($block_path)) {
        mkdir($block_path);

        // block.json
        file_put_contents($block_path . '/block.json', json_encode([
            'name'           => $block_slug,
            'title'          => $label,
            'description'    => $desc,
            'icon'           => $icon,
            'keywords'       => $keywords,
            'category'       => 'custom-blocks',
            'mode'           => 'preview',
            'align'          => 'full',
            'supports'       => [
                'align'      => ['full', 'wide', 'left', 'right', 'center'],
                'anchor'     => true,
                'jsx'        => true,
                'color'      => [
                    'text'       => true,
                    'background' => true,
                ],
                'spacing'    => [
                    'padding'    => true,
                    'margin'     => true,
                ],
                'typography' => [
                    'fontSize'   => true,
                    'lineHeight' => true,
                ],
            ],
            'acf'         => ['mode' => 'preview', 'renderTemplate' => 'template.php'],
            'example' => [
                'attributes' => ['mode' => 'preview'],
                'data' => [
                    'example_field' => 'Example Content'
                ]
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Blade or PHP Template
        file_put_contents($block_path . '/template.php', "<div class=\"acf-block acf-block-{$block_slug} align<?= esc_attr(\$block['align'] ?? '') ?>\">\n\t<!-- Render: <?= \$block['title']; ?> -->\n</div>");

        // Example Data
        file_put_contents($block_path . '/example.json', json_encode([
            'data' => ['example_field' => 'Example Value']
        ], JSON_PRETTY_PRINT));

        // Success message
        add_action('admin_notices', function() use ($label) {
            echo "<div class='notice notice-success is-dismissible'><p>Block <strong>{$label}</strong> generated successfully.</p></div>";
        });
    } else {
        add_action('admin_notices', function() use ($label) {
            echo "<div class='notice notice-error is-dismissible'><p>Block <strong>{$label}</strong> already exists.</p></div>";
        });
    }
    // style.css
    file_put_contents($block_path . '/style.css', <<<CSS
    .acf-block-{$block_slug} {
        padding: 2rem;
        background: #f0f0f0;
        border: 1px dashed #ccc;
    }
    CSS);

    // Add index.php to /blocks
    $indexPath = $blocks_dir . '/index.php';
    if (!file_exists($indexPath)) {
        file_put_contents($indexPath, "<?php // Silence is golden\n");
    }

    // Add index.php to the specific block folder
    $blockIndex = $block_path . '/index.php';
    if (!file_exists($blockIndex)) {
        file_put_contents($blockIndex, "<?php // Silence is golden\n");
    }

    $acfIndex = $acf_json . '/index.php';
    if (!file_exists($acfIndex)) {
        file_put_contents($acfIndex, "<?php // Silence is golden\n");
    }

    $editor_css_path = $blocks_dir . '/editor-style.css';
    $import_line = "@import url('./{$block_slug}/style.css');\n";

    if (!file_exists($editor_css_path)) {
        file_put_contents($editor_css_path, $import_line);
    } else {
        $contents = file_get_contents($editor_css_path);
        if (strpos($contents, $import_line) === false) {
            file_put_contents($editor_css_path, $contents . $import_line);
        }
    }
}