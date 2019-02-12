<?php 
/**
 * Plugin Name:       Heytheme Emails
 * Description:       Creates email text box with send button, This plugin made to use with kaitlincorp.com 
 * Version:           0.0.1
 * Author:            Kish
 * License:           GPL-2.0+
 * Text Domain:       heytheme-emails
 */

class Heytheme_Emails {

	public function __construct() {
		add_action("after_switch_theme", array( $this, "create_tbl_heytheme_emails" ) );
		add_shortcode( 'htsc-email-box', array( $this, 'render_email_box' ) );
		add_action( 'admin_menu', array( $this, 'add_heytheme_emails_menu_item' ) );
		// add_action( 'admin_enqueue_scripts', array($this,'enque_script_exprt_csv') );
		add_action( 'admin_post_print.csv', array( $this,'export_csv') );
		

		add_action("admin_init", array( $this, 'display_admin_manage_emails' ));

		// Hooking up our functions to WordPress filters 
			add_filter( 'wp_mail_from', array( $this, 'ht_sender_email' ) );
			add_filter( 'wp_mail_from_name', array( $this, 'ht_sender_name') );
	}


	public function export_csv(){
    if ( ! current_user_can( 'manage_options' ) )
        return;

		$location = $_SERVER['DOCUMENT_ROOT'];

		include ($location . '/wp-config.php');
		include ($location . '/wp-load.php');
		include ($location . '/wp-includes/pluggable.php');

		  $file = 'emails';

		  global $wpdb;
		  $table_name = $wpdb->prefix . 'heytheme_emails';
		  $results = $wpdb->get_results("SELECT * FROM $table_name;",ARRAY_A);

		  if (empty($results)) {
		    return;
		  }

		  $csv_output = '"'.implode('","',array_keys($results[0])).'"';
		  // $csv_output = 'ww,ww,ww,dd';
		  foreach ($results as $row) {
		    $csv_output .= "\r\n".'"'.implode('","',$row).'"';
		  }

		  	// $filename = $file."_".date("Y-m-d_H-i",time()).".csv";
		  $filename = $file.".csv";
		  header("Content-type: text/csv; charset=utf-8");
		  header("Content-disposition: csv" . date("Y-m-d") . ".csv");
		  header("Content-disposition: attachment; filename=".$filename);
		  
		  echo $csv_output;

		  file_put_contents( $filename, $csv_output );
		  // echo $filename;

		  die();
}
// public function enque_script_exprt_csv(){
// 	    wp_register_script('jquery-3.2.1', 'https://code.jquery.com/jquery-3.3.1.slim.min.js', false, '3.2.1', true );
// 		wp_enqueue_script('jquery-3.2.1');
	
// }
	public function create_tbl_heytheme_emails(){

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table_name = $wpdb->prefix . 'heytheme_emails';
		$sql = "CREATE TABLE $table_name (
				id INTEGER NOT NULL AUTO_INCREMENT,
				email TEXT NOT NULL,
				PRIMARY KEY (id)
				) $charset_collate;";
		dbDelta( $sql );

}

	public function add_heytheme_emails_menu_item() {
		// add_menu_page( string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '', string $icon_url = '', int $position = null );
		// add_submenu_page( string $parent_slug, string $page_title, string $menu_title, string $capability, string $menu_slug, callable $function = '' );

		add_menu_page("Admin Manage Emails", "Emails", "manage_options", "ms_admin_manage_emails", array( $this, 'cb_admin_manage_emails'), 'dashicons-admin-settings', null);
		add_submenu_page( 'ms_admin_manage_emails', 'Retrieve Emails Page', 'Retrieve Emails', 'manage_options', 'ms_retrieve_emails', array( $this, 'cb_retrieve_emails' ) );
		add_submenu_page( 'ms_admin_manage_emails', 'Export Emails Page', 'Export', 'manage_options', 'ms_export', array( $this, 'cb_export' ) );
		// add_submenu_page( 'heytheme_main_settings', 'Theme Panel', 'Agent ID', 'manage_options', 'heytheme_sub_settings', 'agent_id_page' );

	}

	public function cb_admin_manage_emails(){

	?>
	    <div class="wrap">
	    <h1>Options..</h1>
	    <form method="post" action="options.php">
	        <?php
	            settings_fields("sf_admin_manage_emails");
	            do_settings_sections("dss_admin_manage_emails");
	            submit_button();
	        ?>
	    </form>
		</div>
	<?php

	}

	public function display_admin_manage_emails(){
		add_settings_section("sf_admin_manage_emails", "All Settings", null, "dss_admin_manage_emails");

		add_settings_field("notification_receive_email", "Notification Receive Email", array($this,'display_twitter_element'), "dss_admin_manage_emails", "sf_admin_manage_emails");
		register_setting("sf_admin_manage_emails", "notification_receive_email");
	}
	public function display_twitter_element(){
		?>
	    	<input type="text" name="notification_receive_email" id="notification_receive_email" value="<?php echo get_option('notification_receive_email'); ?>" />
	    <?php
	}

	public function cb_retrieve_emails(){

			global $wpdb;

		  $table_name = $wpdb->prefix . "heytheme_emails";

		  // $user = $wpdb->get_results( "SELECT * FROM $table_name" );

		$items_per_page = 50;
		$page = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
		$offset = ( $page * $items_per_page ) - $items_per_page;

		$query = 'SELECT * FROM '.$table_name;

		$total_query = "SELECT COUNT(1) FROM (${query}) AS combined_table";
		$total = $wpdb->get_var( $total_query );

		$results = $wpdb->get_results( $query.' ORDER BY id DESC LIMIT '. $offset.', '. $items_per_page, OBJECT );
		/*
		*
		* Here goes the loop
		*
		***/
		$pagination = paginate_links( array(
		                        'base' => add_query_arg( 'cpage', '%#%' ),
		                        'format' => '',
		                        'prev_text' => __('&laquo;'),
		                        'next_text' => __('&raquo;'),
		                        'total' => ceil($total / $items_per_page),
		                        'current' => $page
		                    ));

		?>
		<div class="wrap">
		<?php if($pagination): ?><table class="wp-list-table widefat fixed striped posts"><tr><td colspan="8" align="center"><?php echo $pagination; ?></td></tr></table><?php endif; ?>
		<table class="wp-list-table widefat fixed striped posts">
			<thead>
				<tr>
					<!-- <th scope="col" class="manage-column">Name</th> -->
					<th scope="col" class="manage-column">Email</th>
					<!-- <th scope="col" class="manage-column">Phone</th> -->
					<!-- <th scope="col" class="manage-column">Address</th> -->
					<!-- <th scope="col" class="manage-column">City</th> -->
					<!-- <th scope="col" class="manage-column">Postal Code</th> -->
					<!-- <th scope="col" class="manage-column">Message</th> -->
					<!-- <th scope="col" class="manage-column">Source</th> -->
				</tr>
			</thead>
			<tbody>
		<?php
		  foreach ($results as $row){
		  	?>
		<tr>
		    <!-- <td><label class="regular-text"><?php //echo $row->name ?></label></td> -->
			<td><label class="regular-text"><?php echo $row->email ?></label></td>
			<!-- <td><label class="regular-text"><?php //echo $row->phone ?></label></td> -->
			<!-- <td><label class="regular-text"><?php //echo $row->address ?></label></td> -->
			<!-- <td><label class="regular-text"><?php //echo $row->city ?></label></td> -->
			<!-- <td><label class="regular-text"><?php //echo $row->postal_code ?></label></td> -->
			<!-- <td><label class="regular-text"><?php //echo $row->comments ?></label></td> -->
			<!-- <td><label class="regular-text"><?php //echo $row->source ?></label></td> -->
		</tr>
		<?php } ?>
		<?php if($pagination): ?><tr><td colspan="8" align="center"><?php echo $pagination; ?></td></tr><?php endif; ?>
		</tbody>
		</table>
		</div>
		<?php
		}







		public function cb_export(){

			?> 
			<table class="form-table">
				<tr>
					<th scope="row">Export emails to csv</th>
					<!-- <td><span id="export-to-csv" class="button button-primary">Export</span></td> -->
					<td><a href="<?= admin_url( 'admin-post.php?action=print.csv' ); ?>" class="button button-primary">Export</a></td>
					<!-- <td><a href= "<?= plugin_dir_path( __FILE__ ) ?>export-to-csv.php" class="button button-primary">Export</span></td> -->
				</tr>
			</table>
			<?php
			}

			public function ht_sender_email( $original_email_address ) {
			    return 'admin@kaitlincorp.com';
			}
			 
			// Function to change sender name
			public function ht_sender_name( $original_email_from ) {
			    return 'Admin';
			}
			 

	public function render_email_box(){
// if the submit button is clicked, send the email
		ob_start();
	if ( isset( $_POST['ht-submitted'] ) ) {

		// sanitize form values
		// $name    = sanitize_text_field( $_POST["cf-name"] );
		$email   = sanitize_email( $_POST["ht-email"] );
		$subject = sanitize_text_field( "New email from kaitlincorp.com" );
		$message = esc_textarea( $email." contacted you." );

		if(!get_option('notification_receive_email')){
			$to = get_option( 'admin_email' );
		} else {
			$to = get_option( 'notification_receive_email' );
		}
		$headers = "From: <$email>" . "\r\n";

		// If email has been process for sending, display a success message
		if ( wp_mail( $to, $subject, $message ) ) {
			global $wpdb;
			$table_name = $wpdb->prefix.'heytheme_emails';
			$email_data['email']= $email;
			$wpdb->insert( $table_name, $email_data);
			echo '<div>';
			echo '<p>Thanks for contacting me, expect a response soon.</p>';
			echo '</div>';
		} else {
			echo 'An unexpected error occurred';
		}
	}
	echo '<form action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" method="post">';
	echo '<input type="email" name="ht-email" value="' . ( isset( $_POST["ht-email"] ) ? esc_attr( $_POST["ht-email"] ) : '' ) . '" size="40" />';
	echo '<p><input type="submit" name="ht-submitted" value="Send"></p>';
	echo '</form>';

	return ob_get_clean();
	}
}

// Initialize the plugin
$heytheme_emails = new Heytheme_Emails();
// register_activation_hook( __FILE__, array( $this, 'create_tbl_heytheme_emails' ) );
register_activation_hook( __FILE__, array( 'heytheme_emails', 'create_tbl_heytheme_emails' ) );
 