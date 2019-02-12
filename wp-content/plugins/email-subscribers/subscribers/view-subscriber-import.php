<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

?>

<div class="wrap">
	<?php
	$es_errors = array();
	$es_success = '';
	$es_error_found = FALSE;
	$csv = array();

	// Preset the form fields
	$form = array(
		'es_email_name' => '',
		'es_email_status' => '',
		'es_email_group' => '',
		'es_email_mail' => '',
		'es_nonce' => ''
	);

	// Form submitted, check the data
	if (isset($_POST['es_form_submit']) && $_POST['es_form_submit'] == 'yes') {

		//	Just security thingy that wordpress offers us
		check_admin_referer('es_form_add');

		$extension = pathinfo( $_FILES['es_csv_name']['name'], PATHINFO_EXTENSION );

		$tmpname = $_FILES['es_csv_name']['tmp_name'];
		
		$es_email_status = isset($_POST['es_email_status']) ? $_POST['es_email_status'] : '';
		$es_email_group = isset($_POST['es_email_group']) ? $_POST['es_email_group'] : '';
		if ( $es_email_group == '' ) {
			$es_email_group = isset($_POST['es_email_group_txt']) ? $_POST['es_email_group_txt'] : '';
		}

		if( $es_email_group != "" ) {
			$special_letters = es_cls_common::es_special_letters();
			if (preg_match($special_letters, $es_email_group)) {
				$es_errors[] = __( 'Error: Special characters ([\'^$%&*()}{@#~?><>,|=_+\"]) are not allowed in the Group name.', 'email-subscribers' );
				$es_error_found = TRUE;
			}
		}

		if ( $es_email_status == '' ) {
			$es_email_status = "Confirmed";
		}

		if ( $es_email_group == '' ) {
			$es_email_group = "Public";
		}

		if( $extension === 'csv' ) {
			$csv = es_cls_common::es_readcsv($tmpname);
			array_shift($csv);
		}

		//	No errors found, we can add this Group to the table
		if ( $es_error_found == FALSE ) {
			if(count($csv) > 0) {
				$inserted = 0;
				$duplicate = 0;
				$invalid = 0;
				foreach ($csv as  $value) {
					$form["es_email_mail"] = trim($value[0]);
					$form["es_email_name"] = trim($value[1]);
					$form["es_email_group"] = $es_email_group;
					$form["es_email_status"] = $es_email_status;
					$form['es_nonce'] = wp_create_nonce( 'es-subscribe' );
					$action = es_cls_dbquery::es_view_subscriber_ins($form, "insert");
					if( $action == "sus" ) {
						$inserted = $inserted + 1;
					} elseif( $action == "ext" ) {
						$duplicate = $duplicate + 1;
					} elseif( $action == "invalid" ) {
						$invalid = $invalid + 1;
					}

					// Reset the form fields
					$form = array(
						'es_email_name' => '',
						'es_email_status' => '',
						'es_email_group' => '',
						'es_email_mail' => '',
						'es_nonce' => ''
					);
				}

				?>
				<div class="notice notice-success is-dismissible">
					<p><strong><?php echo $inserted; ?> <?php echo __( 'email imported.', 'email-subscribers' ); ?></strong></p>
					<p><strong><?php echo $duplicate; ?> <?php echo __( 'email already exists.', 'email-subscribers' ); ?></strong></p>
					<p><strong><?php echo $invalid; ?> <?php echo __( 'email are invalid.', 'email-subscribers' ); ?></strong></p>
					<p><strong>
							<a href="<?php echo ES_ADMINURL; ?>?page=es-view-subscribers">
							<?php echo __( 'Click here', 'email-subscribers' ); ?></a> <?php echo __(' to view details.', 'email-subscribers' ); ?>
					</strong></p>
				</div>
				<?php
			} else {
				?>
				<div class="error fade">
					<p><strong>
						<?php echo __( 'File Upload Failed.', 'email-subscribers' ); ?>
					</strong></p>
				</div>
				<?php
			}
		}
	}

	if ($es_error_found == TRUE && isset($es_errors[0]) == TRUE) {
		?>
		<div class="error fade">
			<p><strong><?php echo $es_errors[0]; ?></strong></p>
		</div>
		<?php
	}

	if ($es_error_found == FALSE && isset($es_success[0]) == TRUE) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>
				<?php echo $es_success; ?><a href="<?php echo ES_ADMINURL; ?>?page=es-view-subscribers">
				<?php echo __( 'Click here', 'email-subscribers' ); ?></a> <?php echo __( ' to view details.', 'email-subscribers' ); ?>
			</strong></p>
		</div>
		<?php
	}

	?>

	<style type="text/css">
		.form-table th {
			width:300px;
		}
	</style>

	<div class="wrap">
		<h2>
			<?php echo __( 'Import Email Addresses', 'email-subscribers' ); ?>
			<a class="add-new-h2" href="<?php echo ES_ADMINURL; ?>?page=es-view-subscribers&amp;ac=add"><?php echo __( 'Add New Subscriber', 'email-subscribers' ); ?></a>
			<a class="add-new-h2" href="<?php echo ES_ADMINURL; ?>?page=es-view-subscribers&amp;ac=export"><?php echo __( 'Export', 'email-subscribers' ); ?></a>
			<a class="add-new-h2" href="<?php echo ES_ADMINURL; ?>?page=es-view-subscribers&amp;ac=sync"><?php echo __( 'Sync', 'email-subscribers' ); ?></a>
			<a class="add-new-h2" target="_blank" href="<?php echo ES_FAV; ?>"><?php echo __( 'Help', 'email-subscribers' ); ?></a>
		</h2>
		<div class="tool-box">
			<form name="form_addemail" id="form_addemail" method="post" action="#" onsubmit="return _es_importemail()" enctype="multipart/form-data">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="tag-image">
									<?php echo __( 'Select CSV file', 'email-subscribers' ); ?>
									<p class="description">
										<?php echo __( 'Check CSV structure ', 'email-subscribers' ); ?>
										<a target="_blank" href="https://www.icegram.com/documentation/es-how-to-import-or-export-email-addresses/?utm_source=es&utm_medium=in_app&utm_campaign=view_docs_help_page"><?php echo __( 'from here', 'email-subscribers' ); ?></a>
									</p>
								</label>
							</th>
							<td>
								<input type="file" name="es_csv_name" id="es_csv_name" />
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="tag-email-status">
									<?php echo __( 'Select Subscribers Email Status', 'email-subscribers' ); ?>
									<p><?php echo __( '', 'email-subscribers' ); ?></p>
								</label>
							</th>
							<td>
								<select name="es_email_status" id="es_email_status">
									<option value='Confirmed' selected="selected"><?php echo __( 'Confirmed', 'email-subscribers' ); ?></option>
									<option value='Unconfirmed'><?php echo __( 'Unconfirmed', 'email-subscribers' ); ?></option>
									<option value='Unsubscribed'><?php echo __( 'Unsubscribed', 'email-subscribers' ); ?></option>
									<option value='Single Opt In'><?php echo __( 'Single Opt In', 'email-subscribers' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th>
								<label for="tag-email-group">
									<?php echo __( 'Select (or) Create Group for Subscribers', 'email-subscribers' ); ?>
								</label>
							</th>
							<td>
								<select name="es_email_group" id="es_email_group">
									<option value=''><?php echo __( 'Select', 'email-subscribers' ); ?></option>
									<?php
									$groups = array();
									$groups = es_cls_dbquery::es_view_subscriber_group();
									if(count($groups) > 0) {
										$i = 1;
										foreach ($groups as $group) {
											?><option value='<?php echo $group["es_email_group"]; ?>'><?php echo $group["es_email_group"]; ?></option><?php
										}
									}
									?>
								</select>
								<?php echo __( '(or)', 'email-subscribers' ); ?>
								<input name="es_email_group_txt" type="text" id="es_email_group_txt" value="" maxlength="225" />
							</td>
						</tr>
					</tbody>
				</table>
				<input type="hidden" name="es_form_submit" value="yes"/>
				<p style="padding-top:10px;">
					<input type="submit" class="button-primary" value="<?php echo __( 'Import', 'email-subscribers' ); ?>" />
				</p>
				<?php wp_nonce_field('es_form_add'); ?>
			</form>
		</div>
	</div>
</div>