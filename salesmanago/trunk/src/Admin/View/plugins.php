<div id="salesmanago-content">
    <form action="" method="post" enctype="application/x-www-form-urlencoded" id="salesmanago-conf">
        <h2>
            <?php _e('Manage integrations with plugins', 'salesmanago') ?>
        </h2>

        <table class="form-table">
            <tbody>
        <?php
            $plugins = array(
                'wp'  => array(
                    'label'       => 'WordPress',
                    'description' => __('Capture contacts created as WordPress users', 'salesmanago'),
                    'onclick'     => 'excludeWc()'
                ),
                'wc'  => array(
                    'label'       => 'WooCommerce',
                    'description' => __('Capture contacts created as WooCommerce customers', 'salesmanago'),
                    'onclick'     => 'excludeWp()'
                ),
                'cf7' => array(
                    'label'       => 'Contact Form 7',
                    'description' => __('Capture Contact Form 7 submissions', 'salesmanago')
                ),
                'gf'  => array(
                    'label'       => 'Gravity Forms',
                    'description' => __('Capture Gravity Forms submissions', 'salesmanago')
                ),
                'ff'  => array(
                    'label'       => 'Fluent Forms',
                    'description' => __('Capture Fluent Forms submissions', 'salesmanago')
                )
            );

            foreach ($plugins as $key => $value): ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="salesmanago-plugin-<?=$key?>">
                        <?=$value['label']?>
                    </label>
                    <?php
                    echo $this->AdminModel->getInstalledPluginByName(SUPPORTED_PLUGINS[$value['label']]) ? '' : '<p class="plugin-not-active">'.__('Plugin not detected', 'salesmanago').'</p>'
                    ?>
                </th>
                <td>
                    <input
                        type="checkbox"
                        name="salesmanago-plugin-<?=$key?>" <?php $this->selected(SUPPORTED_PLUGINS[$value['label']], 'plugins') ?>
                        value="1"
                        <?=isset($value['onclick']) ? 'onclick="' . $value['onclick'] . '"' : '' ?>
                        id="salesmanago-plugin-<?=$key?>"
                    >
                    <label for="salesmanago-plugin-<?=$key?>">
                        <?=$value['description']?>
                    </label>
                </td>
            </tr>
            <?php endforeach; ?>

            </tbody>
        </table>

        <?php
            include('partials/save.php');
        ?>
        <script>
            function excludeWc()
            {
                let wc = document.getElementById('salesmanago-plugin-wc');
                if(wc.checked) {
                    wc.checked = false;
                }
            }
            function excludeWp()
            {
                let wp = document.getElementById('salesmanago-plugin-wp');
                if(wp.checked) {
                    wp.checked = false;
                }
            }
        </script>
    </form>
</div>
