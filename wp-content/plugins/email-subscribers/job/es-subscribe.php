<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class es_cls_job_subscribe {

	public function __construct() {
		if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) {
			add_action( 'wp_ajax_es_add_subscriber', array( $this, 'es_add_subscriber' ) );
			add_action( 'wp_ajax_nopriv_es_add_subscriber', array( $this, 'es_add_subscriber' ) );
		}
	}

	public function es_add_subscriber() {
		$es_response = array();
		//honey-pot validation
		if(!empty($_POST['es_required_field'])){
			$es_response['error'] = 'unexpected-error';
			echo json_encode($es_response);
			die();
		}
		//block address list
		$es_disposable_list = array('mail.ru');
		$es_domain = substr(strrchr(trim($_POST['esfpx_es_txt_email']) , "@"), 1); //extract domain name from email
		if(in_array($es_domain, $es_disposable_list)){ 
			$es_response['error'] = 'unexpected-error';
			echo json_encode($es_response);
			die();
		}
		if ( ( isset( $_POST['es'] ) ) && ( 'subscribe' === $_POST['es'] ) && ( isset( $_POST['action'] ) ) && ( 'es_add_subscriber' === $_POST['action'] ) && !empty( $_POST['esfpx_es-subscribe'] ) ) {

			foreach ($_POST as $key => $value) {
				$new_key = str_replace('_pg', '', $key);
				$_POST[$new_key] = $value;
			}

			$es_subscriber_name  = isset( $_POST['esfpx_es_txt_name'] ) ? trim($_POST['esfpx_es_txt_name']) : '';
			$es_subscriber_email = isset( $_POST['esfpx_es_txt_email'] ) ? trim($_POST['esfpx_es_txt_email']) : '';
			$es_subscriber_group = isset( $_POST['esfpx_es_txt_group'] ) ? trim($_POST['esfpx_es_txt_group']) : '';
			$es_nonce 			 = $_POST['esfpx_es-subscribe'];

			$subscriber_form = array(
									'es_email_name' => '',
									'es_email_mail' => '',
									'es_email_group' => '',
									'es_email_status' => '',
									'es_nonce' => ''
								);

			if( $es_subscriber_group == '' ) {
				$es_subscriber_group = 'Public';
			}

			if ( $es_subscriber_email != '' ) {
				if ( !filter_var( $es_subscriber_email, FILTER_VALIDATE_EMAIL ) ) {
					$es_response['error'] = 'invalid-email';
				} else {
					$action = '';
					global $wpdb;

					$subscriber_form['es_email_name'] = $es_subscriber_name;
					$subscriber_form['es_email_mail'] = $es_subscriber_email;
					$subscriber_form['es_email_group'] = $es_subscriber_group;
					$subscriber_form['es_nonce'] = $es_nonce;

					$es_optintype = get_option( 'ig_es_optintype' );

					if( $es_optintype == "Double Opt In" ) {
						$subscriber_form['es_email_status'] = "Unconfirmed";
					} else {
						$subscriber_form['es_email_status'] = "Single Opt In";
					}

					$action = es_cls_dbquery::es_view_subscriber_widget($subscriber_form);
					if( $action == "sus" ) {
						$subscribers = es_cls_dbquery::es_view_subscriber_one($es_subscriber_email,$es_subscriber_group);
						if( $es_optintype == "Double Opt In" ) {
							es_cls_sendmail::es_sendmail("optin", $template = 0, $subscribers, "optin", 0);
							$es_response['success'] = 'subscribed-pending-doubleoptin';
						} else {
							$es_c_usermailoption = get_option( 'ig_es_welcomeemail' );
							if ( $es_c_usermailoption == "YES" ) {
								es_cls_sendmail::es_sendmail("welcome", $template = 0, $subscribers, "welcome", 0);
							}
							$es_response['success'] = 'subscribed-successfully';
						} 
					} elseif( $action == "ext" ) {
						$es_response['success'] = 'already-exist';
					} elseif( $action == "invalid" ) {
						$es_response['error'] = 'invalid-email';
					}
				}
			} else {
				$es_response['error'] = 'no-email-address';
			}
		} else {
			$es_response['error'] = 'unexpected-error';
		}    
		

		echo json_encode($es_response);
		die();
	}
}

new es_cls_job_subscribe();