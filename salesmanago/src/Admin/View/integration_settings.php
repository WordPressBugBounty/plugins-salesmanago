<div id="salesmanago-content">
	<form action="" method="post" enctype="application/x-www-form-urlencoded" id="salesmanago-conf">
		<h2><?php

			use bhr\Frontend\Model\Helper;
			use bhr\Admin\Controller\CronController;

			_e( 'General settings', 'salesmanago' ); ?></h2>

		<table class="form-table">
			<tbody>
			<tr valign="top">
				<th scope="row">
					<label for="salesmanago-ignored-domains"><?php _e( 'Ignored domains', 'salesmanago' ); ?></label>
				</th>
				<td>
					<input
                            id="salesmanago-ignored-domains"
                            type="text"
                            name="salesmanago-ignored-domains"
                            value="<?php
							$ignoredDomains = $this->AdminModel->getConfiguration()->getIgnoredDomains();
									echo is_array( $ignoredDomains ) ? implode( ', ', $ignoredDomains ) : $ignoredDomains;
                            ?>"
							class="regular-text"
					>
					<p class="description">
						<?php _e( 'Separated with commas, for example: <b>10minutemail.net,temp-mail.com</b>', 'salesmanago' ); ?>
					</p>
				</td>
			</tr>

            <tr valign="top" class="monitcode-wrapper <?php echo false ? 'hidden' : ''; ?>">
                <th scope="row" class="titledesc">
                    <label for="salesmanago-cron"><?php _e( 'Batch product data transfer using CRON', 'salesmanago' ); ?></label>
                </th>
                <?php $cronEnabled = $this->AdminModel->getPlatformSettings()->getCronEnabled(); ?>
                <td>
                    <input
                            id="salesmanago-cron"
                            type="checkbox"
                            name="cron-active"
                        <?php echo $this->selected(true, 'cron') ? 'checked' : '';?>
                            value="1"
                            onchange="salesmanagoToggleCronFrequency()"
                    >
                    <label for="salesmanago-cron">
                        <span><?php _e( 'Implement the CRON mechanism and increase the performance of your e-store by transferring product data in larger batches as a background process', 'salesmanago' ); ?></span>
                    </label>
                    <div id="cron-select-wrapper" style="display: <?php echo $cronEnabled ? 'block' : 'none'; ?>; margin-top: 10px;">
                        <label for="cron-frequency">
                            <?php _e('Select CRON Frequency', 'salesmanago'); ?>
                        </label>
                        <?php $currentCronFrequency = $this->AdminModel->getPlatformSettings()->getCronValue(); ?>
                        <select id="cron-frequency" name="cron-frequency">
                            <option value="one-minute" <?php echo $currentCronFrequency === CronController::CRON_SCHEDULE_KEY_60 ? 'selected' : ''; ?>>
                                <?php _e('1 min', 'salesmanago'); ?></option>
                            <option value="three-minutes" <?php echo $currentCronFrequency === CronController::CRON_SCHEDULE_KEY_180 ? 'selected' : ''; ?>>
                                <?php _e('3 min', 'salesmanago'); ?></option>
                            <option value="five-minutes" <?php echo $currentCronFrequency === CronController::CRON_SCHEDULE_KEY_300 ? 'selected' : ''; ?>>
                                <?php _e('5 min', 'salesmanago'); ?></option>
                        </select>
                    </div>
                </td>
            </tr>

			<tr valign="top" class="monitcode-wrapper <?php echo false ? 'hidden' : ''; ?>">
				<th scope="row" class="titledesc">
					<label for="salesmanago-contact-cookie-ttl-active"><?php _e( 'Custom monitoring cookie lifetime', 'salesmanago' ); ?></label>
				</th>
				<td>
					<input
                            id="salesmanago-contact-cookie-ttl-active"
                            type="checkbox"
                            name="contact-cookie-ttl-active"
                            <?php echo $this->selected( true, 'contact-cookie-ttl-default' ) ? '' : 'checked'; ?>
                            value="1"
                            onchange="salesmanagoToggleContactCookieTtl()"
					>
					<label for="salesmanago-contact-cookie-ttl-active">
						<span><?php _e( 'Change default monitoring cookie lifetime', 'salesmanago' ); ?></span>
					</label>
				</td>
			</tr>
			<tr valign="top" class="monitcode-wrapper <?php echo $this->selected( true, 'contact-cookie-ttl-default', 'bool' ) ? 'hidden' : ''; ?>">
				<th scope="row" class="titledesc">
					<label for="salesmanago-contact-cookie-ttl"><?php _e( 'Monitoring cookie lifetime', 'salesmanago' ); ?></label>
				</th>
				<td>
					<input
                            id="salesmanago-contact-cookie-ttl"
                            type="text"
                            name="contact-cookie-ttl"
                            value="<?php echo (int) ( $this->AdminModel->getConfiguration()->getContactCookieTtl() / ( 24 * 60 * 60 ) ); ?>"
							class="regular-text"
                            onblur="salesmanagoCookieTtlValidation()"
					>
                    <p id="salesmanago-contact-cookie-ttl-error-message" class="description hidden">
                        <span class="span-error"><?php _e( 'Please enter a number between 0 and 3652', 'salesmanago' ); ?></span>
                    </p>
					<p class="description">
						<span><?php _e( 'Time in days. Default value is 10 years (3652 days)', 'salesmanago' ); ?></span>
					</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="salesmanago-language-detection"><?php _e( 'Language detection method', 'salesmanago' ); ?></label>
				</th>
				<td>
					<select id="salesmanago-language-detection" name="language-detection">
						<option value="platform" <?php $this->selected( 'platform', 'language-detection' ); ?>>
							<?php _e( 'Detected by WordPress', 'salesmanago' ); ?>
						</option>
						<option value="browser" <?php $this->selected( 'browser', 'language-detection' ); ?>>
							<?php _e( 'Browser language', 'salesmanago' ); ?>
						</option>
					</select>
					<p class="description">
						<?php _e( 'Language is used when sending double opt-in confirmation emails. You can use language detected by WordPress or user\'s browser language', 'salesmanago' ); ?>
					</p>
				</td>
			</tr>
			</tbody>
		</table>
		<h3><?php _e( 'Account settings', 'salesmanago' ); ?></h3>
		<table class="form-table">
			<?php
				$accountSettingsInputs = array(
					'location'  => array(
						'label'       => __( 'Location field for external events', 'salesmanago' ),
						'description' => __( 'If you want, you can change default \'location\' for external events', 'salesmanago' ),
						'value'       => $this->AdminModel->getConfiguration()->getLocation(),
						'readonly'    => '',
                        'validation'  => 'onblur="salesmanagoLocationValidation()"',
                        'error'       => __( 'Please use lowercase and uppercase letters, numbers, and underscore', 'salesmanago' ),
					),
					'client-id' => array(
						'label'       => __( 'Your client ID', 'salesmanago' ),
						'description' => __( 'Sometimes called \'short ID\'', 'salesmanago' ),
						'value'       => $this->AdminModel->getConfiguration()->getClientId(),
						'readonly'    => 'readonly',
                        'validation'  => '',
                        'error'       => '',
                    ),
					'endpoint'  => array(
						'label'       => __( 'Endpoint', 'salesmanago' ),
						'description' => __( 'Endpoint your account assigned to', 'salesmanago' ),
						'value'       => preg_replace( '^(https?)://^', '', $this->AdminModel->getConfiguration()->getEndpoint() ),
						'readonly'    => 'readonly',
                        'validation'  => '',
                        'error'       => '',
                    ),
					'apiV3CallbackUrl'  => array(
						'label'       => __( 'Product API Webhook URL', 'salesmanago' ),
						'description' => __( 'Webhook is a modern way to report any potential problems with data transfer back to Wordpress. Paste this URL when creating a new API v3 key.', 'salesmanago' ),
						'value'       => Helper::generate_api_v3_webhook_url(),
						'readonly'    => 'readonly',
						'validation'  => '',
						'error'       => __('Important: Your server must have SSL enabled to receive webhooks with error notices from SALESmanago'),
					),
				);
				foreach ( $accountSettingsInputs as $key => $value ) :
					?>
				<tr valign="top">
					<th scope="row">
						<label for="salesmanago-<?php echo $key; ?>"><?php echo $value['label']; ?></label>
					</th>
					<td>
						<input
                                id="salesmanago-<?php echo $key; ?>"
                                type="text"
                                name="salesmanago-<?php echo $key; ?>"
                                value="<?php echo $value['value']; ?>"
							    class="regular-text"
                                maxlength="36"
								<?php echo $value['readonly']; ?>
                                <?php echo $value['validation']; ?>
						>
                        <?php if ( ! empty($value['error'] ) ) : ?>
                        <p id="salesmanago-<?php echo $key?>-error" class="description <?php if ($key !== 'apiV3CallbackUrl' || Helper::checkEndpointForHTTPS( Helper::generate_api_v3_webhook_url() ))
                            {echo 'hidden';}?>">
                            <span class="span-error"><?php echo $value['error'];?></span>
                        </p>
                        <?php endif;?>
						<p class="description">
								<?php echo $value['description']; ?>
						</p>
					</td>
				</tr>
				<?php endforeach; ?>
		</table>
		<h3><?php _e( 'Web Push Notifications', 'salesmanago' ); ?></h3>
		<div class="salesmanago-notice notice notice-info inline">
			<?php _e( 'Learn more about Web Push consents on our', 'salesmanago' ); ?>
			<a target="_blank" href="<?php echo __( 'https://support.salesmanago.com/domain-configuration-for-web-push-notifications/?utm_source=integration&utm_medium=wordpress&utm_content=tooltip', 'salesmanago' ); ?>">
				<?php echo __( 'SALESmanago support page', 'salesmanago' ); ?>
			</a>
		</div>
		<table class="form-table">
			<tbody>
			<tr valign="top">
				<th scope="row">
					<label for="salesmanago-sw-js-button"> <?php _e( 'Web Push consent', 'salesmanago' ); ?></label>
				</th>
				<td>
					<input
                            id="salesmanago-sw-js-button"
                            type="button"
                            name="salesmanago-sw-js-button"
							class="button button-secondary"
							value="<?php echo __( 'Generate sw.js', 'salesmanago' ); ?>"
							onclick="salesmanagoTestFunctionToGenerateSwJs()"
					>
					<p class="description">
						<?php _e( 'Create sw.js file to enable Web Push consents from your domain', 'salesmanago' ); ?>
					</p>
				</td>
			</tr>
			<tr valign="top" id="salesmanago-generate-sw-js-test" style="display: none">
				<th scope="row" class="titledesc">
					<label><?php _e( 'Results', 'salesmanago' ); ?></label>
				</th>
				<td id="salesmanago-generate-sw-js-test-content">
					<?php /* Results will be shown here */ ?>
				</td>
			</tr>
			</tbody>
		</table>
		<?php
		require 'partials/save.php';
		?>
	</form>
</div>
