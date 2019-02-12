<?php
/**
 * Plugin Name:       Heytheme Forgot Your Password
 * Description:       A plugin that creates custom Forgot Password flow. 
 * Version:           0.0.1
 * Author:            Kish
 * License:           GPL-2.0+
 * Text Domain:       ht-forgot-your-password
 */
 
class Heytheme_forgot_your_password {
 
    /**
     * Initializes the plugin.
     *
     * To keep the initialization fast, only add filter and action
     * hooks in the constructor.
     */
    public function __construct() {

    	add_action( 'login_form_lostpassword', array( $this, 'redirect_to_heytheme_forgot_your_password' ) );

        add_shortcode( 'htsc-forgot-your-password', array( $this, 'render_heytheme_forgot_your_password_form' ) );

        add_action( 'login_form_lostpassword', array( $this, 'heytheme_send_password_reset_link_to_user' ) );

        add_action( 'login_form_rp', array( $this, 'redirect_to_heytheme_password_reset' ) );
        add_action( 'login_form_resetpass', array( $this, 'redirect_to_heytheme_password_reset' ) );

        add_shortcode( 'htsc-password-reset', array( $this, 'render_heytheme_password_reset_form' ) );

        add_action( 'login_form_rp', array( $this, 'do_password_reset' ) );
        add_action( 'login_form_resetpass', array( $this, 'heytheme_do_password_reset' ) );

        add_shortcode( 'htsc-success-password-reset', array( $this, 'render_heytheme_success_password_reset_form' ) );
     
    }

        /**
     * Plugin activation hook.
     *
     * Creates all WordPress pages needed by the plugin.
     */
    public static function plugin_activated() {
        // Information needed for creating the plugin's pages
        $page_definitions = array(
            'heytheme-forgot-your-password' => array(
                'title' => __( 'Forgot Your Password?', 'ht-forgot-your-password' ),
                'content' => '[htsc-forgot-your-password]'
            ),
            'heytheme-password-reset' => array(
                'title' => __( 'Password Reset', 'ht-forgot-your-password' ),
                'content' => '[htsc-password-reset]'
            ),
            'heytheme-success-password-reset' => array(
                'title' => __( 'Successfully Password Changed', 'ht-forgot-your-password' ),
                'content' => '[htsc-success-password-reset]'
            )
            
        );
        foreach ( $page_definitions as $slug => $page ) {
            // Check that the page doesn't exist already
            $query = new WP_Query( 'pagename=' . $slug );
            if ( ! $query->have_posts() ) {
                // Add the page using the data from the array above
                wp_insert_post(
                    array(
                        'post_content'   => $page['content'],
                        'post_name'      => $slug,
                        'post_title'     => $page['title'],
                        'post_status'    => 'publish',
                        'post_type'      => 'page',
                        'ping_status'    => 'closed',
                        'comment_status' => 'closed',
                    )
                );
            }
        }
    }

    /**
     * Redirects the user to the custom "Heytheme Forgot your password?" page instead of
     * wp-login.php?action=lostpassword.
     */
    public function redirect_to_heytheme_forgot_your_password() {
        if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
            if ( is_user_logged_in() ) {
                $this->redirect_logged_in_user();
                exit;
            }
            wp_redirect( home_url( 'heytheme-forgot-your-password' ) );
            
            exit;
        }
    }

    /**
     * Renders the contents of the given template to a string and returns it.
     *
     * @param string $template_name The name of the template to render (without .php)
     * @param array  $attributes    The PHP variables for the template
     *
     * @return string               The contents of the template.
     */
    private function get_template_html( $template_name, $attributes = null ) {
        if ( ! $attributes ) {
            $attributes = array();
        }
        ob_start();
        
        $located = self::find_n_locate_template( $template_name );
        if( ! empty($located) ) require( $located );
        
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    public static function find_n_locate_template($template, $template_path = '', $default_path = ''){
            
            if( ! $template_path ) $template_path = get_template_directory() . '/heytheme-forgot-password/';
            if( ! $default_path ) $default_path = plugin_dir_path( __FILE__ ). '/shortcodes/';

            $template_exist = $template_path . $template . '.php';

            if( file_exists($template_exist) ): $template = $template_path . $template . '.php'; 
            else: $template = $default_path . $template . '.php'; 
            endif;
            return $template;

    }



    /**
     * Finds and returns a matching error message for the given error code.
     *
     * @param string $error_code    The error code to look up.
     *
     * @return string               An error message.
     */
    private function get_error_message( $error_code ) {
        switch ( $error_code ) {
            // Login errors
            case 'empty_username':
                return __( 'You do have an email address, right?', 'ht-forgot-your-password' );
            case 'empty_password':
                return __( 'You need to enter a password to login.', 'ht-forgot-your-password' );
            case 'invalid_username':
                return __(
                    "We don't have any users with that email address. Maybe you used a different one when signing up?",
                    'ht-forgot-your-password'
                );
            case 'incorrect_password':
                $err = __(
                    "The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?",
                    'ht-forgot-your-password'
                );
                return sprintf( $err, wp_lostpassword_url() );
            // Registration errors
            case 'email':
                return __( 'The email address you entered is not valid.', 'ht-forgot-your-password' );
            case 'email_exists':
                return __( 'An account exists with this email address.', 'ht-forgot-your-password' );
            case 'closed':
                return __( 'Registering new users is currently not allowed.', 'ht-forgot-your-password' );
            case 'captcha':
                return __( 'The Google reCAPTCHA check failed. Are you a robot?', 'ht-forgot-your-password' );
            // Lost password
            case 'empty_username':
                return __( 'You need to enter your email address to continue.', 'ht-forgot-your-password' );
            case 'invalid_email':
            case 'invalidcombo':
                return __( 'There are no users registered with this email address.', 'ht-forgot-your-password' );
            // Reset password
            case 'expiredkey':
            case 'invalidkey':
                return __( 'The password reset link you used is not valid anymore.', 'ht-forgot-your-password' );
            case 'password_reset_mismatch':
                return __( "The two passwords you entered don't match.", 'ht-forgot-your-password' );
            case 'password_reset_empty':
                return __( "Sorry, we don't accept empty passwords.", 'ht-forgot-your-password' );
            default:
                break;
        }
        return __( 'An unknown error occurred. Please try again later.', 'ht-forgot-your-password' );
    }

    /**
     * A shortcode for rendering the form used to initiate the password reset.
     *
     * @param  array   $attributes  Shortcode attributes.
     * @param  string  $content     The text content for shortcode. Not used.
     *
     * @return string  The shortcode output
     */
    public function render_heytheme_forgot_your_password_form( $attributes, $content = null ) {
        // Parse shortcode attributes
        $default_attributes = array( 'show_title' => false );
        $attributes = shortcode_atts( $default_attributes, $attributes );
        if ( is_user_logged_in() ) {
            return __( 'You are already signed in.', 'ht-forgot-your-password' );
        } else {
            // Retrieve possible errors from request parameters
            $attributes['errors'] = array();
            if ( isset( $_REQUEST['errors'] ) ) {
                $error_codes = explode( ',', $_REQUEST['errors'] );
                foreach ( $error_codes as $error_code ) {
                    $attributes['errors'] []= $this->get_error_message( $error_code );
                }
            }
            return $this->get_template_html( 'htsc-forgot-your-password', $attributes );
        }
    }

    public function render_heytheme_success_password_reset_form( $attributes, $content = null ) {
        // Parse shortcode attributes
        $default_attributes = array( 'show_title' => false );
        $attributes = shortcode_atts( $default_attributes, $attributes );
      
            
        
            return $this->get_template_html( 'htsc-success-password-reset', $attributes );
        
    }

    /**
     * Initiates password reset if everything okay then send an email to user with password reset link.
     */
    public function heytheme_send_password_reset_link_to_user() {
        if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
            $errors = retrieve_password();
            if ( is_wp_error( $errors ) ) {
                // Errors found
                $redirect_url = home_url( 'heytheme-forgot-your-password' );
                $redirect_url = add_query_arg( 'errors', join( ',', $errors->get_error_codes() ), $redirect_url );
            } else {
                // Email sent
                $redirect_url = home_url( 'login' );
                $redirect_url = add_query_arg( 'checkemail', 'confirm', $redirect_url );
                // $redirect_url = add_query_arg('extra','flag', $redirect_url);
                if ( ! empty( $_REQUEST['redirect_to'] ) ) {
                    $redirect_url = $_REQUEST['redirect_to'];
                }
            }
            wp_safe_redirect( $redirect_url );
            exit;
        }
    }

    /**
     * Redirects to the custom password reset page, or the login page
     * if there are errors.
     */
    public function redirect_to_heytheme_password_reset() {
        if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
            // Verify key / login combo
            $user = check_password_reset_key( $_REQUEST['key'], $_REQUEST['login'] );
            if ( ! $user || is_wp_error( $user ) ) {
                if ( $user && $user->get_error_code() === 'expired_key' ) {
                    wp_redirect( home_url( 'heytheme-forgot-your-password?errors=expiredkey' ) );
                } else {
                    wp_redirect( home_url( 'heytheme-forgot-your-password?errors=invalidkey' ) );
                }
                exit;
            }
            $redirect_url = home_url( 'heytheme-password-reset' );
            $redirect_url = add_query_arg( 'login', esc_attr( $_REQUEST['login'] ), $redirect_url );
            $redirect_url = add_query_arg( 'key', esc_attr( $_REQUEST['key'] ), $redirect_url );
            wp_redirect( $redirect_url );
            exit;
        }
    }

    /**
     * A shortcode for rendering the form used to reset a user's password.
     *
     * @param  array   $attributes  Shortcode attributes.
     * @param  string  $content     The text content for shortcode. Not used.
     *
     * @return string  The shortcode output
     */
    public function render_heytheme_password_reset_form( $attributes, $content = null ) {
        // Parse shortcode attributes
        $default_attributes = array( 'show_title' => false );
        $attributes = shortcode_atts( $default_attributes, $attributes );
        if ( is_user_logged_in() ) {
            return __( 'You are already signed in.', 'ht-forgot-your-password' );
        } else {
            if ( isset( $_REQUEST['login'] ) && isset( $_REQUEST['key'] ) ) {
                $attributes['login'] = $_REQUEST['login'];
                $attributes['key'] = $_REQUEST['key'];
                // Error messages
                $errors = array();
                if ( isset( $_REQUEST['error'] ) ) {
                    $error_codes = explode( ',', $_REQUEST['error'] );
                    foreach ( $error_codes as $code ) {
                        $errors []= $this->get_error_message( $code );
                    }
                }
                $attributes['errors'] = $errors;
                return $this->get_template_html( 'htsc-password-reset', $attributes );
            } else {
                return __( 'Invalid password reset link.', 'ht-forgot-your-password' );
            }
        }
    }

    /**
     * Resets the user's password if the password reset form was submitted.
     */
    public function heytheme_do_password_reset() {
        if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
            $rp_key = $_REQUEST['rp_key'];
            $rp_login = $_REQUEST['rp_login'];
            $user = check_password_reset_key( $rp_key, $rp_login );
            if ( ! $user || is_wp_error( $user ) ) {
                if ( $user && $user->get_error_code() === 'expired_key' ) {
                    wp_redirect( home_url( 'heytheme-forgot-your-password?errors=expiredkey' ) );
                } else {
                    wp_redirect( home_url( 'heytheme-forgot-your-password?errors=invalidkey' ) );
                }
                exit;
            }
            if ( isset( $_POST['pass1'] ) ) {
                if ( $_POST['pass1'] != $_POST['pass2'] ) {
                    // Passwords don't match
                    $redirect_url = home_url( 'heytheme-password-reset' );
                    $redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
                    $redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
                    $redirect_url = add_query_arg( 'error', 'password_reset_mismatch', $redirect_url );
                    wp_redirect( $redirect_url );
                    exit;
                }
                if ( empty( $_POST['pass1'] ) ) {
                    // Password is empty
                    $redirect_url = home_url( 'heytheme-password-reset' );
                    $redirect_url = add_query_arg( 'key', $rp_key, $redirect_url );
                    $redirect_url = add_query_arg( 'login', $rp_login, $redirect_url );
                    $redirect_url = add_query_arg( 'error', 'password_reset_empty', $redirect_url );
                    wp_redirect( $redirect_url );
                    exit;
                }
                // Parameter checks OK, reset password
                reset_password( $user, $_POST['pass1'] );
                // wp_redirect( home_url( 'login?password=changed' ) );
                wp_redirect( home_url( 'heytheme-success-password-reset' ) );

            } else {
                echo "Invalid request.";
            }
            exit;
        }
    }
 
     
}
 
// Initialize the plugin
$heytheme_forgot_your_password = new Heytheme_forgot_your_password();


// Create the custom pages at plugin activation
register_activation_hook( __FILE__, array( 'Heytheme_forgot_your_password', 'plugin_activated' ) );