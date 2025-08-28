<?php
/**
 * @var string $context = SUPPORTED_PLUGINS['WordPress'] from SettingsRenderer
 */
?>
<div id="salesmanago-content">
    <form action="" method="post" enctype="application/x-www-form-urlencoded" id="salesmanago-conf">
        <h2>
            <?php _e('Manage WordPress integration', 'salesmanago') ?>
        </h2>
        <div class="notice notice-info inline">
            <?php echo(__('New users must be able to register.','salesmanago') . ' <a href="options-general.php">' . __('Manage options in WordPress settings', 'salesmanago') . '</a>.') ?>
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
                )
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
                                value="<?=$this->AdminModel->getPlatformSettings()->getPluginWp()->getTagsByType($key)?>"
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
            include(__DIR__ . '/../partials/save.php');
        ?>

    </form>
</div>
