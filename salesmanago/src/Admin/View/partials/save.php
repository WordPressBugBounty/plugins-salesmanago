<footer id="salesmanago-form-foot">
    <p class="submit">
        <input
            id="salesmanago-save-btn"
            type="submit"
            class="button button-primary"
            value="<?php _e('Save', 'salesmanago') ?>"
        >
    </p>
</footer>

<input type="hidden" name="name" value="SALESmanago">
<input type="hidden" name="action" value="save">
<?php
    if ( function_exists( 'wp_nonce_field' ) ) {
        wp_nonce_field( 'save', 'sm_nonce' );
    }
?>