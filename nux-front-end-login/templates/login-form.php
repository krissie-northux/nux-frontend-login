<?php
/**
 * Login form
 */

if ( ! isset( $_GET['forgot'] ) && ! isset( $_GET['hash'] ) ) :
?>
<style>
    #custom-login-fp-form .custom-login-fp__messages p.error {
        border: 1px solid #c00;
        margin: 0 0 10px;
        padding: 4px;
        background: #ffebe8;
        color: #333;
    }
</style>
<div class="card card--center card--login">
    <div class="form__heading">
        <header><h1 class="form__title"><?php echo get_the_title(); ?></h1></header>
    </div>
    <div class="form__wrapper">
        <div class="container--login">
            <form method="post" action="<?php echo get_permalink(); ?>" id="custom-login-fp-form">
                <div class="custom-login-fp__messages">
                    <p></p>
                </div>
                <?php NorthUX\FrontendLogin\Custom_Login::print_notifications(); ?>

                <input type="hidden" name="action" value="<?php echo NorthUX\FrontendLogin\Custom_Login::$login_action; ?>">
                <?php wp_nonce_field( NorthUX\FrontendLogin\Custom_Login::$login_action ); ?>

                <fieldset>
                    <div class="form__field">
                        <label for="custom_login_email"><?php _e( 'Login Email', 'nux' ); ?></label>
                        <input type="text" name="email" id="custom_login_email" class="form__input" placeholder="<?php _e( 'Login Email', 'nux' ); ?>" required>
                    </div>
                    <div class="form__field">
                        <label for="custom_login_password"><?php _e( 'Password', 'nux' ); ?></label>
                        <input type="password" name="password" id="custom_login_password" class="form__input" minlength="3" placeholder="Password" required>
                    </div>
                    <div class="form__checkbox">
                        <input type="checkbox" id="remember_me" name="remember_me" value="1">
                        <label for="remember_me"><?php _e( 'Remember me', 'nux' ); ?></label>
                    </div>
                </fieldset>
                <div class="form__field form__forgot mt-10 mb-20">
                    <a href="<?php echo add_query_arg( 'forgot', '', site_url('/' . NorthUX\FrontendLogin\Custom_Login::$login_page_slug) ); ?>"><?php _e( 'Forgot password?', 'nux' ); ?></a>
                </div>
                <?php
                // check if we are on the login page and if so use referrer as redirect, else use permalink
                global $post;
                $post_slug = $post->post_name;
                if ( NorthUX\FrontendLogin\Custom_Login::$login_page_slug === $post_slug && wp_get_referer() ) {
                    $redirect = wp_get_referer();
                } else {
                    $redirect = get_permalink();
                }
                ?>
                <div>
                    <input type="submit" value="<?php _e( 'Log In', 'nux' ); ?>" class="button button-primary form__button">
                    <input type="hidden" name="redirect_to" value="<?php echo $redirect; ?>">
                </div>
            </form>

            
        </div>
    </div>
</div>

<?php endif; ?>

<?php
/**
 * Forgot password form.
 */
?>

<?php if ( isset( $_GET['forgot'] ) && ! isset( $_GET['hash'] ) ) : ?>
<div class="card card--center card--login">
    <div class="form__heading">
    <header><h1 class="form__title"><?php _e( 'Reset your password', 'nux' ); ?></h1></header>
    </div>
    <div class="form__wrapper">
        <div class="container container--login container--spacing container--narrow container--rp">

            <form method="post" action="<?php echo get_permalink(); ?>" id="custom-login-fp-form">
                <div class="custom-login-fp__messages">
                    <p></p>
                </div>
                <?php NorthUX\FrontendLogin\Custom_Login::print_notifications(); ?>

                <input type="hidden" name="action" value="<?php echo NorthUX\FrontendLogin\Custom_Login::$forgot_password_action; ?>">
                <?php wp_nonce_field( NorthUX\FrontendLogin\Custom_Login::$forgot_password_action ); ?>

                <fieldset>
                    <div class="form__field">
                        <label for="custom_login_fp_email"><?php _e( 'Email Address', 'nux' ); ?></label>
                        <input type="email" name="email" id="custom_login_fp_email" class="form__input" placeholder="<?php _e( 'Email Address', 'nux' ); ?>" required>
                    </div>
                </fieldset>

                <div>
                    <input type="submit" value="<?php _e( 'Submit', 'nux' ); ?>" class="button button-primary form__button">
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
/**
 * Password reset form.
 */
?>

<?php if ( isset( $_GET['hash'] ) && ! isset( $_GET['forgot'] ) ) : ?>
<div class="card card--center card--login">
    <div class="form__heading">
    <header><h1 class="form__title"><?php _e( 'Reset your password', 'nux' ); ?></h1></header>
    </div>
    <div class="form__wrapper">
        <div class="container container--login container--spacing container--narrow container--rp">
            <?php 
            if ( NorthUX\FrontendLogin\Custom_Login::error_check() ) {
                NorthUX\FrontendLogin\Custom_Login::print_notifications();
                ?>
                <div class="form__field form__forgot">
                    <a href="<?php echo add_query_arg( 'forgot', '', site_url('/' . NorthUX\FrontendLogin\Custom_Login::$login_page_slug) ); ?>"><?php _e( 'Please request another reset link.', 'nux' ); ?></a>
                </div>
                <?php 
            } else { ?>

            <form method="post" action="<?php echo add_query_arg( array('hash' => esc_attr( $_GET['hash']), 'login' => esc_attr( $_GET['login']) ), get_permalink() ); ?>" id="custom-login-rp-form">
                <div class="custom-login-fp__messages">
                    <p></p>
                </div>
                <?php NorthUX\FrontendLogin\Custom_Login::print_notifications(); ?>

                <input type="hidden" name="action" value="<?php echo NorthUX\FrontendLogin\Custom_Login::$reset_password_action; ?>">
                <input type="hidden" name="hash" value="<?php echo esc_attr( $_GET['hash'] ); ?>">
                <input type="hidden" name="login" value="<?php echo esc_attr( $_GET['login'] ); ?>">
                <?php wp_nonce_field( NorthUX\FrontendLogin\Custom_Login::$reset_password_action ); ?>

                <fieldset>
                    <div class="form__field">
                        <label for="custom_login_fp_new_password"><?php _e( 'New password', 'nux' ); ?></label>
                        <input type="password" name="password" id="custom_login_fp_new_password" placeholder="<?php _e( 'New password', 'nux' ); ?>" minlength="6" class="form__input" required>
                    </div>

                    <div class="form__field">
                        <label for="custom_login_fp_confirm_password"><?php _e( 'Confirm password', 'nux' ); ?></label>
                        <input type="password" name="confirm_password" id="custom_login_fp_confirm_password" placeholder="<?php _e( 'Confirm password', 'nux' ); ?>" minlength="6" class="form__input" required>
                    </div>
                </fieldset>

                <div>
                    <input type="submit" value="<?php _e( 'Submit', 'nux' ); ?>" class="button button-primary form__button">
                </div>
            </form>
            <?php } ?>
        </div>
    </div>
</div>
<?php endif; ?>
