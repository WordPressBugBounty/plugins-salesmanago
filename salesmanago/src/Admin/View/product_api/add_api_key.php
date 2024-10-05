<?php
/**
 * @var $api_v3_key = $this->AdminModel->getConfiguration()->getApiV3Key();
 */
use bhr\Frontend\Model\Helper;
?>
<div class="sm-product-catalog-container">
	<div class="sm-product-catalog-text-container">
		<h2>
			<?php _e( 'Real-time product synchronization', 'salesmanago' ); ?>
		</h2>
		<h2>
			<?php _e( 'Get all the benefits of real-time Product Catalog synchronization with Product API', 'salesmanago' ); ?>
		</h2>
		<ol class="sm-product-catalog-info-list">
			<li>
				<?php _e( 'Easily set up product synchronization to instantly reflect all changes from WordPress in Recommendation Frames, Personal Shopping Inbox, and other modules', 'salesmanago' ); ?>
			</li>
			<li>
				<?php _e( 'Log in to SALESmanago and go to Integration Center ➔ API ➔ API v3 tab', 'salesmanago' ); ?>
			</li>
			<li>
				<?php _e( 'Create a new API key. Enter your name (e.g. WordPress), Webhook URL, and expiry time. You can copy the webhook URL from the Webhook URL field below.', 'salesmanago' ); ?>
			</li>
			<li>
				<?php _e( 'Display the API key using an eye icon ', 'salesmanago' ); ?>
				<svg role="img"
					 width="10px"
					 aria-hidden="true"
					 focusable="false"
					 data-prefix="fas"
					 data-icon="eye"
					 class="svg-inline--fa fa-eye fa-w-18"
					 xmlns="http://www.w3.org/2000/svg"
					 viewBox="0 0 576 512">
					<path fill="#676a6c" d="M572.52 241.4C518.29 135.59 410.93 64 288 64S57.68 135.64 3.48 241.41a32.35 32.35 0 0 0 0 29.19C57.71 376.41 165.07 448 288 448s230.32-71.64 284.52-177.41a32.35 32.35 0 0 0 0-29.19zM288 400a144 144 0 1 1 144-144 143.93 143.93 0 0 1-144 144zm0-240a95.31 95.31 0 0 0-25.31 3.79 47.85 47.85 0 0 1-66.9 66.9A95.78 95.78 0 1 0 288 160z"></path>
				</svg>
				<?php _e( ' and paste it below in the API v3 key field', 'salesmanago' ); ?>
			</li>
		</ol>
		<div>
			<label for="api-v3-webhook-url-input" class="sm-product-api-label">
				<?php _e( 'Webhook URL', 'salesmanago' ); ?>
			</label>
			<span class="dashicons dashicons-editor-help sm-tooltip">
					<span class="sm-tooltip-text description">
						<?php _e( 'Webhook is a modern way to report any potential problems with data transfer back to WordPress. Paste this URL when creating a new API v3 key.', 'salesmanago' ); ?>
					</span>
			</span>
		</div>
		<div>
			<div class="sm-product-catalog-input-container">
				<input id="api-v3-webhook-url-input"
					   class="regular-text sm-product-catalog-input"
					   value="<?php $api_v3_webhook_url = Helper::generate_api_v3_webhook_url(); echo $api_v3_webhook_url;?>"
					   readonly
				/>
				<button class="sm-product-catalog-button" onclick=copyApiV3EndpointToClipBoard()
				>
					<?php _e( 'COPY', 'salesmanago' ); ?>
				</button>
			</div>
			<?php
			if ( ! Helper::checkEndpointForHTTPS( $api_v3_webhook_url ) ) :
				?>
				<span class="span-error">
					<?php _e( 'Important: Your server must have SSL enabled to receive webhooks with error notices from SALESmanago', 'salesmanago' ); ?>
				</span>
			<?php endif; ?>
		</div>
		<br>
		<div>
			<label for="api-v3-key-input" class="sm-product-api-label">
				<?php _e( 'API v3 key', 'salesmanago' ); ?>
			</label>
		</div>
		<form action="" method="post" enctype="application/x-www-form-urlencoded">
			<div class="sm-product-catalog-input-container">
				<input id="api-v3-key-input"
					   name="api-v3-key"
					   class="regular-text sm-product-catalog-input"
					   value="<?php echo $api_v3_key; ?>"
					   placeholder="<?php _e( 'Paste your API v3 key here', 'salesmanago' ); ?>"
					   required
					   onchange="salesmanagoValidateApiKey()"
				/>
				<input id="sm-btn-submit-api-key" type="submit" class="sm-product-catalog-button" value="➔" required/>
				<input type="hidden" name="name" value="SALESmanago">
				<input type="hidden" name="action" value="addApiV3Key">
			</div>
			<p class="description sm-api-key-error-wrapper">
				<span id="sm-api-key-error" class="span-error hidden"><?php _e( 'Invalid API Key. Make sure the key exists in the SALESmanago app', 'salesmanago' ); ?></span>
			</p>
		</form>
	</div>
	<div class="sm-product-catalog-image-container">
		<img height="300px" id="sm-product-catalog-flow" src="<?php echo( $this->AdminModel->getPluginUrl() . 'src/Admin/View/img/product_api.png' ); ?>" alt="Product catalog flow"/>
	</div>
