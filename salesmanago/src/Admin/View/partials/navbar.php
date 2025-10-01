<?php echo( '<h2 id="sm-notifcation-anchor"></h2>');?><!-- Do not remove, WP notifications are displayed after the first h1 or h2 tag-->
<nav class="nav-tab-wrapper">
	<?php
		$tabsList = array(
			'integrations settings' => array(
				'name'      => 'salesmanago-integration-settings',
				'available' => true,
				'active'    => $this->active( 'salesmanago-integration-settings' ),
				'label'     => __( 'Integration settings', 'salesmanago' ),
			),
			'monitoring code'       => array(
				'name'      => 'salesmanago-monit-code',
				'available' => true,
				'active'    => $this->active( 'salesmanago-monit-code' ),
				'label'     => __( 'Monitoring code', 'salesmanago' ),
			),
			'export'                => array(
				'name'      => 'salesmanago-export',
				'available' => true,
				'active'    => $this->active( 'salesmanago-export' ),
				'label'     => __( 'Export', 'salesmanago' ),
			),
            'product-catalog'       => array(
                'name'      => 'salesmanago-product-catalog',
                'available' => true,
                'active'    => $this->active( 'salesmanago-product-catalog' ),
                'label'     => __( 'Product catalog', 'salesmanago' ),
            ),
			'plugins'               => array(
				'name'      => 'salesmanago-plugins',
				'available' => true,
				'active'    => $this->active( 'salesmanago-plugins' ),
				'label'     => __( 'Plugins', 'salesmanago' ),
			),
			'wordpress'             => array(
				'name'      => 'salesmanago-plugin-wp',
				'available' => $this->available( 'salesmanago-plugin-wp' ),
				'active'    => $this->active( 'salesmanago-plugin-wp' ),
				'label'     => 'WordPress',
			),
			'woocommerce'           => array(
				'name'      => 'salesmanago-plugin-wc',
				'available' => $this->available( 'salesmanago-plugin-wc' ),
				'active'    => $this->active( 'salesmanago-plugin-wc' ),
				'label'     => 'WooCommerce',
			),
			'contact form 7'        => array(
				'name'      => 'salesmanago-plugin-cf7',
				'available' => $this->available( 'salesmanago-plugin-cf7' ),
				'active'    => $this->active( 'salesmanago-plugin-cf7' ),
				'label'     => 'Contact Form 7',
			),
			'gravity forms'         => array(
				'name'      => 'salesmanago-plugin-gf',
				'available' => $this->available( 'salesmanago-plugin-gf' ),
				'active'    => $this->active( 'salesmanago-plugin-gf' ),
				'label'     => 'Gravity Forms',
			),
			'fluent forms'          => array(
				'name'      => 'salesmanago-plugin-ff',
				'available' => $this->available( 'salesmanago-plugin-ff' ),
				'active'    => $this->active( 'salesmanago-plugin-ff' ),
				'label'     => 'Fluent Forms',
			),
            'about'                 => array(
                'name'      => 'salesmanago-about',
                'available' => true,
                'active'    => $this->active( 'salesmanago-about' ),
                'label'     => __('About', 'salesmanago'),
            ),
            'leadoo'                 => array(
                'name'      => 'salesmanago-discover-leadoo',
                'available' => true,
                'active'    => $this->active( 'salesmanago-discover-leadoo' ),
                'label'     => __('Discover Leadoo', 'salesmanago'),
            )
		);

		foreach ( $tabsList as $key => $value ) {
			if ( $value['available'] ) {
				echo(
					'<a href="admin.php?page=' . $value['name'] . '" class="nav-tab ' . $value['active'] . '">'
					. $value['label'] . '</a>'
				);
			}
		}

		echo '<form action="admin.php" method="POST">' . wp_nonce_field( 'logout', 'sm_nonce' )
			. '<input type="hidden" name="action" value="logout" />'
			. '<input type="hidden" name="page" value="salesmanago" />'
			. '<input type="submit" name="submit" id="submit" class="button button-primary button-logout" value="' . __( 'Log out', 'salesmanago' ) . '"></form>';
		?>
</nav>
