<div id="htsc-success-password-reset" class="widecolumn">
    <?php if ( $attributes['show_title'] ) : ?>
        <h3><?php _e( 'Password successfully changed', 'ht-forgot-your-password' ); ?></h3>
    <?php endif; ?>
  
        <?php if ( count( $attributes['errors'] ) > 0 ) : ?>
            <?php foreach ( $attributes['errors'] as $error ) : ?>
                <p>
                    <?php echo $error; ?>
                </p>
            <?php endforeach; ?>
        <?php endif; ?>

        <p>Your password has been changed successfully.</p>
  
</div>