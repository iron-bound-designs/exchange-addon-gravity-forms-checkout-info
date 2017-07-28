<?php
/**
 * Main add-on settings page.
 *
 * @author ExchangeWP
 * @since  1.5
 */

/**
 * Load the plugin settings page.
 *
 * @since 1.5
 */
function it_exchange_gfci_addon_settings() {
	$settings = new IBD_GFCI_Settings();
	$settings->print_settings_page();
}

/**
 * Class IT_Exchange_gfci_Add_On_Settings
 */
class IBD_GFCI_Settings {
	/**
	 * @var boolean $_is_admin true or false
	 * @since 0.1.0
	 */
	var $_is_admin;
	/**
	 * @var string $_current_page Current $_GET['page'] value
	 * @since 0.1.0
	 */
	var $_current_page;
	/**
	 * @var string $_current_add_on Current $_GET['add-on-settings'] value
	 * @since 0.1.0
	 */
	var $_current_add_on;
	/**
	 * @var string $status_message will be displayed if not empty
	 * @since 0.1.0
	 */
	var $status_message;
	/**
	 * @var string $error_message will be displayed if not empty
	 * @since 0.1.0
	 */
	var $error_message;

	/**
	 * Class constructor
	 *
	 * Sets up the class.
	 *
	 * @since 1.0
	 */
	function __construct() {
		$this->_is_admin       = is_admin();
		$this->_current_page   = empty( $_GET['page'] ) ? false : $_GET['page'];
		$this->_current_add_on = empty( $_GET['add-on-settings'] ) ? false : $_GET['add-on-settings'];
		if ( ! empty( $_POST ) && $this->_is_admin && 'it-exchange-addons' == $this->_current_page
		     && 'ibd-gravity-forms-info-product-feature' == $this->_current_add_on
		) {
			add_action( 'it_exchange_save_add_on_settings_gfci', array(
				$this,
				'save_settings'
			) );
			do_action( 'it_exchange_save_add_on_settings_gfci' );
		}
	}

	/**
	 * Prints settings page
	 *
	 * @since 0.4.5
	 * @return void
	 */
	function print_settings_page() {
		$settings     = it_exchange_get_option( 'addon_ibd_gfci', true );
		$form_values  = empty( $this->error_message ) ? $settings : ITForm::get_post_data();
		$form_options = array(
			'id'     => apply_filters( 'it_exchange_add_on_gfci', 'it-exchange-add-on-gfci-settings' ),
			'action' => 'admin.php?page=it-exchange-addons&add-on-settings=ibd-gravity-forms-info-product-feature',
		);

		$form = new ITForm( $form_values, array( 'prefix' => 'ibd_gfci' ) );
		if ( ! empty ( $this->status_message ) ) {
			ITUtility::show_status_message( $this->status_message );
		}

		if ( ! empty( $this->error_message ) ) {
			ITUtility::show_error_message( $this->error_message );
		}
		?>
		<div class="wrap">
			<h2><?php _e( 'Gravity Forms Checkout Info Settings', IBD_GFCI_Plugin::SLUG ); ?></h2>

			<?php do_action( 'it_exchange_gfci_settings_page_top' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_top' ); ?>
			<?php $form->start_form( $form_options, 'it-exchange-gfci-settings' ); ?>
			<?php do_action( 'it_exchange_gfci_settings_form_top' ); ?>
			<?php $this->get_form_table( $form, $form_values ); ?>
			<?php do_action( 'it_exchange_gfci_settings_form_bottom' ); ?>

			<p class="submit">
				<?php $form->add_submit( 'submit', array(
					'value' => __( 'Save Changes', IBD_GFCI_Plugin::SLUG ),
					'class' => 'button button-primary button-large'
				) ); ?>
			</p>

			<?php $form->end_form(); ?>
			<?php do_action( 'it_exchange_gfci_settings_page_bottom' ); ?>
			<?php do_action( 'it_exchange_addon_settings_page_bottom' ); ?>
		</div>
	<?php
	}

	/**
	 * Render the settings table
	 *
	 * @param ITForm $form
	 * @param array  $settings
	 *
	 * @return void
	 */
	function get_form_table( $form, $settings = array() ) {
		if ( ! empty( $settings ) ) {
			foreach ( $settings as $key => $var ) {
				$form->set_option( $key, $var );
			}
		}
		?>

		<div class="it-exchange-addon-settings it-exchange-gfci-addon-settings">
			<h4>License Key</h4>
			<?php
			   $exchangewp_gravityforms_options = get_option( 'it-storage-exchange_addon_ibd_gfci' );
			   $license = $exchangewp_gravityforms_options['gravityforms_license'];
			   // var_dump($license);
			   $exstatus = trim( get_option( 'exchange_gravityforms_license_status' ) );
			   // var_dump($exstatus);
			?>
			<p>
			 <label class="description" for="exchange_gravityforms_license_key"><?php _e('Enter your license key'); ?></label>
			 <!-- <input id="gravityforms_license" name="it-exchange-add-on-gravityforms-gravityforms_license" type="text" value="<?php #esc_attr_e( $license ); ?>" /> -->
			 <?php $form->add_text_box( 'gravityforms_license' ); ?>
			 <span>
			   <?php if( $exstatus !== false && $exstatus == 'valid' ) { ?>
						<span style="color:green;"><?php _e('active'); ?></span>
						<?php wp_nonce_field( 'exchange_gravityforms_nonce', 'exchange_gravityforms_nonce' ); ?>
						<input type="submit" class="button-secondary" name="exchange_gravityforms_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
					<?php } else {
						wp_nonce_field( 'exchange_gravityforms_nonce', 'exchange_gravityforms_nonce' ); ?>
						<input type="submit" class="button-secondary" name="exchange_gravityforms_license_activate" value="<?php _e('Activate License'); ?>"/>
					<?php } ?>
			 </span>
			</p>
			<label>
				<?php $form->add_check_box( 'add-fields-to-admin-email' ); ?>
				<?php _e( "Append the submitted Gravity Form fields to the admin order email notification.", IBD_GFCI_Plugin::SLUG ); ?>
			</label>
		</div>
	<?php
	}

	/**
	 * Save settings
	 *
	 * @since 0.1.0
	 * @return void
	 */
	function save_settings() {
		$defaults   = it_exchange_get_option( 'addon_ibd_gfci' );
		$new_values = wp_parse_args( ITForm::get_post_data(), $defaults );

		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'it-exchange-gfci-settings' ) ) {
			$this->error_message = __( 'Error. Please try again', IBD_GFCI_Plugin::SLUG );

			return;
		}

		$errors = apply_filters( 'it_exchange_add_on_gfci_validate_settings', $this->get_form_errors( $new_values ), $new_values );

		if ( ! $errors && it_exchange_save_option( 'addon_ibd_gfci', $new_values ) ) {
			ITUtility::show_status_message( __( 'Settings saved.', IBD_GFCI_Plugin::SLUG ) );
		} else if ( $errors ) {
			$errors              = implode( '<br />', $errors );
			$this->error_message = $errors;
		} else {
			$this->status_message = __( 'Settings not saved.', IBD_GFCI_Plugin::SLUG );
		}

		if( isset( $_POST['exchange_gravityforms_license_activate'] ) ) {

			// run a quick security check
		 	if( ! check_admin_referer( 'exchange_gravityforms_nonce', 'exchange_gravityforms_nonce' ) )
				return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			// $license = trim( get_option( 'exchange_gravityforms_license_key' ) );
	   $exchangewp_gravityforms_options = get_option( 'it-storage-exchange_addon_ibd_gfci' );
	   $license = trim( $exchangewp_gravityforms_options['gravityforms_license'] );

			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( 'gravity-forms-pro' ), // the name of our product in EDD
				'url'        => home_url()
			);

			// Call the custom API.
			$response = wp_remote_post( 'https://exchangewp.com', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.' );
				}

			} else {

				$license_data = json_decode( wp_remote_retrieve_body( $response ) );

				if ( false === $license_data->success ) {

					switch( $license_data->error ) {

						case 'expired' :

							$message = sprintf(
								__( 'Your license key expired on %s.' ),
								date_i18n( get_option( 'date_format' ), strtotime( $license_data->expires, current_time( 'timestamp' ) ) )
							);
							break;

						case 'revoked' :

							$message = __( 'Your license key has been disabled.' );
							break;

						case 'missing' :

							$message = __( 'Invalid license.' );
							break;

						case 'invalid' :
						case 'site_inactive' :

							$message = __( 'Your license is not active for this URL.' );
							break;

						case 'item_name_mismatch' :

							$message = sprintf( __( 'This appears to be an invalid license key for %s.' ), 'gravityforms' );
							break;

						case 'no_activations_left':

							$message = __( 'Your license key has reached its activation limit.' );
							break;

						default :

							$message = __( 'An error occurred, please try again.' );
							break;
					}

				}

			}

			// Check if anything passed on a message constituting a failure
			if ( ! empty( $message ) ) {
				$base_url = admin_url( 'admin.php?page=' . 'gravityforms-license' );
				$redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

				wp_redirect( $redirect );
				exit();
			}

			//$license_data->license will be either "valid" or "invalid"
			update_option( 'exchange_gravityforms_license_status', $license_data->license );
			// wp_redirect( admin_url( 'admin.php?page=' . 'gravityforms-license' ) );
			exit();
		}

	 // deactivate here
	 // listen for our activate button to be clicked
		if( isset( $_POST['exchange_gravityforms_license_deactivate'] ) ) {

			// run a quick security check
		 	if( ! check_admin_referer( 'exchange_gravityforms_nonce', 'exchange_gravityforms_nonce' ) )
				return; // get out if we didn't click the Activate button

			// retrieve the license from the database
			// $license = trim( get_option( 'exchange_gravityforms_license_key' ) );

	   $exchangewp_gravityforms_options = get_option( 'it-storage-exchange_addon_ibd_gfci' );
	   $license = $exchangewp_gravityforms_options['gravityforms_license'];


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'deactivate_license',
				'license'    => $license,
				'item_name'  => urlencode( 'gravity-forms-pro' ), // the name of our product in EDD
				'url'        => home_url()
			);
			// Call the custom API.
			$response = wp_remote_post( 'https://exchangewp.com', array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.' );
				}

				// $base_url = admin_url( 'admin.php?page=' . 'gravityforms-license' );
				// $redirect = add_query_arg( array( 'sl_activation' => 'false', 'message' => urlencode( $message ) ), $base_url );

				wp_redirect( 'admin.php?page=gravityforms-license' );
				exit();
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			// $license_data->license will be either "deactivated" or "failed"
			if( $license_data->license == 'deactivated' ) {
				delete_option( 'exchange_gravityforms_license_status' );
			}

			// wp_redirect( admin_url( 'admin.php?page=' . 'gravityforms-license' ) );
			exit();

		}

	}

	/**
	* This is a means of catching errors from the activation method above and displaying it to the customer
	*
	* @since 1.2.2
	*/
	function exchange_gravityforms_admin_notices() {
	  if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

	  	switch( $_GET['sl_activation'] ) {

	  		case 'false':
	  			$message = urldecode( $_GET['message'] );
	  			?>
	  			<div class="error">
	  				<p><?php echo $message; ?></p>
	  			</div>
	  			<?php
	  			break;

	  		case 'true':
	  		default:
	  			// Developers can put a custom success message here for when activation is successful if they way.
	  			break;

	  	}
	  }
	}


	/**
	 * Validates for values
	 *
	 * Returns string of errors if anything is invalid
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_form_errors( $values ) {
		$errors = array();

		return $errors;
	}
}
