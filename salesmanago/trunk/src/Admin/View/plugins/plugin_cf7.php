<?php
/**
 * @var string $context = SUPPORTED_PLUGINS['Contact Form 7'] from SettingsRenderer
 */
?>
<div id="salesmanago-content">
	<form action="" method="post" enctype="application/x-www-form-urlencoded" id="salesmanago-conf">
		<h2>
			<?php _e( 'Manage Contact Form 7 integration', 'salesmanago' ); ?>
		</h2>
		<div class="notice notice-info inline">
			<?php echo( __( 'Learn how to name input fields on', 'salesmanago' ) . ' <a target="_blank" href="' . __( 'https://support.salesmanago.com/how-do-i-integrate-salesmanago-with-contact-form-7-wordpress/?utm_source=integration&utm_medium=wordpress&utm_content=tooltip', 'salesmanago' ) . '">' . __( 'SALESmanago support page', 'salesmanago' ) . '</a>.' ); ?>
		</div>
		<?php
		require __DIR__ . '/../partials/forms.php';
		require __DIR__ . '/../partials/custom_properties.php';
		require __DIR__ . '/../partials/double_opt_in.php';
		require __DIR__ . '/../partials/save.php';
		?>
	</form>



</div>
