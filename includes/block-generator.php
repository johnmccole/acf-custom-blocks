<?php

add_action('admin_init', function() {
    if (isset($_POST['block_name']) && check_admin_referer('acfcb_generate_block')) {
        acfcb_generate_block($_POST);
    }
});

function acfcb_generate_block($data) {
    $theme_dir = get_stylesheet_directory();
    $acf_json = $theme_dir . '/acf-json';
    $blocks_dir = $theme_dir . '/blocks';

    // Create directories if they don’t exist
    if (!file_exists($acf_json)) mkdir($acf_json, 0755, true);
    if (!file_exists($blocks_dir)) mkdir($blocks_dir, 0755, true);

    // Create helpers.php if it doesn't exist
    $helpers_path = $blocks_dir . '/helpers.php';
    if (!file_exists($helpers_path)) {
    file_put_contents($helpers_path, <<<'PHP'
    <?php
    if (!function_exists('flatten_block_styles')) {
        function flatten_block_styles($styles, $prefix = ''): array {
            $flattened = [];

            $style_map = [
                // Existing spacing
                'spacing-margin-top'         => 'margin-top',
                'spacing-margin-bottom'      => 'margin-bottom',
                'spacing-margin-left'        => 'margin-left',
                'spacing-margin-right'       => 'margin-right',
                'spacing-padding-top'        => 'padding-top',
                'spacing-padding-bottom'     => 'padding-bottom',
                'spacing-padding-left'       => 'padding-left',
                'spacing-padding-right'      => 'padding-right',

                // Typography
                'typography-line-height'     => 'line-height',
                'typography-font-size'       => 'font-size',
                'typography-text-transform'  => 'text-transform',
                'typography-letter-spacing'  => 'letter-spacing',

                // Colors
                'color-text'                 => 'color',
                'color-background'           => 'background-color',
                'elements-link-color-text'   => 'color',

                // Dimensions
                'dimensions-min-height'      => 'min-height',
                'dimensions-width'           => 'width',

                // Borders
                'border-radius'              => 'border-radius',
                'border-width'               => 'border-width',
                'border-style'               => 'border-style',
                'border-color'               => 'border-color',
            ];

            foreach ($styles as $key => $value) {
                // Convert camelCase to kebab-case
                $kebabKey = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $key));

                $full_key = $prefix ? $prefix . '-' . $kebabKey : $kebabKey;

                if (is_array($value)) {
                    // Optional: skip unsupported sections
                    if ($prefix === 'elements') continue;

                    // Continue recursion
                    $nested = flatten_block_styles($value, $full_key);
                    foreach ($nested as $sub) {
                        $flattened[] = $sub;
                    }
                } else {
                    // Now map to real CSS property if it exists
                    $css_prop = $style_map[$full_key] ?? null;
                    if ($css_prop) {
                        // Convert WordPress preset syntax to CSS var
                        if (is_string($value) && str_starts_with($value, 'var:preset|')) {
                            $value = str_replace('var:preset|', 'var(--wp--preset--', $value);
                            $value = str_replace('|', '--', $value) . ')';
                        }

                        $flattened[] = $css_prop . ': ' . $value;
                    }
                }
            }

            return $flattened;
        }
    }
    PHP);
    }

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
            'supports' => [
                'anchor' => true,
                'align' => ['wide', 'full', 'left', 'right', 'center'],
                'customClassName' => true,
                'jsx' => true,
                'spacing' => ['margin' => true, 'padding' => true],
                'typography' => [
                    'fontSize' => true,
                    'lineHeight' => true,
                    'textTransform' => true,
                    'letterSpacing' => true
                ],
                'color' => [
                    'text' => true,
                    'background' => true,
                    'gradients' => true
                ],
                'dimensions' => [
                    'minHeight' => true,
                    'width' => true
                ],
                'border' => [
                    'radius' => true,
                    'width' => true,
                    'style' => true,
                    'color' => true
                ]
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
        file_put_contents($block_path . '/template.php', <<<PHP
        <?php require_once get_template_directory() . '/blocks/helpers.php'; ?>

        <?php
        \$style_attr = '';
        if (!empty(\$block['style']) && is_array(\$block['style'])) {
            \$style_attr = implode('; ', flatten_block_styles(\$block['style']));
        } elseif (!empty(\$block['style']) && is_string(\$block['style'])) {
            \$style_attr = \$block['style'];
        }

        \$extra_classes = [];

        if (!empty(\$block['backgroundColor'])) {
            \$extra_classes[] = 'has-' . sanitize_html_class(\$block['backgroundColor']) . '-background-color';
        }

        if (!empty(\$block['textColor'])) {
            \$extra_classes[] = 'has-' . sanitize_html_class(\$block['textColor']) . '-color';
        }

        if (!empty(\$block['fontSize'])) {
            \$extra_classes[] = 'has-' . sanitize_html_class(\$block['fontSize']) . '-font-size';
        }

        if (!empty(\$block['align'])) {
            \$extra_classes[] = 'align' . sanitize_html_class(\$block['align']);
        }

        if (!empty(\$block['className'])) {
            \$extra_classes[] = sanitize_html_class(\$block['className']);
        }

        \$class_attr = implode(' ', array_filter(\$extra_classes));
        ?>

        <section id="<?= esc_attr(\$block['anchor'] ?? '') ?>" class="acf-block-{$block_slug} <?= esc_attr(\$class_attr) ?>" style="<?= esc_attr(\$style_attr) ?>">
            <!-- Render: <?= \$block['title']; ?> -->
        </section>
        PHP);

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