<div id="salesmanago-content">
<?php
$translations = function(){
    return "<script>
                window.salesmanago = {};
                window.salesmanago.translations = {
                    preparing:   '" . __( 'Preparing', 'salesmanago' ) . "',
                    in_progress: '" . __( 'In progress', 'salesmanago' ) . "',
                    done:        '" . __( 'Done', 'salesmanago' ) . "',
                    failed:      '" . __( 'Failed. Check console for details.', 'salesmanago' ) . "',
                    unknown:     '" . __( 'Unknown', 'salesmanago' ) . "',
                    starting:    '" . __( 'Starting', 'salesmanago' ) . "',
                    no_data:     '" . __( 'No products to export', 'salesmanago' ) . "',
                    packages_exported: '" . __( 'Exported packages: ', 'salesmanago' ) . "',
                    products_exported: '" . __( 'Number of products: ', 'salesmanago' ) . "',
                    expired_api_key: '" . __( 'Expired API Key. Refresh the page and add a new API key ', 'salesmanago' ) . "',                    
                };
                </script>";
};
echo $translations();

if ( $this->AdminModel->getInstalledPluginByName( 'wc' ) ):?>
       <?php
            $api_v3_key = $this->AdminModel->getConfiguration()->getApiV3Key();
            if ( empty( $api_v3_key ) ) :
                require_once 'add_api_key.php';
            ?>
            <?php else:
            $product_catalogs = json_decode( $this->AdminModel->getConfiguration()->getCatalogs() );
            $active_catalog = $this->AdminModel->getConfiguration()->getActiveCatalog();
            if ( empty ( $product_catalogs ) && ( ! empty ( $_REQUEST['subpage'] ) && $_REQUEST['subpage'] !== 'go-back' ) ):
                require_once 'create_catalog.php';
                ?>
            <?php else: ?>
	            <?php
	            if ( ! empty( $_REQUEST['catalog-created'] ) ):?>
                    <div class="salesmanago-notice notice notice-success inline"">
                        <?php _e( 'New Product Catalog has been created. Please refresh Catalog list to use it.', 'salesmanago' );?>
                    </div>
	            <?php endif ?>
                <div class="sm-product-catalog-synchro-container">
                    <div class="sm-product-catalog-headline-and-btn-wrapper">
                        <h1><?php _e( 'Real-time product synchronization', 'salesmanago' );?></h1>
                        <a href="?page=salesmanago-product-catalog&subpage=create-catalog">
                            <div class="sm-btn-link">
                                <span class="dashicons dashicons-insert"></span>
                                <p>
                                    <?php _e( 'New Product Catalog', 'salesmanago' ) ?>
                                </p>
                            </div>
                        </a>
                    </div>
                    <h3><?php _e( 'Product Catalog setup', 'salesmanago' );?></h3>
                    <form action="" method="post">
                        <div class="sm-product-catalog-select-wrapper">
                            <div>
                                <select name="sm-product-catalog-select"
                                        id="sm-product-catalog-select"
                                        class="regular-text"
                                        onchange="salesmanagoShowModal()"
                                >
                                    <option
                                        id="select-option-none"
                                        value=""
                                        <?php if ( empty( $active_catalog ) ) echo 'selected';?>>
                                        <?php _e( 'None (do not synchronize products in real-time)', 'salesmanago' ); ?>
                                    </option>
                                    <?php
                                        foreach ( $product_catalogs as $catalog ) {
                                            $optionTag = '<option value="' . $catalog->catalogId . '" ';
                                            if ( $catalog->catalogId === $active_catalog )
                                            {
                                                $optionTag .= 'selected';
                                            }
                                            $optionTag .= '>';
                                            echo $optionTag . $catalog->name . '</option>';
                                        }
                                    ?>
                                </select>
                                <div class="sm-product-catalog-label-container">
                                    <label for="sm-product-catalog-select">
                                        <?php _e( 'Select Product Catalog for real-time synchronization', 'salesmanago' );?>
                                    </label>
                                </div>
                            </div>
                            <div id="sm-product-catalog-btn-and-refresh-wrapper">
                                <input type="submit"
                                       class="button button-primary"
                                       id="sm-btn-set-active-catalog"
                                       onclick="salesmanagoClearInterruptedExportData()"
                                       value="<?php _e( 'Save', 'salesmanago' ) ?>">
                                <div
                                   class="sm-refresh-catalogs"
                                   id="sm-refresh-catalogs"
                                   onclick="salesmanagoRefreshCatalogList()"
                                >
                                    <span id="sm-refresh-icon" class="dashicons dashicons-update sm-tooltip">
                                        <span class="sm-tooltip-text description">
                                            <?php _e( 'Refresh Catalog list', 'salesmanago' ); ?>
                                        </span>
                                    </span>
                                </div>
                                <div>
                                    <div id="sm-refresh-catalog-success" class="sm-refresh-catalog-success hidden">
                                        <span class="dashicons dashicons-yes sm-tooltip">
                                            <span class="sm-tooltip-text description">
                                            <?php _e( 'Successfully refreshed the list', 'salesmanago' ); ?>
                                        </span>
                                        </span>
                                    </div>
                                    <div id="sm-refresh-catalog-fail" class="sm-refresh-catalog-fail hidden">
                                        <span class="dashicons dashicons-no sm-tooltip">
                                            <span class="sm-tooltip-text description">
                                            <?php _e( 'Failed to refresh the list. Try reloading the page.', 'salesmanago' ); ?>
                                        </span>
                                        </span>
                                    </div>
                                </div>
                            <input type="hidden" name="name" value="SALESmanago">
                            <input type="hidden" name="action" value="setActiveCatalog">
                            </div>
                            <?php add_thickbox(); ?>
                            <a href="#TB_inline?&width=350&height=220&inlineId=sm-modal-warning-disconnect-catalog" class="thickbox" id="sm-anchor-open-warning-modal"></a>
                            <div class="sm-modal" id="sm-modal-warning-disconnect-catalog">
                                <div class="sm-center-content">
                                    <h2><?php _e( 'Important', 'salesmanago' )?></h2>
                                </div>
                                <hr />
                                <p>
                                    <?php _e('You are about to turn off the real-time product synchronization. As a result, your emails, Web Push notifications, and Recommendation Frames might not display accurate product data.', 'salesmanago')?>
                                </p>
                                <div class="sm-center-content">
                                    <button
                                            id="sm-btn-turn-off-catalog"
                                            class="button button-primary"
                                            onclick="salesmanagoTurnOffCatalogSynchro()"
                                    >
                                        <?php _e( 'Turn off', 'salesmanago' ) ?><span class="dashicons dashicons-dismiss"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <script>
                        window.onload = () => {
                            salesmanagoCheckForInterruptedProductExport();
                        }
                    </script>
                    <div id="sm-product-export-container" class="sm-product-export-container">
                        <div class="sm-create-catalog-tooltip-container">
                            <h3><?php _e( 'Export all products', 'salesmanago' );?></h3>
                            <span class="dashicons dashicons-editor-help sm-tooltip">
                            <span class="sm-tooltip-text description">
		                        <?php _e( 'The export is divided into packages, each consisting of 100 products. This improves the export speed, without impacting the performance of your website.', 'salesmanago' ); ?>
                            </span>
                        </span>
                        </div>
                        <hr />
                        <h3 class="sm-product-synchro-heading"><?php _e( 'Synchronize all products from WordPress now', 'salesmanago' );?></h3>
                        <div id="sm-notice-product-exp-interrupted-wrapper">
                        <div
                            id="sm-notice-product-exp-interrupted"
                            class="salesmanago-notice notice notice-warning inline"
                        >
                            <h4>
	                            <?php _e('The previous export has not been completed.', 'salesmanago'); ?>
                            </h4>
                            <div id="sm-product-exp-interrupted-summary">
                                <div class="sm-int-exp-item">
                                    <div id="sm-int-exp-sum-started"><?php _e( 'Started on:', 'salesmanago' ); ?></div>
                                    <div></div>
                                </div>
                                <div class="sm-int-exp-item">
                                    <div id="sm-int-exp-sum-interrupted"><?php _e( 'Interrupted on:', 'salesmanago' ); ?></div>
                                    <div></div>
                                </div>
                                <div class="sm-int-exp-item">
                                    <div id="sm-int-exp-sum-last-package"><?php _e( 'Last exported package:', 'salesmanago' ); ?></div>
                                    <div></div>
                                </div>
                                <div class="sm-int-exp-item">
                                    <div id="sm-int-exp-sum-completion"><?php _e( 'Completion:', 'salesmanago' ); ?></div>
                                    <div></div>
                                </div>
                            </div>
                            <form action="" method="post" enctype="application/x-www-form-urlencoded" id="salesmanago-export-continue">
                                <input type="submit" onclick="salesmanagoContinueProductExport( event )" class="button button-primary" value="<?php _e( 'Continue this export', 'salesmanago' ); ?>">
                                <input type="submit" onclick="salesmanagoAbortProductExport( event )" class="button button-secondary salesmanago-export-modal-button" value="<?php _e( 'Abort this export', 'salesmanago' ); ?>">
                            </form>
                        </div>
                        </div>
                        <form onsubmit="return salesmanagoLaunchProductExport( event )" method="post" id="salesmanago-export-products">
                            <input
                                type="submit"
                                class="button button-primary sm-btn-top-margin"
                                id="sm-btn-product-export"
                                <?php if ( empty( $active_catalog ) ) echo "disabled" ?>
                                value="<?php _e( 'Start export', 'salesmanago' );?>"
                            >
                        </form>
                    </div>
                    <div id="sm-product-export-notice">
                        <div class="salesmanago-notice notice notice-info inline" id="sm-product-export-notice-type">
			                <?php _e( 'Export status', 'salesmanago' ); ?>: <span id="sm-product-export-status"><?php _e( 'Starting', 'salesmanago' ); ?></span><br>
                            <progress id="sm-product-export-progress" value="0" max="100"> 0% </progress>
                            <div id="sm-product-export-summary" hidden>
                            </div> <!-- Content filled by salesmanagoShowProductExportSummary() function -->
                        </div>
                        <div id="sm-continue-product-exp-btn-wrapper">
                            <button
                                id="sm-btn-product-export-try-again"
                                class="button button-primary"
                                onclick="salesmanagoContinueProductExport( event )"
                            >
                                <?php _e('Try again', 'salesmanago');?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif;?>
        <?php endif;?>
<?php else: ?>
    <div class="salesmanago-notice notice notice-info inline">
        <?php _e( 'In order to use Product Catalog, please install the WooCommerce plugin.', 'salesmanago' )?>
    </div>
<?php endif;?>
</div>
