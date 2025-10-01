<?php
/**
 * @var string $data = $this->AdminModel->getAboutInfo() from SettingsRenderer
 * @var string $logs = $this->AdminModel->getErrorLog() from SettingsRenderer
 * @var string $api_v3_logs = $this->AdminModel->getErrorLog( true ) from SettingsRenderer
 * @var bool $is_new_api_v3_error = $this->AdminModel->isNewApiError from SettingsRenderer
 */

use bhr\Admin\Entity\MessageEntity;

?>
<div id="salesmanago-content">
    <h2>
		<?php _e( 'Error log for API v3 data transfer', 'salesmanago' ) ?>
    </h2>
    <?php if ($api_v3_logs): ?>
    <form action="" method="post">
        <textarea class="log-textarea<?php if ( $is_new_api_v3_error ) echo ' sm-new-api-v3-error'; ?>" id="salesmanago-api-v3-error-log" onclick="this.select()" readonly>
            <?= $api_v3_logs ?>
        </textarea>
        <div class="sm-api-v3-log-label-wrapper">
            <label>
                <?php _e( 'Error log contains crucial information about any potential problems with API v3 data transfer. If you need support with troubleshooting, contact us at ', 'salesmanago' ) ?><a href="mailto:support@salesmanago.com">support@salesmanago.com</a>
            </label>
            <div>
                <button class="button button-secondary" onclick="salesmanagoCopyLog('salesmanago-api-v3-error-log')"><?php _e('Copy', 'salesmanago') ?></button>
            </div>
        </div>
            <input type="submit"
                   class="button button-primary sm-btn-top-margin"
                   id="sm-btn-acknowledge-api-errors"
                   value="<?php _e( 'NOTICED!', 'salesmanago' ) ?>">
            <input type="hidden" name="name" value="SALESmanago">
            <?php
            if ( function_exists( 'wp_nonce_field' ) ) {
                wp_nonce_field( 'acknowledgeProductApiError', 'sm_nonce' );
            }
            ?>
            <input type="hidden" name="action" value="acknowledgeProductApiError">
    </form>
    <?php else:
        //Remove notification, as there are no logs to be seen
		$this->AdminModel->getConfiguration()->setIsNewApiError( false );
		$this->AdminModel->saveConfiguration();
        ?>
    <div class="salesmanago-notice notice notice-info inline">
		<?=__( 'There is no SALESmanago error or plugin has no permission to read error log.', 'salesmanago' )?>
    </div>
    <?php endif ?>
    <h2><?= __('Our support pages', 'salesmanago')?></h2>
    <table class="form-table">
        <tbody>
        <?php
            $supportPages = array(
                "Contact Form 7" => __( 'https://support.salesmanago.com/how-do-i-integrate-salesmanago-with-contact-form-7-wordpress/?utm_source=integration&utm_medium=wordpress&utm_content=tooltip', 'salesmanago' ),
                "Gravity Forms"  => __( 'https://support.salesmanago.com/integration-with-wordpress-gravity-forms-plugin/?utm_source=integration&utm_medium=wordpress&utm_content=tooltip', 'salesmanago' ),
                "WooCommerce"    => __( 'https://support.salesmanago.com/woocommerce-integration-settings/?utm_source=integration&utm_medium=wordpress&utm_content=tooltip', 'salesmanago' ),
                "WordPress"      => __( 'https://support.salesmanago.com/integration-with-wordpress/?utm_source=integration&utm_medium=wordpress&utm_content=tooltip', 'salesmanago' ),
            );
            foreach ($supportPages as $key => $value):
        ?>
        <tr valign="top">
            <th scope="row">
                <span class="about-info-span"> <?=$key?></span>
            </th>
            <td>
                <a href="<?=$value?>" target="_blank"><?=$key?> support page</a>
            </td>
        </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    <table class="form-table">
        <tbody>
        <h2><?php _e('Functionality tests', 'salesmanago') ?></h2>
        <tr valign="top">
            <th scope="row">
                <label for="salesmanago-cart-recovery-button"><?php _e( 'Test cart recovery', 'salesmanago' ); ?></label>
            </th>
            <td>
                <?php
                $url = home_url() . '?rest_route=/salesmanago/v1/cart';
                try {
                    global $woocommerce;
                    $url = add_query_arg( $woocommerce->query_string, '', home_url( $woocommerce->request ) ) . '?rest_route=/salesmanago/v1/cart';
                } catch ( \Exception $e ) {
                    MessageEntity::getInstance()->addException( new Exception( $e->getMessage(), 655 ) );
                }
                ?>
                <button class="button button-secondary" id="salesmanago-cart-recovery-button" onclick="salesmanagoTestCartRecovery('<?php echo $url; ?>'); return false;"><?php _e( 'Test REST routes and functions', 'salesmanago' ); ?></button>
                <label for="salesmanago-cart-recovery-button">
                    <p class="description">
                        <?php _e( 'Test REST API to debug cart recovery links.', 'salesmanago' ); ?>
                    </p>
                </label>
            </td>
        </tr>
        <tr valign="top" id="salesmanago-cart-recovery-test" style="display: none">
            <th scope="row" class="titledesc">
                <label class="about-info-span" for="salesmanago-cart-recovery-test-content"><?php _e( 'Results', 'salesmanago' ); ?></label>
            </th>
            <td id="salesmanago-cart-recovery-test-content">
                <?php /* Cart recovery test results will be shown here */ ?>
            </td>
        </tr>
        </tbody>
    </table>
    <form action="" method="post" enctype="application/x-www-form-urlencoded" id="salesmanago-conf">
        <h2>
            <?php
            _e('Integration data', 'salesmanago') ?>
        </h2>
        <textarea class="log-textarea" onclick="this.select()" id="salesmanago-about-log" readonly><?=$data?></textarea>
        <button style="display: block" class="button button-secondary" onclick="salesmanagoCopyLog('salesmanago-about-log')"><?php _e('Copy', 'salesmanago') ?></button>
    </form>
    <form action="" method="post" enctype="application/x-www-form-urlencoded" id="salesmanago-conf">
        <h2>
            <?php
            _e('Wordpress error log', 'salesmanago') ?>
        </h2>
        <?php if ($logs): ?>
        <textarea class="log-textarea" onclick="this.select()" id="salesmanago-error-log" readonly><?=$logs?></textarea>
        <button style="display: block" class="button button-secondary" onclick="salesmanagoCopyLog('salesmanago-error-log')"><?php _e('Copy', 'salesmanago') ?></button>
        <?php else: ?>
        <div class="salesmanago-notice notice notice-info inline">
            <?=__( 'There is no SALESmanago error or plugin has no permission to read error log.', 'salesmanago' )?>
        </div>
        <?php endif;?>
    </form>
</div>
<script>
    function salesmanagoScrollLog( isApiv3Log = true )
    {
        let logTextArea =
            isApiv3Log ?
                document.getElementById( 'salesmanago-api-v3-error-log' ) :
                document.getElementById( 'salesmanago-error-log' );
        if ( logTextArea ) {
            logTextArea.scrollTop = logTextArea.scrollHeight;
        }
    }
    salesmanagoScrollLog( true );
    salesmanagoScrollLog( false );
</script>
