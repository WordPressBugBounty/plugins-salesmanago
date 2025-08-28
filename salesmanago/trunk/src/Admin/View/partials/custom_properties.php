<?php
/**
 * @var string $context = SUPPORTED_PLUGINS['Contact Form 7'] | SUPPORTED_PLUGINS['Gravity Forms'] from SettingsRenderer
 */
?>
<h3><?php _e('Custom details', 'salesmanago') ?></h3>
<p class="description">
    <?php _e('Set input names that will be transferred as custom details.', 'salesmanago') ?>
</p>
<table class="form-table" id="salesmanago-custom-properties">
    <?php
        $properties = $this->AdminModel->getPlatformSettings()->getPluginByName($context)->getProperties();
        $key = -1;
        foreach ($properties as $key => $propertyField):
    ?>
    <tr valign="top">
        <th scope="row">
            <label for="salesmanago-custom-properties-<?php echo ($key+1) ?>"><?php echo(__('Custom detail', 'salesmanago')." ".($key+1)); ?></label>
        </th>
        <td>
            <input type="text" id="salesmanago-custom-properties-<?php echo ($key+1) ?>" name="custom-properties[]" value="<?php
            echo $propertyField;
            ?>" class="regular-text">
        </td>
    </tr>
    <?php
        endforeach;
    ?>
    </table>
    <button class="button" onclick="salesmanagoAppendCustomPropertiesFields(<?php echo $key+1 ?>); return false"><?php _e('Add', 'salesmanago') ?></button>

<h3><?php _e('Additional details type', 'salesmanago') ?></h3>
<p class="description">
	<?php _e('Choose how additional details are transferred to SALESmanago.', 'salesmanago') ?>
</p>
<table class="form-table" id="salesmanago-properties-type">
    <tr valign="top">
        <th scope="row">
            <label for="salesmanago-properties-type"><?=__('Transfer additional details as', 'salesmanago')?></label>
        </th>
        <td>
            <select id="salesmanago-properties-type" name="salesmanago-properties-type" class="regular-text">
                <option value="details" <?=$this->selected('details', 'properties-type', !empty($context) ? $context : null)?>>
                    <?=__('Properties', 'salesmanago')?>
                </option>
                <option value="tagValues"<?=$this->selected('tagValues', 'properties-type', !empty($context) ? $context : null)?>>
                    <?=__('Tags (value)', 'salesmanago')?>
                </option>
                <option value="tagNamesValues"<?=$this->selected('tagNamesValues', 'properties-type', !empty($context) ? $context : null)?>>
                    <?=__('Tags (key-value)', 'salesmanago')?>
                </option>
            </select>
        </td>
    </tr>
</table>
<script>
    let lastKey = 0;
    function salesmanagoAppendCustomPropertiesFields(_lastKey)
    {
        if(lastKey === 0) {
            lastKey = _lastKey;
        }
        let node = document.createElement('tr');
        lastKey++;
        node.innerHTML = '<th scope="row"><label for="salesmanago-custom-properties-' + lastKey + '"><?php _e('Custom detail', 'salesmanago'); ?> ' + lastKey +'</label> </th>'+
                         '<td><input type="text" name="custom-properties[]" value="" id="salesmanago-custom-properties-' + lastKey + '" class="regular-text"></td>';
        document.getElementById('salesmanago-custom-properties').appendChild(node);
    }
</script>
