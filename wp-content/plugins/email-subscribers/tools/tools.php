<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ES_Tools' ) ) {

	class ES_Tools {

		public $nav_tabs, $form = array();

		public function __construct() {
			$this->nav_tabs = $this->es_get_tabs_list();
			$this->es_display_nav_tabs();
			$this->form = $this->es_process_tools_data();
			$this->es_display_settings();
		}

		public function es_get_tabs_list() {
			$tabs = array(
				'test-email'				  => __( 'Test Email', 'email-subscribers' )
			);

			return apply_filters( 'es_tools_tabs', $tabs );
		}

		public function es_display_nav_tabs() {
			?>
			<style>
				.form-table th {
					width: 450px;
				}
			</style>

			<div class="wrap">
				<h2>
					<?php echo __( 'Tools', 'email-subscribers' ); ?>
					<a class="add-new-h2" target="_blank" href="<?php echo ES_FAV; ?>"><?php echo __( 'Help', 'email-subscribers' ); ?></a>
				</h2>
				<div id="icon-options-general" class="icon32"><br /></div>
				<h2 id="es-tabs" class="nav-tab-wrapper">
				<?php foreach ( $this->nav_tabs as $tab => $name ) { ?>
					<a class="nav-tab active" id=<?php echo $tab; ?> href='#'><?php echo $name; ?></a>
				<?php } ?>
				</h2>
				<?php
		}

		public function es_display_settings() {
			?>
			<form name="es_form" id="es_form" method="post" action="#">
				<table class="es-settings form-table">
					<tbody>
						<?php $this->display_test_email(); ?>
					</tbody>
				</table>
				<input type="hidden" name="es_form_submit" value="yes"/>
				<p style="padding-top:10px;">
					<input type="submit" name="publish" id="es-save-settings" class="button-primary" value="<?php echo __( 'Send Email', 'email-subscribers' ); ?>" />
				</p>
				<?php wp_nonce_field('es_form_edit'); ?>
			</form>
			<?php
		}

		public function display_test_email() {
			?>
			<tr class="es-admin test-email">
				<th scope="row">
					<label for="elp"><?php echo __( 'Sende a Test Email', 'email-subscribers' ); ?>
						<p class="description"><?php echo __( 'Type an email address here and then click a button below to generate a test email.', 'email-subscribers' ); ?></p>
					</label>
				</th>
				<td>
					<input name="es_c_toemail" type="text" id="es_c_toemail" value="<?php echo stripslashes($this->form['ig_es_toemail']); ?>" size="35" maxlength="225" />
				</td>
			</tr>
			<?php
		}

		public function es_process_tools_data() {

			$es_error = '';
			$es_success = '';
			$es_error_found = false;

			$form = array(
			        'ig_es_toemail' => ''
			);


			// Form submitted, check & update the data in options table
			if (isset($_POST['es_form_submit']) && $_POST['es_form_submit'] == 'yes') {

				// Just security thingy that wordpress offers us
				check_admin_referer('es_form_edit');

				// Fetch submitted Admin Data
				$form['ig_es_toemail'] = isset($_POST['es_c_toemail']) ? $_POST['es_c_toemail'] : '';
				if ($form['ig_es_toemail'] == '') {
					$es_error = __( 'Please enter email.', 'email-subscribers' );
					$es_error_found = true;
				} else {

				    $result = $this->send_test_email( $form['ig_es_toemail'] );
				    $status  = $result['status'];

				    if('ERROR' === $status) {
                        $es_error_found = true;
                        $es_error = __( sprintf("Error Sending Email: %s", $result['message']), 'email-subscribers' );
				    } else {
				        $es_success = __( 'Email have been sent successfully.', 'email-subscribers' );
				    }

				}


				if ($es_error_found == true) {
                    ?><div class="error fade">
                        <p><strong>
                            <?php echo $es_error; ?>
                        </strong></p>
                    </div><?php
			    } else {
				    ?><div class="notice notice-success is-dismissible">
                        <p><strong>
                            <?php echo $es_success; ?>
                        </strong></p>
                    </div><?php
			    }


			}


			return $form;
		}

		public function es_settings_update( $form = '', $roles = '' ) {
			if ( ! empty( $form ) ) {
				foreach ( $form as $key => $value ) {
					update_option( $key, $value );
				}
			}
			if ( ! empty( $roles ) ) {
				update_option( 'ig_es_rolesandcapabilities', $roles );
			}

			return 'sus';
		}

		public function send_test_email( $email ) {

		    global $phpmailer;

            if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
                require_once ABSPATH . WPINC . '/class-phpmailer.php';
                $phpmailer = new PHPMailer( true );
            }

            ob_start();

            $settings = es_cls_settings::es_get_all_settings();

            if( trim($settings['ig_es_fromname']) == "" || trim($settings['ig_es_fromemail']) == '' ) {
                $current_user = ( function_exists('wp_get_current_user') ) ? wp_get_current_user() : get_currentuserinfo();
                $sender_name = $current_user->user_login;
                $sender_email = $current_user->user_email;
            } else {
                $sender_name = stripslashes($settings['ig_es_fromname']);
                $sender_email = $settings['ig_es_fromemail'];
            }

            $headers  = "From: \"$sender_name\" <$sender_email>\n";
            $headers .= "Return-Path: <" . $sender_email . ">\n";
            $headers .= "Reply-To: \"" . $sender_name . "\" <" . $sender_email . ">\n";
            $headers .= "Content-Type: text/html; charset=\"". get_bloginfo('charset') . "\"\n";

            $send_mail = wp_mail(
                $email,
                /* translators: %s - email address a test email will be sent to. */
                'Email Subscribers: ' . sprintf( esc_html__( 'Test email to %s', 'email-subscribers' ), $email ),
                sprintf( esc_html__( 'This email was generated by the Email Subscribers WordPress plugin.', 'email-subscribers' )),
                $headers
            );

            $smtp_debug = ob_get_clean();

            $result = array('status' => 'SUCCESS');

            if(!$send_mail) {

                $result = array(
                  'status' => 'ERROR',
                  'message' => wp_strip_all_tags( $phpmailer->ErrorInfo )
                );

            }

            return $result;
        }

	}
}

new ES_Tools();