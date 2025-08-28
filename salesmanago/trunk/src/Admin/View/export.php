<?php
/**
 * @var string $installedDate from SettingsRenderer
 */
use bhr\Admin\Model\Helper;
?>
<?php
printWindowVariables();
?>
<div id="salesmanago-export-notice">
	<div class="salesmanago-notice notice notice-info inline" id="salesmanago-export-notice-type">
	<?php _e( 'Export status', 'salesmanago' ); ?>: <span id="salesmanago-export-status"><?php _e( 'Starting', 'salesmanago' ); ?></span><br>
	<progress id="salesmanago-export-progress" value="0" max="100"> 0% </progress>
	</div>
</div>
<div id="salesmanago-export-restore">
	<div class="salesmanago-notice notice notice-warning inline is-dismissible">
		<h2><?php _e( 'Previous export has not been completed!', 'salesmanago' ); ?></h2>
		<span><?php _e( 'Previous export details:', 'salesmanago' ); ?></span>
		<table id="salesmanago-export-details">
			<tr>
				<td><?php _e( 'Type:', 'salesmanago' ); ?> </td><td></td>
			</tr>
			<tr>
				<td><?php _e( 'Started on:', 'salesmanago' ); ?> </td><td></td>
			</tr>
			<tr>
				<td><?php _e( 'Interrupted on:', 'salesmanago' ); ?> </td><td></td>
			</tr>
			<tr>
				<td><?php _e( 'Completion:', 'salesmanago' ); ?> </td><td></td>
			</tr>
			<tr>
				<td><?php _e( 'Tags:', 'salesmanago' ); ?> </td><td></td>
			</tr>
		</table>
		<form action="" method="post" enctype="application/x-www-form-urlencoded" id="salesmanago-export-continue">
			<input type="submit" onclick="return salesmanagoContinueExport()" class="button button-primary" value="<?php _e( 'Continue this export', 'salesmanago' ); ?>">
			<input type="submit" onclick="return salesmanagoAbortExport()" class="button button-secondary salesmanago-export-modal-button" value="<?php _e( 'Abort this export', 'salesmanago' ); ?>">
		</form>
	</div>
</div>
<div id="salesmanago-export-forms">
	<h2><?php _e( 'Export WooCommerce cutomers to SALESmanago', 'salesmanago' ); ?></h2>
	<form onsubmit="return salesmanagoExportContacts()" action="" method="post" enctype="application/x-www-form-urlencoded" id="salesmanago-export-contacts">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="salesmanago-export-contacts-from"><?php _e( 'From date', 'salesmanago' ); ?></label>
				</th>
				<td>
					<input
							type="date"
							name="salesmanago-export-contacts-from"
							value=""
							id="salesmanago-export-contacts-from"
							class="regular-text"
							min="2000-01-01"
							max="<?php echo( date( 'Y-m-d', time() ) ); ?>"
					>
					<p class="description">
						<?php _e( 'Leave empty/default to export all', 'salesmanago' ); ?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="salesmanago-export-contacts-to"><?php _e( 'To date', 'salesmanago' ); ?></label>
				</th>
				<td>
					<input
							type="date"
							name="salesmanago-export-contacts-to"
							value="<?php echo( date( 'Y-m-d', time() ) ); ?>"
							id="salesmanago-export-contacts-to"
							class="regular-text"
							min="2000-01-01"
							max="<?php echo( date( 'Y-m-d', time() ) ); ?>"
					>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="salesmanago-export-tags"><?php _e( 'Add tags', 'salesmanago' ); ?></label>
				</th>
				<td>
					<input
                           type="text"
                           name="salesmanago-export-tags"
                           value="<?php echo ( $this->AdminModel->getDefaultExportTags() );?>"
                           id="salesmanago-export-tags"
                           class="regular-text"
                    >
					<p class="description">
						<?php _e( 'Tags assigned to all exported contacts (separated with commas)', 'salesmanago' ); ?>
					</p>
				</td>
			</tr>
            <tr valign="top">
                <th scope="row">
                    <input
                            type="button"
                            class="button button-secondary button-count"
                            value="<?php _e('Count', 'salesmanago');?>"
                            onclick="salesmanagoExportCount('contacts')"
                    >
                </th>
                <td>
                    <div class="salesmanago-count-result wp-ui-text-highlight">
                        <?php _e('To export:', 'salesmanago') ?>
                        <span id="salesmanago-count-contacts-result" name="salesmanago-count-contacts-result">
                        <!-- result of count contact will be show here -->
                    </span>
                    </div>
                </td>
            </tr>
		</table>

        <input type="submit" class="button button-primary" value="<?php _e( 'Export contacts', 'salesmanago' ); ?>">
    </form>
	<hr>
	<h2><?php _e( 'Export WooCommerce orders as external events to SALESmanago', 'salesmanago' ); ?></h2>
	<!--<p>--><?php // _e('', 'salesmanago') ?><!--</p>-->
	<div class="salesmanago-notice notice notice-warning inline">
		<?php _e( 'Remember to export contacts before exporting external events.', 'salesmanago' ); ?>
	</div>
	<br>
	<div class="salesmanago-notice notice notice-warning inline">
		<?php _e( 'Exporting external events for the second time will duplicate them in SALESmanago.', 'salesmanago' ); ?>
		<?php
		if ( ! empty( $installedDate ) && $installedDate < time() - 86400 ) {
			$installedDate = date( 'Y-m-d', $installedDate );
			echo( __( 'Events created after', 'salesmanago' ) . ' <b>' . $installedDate . '</b> ' . __( 'are most likely already in SALESmanago.', 'salesmanago' ) );
		}
		?>
	</div>
	<form onsubmit="return salesmanagoExportEvents()"  action="" method="post" enctype="application/x-www-form-urlencoded" id="salesmanago-export-events">
		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="salesmanago-export-events-from"><?php _e( 'From date', 'salesmanago' ); ?></label>
				</th>
				<td>
					<input
							type="date"
							name="salesmanago-export-events-from"
							value=""
							id="salesmanago-export-events-from"
							class="regular-text"
							min="2000-01-01"
							max="<?php echo( date( 'Y-m-d', time() ) ); ?>"
					>
					<p class="description">
						<?php _e( 'Leave empty/default to export all', 'salesmanago' ); ?>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="salesmanago-export-events-to"><?php _e( 'To date', 'salesmanago' ); ?></label>
				</th>
				<td>
					<input
							type="date"
							name="salesmanago-export-events-to"
							value="<?php echo( date( 'Y-m-d', time() ) ); ?>"
							id="salesmanago-export-events-to"
							class="regular-text"
							min="2000-01-01"
							max="<?php echo( date( 'Y-m-d', time() ) ); ?>"
					>
				</td>
			</tr>
            <tr valign="top">
                <th scope="row">
                    <label for="salesmanago-export-events-advanced-option-checkbox"> <?php _e('Advanced options', 'salesmanago');?></label>
                </th>
                <td>
                    <input
                            type="checkbox"
                            id="salesmanago-export-events-advanced-option-checkbox"
                            name="salesmanago-export-events-advanced-option-checkbox"
                            value="1"
                            onclick="salesmanagoExportAdvancedOptionToggle()"
                    >
                    <label for="salesmanago-export-events-advanced-option-checkbox">
                        <?php _e('Enable this option to choose order statuses and product identifier', 'salesmanago') ?>
                    </label>
                </td>
            </tr>
        </table>
        <table class="form-table hidden" id="salesmanago-export-events-advanced-option-wrapper">
            <?php require __DIR__ . '/partials/identifier.php'; ?>
            <tr valign="top">
                <th scope="row">
                    <label for="salesmanago-export-events-statuses[]">
                        <?php _e('Select order statuses to be exported', 'salesmanago');?>
                    </label>
                </th>
                <td>
                    <fieldset>
                        <?php foreach (Helper::wcGetOrderStatuses() as $key => $value): ?>
                            <input
                                    type="checkbox"
                                    name="salesmanago-export-events-statuses[]"
                                    value="<?=$key?>"
                                    id="salesmanago-export-events-statuses[<?=$key?>]"
                                <?php if ($key === 'wc-completed'):?>
                                    checked
                                <?php endif;?>
                            >
                            <label for="salesmanago-export-events-statuses[<?=$key?>]">
                                <?= $value ?>
                            </label>
                            <br/>
                        <?php endforeach;?>
                    </fieldset>
                </td>
            </tr>
            <tr id="salesmanago-export-events-advanced-option">
                <th scope="row">
                    <label for="salesmanago-export-events-advanced-option-export-as"><?php _e('Export as','salesmanago')?></label>
                </th>
                <td>
                    <select id="salesmanago-export-events-advanced-option-export-as">
                        <option id="salesmanago-export-events-advanced-option-export-as-purchase" value="PURCHASE">PURCHASE</option>
                        <option id="salesmanago-export-events-advanced-option-export-as-cancelled" value="CANCELLED">CANCELLED</option>
                        <option id="salesmanago-export-events-advanced-option-export-as-other" value="OTHER">OTHER</option>
                    </select>
                    <p class="description">
                        <?php _e('Choose external event type to be assigned to the exported orders', 'salesmanago') ?>
                    </p>
                </td>
            </tr>
        </table>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <input
                            type="button"
                            class="button button-secondary button-count"
                            value="<?php _e('Count', 'salesmanago');?>"
                            onclick="salesmanagoExportCount('events')"
                    >
                </th>
                <td>
                    <div class="salesmanago-count-result wp-ui-text-highlight">
                        <?php _e('To export:', 'salesmanago') ?>
                        <span id="salesmanago-count-events-result" name="salesmanago-count-events-result">
                        <!-- result of count contact will be show here -->
                    </span>
                    </div>
                </td>
            </tr>
		</table>
	<input type="submit" class="button button-primary" value="<?php _e( 'Export events', 'salesmanago' ); ?>">
	</form>
</div>
<script>
    function salesmanagoExportAdvancedOptionToggle() {
        if (document.querySelector('#salesmanago-export-events-advanced-option-checkbox:checked')) {
            document.querySelector('#salesmanago-export-events-advanced-option-wrapper').classList.remove('hidden')
        } else {
            document.querySelector('#salesmanago-export-events-advanced-option-wrapper').classList.add('hidden')
        }
    }
</script>
<?php
function printWindowVariables() {
	echo "
<script>
//declared in Salesmanago/Admin/View/Export.php
    window.salesmanago = {};
    window.salesmanago.ajaxDir = '" . admin_url( 'admin-ajax.php' ) . "';
    window.salesmanago.exportNonce = '" . wp_create_nonce( 'salesmanagoExport' ) . "';
    window.salesmanago.isExportPage = true;
    window.salesmanago.translations = {
        preparing:   '" . __( 'Preparing', 'salesmanago' ) . "',
        in_progress: '" . __( 'In progress', 'salesmanago' ) . "',
        done:        '" . __( 'Done', 'salesmanago' ) . "',
        failed:      '" . __( 'Failed. Check console for details.', 'salesmanago' ) . "',
        unknown:     '" . __( 'Unknown', 'salesmanago' ) . "',
        last_check:  '" . __( 'Making sure everything was exported', 'salesmanago' ) . "',
        starting:    '" . __( 'Starting', 'salesmanago' ) . "',
        contacts:    '" . __( 'WooCommerce customers', 'salesmanago' ) . "',
        events:      '" . __( 'WooCommerce orders', 'salesmanago' ) . "',
        no_data:     '" . __( 'No data to export in the selected time range', 'salesmanago' ) . "',

    };
</script>";
}
