<?php
/**
 * @var string $context from SettingsRenderer
 */
?>
<?php
if(empty($context)) {
    return;
}
$mode = $this->selected('', 'opt-in-input-mode', $context);
$modeMobile = $this->selected('', 'opt-in-mobile-input-mode', $context);
?>
<h3><?php _e('Newsletter and Mobile Marketing consents', 'salesmanago') ?></h3>
<table class="form-table">
    <tr valign="top">
        <th scope="row">
            <label for="salesmanago-opt-in-input-mode"><?php _e('Opt-in input', 'salesmanago') ?></label>
        </th>
        <td>
            <select onchange="salesmanagoChangeOptInInput()" name="opt-in-input[mode]" id="salesmanago-opt-in-input-mode" class="regular-text">
                <option value="none" <?php $this->selected('none', 'opt-in-input-mode', $context);?>>
                    <?php _e('Do not use opt-in field', 'salesmanago') ?>
                </option>
                <option value="map" <?php $this->selected('map', 'opt-in-input-mode', $context); ?>>
                    <?php _e('Map an existing field', 'salesmanago') ?>
                </option>
                <option value="append"<?php $this->selected('append', 'opt-in-input-mode', $context);?>>
                    <?php _e('Append field to registration form', 'salesmanago') ?>
                </option>
                <?php if($context === SUPPORTED_PLUGINS['WooCommerce']): ?>
                    <option value="appendEverywhere" <?php $this->selected('appendEverywhere', 'opt-in-input-mode', $context);?>>
                        <?php _e('Append field to registration and checkout forms', 'salesmanago') ?>
                    </option>
                <?php endif ?>
            </select>
            <p class="description">
            </p>
        </td>
    </tr>
    <?php $hidden = ($mode === 'map') ? '' : 'hidden'; ?>
    <tr valign="top" id="salesmanago-opt-in-input-map" class="<?=$hidden?>">
        <th scope="row">
            <label for="salesmanago-opt-in-input-name"><?php _e('Input name', 'salesmanago') ?></label>
        </th>
        <td>
            <input type="text" name="opt-in-input[name]" value="<?php
            echo $this->AdminModel->getPlatformSettings()->getPluginByName($context)->getOptInInput()->getMappedName();
            ?>" id="salesmanago-opt-in-input-name" class="regular-text">
            <p class="description">
                <?php _e('Input name', 'salesmanago') ?>
            </p>
        </td>
    </tr>
    <?php $hidden = ($mode === 'append' || $mode === 'appendEverywhere') ? '' : 'hidden'; ?>
    <tr valign="top" id="salesmanago-opt-in-input-append" class="<?=$hidden?>">
        <th scope="row">
            <label for="salesmanago-opt-in-input-label"><?php _e('Label', 'salesmanago') ?></label>
        </th>
        <td>
            <input type="text" name="opt-in-input[label]" value="<?php
            echo $this->AdminModel->getPlatformSettings()->getPluginByName($context)->getOptInInput()->getLabel();
            ?>" id="salesmanago-opt-in-input-label" class="regular-text">
            <p class="description">
                <?php _e('Text to be displayed next to checkbox', 'salesmanago') ?>
            </p>
        </td>
    </tr>
</table>
<?php $hidden = ($mode === 'append' || $mode === 'appendEverywhere') ? '' : 'hidden'; ?>
<div class="notice notice-info inline <?=$hidden?>" id="salesmanago-opt-in-input-append-info">
    <?php
    _e('This input label is available for translation under the name', 'salesmanago');
    echo(' <span style="font-family: monospace">!optInInputLabel</span> ');
    _e("('salesmanago' plugin text domain).", 'salesmanago');

    /* This is a declaration for translation plugins. Do not remove */
    __('!optInInputLabel', 'salesmanago');
    /* End do not remove */
    ?>
</div>
<table class="form-table">
    <tr valign="top">
        <th scope="row">
            <label for="salesmanago-opt-in-mobile-input-mode"><?php _e('Opt-in mobile input', 'salesmanago') ?></label>
        </th>
        <td>
            <select onchange="salesmanagoChangeOptInMobileInput()" name="opt-in-mobile-input[mode]" id="salesmanago-opt-in-mobile-input-mode" class="regular-text">
                <option value="none" <?php $this->selected('none', 'opt-in-mobile-input-mode', $context);?>>
					<?php _e('Do not use opt-in field', 'salesmanago') ?>
                </option>
                <option value="map" <?php $this->selected('map', 'opt-in-mobile-input-mode', $context); ?>>
					<?php _e('Map an existing field', 'salesmanago') ?>
                </option>
                <option value="append"<?php $this->selected('append', 'opt-in-mobile-input-mode', $context);?>>
					<?php _e('Append field to registration form', 'salesmanago') ?>
                </option>
				<?php if($context === SUPPORTED_PLUGINS['WooCommerce']): ?>
                    <option value="appendEverywhere" <?php $this->selected('appendEverywhere', 'opt-in-mobile-input-mode', $context);?>>
						<?php _e('Append field to registration and checkout forms', 'salesmanago') ?>
                    </option>
				<?php endif ?>
            </select>
            <p class="description">
            </p>
        </td>
    </tr>
	<?php $hidden = ($modeMobile === 'map') ? '' : 'hidden'; ?>
    <tr valign="top" id="salesmanago-opt-in-mobile-input-map" class="<?=$hidden?>">
        <th scope="row">
            <label for="salesmanago-opt-in-mobile-input-name"><?php _e('Input name', 'salesmanago') ?></label>
        </th>
        <td>
            <input type="text" name="opt-in-mobile-input[name]" value="<?php
			echo $this->AdminModel->getPlatformSettings()->getPluginByName($context)->getOptInMobileInput()->getMappedName();
			?>" id="salesmanago-opt-in-input-mobile-name" class="regular-text">
            <p class="description">
				<?php _e('Input name', 'salesmanago') ?>
            </p>
        </td>
    </tr>
	<?php $hidden = ($modeMobile === 'append' || $modeMobile === 'appendEverywhere') ? '' : 'hidden'; ?>
    <tr valign="top" id="salesmanago-opt-in-mobile-input-append" class="<?=$hidden?>">
        <th scope="row">
            <label for="salesmanago-opt-in-mobile-input-label"><?php _e('Label', 'salesmanago') ?></label>
        </th>
        <td>
            <input type="text" name="opt-in-mobile-input[label]" value="<?php
			echo $this->AdminModel->getPlatformSettings()->getPluginByName($context)->getOptInMobileInput()->getLabel();
			?>" id="salesmanago-opt-in-mobile-input-label" class="regular-text">
            <p class="description">
				<?php _e('Text to be displayed next to checkbox', 'salesmanago') ?>
            </p>
        </td>
    </tr>
</table>
<?php $hidden = ($modeMobile === 'append' || $modeMobile === 'appendEverywhere') ? '' : 'hidden'; ?>
<div class="notice notice-info inline <?=$hidden?>" id="salesmanago-opt-in-mobile-input-append-info">
	<?php
	_e('This input label is available for translation under the name', 'salesmanago');
	echo(' <span style="font-family: monospace">!optInMobileInputLabel</span> ');
	_e("('salesmanago' plugin text domain).", 'salesmanago');

	/* This is a declaration for translation plugins. Do not remove */
	__('!optInMobileInputLabel', 'salesmanago');
	/* End do not remove */
	?>
</div>
