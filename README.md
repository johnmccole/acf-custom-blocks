A free plugin to generate custom Blocks and their CSS file in the active WordPress theme.

1. Install plugin.
2. Activate plugin.
3. Go to: your-domain/wp-admin/admin.php?page=acf-custom-blocks
4. Enter the details of the new block.
5. Add a new ACF Field Group, add fields and assign the new field group to the Block.

A 'blocks' directory is added to the theme root, each block is in its own sub-directory. Within each of these are 6 files:

1. block.json
2. example.json
3. index.php
4. style.css
5. template.php

Update template.php with markup and custom fields, update style.css with custom styles. 

Blocks support InnerBlocks too, add: 

    <?= '<InnerBlocks />'; ?>

To where you would like InnerBlocks to display.
