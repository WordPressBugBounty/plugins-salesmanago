<?php
/**
 * @var string $context from SettingsRenderer
 */
?>
<?php
if(empty($context)) {
    return;
}
?>
<h3><?php _e('Contact owner', 'salesmanago') ?></h3>
<table class="form-table">
    <tr valign="top">
        <th scope="row">
            <label for="salesmanago-owner"><?php _e('Contact owner', 'salesmanago') ?></label>
        </th>
        <td>
            <select name="owner" id="salesmanago-owner" class="regular-text">
                <?php
                $owners = $this->AdminModel->getConfiguration()->getOwnersList();
                foreach ($owners as $key=>$owner) {
                    echo('<option value="' . $owner. '"' . $this->selected($owner, 'owner', $context) . '>');
                    echo($owner);
                    echo('</option>');
                }
                ?>
            </select>
            <?php $url = home_url().'?rest_route=/salesmanago/v1/refreshOwner'; ?>
            <span id="button-wrapper">
                <input type="button"
                       name="salesmanago-refresh-owner"
                       id="salesmanago-refresh-owner"
                       value="<?=__("Refresh list", "salesmanago") ?>"
                       class="button button-secondary salesmanago-button"
                       onclick="salesmanagoRefreshOwnerList()"
                />
                <div id="salesmanago-refresh-owner-success" class="refresh-owner-success hidden">
                        <div id="checkmark_stem"></div>
                        <div id="checkmark_kick"></div>
                </div>
            </span>
            <p class="description">
                <?php _e('Choose owner contacts will be assigned to', 'salesmanago'); //Garden path sentence ?>
            </p>
        </td>
    </tr>

</table>
