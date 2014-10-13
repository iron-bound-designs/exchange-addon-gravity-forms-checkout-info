<?php
/**
 *
 * @package Exchange Addon Gravity Forms Checkout Info
 * @subpackage Lib
 * @since 1.0
 */

/**
 * Register the purchase requirement
 */
function ibd_gfci_register_purchase_requirement() {
	$properties = array(
	  'priority'               => 4.5,
	  'requirement-met'        => 'ibd_gfci_all_checkout_gravity_forms_submitted',
	  'sw-template-part'       => 'gravity-forms-checkout-info',
	  'checkout-template-part' => 'gravity-forms-checkout-info',
	  'notification'           => 'We need some more information from you before you can checkout',
	);
	it_exchange_register_purchase_requirement( 'gravity-forms-checkout-info', $properties );
}

add_action( 'init', 'ibd_gfci_register_purchase_requirement' );

/**
 * Register Gravity Forms Scripts
 */
function ibd_gfci_register_scripts() {
  if (it_exchange_is_page()) {
    wp_enqueue_script( 'gform_conditional_logic' );
    wp_enqueue_script( 'gform_gravityforms' );
  }
}
add_action('wp_enqueue_scripts', 'ibd_gfci_register_scripts', 100);

/**
 * Determine if all of the checkout gravity forms have been submitted
 *
 * @return bool
 */
function ibd_gfci_all_checkout_gravity_forms_submitted() {

	$products = it_exchange_get_cart_products();

	foreach ( $products as $product ) {
		if ( !it_exchange_product_has_feature( $product['product_id'], 'ibd-gravity-forms-info' ) )
			continue;

		$forms = it_exchange_get_session_data( 'ibd_gfci_checkout_forms' );

		if ( !isset( $forms[$product['product_id']] ) )
			return false;
	}

	return true;
}

/**
 * Force the gravity forms checkout info as a valid SW state
 * during non multi item cart because the purchase requirements seem to get wiped.
 *
 * @param $valid_states array
 *
 * @return array
 */
function ibd_gfci_force_sw_valid_states_on_multi_item_cart( $valid_states ) {
	if ( ! it_exchange_is_multi_item_cart_allowed() )
		$valid_states[] = 'gravity-forms-checkout-info';

	return $valid_states;
}

add_filter( 'it_exchange_super_widget_valid_states', 'ibd_gfci_force_sw_valid_states_on_multi_item_cart' );

/**
 * Add a hidden field to the gravity form
 * so that we can detect this is a purchase requirement
 * related form during submission
 *
 * @param $form_html string
 * @param $form array
 *
 * @return string
 */
function ibd_gfci_add_hidden_field_to_gravity_form_during_checkout( $form_html, $form ) {
	if ( isset( $GLOBALS['it_exchange']['cart-item']['product_id'] ) )
		$current_product_id = $GLOBALS['it_exchange']['cart-item']['product_id'];
	elseif ( isset( $GLOBALS['it_exchange']['product'] ) )
		$current_product_id = $GLOBALS['it_exchange']['product']->ID;
	else
		return $form_html;

	if ( !it_exchange_product_has_feature( $current_product_id, 'ibd-gravity-forms-info' ) )
		return $form_html;

	$form_html .= "<input type='hidden' name='ibd_gravity_forms_info_product_id' value='$current_product_id'>";

	return $form_html;
}

add_filter( 'gform_form_tag', 'ibd_gfci_add_hidden_field_to_gravity_form_during_checkout', 10, 2 );

/**
 * Save gravity form submission
 *
 * @param $lead array
 * @param $form array
 */
function ibd_gfci_process_purchase_requirement_gravity_form_submission( $lead, $form ) {
	if ( !isset( $_POST['ibd_gravity_forms_info_product_id'] ) )
		return;

	$forms = it_exchange_get_session_data( 'ibd_gfci_checkout_forms' );

	if ( !is_array( $forms ) )
		$forms = array();

	$forms[$_POST['ibd_gravity_forms_info_product_id']] = $lead['id'];

	it_exchange_update_session_data( 'ibd_gfci_checkout_forms', $forms );
}

add_action( 'gform_after_submission', 'ibd_gfci_process_purchase_requirement_gravity_form_submission', 10, 2 );

/**
 * Save our GFCI submission with its product
 * in the exchange transaction object
 *
 * @param $products array
 * @param $key string
 * @param $product array
 *
 * @return object
 */
function ibd_gfci_save_gravity_form_submission_to_transaction_product( $products, $key, $product ) {

	$forms = it_exchange_get_session_data( 'ibd_gfci_checkout_forms' );

	foreach ( $forms as $product_id => $submission )
		if ( $product_id == $product['product_id'] )
			$products[$key]['ibd_gfci_entry_id'] = $submission;

	return $products;
}

add_filter( 'it_exchange_generate_transaction_object_products', 'ibd_gfci_save_gravity_form_submission_to_transaction_product', 10, 3 );

/**
 * Clear our gravity form submission data
 * after the transaction object has been generated
 *
 * @param $transaction_object object
 *
 * @return object
 */
function ibd_gfci_clear_gravity_form_session( $transaction_object ) {
	it_exchange_clear_session_data( 'ibd_gfci_checkout_forms' );

	return $transaction_object;
}

add_filter( 'it_exchange_transaction_object', 'ibd_gfci_clear_gravity_form_session' );

/**
 * Display the gravity forms submission data in the transaction admin panel
 */
function ibd_gfci_display_gravity_forms_submission_data_on_transaction_admin( $post, $transaction_product ) {
	if ( !isset( $transaction_product['ibd_gfci_entry_id'] ) )
		return;

	$lead_id = $transaction_product['ibd_gfci_entry_id'];
	$lead = RGFormsModel::get_lead( $lead_id );
	$form_id = $lead['form_id'];
	$form = RGFormsModel::get_form($form_id);

	$url = "admin.php?page=gf_entries&view=entry&id=$form_id&lid=$lead_id";
	echo "<h4><a href='{$url}'>" . sprintf( __( 'View Gravity Form Checkout Info: %s', IBD_GFCI_Plugin::SLUG ), $form->title ) . "</a></h4>";
}

add_action( 'it_exchange_transaction_details_end_product_details', 'ibd_gfci_display_gravity_forms_submission_data_on_transaction_admin', 10, 2 );

/**
 * Register our template paths
 *
 * @param array $paths existing template paths
 *
 * @return array
 */
function it_exchange_ibd_gfci_addon_add_template_paths( $paths = array() ) {
	$paths[] = IBD_GFCI_Plugin::$dir . "lib/templates";

	return $paths;
}

add_filter( 'it_exchange_possible_template_paths', 'it_exchange_ibd_gfci_addon_add_template_paths' );