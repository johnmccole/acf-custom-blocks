A free plugin to generate custom Blocks and their CSS file in the active WordPress theme.

1. Install plugin.
2. Activate plugin.
3. Go to: your-domain/wp-admin/admin.php?page=acf-custom-blocks
4. Enter the details of the new block.
5. Add a new ACF Field Group, an option in the plugin UI tells the admin if the block has an assigned group.
6. Blocks generated via the plugin are listed in the plugin UI.

A 'blocks' directory is added to the theme root, each block is in its own sub-directory. Within each of these are 4 files:

1. block.json
2. index.php
3. style.css
4. template.php

Update template.php with markup and custom fields, update style.css with custom styles. 

Blocks support InnerBlocks too, add: 

    <?= '<InnerBlocks />'; ?>

To where you would like InnerBlocks to display.
