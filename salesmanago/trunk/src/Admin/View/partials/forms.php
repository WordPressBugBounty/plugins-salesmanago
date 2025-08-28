<?php
/**
 * @var string $context from SettingsRenderer
 */
?>
<?php
if ( empty( $context ) || ! isset( $availableFormsList ) ) {
	return;
}
$formsWithSetup = $this->AdminModel->getPlatformSettings()->getPluginByName( $context )->getForms();
?>
<h3><?php _e( 'Add forms to synchronize', 'salesmanago' ); ?></h3>
		<table class="form-table" id="salesmanago-forms-list">
			<tr valign="top">
				<th scope="row">
					<label for="salesmanago-available-forms-list"><?php _e( 'Forms to synchronize', 'salesmanago' ); ?></label>
				</th>
				<td>
					<?php

					if ( isset( $availableFormsList ) && count( $availableFormsList ) > 0 ) {
						echo( '<select name="salesmanago-available-forms-list" id="salesmanago-available-forms-list" class="regular-text">' );
						$allFormsConfigured = false;
						foreach ( $availableFormsList as $key => $form ) {
							if ( ! empty( $formsWithSetup[ $key ] ) ) {
								continue;
							}
							$allFormsConfigured = true;
							echo( '<option value="' . $key . '">' . $form . '</option>' );
						}
						if ( ! $allFormsConfigured ) {
							echo( '<option disabled selected value="">' . __( 'All forms have been configured', 'salesmanago' ) . '</option>' );
							echo( '</select><button id="salesmanago-add-form-button" disabled class="button button-primary button-inline" onclick="salesmanagoAddForm()">' . __( 'Add', 'salesmanago' ) . '</button>' );
						} else {
							echo( '</select><button id="salesmanago-add-form-button" class="button button-primary button-inline" onclick="salesmanagoAddForm()">' . __( 'Add', 'salesmanago' ) . '</button>' );
						}
					} else {
						echo ( $this->getNoFormsMessageByPluginName( $context ) );
					}
					?>
				</td>
			</tr>
		</table>
		<hr id="salesmanago-forms-to-configure-spacer">
<?php
if ( isset( $formsWithSetup ) ) :
	foreach ( $formsWithSetup as $key => $form ) :
		if ( ! isset( $availableFormsList[ $key ] ) ) {
			echo( '<div class="salesmanago-notice notice notice-warning inline">' . __( 'Form with ID', 'salesmanago' ) . ' ' . $key . ' ' . __( 'has been removed', 'salesmanago' ) . '.</div><hr>' );
			continue;
		}
		?>
<div class="salesmanago-form-configuration" id="salesmanago-form-<?php echo( $key ); ?>">
	<div class="form-title-header">
		<h3 class="h3-inline"><?php echo( __( 'Configuration for:', 'salesmanago' ) . ' ' . $availableFormsList[ $key ] ); ?></h3>
		<button class="button button-inline" onclick="salesmanagoRemoveForm(<?php echo( $key ); ?>); return false;"><?php _e( 'Delete', 'salesmanago' ); ?></button>
	</div>

	<table class="form-table" >
		<tr valign="top">
			<th>
				<label for="salesmanago-forms-<?php echo( $key ); ?>-owner"><?php _e( 'SALESmanago owner', 'salesmanago' ); ?></label>
			</th>
			<td>
				<select required id="salesmanago-forms-<?php echo( $key ); ?>-owner" name="salesmanago-forms[<?php echo( $key ); ?>][owner]" class="regular-text">
					<?php
					$owners = $this->AdminModel->getConfiguration()->getOwnersList();
					foreach ( $owners as $owner ) {
						echo( '<option value="' . $owner . '"' );
						echo( $owner === $form['owner'] ) ? 'selected>' : '>';
						echo( $owner );
						echo( '</option>' );
					}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="salesmanago-forms-<?php echo $key; ?>-tags-to-add"><?php _e( 'Tags to add', 'salesmanago' ); ?></label></th>
			<td><input
						type="text"
						id="salesmanago-forms-<?php echo $key; ?>-tags-to-add"
						name="salesmanago-forms[<?php echo $key; ?>][tags-to-add]"
						value="<?php echo $form['tags']; ?>"
						class="regular-text"/>
			</td>
		</tr>
		<tr>
			<th><label for="salesmanago-forms-<?php echo $key; ?>-tags-to-remove"><?php _e( 'Tags to remove', 'salesmanago' ); ?></label></th>
			<td><input
						type="text"
						id="salesmanago-forms-<?php echo $key; ?>-tags-to-remove"
						name="salesmanago-forms[<?php echo $key; ?>][tags-to-remove]"
						value="<?php echo( $form['tagsToRemove'] ); ?>"
						class="regular-text"/>
			</td>
		</tr>
	</table>
	<hr>
</div>
    <?php
    endforeach;
    endif;
    ?>
<script>
    function salesmanagoAddForm()
    {
        let select = document.getElementById('salesmanago-available-forms-list');
        let key = select.value;
        if(!key) {
            return;
        }
        let title = select.options[select.selectedIndex].text;
        let node = document.createElement('div');
        node.classList.add("salesmanago-form-configuration");
        node.id = "salesmanago-form-" + key;
        let nodeInnerHTML = '<div class="form-title-header">\n' +
            '        <h3 class="h3-inline"><?php _e('Configuration for:', 'salesmanago') ?> ' + title +'</h3>\n'+
            '        <button class="button button-inline" onclick="salesmanagoRemoveForm(' + key + '); return false;"><?php _e('Delete' , 'salesmanago') ?></button>\n'+
            '    </div>\n' +
            '    <table class="form-table">' +
            '          <tr valign="top">\n' +
            '            <th>\n' +
            '                <label for="salesmanago-forms-' + key + '-owner"><?php _e('SALESmanago owner', 'salesmanago') ?></label>\n'+
            '            </th>\n'+
            '            <td>\n'+
            '                <select required id="salesmanago-forms-' + key + '-owner" name="salesmanago-forms[' + key + '][owner]" class="regular-text">\n'+
            '<?php
                $owners = $this->AdminModel->getConfiguration()->getOwnersList();
                foreach ($owners as $owner) {
                    echo('<option value="' . $owner. '">');
                    echo($owner);
                    echo('</option>');
                }
                ?>\n'+
            '                </select>\n'+
            '            </td>\n'+
            '        </tr>\n'+
            '        <tr>\n'+
            '            <th><label for="salesmanago-forms-' + key + '-tags-to-add"><?php _e('Tags to add', 'salesmanago') ?></label></th>\n'+
            '            <td><input\n'+
            '                        type="text"\n'+
            '                        id="salesmanago-forms-' + key + '-tags-to-add"\n'+
            '                        name="salesmanago-forms[' + key + '][tags-to-add]"\n'+
            '                        class="regular-text"/>\n' +
            '           </td>\n'+
            '        </tr>\n'+
            '        <tr>\n'+
            '            <th><label for="salesmanago-forms-' + key + '-tags-to-remove"><?php _e('Tags to remove', 'salesmanago') ?></label></th>\n'+
            '            <td><input\n'+
            '                        type="text"\n'+
            '                        id="salesmanago-forms-' + key + '-tags-to-remove"\n'+
            '                        name="salesmanago-forms[' + key + '][tags-to-remove]"\n'+
            '                        class="regular-text"/>\n' +
            '           </td>\n'+
            '        </tr>\n' +
            '</table><hr>';
        node.innerHTML = nodeInnerHTML;
        let formsToConfigureSpacer = document.getElementById('salesmanago-forms-to-configure-spacer')
        insertAfter(node, formsToConfigureSpacer);
        select.remove(select.selectedIndex);
        if(!select.value) {
            let buttonToDisable = document.getElementById('salesmanago-add-form-button')
            if(buttonToDisable) {
                buttonToDisable.disabled = true;
                select.innerHTML = '<option disabled selected value=""> <?php _e('All forms have been configured', 'salesmanago') ?></option>';
            }
        }
    }
</script>
