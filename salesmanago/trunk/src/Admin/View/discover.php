<div class="discover-section video-wrapper" style="border:1px solid #ddd; padding:16px; background:#fff;">
    <p class="discover-subtitle" style="margin:0 0.5em 8px; font-weight:600;">
        <?php _e('Discover SALESmanago & Leadoo', 'salesmanago'); ?>
    </p>
    <p class="discover-text video-description" style="margin:0 0.5em 16px; color:#555; line-height:1.4;">
        <?php _e(
            'Enhance SALESmanago by integrating with Leadoo, a conversational marketing platform that captures and qualifies leads in real-time. Automatically sync these leads to fuel personalised campaigns and boost conversions. Combine Leadoo with SALESmanago for smarter engagement.',
            'salesmanago'
        ); ?>
    </p>
    <?php
        if ( isset($_GET['page']) && $_GET['page'] === 'salesmanago-discover-leadoo' ) {
            $url = 'https://www.salesmanago.com/info/conversational-marketing.htm?utm_source=product&utm_medium=wpplugin&utm_campaign=cosell';
        } elseif ( isset($_GET['page'], $_GET['action'])
            && $_GET['page'] === 'leadoo'
            && $_GET['action'] === 'discover' ) {
            $url = 'https://leadoo.com/landing/salesmanago-x-leadoo/?utm_source=product&utm_medium=wpplugin&utm_campaign=cosell';
        } else {
            $url = '#';
        }
    ?>
    <p class="discover-cta-paragraph">
        <a class="discover-cta-button" href="<?= esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer">
            <img class="discover-cta-button-image" src="<?= plugin_dir_url(__FILE__); ?>img/salesmanago-leadoo-combo-small.png" alt="" />
            <?php _e('Discover more', 'salesmanago'); ?>
        </a>
    </p>
    <div class="discover-image video-container">
        <iframe src="https://www.youtube.com/embed/o7btgaVizNo?si=jBRlnA7uG0juzj_L" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
    </div>
</div>
