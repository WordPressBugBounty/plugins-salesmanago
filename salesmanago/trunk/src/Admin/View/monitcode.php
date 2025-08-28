<?php
/**
 * @var string $context from SettingsRenderer
 */
?>
<?php
use bhr\Frontend\Model\MonitCodeModel;

if (empty($context)) {
    return;
}
$active = $this->selected(true, 'salesmanago-monitcode-disable-monitoring-code', $context);

?>
<div id="salesmanago-content">
    <form action="" method="post" enctype="application/x-www-form-urlencoded" id="salesmanago-conf">
        <h2>
            <?php _e('Manage your monitoring code features', 'salesmanago') ?>
        </h2>

        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="salesmanago-monitcode-disable-monitoring-code"><?php _e('Disable monitoring code', 'salesmanago') ?></label>
                </th>
                <td>
                    <input
                        type="checkbox"
                        onclick="salesmanagoToggleDisableMonitCode()"
                        name="salesmanago-monitcode-disable-monitoring-code"
                        <?=$active?>
                        value="1"
                        id="salesmanago-monitcode-disable-monitoring-code">
                    <label for="salesmanago-monitcode-disable-monitoring-code">
                        <span><?php _e('Enable this option if you want to add monitoring code on your own', 'salesmanago') ?></span>
                    </label>
                </td>
            </tr>
            <tr valign="top" class="monitcode-wrapper <?=$active ? 'hidden': ''; ?>">
                <th scope="row" class="titledesc">
                    <label for="salesmanago-monitcode-smcustom"><?php _e('smcustom', 'salesmanago') ?></label>
                </th>
                <td>
                    <input
                            type="checkbox"
                            name="salesmanago-monitcode-smcustom"
                            <?php $this->selected(true, 'salesmanago-monitcode-smcustom') ?>
                            value="1"
                            id="salesmanago-monitcode-smcustom">
                    <label for="salesmanago-monitcode-smcustom">
                        <span><?php _e('Add \'smcustom\' flag to monitoring code', 'salesmanago') ?></span>
                    </label>
                </td>
            </tr>
            <tr valign="top" class="monitcode-wrapper <?=$active ? 'hidden': ''; ?>">
                <th scope="row" class="titledesc">
                    <label for="salesmanago-monitcode-smbanners"><?php _e('smbanners', 'salesmanago') ?></label>
                </th>
                <td>
                    <input
                            type="checkbox"
                            name="salesmanago-monitcode-smbanners"
                            <?php $this->selected(true, 'salesmanago-monitcode-smbanners') ?>
                            value="1"
                            id="salesmanago-monitcode-smbanners">
                    <label for="salesmanago-monitcode-smbanners">
                        <span><?php _e('Add \'smbanners\' flag to monitoring code', 'salesmanago') ?></span>
                    </label>
                </td>
            </tr>
            <tr valign="top" class="monitcode-wrapper <?=$active ? 'hidden': ''; ?>">
                <th scope="row" class="titledesc">
                    <label for="salesmanago-monitcode-popup-js"><?php _e('popup.js', 'salesmanago') ?></label>
                </th>
                <td>
                    <input
                            type="checkbox"
                            name="salesmanago-monitcode-popup-js"
                            <?=$this->selected(true, 'salesmanago-monitcode-popup-js')?>
                            value="1"
                            id="salesmanago-monitcode-popup-js">
                    <label for="salesmanago-monitcode-popup-js">
                        <span><?php _e('Add popup.js (necessary for HTML and Advanced Popups)', 'salesmanago') ?></span>
                    </label>
                </td>
            </tr>
            <tr valign="top" class="monitcode-wrapper <?=$active ? 'hidden': ''; ?>">
                <th scope="row" class="titledesc">
                    <label for="salesmanago-monitcode-monitcode"><?php _e('Currently used JavaScript', 'salesmanago')?></label>
                </th>
                <td>
                    <textarea id="monitcode-area" cols="60" rows="15" disabled><?=$this->showMonitCode(); ?></textarea>
                </td>
            </tr>
            </tbody>
        </table>
        <?php
        include('partials/save.php');
        ?>
    </form>
</div>
