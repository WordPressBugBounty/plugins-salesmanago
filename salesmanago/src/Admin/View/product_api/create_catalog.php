<div>
    <div class="sm-product-catalog-text-container">
        <div class="sm-product-catalog-headline-and-btn-wrapper">
            <h2>
                <?php
				use bhr\Admin\Entity\Configuration;

				$api_v3_key = $this->AdminModel->getConfiguration()->getApiV3Key();
				if ( empty( $api_v3_key ) ) :
                    wp_redirect('admin.php?page=salesmanago-product-catalog');
                endif;

                _e( 'Start real-time product synchronization by creating a new Product Catalog', 'salesmanago' ); ?>
            </h2>
            <a href="?page=salesmanago-product-catalog&subpage=go-back">
                <div class="sm-btn-link">
                    <span class="dashicons dashicons-arrow-left-alt"></span>
                    <p>
                        <?php _e( 'Go back', 'salesmanago' ); ?>
                    </p>
                </div>
            </a>
        </div>
        <h3>
	        <?php _e( 'Product Catalog setup', 'salesmanago' ); ?>
        </h3>
        <hr/>
		<?php
		if ( $this->catalogsLimitReached ):?>
            <div class="salesmanago-notice notice inline"">
                <?php _e( 'You have reached the limit of catalogs. Contact the Customer Success department if you would like to create more.', 'salesmanago' );?>
            </div>
        <?php endif ?>
        <form action="" method="post" enctype="application/x-www-form-urlencoded">
            <div class="sm-create-catalog-form-container">
                <div class="sm-create-catalog-input-container">
                    <label for="sm-catalog-name" class="sm-product-api-label">
                        <?php _e( 'Catalog name', 'salesmanago' ) ?>
                    </label>
                    <input
                            id="sm-catalog-name"
                            type="text"
                            name="sm-catalog-name"
                            value=""
                            required
                            minlength="5"
                            maxlength="64"
                            placeholder="<?= function_exists('get_bloginfo') ? str_replace( ' ', '_', get_bloginfo( 'name' ) ) : 'Your catalog name' ?>"
                            class="regular-text"
                    >
                </div>
                <div class="sm-create-catalog-input-container">
                    <label for="sm-catalog-currency" class="sm-product-api-label">
                        <?php _e( 'Currency', 'salesmanago' ) ?>
                    </label>
                    <div class="sm-create-catalog-tooltip-container">
                        <input
                                id="sm-catalog-currency"
                                type="text"
                                name="sm-catalog-currency"
                                value="<?= get_woocommerce_currency() ?? '' ?>"
                                required
                                maxlength="3"
                                class="regular-text"
                                onchange="salesmanagoValidateCurrencyCode()"
                        >
                        <span class="dashicons dashicons-editor-help sm-tooltip">
                            <span class="sm-tooltip-text sm-tooltip-text-responsive description">
                                <?php _e( 'ISO currency code of your store (e.g. EUR, USD)', 'salesmanago' ); ?>
                            </span>
                        </span>
                    </div>
                    <p class="description">
                        <span id="sm-catalog-currency-error" class="span-error hidden">
                            <?php _e( 'Please enter the currency as ISO code', 'salesmanago' ) ?>
                        </span>
                    </p>
                </div>
                <div class="sm-create-catalog-input-container">
                    <label for="sm-catalog-location" class="sm-product-api-label">
                        <?php _e( 'Location', 'salesmanago' ) ?>
                    </label>
                    <div class="sm-create-catalog-tooltip-container">
                        <input
                                id="sm-catalog-location"
                                type="text"
                                name="sm-catalog-location"
                                value="<?= Configuration::getInstance()->getLocation() ?>"
                                readonly
                                class="regular-text"
                        >
                        <span class="dashicons dashicons-editor-help sm-tooltip">
                            <span class="sm-tooltip-text sm-tooltip-text-responsive description">
                                <?php _e( 'This field is used to assign products to External Events. The value is assigned automatically.', 'salesmanago' ); ?>
                            </span>
                        </span>
                    </div>
                </div>
                <div class="sm-create-catalog-input-container">
                    <label for="sm-catalog-allow-in-recommendation-frames" class="sm-product-api-label">
                        <?php _e( 'Available in Recommendation Frames', 'salesmanago' ) ?>
                    </label>
                    <div class="sm-create-catalog-tooltip-container">
                        <select
                                id="sm-catalog-allow-in-recommendation-frames"
                                name="sm-catalog-allow-in-recommendation-frames"
                                class="regular-text"
                        >
                            <option selected value="1">
                                <?php _e( 'Yes', 'salesmanago' ) ?>
                            </option>
                            <option value="0">
                                <?php _e( 'No', 'salesmanago' ) ?>
                            </option>
                        </select>
                        <span class="dashicons dashicons-editor-help sm-tooltip">
                            <span class="sm-tooltip-text sm-tooltip-text-responsive description">
                            <?php _e( 'In SALESmanago, you can limit the access to the Product Catalog to analytics only. To make the Catalog available in Recommendation Frames, Emails, PSI, and other features, select "true".', 'salesmanago' ); ?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>
            <a class="button-secondary sm-btn-top-margin" href="?page=salesmanago-product-catalog&subpage=go-back">
                <span class="dashicons dashicons-arrow-left-alt salesmanago-arrow-icon"></span>
                <?php _e( 'Back', 'salesmanago' ) ?>
            </a>
            <button type="submit"
                    class="button-primary sm-btn-top-margin"
                    id="sm-btn-create-catalog"
				    <?php if ( $this->catalogsLimitReached ): ?> disabled <?php endif ?>
            >
	            <?php _e( 'CREATE', 'salesmanago' ) ?>
                <span class="dashicons dashicons-arrow-right-alt salesmanago-arrow-icon"></span>
            </button>
            <input type="hidden" name="name" value="SALESmanago">
            <input type="hidden" name="action" value="addProductCatalog">
        </form>
    </div>
</div>
