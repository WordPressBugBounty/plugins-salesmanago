<tr valign="top">
    <th scope="row">
        <label for="salesmanago-product-identifier-type">
            <?php _e('Product identifier transferred in external events', 'salesmanago') ?>
        </label>
</th>
<td>
    <select name="product-identifier-type" id="salesmanago-product-identifier-type" class="regular-text">
        <?php
        $productIdentifiersOptions = array(
            __('Product ID', 'salesmanago') => 'id',
            __('SKU', 'salesmanago')        => 'sku',
            __('Variant ID', 'salesmanago') => 'variant Id'
        );

        foreach ($productIdentifiersOptions as $label => $value) {
            echo('<option value="' . $value . '" ' . $this->selected($value, 'product-identifier-type', !empty($context) ? $context : null) . '>
                            ' . $label . '
                            </option>');
        }
        ?>
    </select>
    <p class="description">
        <?php _e('Chosen identifier type must match ID in Product Feed uploaded to SALESmanago', 'salesmanago'); ?>
    </p>
</td>
</tr>
