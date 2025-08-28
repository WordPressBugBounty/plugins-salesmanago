<div class="wrap leadoo-wrap" <?php if ($_GET['page'] === 'leadoo' && $_GET['action'] !== 'discover'): ?> style="max-width: 100%;"<?php endif; ?> >
    <h1>
        <img src="<?php echo plugin_dir_url(__FILE__) . 'img/leadoo-logo.svg'; ?>" class="logo" alt="Leadoo logo">
    </h1>
    <h2 class="nav-tab-wrapper">
        <a href="?page=leadoo" class="nav-tab <?= ($_GET['page'] === 'leadoo' && !isset($_GET['action'])) ? 'nav-tab-active' : '' ?>">Leadoo</a>
        <a href="<?= admin_url('admin.php?page=' . $_GET['page']) . '&action=discover';//pageInSmLink should be set before include this file ?>"
           class="nav-tab <?= ($_GET['page'] === 'leadoo' && $_GET['action'] === 'discover') ? 'nav-tab-active' : '' ?>">
            <?php _e('Discover SALESmanago', 'salesmanago') ?>
        </a>
    </h2>
    <?php if ($_GET['page'] === 'leadoo' && !isset($_GET['action'])): ?>
        <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
            <div class="alert-bar">
                <span class="message">
                  <?php _e("Script was", "salesmanago"); ?> <span class="highlight"> <?php _e("successfully saved", "salesmanago"); ?></span>
                </span>
                <a href="<?= admin_url('admin.php?page=' . $_GET['page']); ?>" class="close-btn" aria-label="Dismiss">&times;</a>
            </div>
        <?php endif; ?>
        <div id="leadoo-tutorial">
            <h2 class="tutorial-toggle">
                <?php _e('Tutorial: Adding Leadoo Bots to Your Website', 'salesmanago'); ?>
                <span class="dashicons dashicons-arrow-down" style="float:right;"></span>
            </h2>
            <div class="tutorial-content">
                <p>
                    <?php _e("Implementing Leadoo is extremely easy. Simply add the Leadoo bot script to the header section and it will automatically start working", "salesmango"); ?>
                </p>
            </div>
            <div class="tutorial-content">
                <div class="tutorial-steps">
                    <ul class="steps-list">
                        <li>
                            <strong><?php _e("Step#1:", "salesmanago");?></strong>
                            <ul class="step-details">
                                <li><?php _e("Open the Leadoo admin panel and navigate to the chatbot or relevant section. Click the blue “Add to site” button.", "salesmanago");?></li>
                            </ul>
                        </li>
                        <li>
                            <strong><?php _e("Step#2:", "salesmanago");?></strong>
                            <ul class="step-details">
                                <li><?php _e("Select and copy the script you want to use on your site.", "salesmanago");?></li>
                            </ul>
                        </li>
                        <li>
                            <strong><?php _e("Step#3:", "salesmanago");?></strong>
                            <ul class="step-details">
                                <li><?php _e("Paste the script into the Header Script field at the bottom and click Save. The plugin will handle the rest automatically.", "salesmanago");?></li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="tutorial-images">
                    <img src="<?php echo plugin_dir_url(__FILE__).'img/leadoo_img_1.png'; ?>" alt="">
                    <img src="<?php echo plugin_dir_url(__FILE__).'img/leadoo_img_2.png'; ?>" alt="">
                </div>
            </div>
        </div>
        <form method="post" action="<?= admin_url('admin.php?page=' . $_GET['page']) . '&success=1' ?>">
            <?php
            settings_fields('leadoo_header_scripts');
            do_settings_sections('leadoo_header_scripts');
            ?>
            <h2 class="section-title"><?php _e('Header Scripts:', 'salesmanago'); ?></h2>
            <textarea name="leadoo_script" class="large-text code" rows="8"><?= esc_textarea($this->showLeadooScript()) ?></textarea>
            <p class="description">
                <?php _e('These script will be printed in the ', 'salesmanago'); ?>
                <code>&lt;head&gt;</code>
                <?php _e('section.', 'salesmanago'); ?>
            </p>
            <input type="hidden" name="action" value="save">
            <?php submit_button(__('Save script','salesmanago')); ?>
        </form>
    <?php endif; ?>

    <?php if ($_GET['page'] === 'leadoo' && (isset($_GET['action']) && $_GET['action'] === 'discover')): ?>
        <?php include __DIR__ . '/discover.php'; ?>
    <?php endif; ?>
</div>
<script>
    jQuery(function($){
        $('#leadoo-tutorial .tutorial-toggle').on('click', function(){
            var $wrap = $('#leadoo-tutorial');
            var $icon = $(this).find('.dashicons');
            $wrap.find('.tutorial-content').slideToggle(200);
            $icon.toggleClass('dashicons-arrow-down dashicons-arrow-up');
        });

        var $ta     = $('textarea[name="leadoo_script"], textarea[name="leadoo_script"]');
        var $submit = $ta.closest('form').find('input[type="submit"], button[type="submit"]');

        var $msg = $('<div class="notice notice-error inline"><p><strong>Warning:</strong> Detected duplicate <code>&lt;script&gt;</code> tags or identical src attributes. Please remove duplicates before saving.</p></div>');
        $msg.insertBefore( $ta ).hide();

        function checkDuplicates(){
            var html = $ta.val() || '';
            var scripts = html.match(/<script\b[\s\S]*?<\/script>/gi) || [];
            var seen = {}, dupes = [];

            scripts.forEach(function(tag){
                var m = tag.match(/\bsrc\s*=\s*['"]([^'"]+)['"]/i);
                var key = m ? m[1] : tag.trim();
                if( seen[key] ) dupes.push(key);
                seen[key] = true;
            });

            if( dupes.length ){
                $msg.show();
                $submit.prop('disabled', true);
            } else {
                $msg.hide();
                $submit.prop('disabled', false);
            }
        }

        $ta.on('input paste', function(){
            setTimeout(checkDuplicates, 10);
        });

        checkDuplicates();
    });
</script>
