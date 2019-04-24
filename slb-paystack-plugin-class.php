<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SLB_Paystack_Plugin {

	/**
	 * SLB_Paystack_Plugin constructor.
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );

		// edd license actions
		add_action( 'admin_init', array( $this, 'edd_slb_paystack_plugin_updater' ), 0 );
		add_action( 'admin_menu', array( $this, 'edd_slb_paystack_license_menu' ) );
		add_action( 'admin_init', array( $this, 'edd_slb_paystack_register_option' ) );
		add_action( 'admin_init', array( $this, 'edd_slb_paystack_activate_license' ) );
		add_action( 'admin_init', array( $this, 'edd_slb_paystack_deactivate_license' ) );
	}

	public function init() {
		include_once 'slb-paystack-service-class.php';
		SLN_Enum_PaymentMethodProvider::addService( 'paystack', 'Paystack', 'SLB_PaymentMethod_Paystack' );
	}

	function edd_slb_paystack_plugin_updater() {

		// retrieve our license key from the DB
		$license_key = trim( get_option( 'edd_slb_paystack_license_key' ) );

		// setup the updater
		$edd_updater = new EDD_SL_Plugin_Updater( SLB_PAYSTACK_STORE_URL, __FILE__, array(
						'version' 	=> SLB_PAYSTACK_VERSION,   // current version number
						'license' 	=> $license_key,           // license key (used get_option above to retrieve from DB)
						'item_name' => SLB_PAYSTACK_ITEM_NAME, // name of this plugin
						'author' 	=> SLB_PAYSTACK_AUTHOR     // author of this plugin
				)
		);

	}

	function edd_slb_paystack_license_menu() {
		add_plugins_page( 'Salon Booking Paystack Payment Plugin License', 'Salon Booking Paystack Payment Plugin License', 'manage_options', 'slb-paystack-license', array($this, 'edd_slb_paystack_license_page') );
	}

	function edd_slb_paystack_license_page() {
		$license 	= get_option( 'edd_slb_paystack_license_key' );
		$status 	= get_option( 'edd_slb_paystack_license_status' );
		?>
		<div class="wrap">
		<h2><?php _e('Plugin License Options'); ?></h2>
		<form method="post" action="options.php">

			<?php settings_fields('edd_slb_paystack_license'); ?>

			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row" valign="top">
						<?php _e('License Key'); ?>
					</th>
					<td>
						<input id="edd_slb_paystack_license_key" name="edd_slb_paystack_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
						<label class="description" for="edd_slb_paystack_license_key"><?php _e('Enter your license key'); ?></label>
					</td>
				</tr>
				<?php if( false !== $license ) { ?>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e('Activate License'); ?>
						</th>
						<td>
							<?php if( $status !== false && $status == 'valid' ) { ?>
								<span style="color:green;"><?php _e('active'); ?></span>
								<?php wp_nonce_field( 'edd_slb_paystack_nonce', 'edd_slb_paystack_nonce' ); ?>
								<input type="submit" class="button-secondary" name="edd_slb_paystack_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
							<?php } else {
								wp_nonce_field( 'edd_slb_paystack_nonce', 'edd_slb_paystack_nonce' ); ?>
								<input type="submit" class="button-secondary" name="edd_slb_paystack_license_activate" value="<?php _e('Activate License'); ?>"/>
							<?php } ?>
						</td>
					</tr>
				<?php } ?>
				</tbody>
			</table>
			<?php submit_button(); ?>

		</form>
		<?php
	}

	function edd_slb_paystack_register_option() {
		// creates our settings in the options table
		register_setting('edd_slb_paystack_license', 'edd_slb_paystack_license_key', array($this, 'edd_sanitize_license') );
	}

	function edd_sanitize_license( $new ) {
		$old = get_option( 'edd_slb_paystack_license_key' );
		if( $old && $old != $new ) {
			delete_option( 'edd_slb_paystack_license_status' ); // new license has been entered, so must reactivate
		}
		return $new;
	}



	/************************************
	 * this illustrates how to activate
	 * a license key
	 *************************************/

	function edd_slb_paystack_activate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST['edd_slb_paystack_license_activate'] ) ) {

			// run a quick security check
			if( ! check_admin_referer( 'edd_slb_paystack_nonce', 'edd_slb_paystack_nonce' ) )
				return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( 'edd_slb_paystack_license_key' ) );


			// data to send in our API request
			$api_params = array(
					'edd_action'=> 'activate_license',
					'license' 	=> $license,
					'item_name' => urlencode( SLB_PAYSTACK_ITEM_NAME ), // the name of our product in EDD
					'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( SLB_PAYSTACK_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "valid" or "invalid"

			update_option( 'edd_slb_paystack_license_status', $license_data->license );

		}
	}

	function edd_slb_paystack_deactivate_license() {

		// listen for our activate button to be clicked
		if( isset( $_POST['edd_slb_paystack_license_deactivate'] ) ) {

			// run a quick security check
			if( ! check_admin_referer( 'edd_slb_paystack_nonce', 'edd_slb_paystack_nonce' ) )
				return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( get_option( 'edd_slb_paystack_license_key' ) );


			// data to send in our API request
			$api_params = array(
					'edd_action'=> 'deactivate_license',
					'license' 	=> $license,
					'item_name' => urlencode( SLB_PAYSTACK_ITEM_NAME ), // the name of our product in EDD
					'url'       => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( SLB_PAYSTACK_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) )
				return false;

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "deactivated" or "failed"
			if( $license_data->license == 'deactivated' )
				delete_option( 'edd_slb_paystack_license_status' );

		}
	}

	function edd_slb_paystack_check_license() {

		global $wp_version;

		$license = trim( get_option( 'edd_slb_paystack_license_key' ) );

		$api_params = array(
				'edd_action' => 'check_license',
				'license' => $license,
				'item_name' => urlencode( SLB_PAYSTACK_ITEM_NAME ),
				'url'       => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( SLB_PAYSTACK_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if( $license_data->license == 'valid' ) {
			echo 'valid'; exit;
			// this license is still valid
		} else {
			echo 'invalid'; exit;
			// this license is no longer valid
		}
	}
}
