<?php
/**
 * @var string $context from SettingsRenderer
 */
?>
<?php
if(empty($context)) {
    return;
}
$active = $this->selected('', 'double-opt-in-active', $context);
?>
<h3><?php _e('Double opt-in', 'salesmanago') ?></h3>
<table class="form-table">
    <tr valign="top">
        <th scope="row">
            <label for="double-opt-in-active">
                <?php _e('Use double opt-in', 'salesmanago') ?>
            </label>
        </th>
        <td>
            <input type="checkbox" onclick="salesmanagoToggleDoubleOptIn()" <?php echo($active) ?> name="double-opt-in[active]" id="double-opt-in-active" value="1">
            <label for="double-opt-in-active">
                <?php _e('Let contacts confirm newsletter signup with email confirmation', 'salesmanago');?>
            </label>
            <p class="description">
                <?php echo(__('Learn more on', 'salesmanago') . ' <a href="' . __('https://support.salesmanago.com/email-confirming-subscription/?utm_source=integration&utm_medium=wordpress&utm_content=tooltip', 'salesmanago') . '" target="_blank">' . __('SALESmanago support page.', 'salesmanago')) ?>
            </p>
        </td>
    </tr>
    <tr class="salesmanago-double-opt-in <?php echo ($active) ? '' : 'hidden' ?>">
        <th scope="row">
            <label for="double-opt-in-email-id"><?php _e('Email ID', 'salesmanago') ?></label>
        </th>
        <td>
            <input
                type="text"
                name="double-opt-in[email-id]"
                value="<?php echo $this->AdminModel->getPlatformSettings()->getPluginByName($context)->getDoubleOptIn()->getEmailId(); ?>"
                id="double-opt-in-email-id"
                class="regular-text"
                data-legacy-template-id="<?php echo $this->AdminModel->getPlatformSettings()->getPluginByName($context)->getDoubleOptIn()->getTemplateId(); ?>"
                onchange="salesmanagoCheckDoi()">
            <p id="salesmanago-doi-input-error" class="description hidden">
                <span class="span-error"><?php _e('This field has to contain 36 characters or be empty', 'salesmanago');?></span>
            </p>
            <p id="salesmanago-doi-input-warning" class="description hidden">
                <span class="span-error"><?php _e('This looks like template ID. You can find the email ID in Email list ➔ Subscription confirmation ➔ Preview', 'salesmanago');?></span>
            </p>
            <p class="description">
				<?php _e('Paste the ID of the subscription confirmation email. You can find it in Email list ➔ Subscription confirmation ➔ Preview', 'salesmanago') ?>
            </p>
        </td>
    </tr>
</table>
