    <form  method="post">
        <?php wp_nonce_field( 'salesmanago-nonce', 'salesmanago-nonce-name' ); ?>
        <input type="hidden" name="action" value="login" />
        <table class="form-table" role="presentation">
        <tbody><tr>
            <th><label for="salesmanago-username"><?php _e('Email') ?></label></th>
            <td> <input name="username" id="salesmanago-username" type="text" value="" class="input"></td>
        </tr>
        <tr valign="top">
            <th><label for="salesmanago-password"><?php _e('Password') ?></label></th>
            <td> <input name="password" id="salesmanago-password" type="password" value="" class="input password-input"></td>
        </tr>
        <tr>
        <th scope="row">
            <label for="endpoint-active"><?php _e('Use custom endpoint', 'salesmanago') ?></label>
        </th>
        <td>
            <input type="checkbox" onclick="salesmanagoToggleEndpoint()" name="endpoint-active" id="salesmanago-endpoint-active" value="1" class="regular-text">
            <label for="salesmanago-endpoint-active">
                <?php _e('Select this option to set a custom endpoint', 'salesmanago') ?>
            </label>
        </td>
        </tr>
        <tr valign="top" class="hidden" id="endpoint">
            <th scope="row">
                <label for="salesmanago-endpoint"><?php _e('Endpoint', 'salesmanago') ?></label>
            </th>
            <td>
                <input type="text" name="salesmanago-endpoint" value="app2.salesmanago.pl" class="regular-text" id="salesmanago-endpoint">
                <p class="description">
                    <?php _e('Set custom endpoint only if you were instructed to do so by your Project Manager.', 'salesmanago') ?>
                </p>
            </td>
        </tr>
      </tbody>
    </table>
    <p class="submit">
        <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Log in') ?>">
    </p>
</form>
