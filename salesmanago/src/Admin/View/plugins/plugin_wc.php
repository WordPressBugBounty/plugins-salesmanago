<?php
/**
 * @var string $context = SUPPORTED_PLUGINS['WooCommerce'] from SettingsRenderer
 */

use bhr\Admin\Entity\MessageEntity;
use SALESmanago\Exception\Exception;

?>
<div id="salesmanago-content">
    <form action="" method="post" enctype="application/x-www-form-urlencoded" id="salesmanago-conf">
        <h2>
            <?php _e('Manage WooCommerce integration', 'salesmanago') ?>
        </h2>
        <div class="notice notice-info inline">
            <?php
                echo(__('You can allow customers to create new accounts.','salesmanago')
                . ' <a href="options-general.php">' . __('Manage options in WooCommerce settings', 'salesmanago') . '</a>.'
                ) ?>
        </div>
        <?php
            include(__DIR__ . '/../partials/owner.php');
        ?>
        <h3><?php _e('Tags', 'salesmanago') ?></h3>
        <table class="form-table">
        <?php
            $tagsInputs = array(
                'registration' => array(
                    'label'       => __('Registration tags', 'salesmanago'),
                    'description' => __('Tags assigned after registration (separated with commas)', 'salesmanago'),
                ),
                'newsletter' => array(
                    'label'       => __('Newsletter tags', 'salesmanago'),
                    'description' => __('Tags assigned after subscription to the newsletter (separated with commas)', 'salesmanago'),
                ),
                'login' => array(
                    'label'       => __('Login tags', 'salesmanago'),
                    'description' => __('Tags assigned after login (separated with commas)', 'salesmanago'),
                ),
                'purchase' => array(
                    'label'       => __('Purchase tags', 'salesmanago'),
                    'description' => __('Tags assigned after purchase (separated with commas)', 'salesmanago'),
                ),
                'guestPurchase' => array(
                    'label'       => __('Guest Purchase tags', 'salesmanago'),
                    'description' => __('Tags assigned after guest purchase (separated with commas)', 'salesmanago'),
                ),
            );
        foreach ($tagsInputs as $key => $value): ?>
            <tr valign="top">
                <th scope="row">
                    <label for="salesmanago-tags-<?=$key?>"><?=$value['label']?></label>
                </th>
                <td>
                    <input
                        type="text"
                        name="tags[<?=$key?>]"
                        value="<?=$this->AdminModel->getPlatformSettings()->getPluginWc()->getTagsByType($key)?>"
                        id="salesmanago-tags-<?=$key?>"
                        class="regular-text"
                    >
                    <p class="description">
                        <?=$value['description']?>
                    </p>
                </td>
            </tr>
        <?php endforeach; ?>
        </table>
            <?php
                include(__DIR__ . '/../partials/double_opt_in.php');
                include(__DIR__ . '/../partials/opt_in.php');
            ?>
            <h3><?php _e('Events', 'salesmanago') ?></h3>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="salesmanago-purchase-hook"><?php _e('Hook activating purchase event', 'salesmanago') ?></label>
                    </th>
                    <td>
                        <select name="purchase-hook" id="salesmanago-purchase-hook" class="regular-text">
                            <?php
                            $hookOptions = array(
                                'woocommerce_order_status_changed',
                                'woocommerce_checkout_order_processed',
                                'woocommerce_checkout_update_order_meta',
                                'woocommerce_pre_payment_complete',
                                'woocommerce_payment_complete'
                            );

                            foreach ($hookOptions as $hookName) {
                                echo('<option value="' . $hookName . '" ' . $this->selected($hookName, 'purchase-hook', $context) . '>
                            ' . $hookName . '
                            </option>');
                            }
                            ?>
                        </select>
                        <p class="description">
                            <?php _e('If you use a custom payment method, the default hook might not work. You can try using a different one', 'salesmanago'); ?>
                        </p>
                    </td>
                </tr>
                <?php include(__DIR__ . '/../partials/identifier.php') ?>
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="salesmanago-prevent-event-duplication"><?php _e('Prevent event duplication', 'salesmanago') ?></label>
                    </th>
                    <td>
                        <input type="checkbox" name="prevent-event-duplication" <?php $this->selected('true', 'prevent-event-duplication', $context) ?> value="1" id="salesmanago-prevent-event-duplication">
                        <label for="salesmanago-prevent-event-duplication">
                            <span><?php _e('Prevent duplication of PURCHASE events', 'salesmanago') ?></span>
                            <p class="description">
                                <?php _e('Enable this option to omit consecutive PURCHASE events (within 30 second)', 'salesmanago') ?>                            </p>
                        </label>
                    </td>
                </tr>
                <th scope="row">
                    <label for="salesmanago-event-cookie-ttl"><?php _e('Cookie lifetime', 'salesmanago') ?></label>
                </th>
                <td>
                    <select name="event-cookie-ttl" id="salesmanago-event-cookie-ttl">
                    <?php
                    $eventCookieTtlOptions = array(
                        __('Cart updates will always create new ext. events', 'salesmanago') => 0,
                        __('15 min', 'salesmanago')                                          => 900,
                        __('1 h', 'salesmanago')                                             => 3600,
                        __('4 h', 'salesmanago')                                             => 14400,
                        __('12 h', 'salesmanago')                                            => 43200,
                        __('7 days', 'salesmanago')                                          => 604800,
                        __('Cart update will always update ext. events', 'salesmanago')      => -1,
                    );

                    foreach ($eventCookieTtlOptions as $label => $value) {
                        echo('<option value="' . $value . '" ' . $this->selected($value, 'event-cookie-ttl') . '>
                            ' . $label . '
                        </option>');
                        }
                    ?>
                    </select>
                    <p class="description">
                        <?php _e('After this time, cart updates will be treated as new external events', 'salesmanago') ?>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label><?php _e('Test cart recovery', 'salesmanago') ?></label>
                </th>
                <td>
                    <?php
                    $url = home_url().'?rest_route=/salesmanago/v1/cart';
                    try {
                        global $woocommerce;
                        $url = add_query_arg($woocommerce->query_string, '', home_url($woocommerce->request)) . "?rest_route=/salesmanago/v1/cart";
                    } catch (\Exception $e) {
                        MessageEntity::getInstance()->addException(new Exception($e->getMessage(), 655));
                    }
                    ?>
                    <button class="button button-secondary" onclick="salesmanagoTestCartRecovery('<?=$url?>'); return false;"><?php _e('Test REST routes and functions', 'salesmanago') ?></button>
                    <label for="salesmanago-prevent-event-duplication">
                        <p class="description">
                            <?php _e('Test REST API to debug cart recovery links.', 'salesmanago') ?>
                        </p>
                    </label>
                </td>
            </tr>
            <tr valign="top" id="salesmanago-cart-recovery-test" style="display: none">
                <th scope="row" class="titledesc">
                    <label><?php _e('Results', 'salesmanago') ?></label>
                </th>
                <td id="salesmanago-cart-recovery-test-content">
                    <?php /* Cart recovery test results will be shown here */ ?>
                </td>
            </tr>
        </table>
        <?php
            include(__DIR__ . '/../partials/save.php');
        ?>
    </form>
</div>
<script>
    window.salesmanago.translations = {
        urlNotFound:   ' <?php _e('returned status:', 'salesmanago')?>',
        problem:       ' <?php _e('Problem', 'salesmanago')?>',

    };
</script>
