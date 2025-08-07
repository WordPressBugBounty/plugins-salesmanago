<div class="wrap integration-wrap">
    <h1><?php _e('Define integration scope', 'salesmanago') ?></h1>
    <p class="intro">
        <?php _e('Choose the platform(s) you want to integrate with your service.', 'salesmanago') ?>
    </p>

    <a href="<?php echo admin_url('admin.php?page=' . $pageInSmLink); ?>" class="integration-card">
        <div class="icon sales">
            <img
                    src="<?php echo plugin_dir_url(__FILE__); ?>img/sm_logo.png"
                    alt="<?php esc_attr_e('SALESmanago', 'salesmanago'); ?>"
                    width="48" height="48"
            />
        </div>
        <div>
            <h2><?php echo __('SALESmanago', 'salesmanago'); ?></h2>
            <p>
                <?php _e('A Customer Engagement Platform that combines CDP, marketing automation, and AI capabilities. Personalize customer experiences and drive conversions through precisely targeted, effective marketing campaigns.', 'salesmanago') ?>
            </p>
        </div>
    </a>

    <a href="<?php echo admin_url('admin.php?page=leadoo'); ?>" class="integration-card">
        <div class="icon leadoo">
            <img
                    src="<?php echo plugin_dir_url(__FILE__); ?>img/leadoo_logo.png"
                    alt="<?php esc_attr_e('Leadoo', 'salesmanago'); ?>"
                    width="48" height="48"
            />
        </div>
        <div>
            <h2><?php echo __('Leadoo', 'salesmanago'); ?></h2>
            <p>
                <?php _e('A Conversion Platform designed to turn passive website traffic into active leads. Identify, activate, nurture, and ultimately convert more of your website visitors.', 'salesmanago') ?>
            </p>
        </div>
    </a>

    <div class="integration-card integration-note" style="display: flex; align-items: flex-start; gap: 16px; border: 1px solid #ddd; padding: 12px; margin-top: 12px;">
        <div class="icon combo" style="flex: 0 0 auto;">
            <img
                    src="<?php echo plugin_dir_url(__FILE__); ?>img/leadoo_and_salesmanago.png"
                    alt="<?php esc_attr_e('SALESmanago & Leadoo', 'salesmanago'); ?>"
                    width="48" height="48"
            />
        </div>
        <div class="text">
            <strong style="display: block; margin-bottom: 4px;">
                <?php echo __('SALESmanago & Leadoo', 'salesmanago'); ?>
            </strong>
            <p style="margin: 0;">
                <?php _e(
                    'Benefit from the combined power of two market-leading, GenAI-enabled platforms. Integrate SALESmanago and Leadoo with your website using a single plugin.',
                    'salesmanago'
                ); ?>
            </p>
        </div>
    </div>
</div>

