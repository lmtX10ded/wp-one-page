<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit; 
}

?>

<div class="wrap">
	<?php
	$did = isset($_GET['did']) ? $_GET['did'] : '0';
	es_cls_security::es_check_number($did);

	// First check if ID exist with requested ID
	$result = es_cls_notification::es_notification_count($did);
	if ($result != '1') {
		?><div class="error fade">
			<p><strong>
				<?php echo __( 'Oops, selected details does not exists.', 'email-subscribers' ); ?>
			</strong></p>
		</div><?php
	} else {
		$es_errors = array();
		$es_success = '';
		$es_error_found = FALSE;

		$data = array();
		$data = es_cls_notification::es_notification_select($did);

		// Preset the form fields
		$form = array(
			'es_note_id' => $data['es_note_id'],
			'es_note_cat' => $data['es_note_cat'],
			'es_note_group' => $data['es_note_group'],
			'es_note_templ' => $data['es_note_templ'],
			'es_note_status' => $data['es_note_status']
		);
	}

	// Form submitted, check the data
	if (isset($_POST['es_form_submit']) && $_POST['es_form_submit'] == 'yes') {

		//	Just security thingy that wordpress offers us
		check_admin_referer('es_form_edit');

		$form['es_note_group'] = isset($_POST['es_note_group']) ? $_POST['es_note_group'] : '';
		if ($form['es_note_group'] == '') {
			$es_errors[] = __( 'Please select subscribers group', 'email-subscribers' );
			$es_error_found = TRUE;
		}

		$form['es_note_status'] = isset($_POST['es_note_status']) ? $_POST['es_note_status'] : '';
		if ($form['es_note_status'] == '') {
			$es_errors[] = __( 'Please select notification status', 'email-subscribers' );
			$es_error_found = TRUE;
		}

		$form['es_note_templ'] = isset($_POST['es_note_templ']) ? $_POST['es_note_templ'] : '';
		if ($form['es_note_templ'] == '') {
			$es_errors[] = __( 'Please select notification mail subject. Use templates menu to create new.', 'email-subscribers' );
			$es_error_found = TRUE;
		}

		$es_note_cat = isset($_POST['es_note_cat']) ? $_POST['es_note_cat'] : '';
		if ($es_note_cat == '') {
			$es_errors[] = __( 'Please select post categories.', 'email-subscribers' );
			$es_error_found = TRUE;
		}
		$form['es_note_id'] = isset($_POST['es_note_id']) ? $_POST['es_note_id'] : '';

		//	No errors found, we can add this Group to the table
		if ($es_error_found == FALSE) {	
			$action = false;
			$listcategory = "";
			$total = count($es_note_cat);
			if( $total > 0 ) {
				for($i = 0; $i < $total; $i++) {
					$listcategory = $listcategory . " ##" . wp_specialchars_decode(stripslashes($es_note_cat[$i]),ENT_QUOTES) . "## ";
					if($i != ($total - 1)) {
						$listcategory = $listcategory .  "--";
					}
				}
			}

			$form['es_note_cat'] = $listcategory;
			$action = es_cls_notification::es_notification_ins($form, $action = "update");
			if($action == "sus") {
				$es_success = __( 'Notification successfully updated. ', 'email-subscribers' );
			}
		}
	}

	if ($es_error_found == TRUE && isset($es_errors[0]) == TRUE) {
		?><div class="error fade">
			<p><strong>
				<?php echo $es_errors[0]; ?>
			</strong></p>
		</div><?php
	}

	if ($es_error_found == FALSE && strlen($es_success) > 0) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><strong>
				<?php echo $es_success; ?>
			</strong></p>
		</div>
		<?php
	}

	?>

	<style>
		.form-table th {
			width: 400px;
		}
	</style>

	<div class="wrap">
		<h2>
			<?php echo __( 'Edit Notification', 'email-subscribers' ); ?>
			<a class="add-new-h2" href="<?php echo ES_ADMINURL; ?>?page=es-notification&amp;ac=add"><?php echo __( 'Add New', 'email-subscribers' ); ?></a>
			<a class="add-new-h2" target="_blank" href="<?php echo ES_FAV; ?>"><?php echo __( 'Help', 'email-subscribers' ); ?></a>
		</h2>
		<div class="es-form" style="width: 80%;float: left;">
			<form name="es_form" method="post" action="#" onsubmit="return _es_submit()">
				<table class="form-table">
					<tbody>
						<tr>
							<th scope="row">
								<label for="tag-link"><?php echo __( 'Subscribers Group to send post notification to', 'email-subscribers' ); ?></label>
							</th>
							<td>
								<select name="es_note_group" id="es_note_group">
									<option value=''><?php echo __( 'Select', 'email-subscribers' ); ?></option>
									<?php
									$thisselected = "";
									$groups = array();
									$groups = es_cls_dbquery::es_view_subscriber_group();
									if(count($groups) > 0) {
										$i = 1;
										foreach ($groups as $group) {
											if(stripslashes($group['es_email_group']) == $form['es_note_group']) {
												$thisselected = 'selected="selected"' ;
											}
											?>
											<option value="<?php echo esc_html(stripslashes($group["es_email_group"])); ?>" <?php echo $thisselected; ?>>
												<?php echo esc_html(stripslashes($group["es_email_group"])); ?>
											</option>
											<?php
											$thisselected = "";
										}
									}
									?>
								</select>
								
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="tag-link">
									<?php echo __( 'Select Notification Email Subject', 'email-subscribers' ); ?>
									<p class="description"><?php echo __( '(Use templates menu to create new)', 'email-subscribers' ); ?></p>
								</label>
							</th>
							<td>
								<select class="es_tmpl_select" name="es_note_templ" id="es_note_templ" onchange="return _es_change(this.options[this.selectedIndex])">
									<option value=''><?php echo __( 'Select', 'email-subscribers' ); ?></option>
									<?php
									$subject = array();
									$subject = es_cls_templates::es_template_select_type($type = "Post Notification");
									$thisselected = "";
									if(count($subject) > 0) {
										$i = 1;
										foreach ($subject as $sub) {
											if($sub["es_templ_id"] == $form['es_note_templ']) { 
												$thisselected = "selected='selected'" ;
											}
											?><option data-img='<?php echo $sub["es_templ_thumbnail"]; ?>' value='<?php echo $sub["es_templ_id"]; ?>' <?php echo $thisselected; ?>><?php echo $sub["es_templ_heading"]; ?></option><?php
											$thisselected = "";
										}
									}
									?>
								</select>
								<?php do_action('es_beside_select_notification');?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="tag-link">
									<?php echo __( 'Select Post Categories', 'email-subscribers' ); ?>
								</label>
							</th>
							<td>
								<?php
								$args = array( 'hide_empty' => 0, 'orderby' => 'name', 'order' => 'ASC' );
								$categories = get_categories($args);
								$count = 0;
								$col = 3;
								$checked = "";
								echo "<table border='0' cellspacing='0'><tr>"; 
								foreach($categories as $category) {
									echo "<td style='padding-top:4px;padding-bottom:4px;padding-right:10px;'>";
									if (strpos($form['es_note_cat'],'##'.wp_specialchars_decode(stripslashes($category->cat_name),ENT_QUOTES).'##') !== false) {
										$checked = 'checked="checked"';
									} else {
										$checked = "";
									}
									?>
									<input type="checkbox" <?php echo $checked; ?> value="<?php echo htmlspecialchars($category->cat_name, ENT_QUOTES); ?>" id="es_note_cat[]" name="es_note_cat[]">
									<?php
									echo $category->cat_name;
									if($col > 1) {
										$col = $col-1;
										echo "</td><td>"; 
									} elseif($col = 1) {
										$col = $col-1;
										echo "</td></tr><tr>";;
										$col = 3;
									}
									$count = $count + 1;
								}
								echo "</tr></table>";
								?>
								<p class="select_all" style="margin-left: 0.7em;">
									<input type="button" name="CheckAll" class="button add-new-h2" value="<?php echo __( 'Check All', 'email-subscribers' ); ?>" onClick="_es_checkall('es_form', 'es_note_cat[]', true);">
									<input type="button" name="UnCheckAll" class="button add-new-h2" value="<?php echo __( 'Uncheck All', 'email-subscribers' ); ?>" onClick="_es_checkall('es_form', 'es_note_cat[]', false);">
								</p>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="tag-link">
									<?php echo __( 'Select your Custom Post Type', 'email-subscribers' ); ?>
									<p class="description"><?php echo __( '(Optional)', 'email-subscribers' ); ?></p>
								</label>
							</th>
							<td>
								<?php
								$args = array('public'=> true, 'exclude_from_search'=> false, '_builtin' => false); 
								$output = 'names';
								$operator = 'and';
								$post_types = get_post_types($args,$output,$operator);
								if( !empty( $post_types ) ) {
									$col = 3;
									echo "<table border='0' cellspacing='0'><tr>"; 
									foreach($post_types as $post_type) {     
										echo "<td style='padding-top:4px;padding-bottom:4px;padding-right:10px;'>";
										if (strpos($form['es_note_cat'],'##{T}'.$post_type.'{T}##') !== false) {
											$checked = 'checked="checked"';
										} else {
											$checked = "";
										}
										?>
										<input type="checkbox" <?php echo $checked; ?>  value='{T}<?php echo $post_type; ?>{T}' id="es_note_cat[]" name="es_note_cat[]">
										<?php echo $post_type;
										if($col > 1) {
											$col = $col-1;
											echo "</td><td>"; 
										} elseif($col = 1) {
											$col = $col-1;
											echo "</td></tr><tr>";;
											$col = 3;
										}
										$count = $count + 1;
									}
									echo "</tr></table>";
								} else {
									echo __( 'No Custom Post Types Available', 'email-subscribers' );
								}
								?>
							</td>
						</tr>
						<tr>
							<th scope="row">
								<label for="tag-link"><?php echo __( 'Select Notification Status when a new post is published', 'email-subscribers' ); ?></label>
							</th>
							<td>
								<select name="es_note_status" id="es_note_status">
									<option value='Enable' <?php if($form['es_note_status']=='Enable') { echo 'selected="selected"' ; } ?>><?php echo __( 'Send email immediately', 'email-subscribers' ); ?></option>
									<option value='Cron' <?php if($form['es_note_status']=='Cron') { echo 'selected="selected"' ; } ?>><?php echo __( 'Add to cron and send email via cron job', 'email-subscribers' ); ?></option>
									<option value='Disable' <?php if($form['es_note_status']=='Disable') { echo 'selected="selected"' ; } ?>><?php echo __( 'Disable email notification', 'email-subscribers' ); ?></option>
								</select>
								<?php do_action('es_after_email_sent_option'); ?>
							</td>
						</tr>
					</tbody>
				</table>
				<input type="hidden" name="es_form_submit" value="yes"/>
				<input type="hidden" name="es_note_id" id="es_note_id" value="<?php echo $form['es_note_id']; ?>"/>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php echo __( 'Save', 'email-subscribers' ); ?>" />
				</p>
				<?php wp_nonce_field('es_form_edit'); ?>
			</form>
		</div>
		<div clas="es-preview" style="float: right;width: 19%;">
			<div class="es-templ-img"></div>
		</div>
	</div>
</div>