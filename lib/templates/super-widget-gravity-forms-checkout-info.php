<?php
/**
 * The gravity forms checkout info template for the Super Widget.
 *
 * @since 1.3.0
 * @version 1.3.0
 * @link http://ithemes.com/codex/page/Exchange_Template_Updates
 * @package IT_Exchange
 *
 * WARNING: Do not edit this file directly. To use
 * this template in a theme, simply copy over this
 * file's content to the exchange directory located
 * at your templates root.
 */

$id = array_pop( it_exchange_get_cart_products() );
$id = $id['product_id'];

if (empty($id)) {
	?>
	<script>
		location.reload();
	</script>
	<?php
}

if ( !it_exchange_product_has_feature( $id, 'ibd-gravity-forms-info' ) || isset( $session_data[$id] ) )
	return;

?>

<script>
jQuery( document ).on( 'click', '.it-exchange-super-widget a.it-exchange-gravity-forms-checkout-info-form-cancel', function ( event ) {
	event.preventDefault();
	if ( itExchangeSWMultiItemCart )
		if ( itExchangeSWOnProductPage )
			itExchangeGetSuperWidgetState( 'product', itExchangeSWOnProductPage );
		else
			itExchangeGetSuperWidgetState( 'cart' );
	else
		itExchangeSWEmptyCart( itExchangeSWOnProductPage );
} );

jQuery( document ).bind( 'gform_confirmation_loaded', function ( event, form_id ) {
	itExchangeGetSuperWidgetState( 'checkout' );
} );

function gformInitSpinner( formId, spinnerUrl ) {

	if ( typeof spinnerUrl == 'undefined' || !spinnerUrl )
		spinnerUrl = gform.applyFilters( "gform_spinner_url", gf_global.spinnerUrl, formId );

	jQuery( '#gform_' + formId ).submit( function () {
		if ( jQuery( '#gform_ajax_spinner_'.formId ).length == 0 ) {
			jQuery( '#gform_submit_button_' + formId + ', #gform_wrapper_' + formId + ' .gform_next_button, #gform_wrapper_' + formId + ' .gform_image_button' )
			  .after( '<img id="gform_ajax_spinner_' + formId + '"  class="gform_ajax_spinner" src="' + spinnerUrl + '" alt="" />' );
		}
	} );

}

</script>

<style>
	.gravity-forms-checkout-info {
		padding: 1em;
	}

	.gravity-forms-checkout-info ul {
		list-style-type: none;
		margin-left:     0;
	}

	.it-exchange-gravity-forms-checkout-info-form {
		padding: 10px 0 10px 0;
	}
</style>

<?php do_action( 'it_exchange_super_widget_gravity_forms_checkout_info_before_wrap' ); ?>
<div class="gravity-forms-checkout-info it-exchange-sw-processing" style="padding: 1em;">
	<?php do_action( 'it_exchange_super_widget_gravity_forms_checkout_info_begin_wrap' ); ?>
	<?php it_exchange_get_template_part( 'messages' ); ?>

	<div class="it-exchange-gravity-forms-checkout-info-form">
		<?php do_action( 'it_exchange_super_widget_gravity_forms_checkout_info_purchase_requirement_before_form' ); ?>
		<?php gravity_form( it_exchange_get_product_feature( $id, 'ibd-gravity-forms-info', array( 'field' => 'form_id' ) ), $display_title = true, $display_description = true, $display_inactive = false, $field_values = null, $ajax = true ); ?>
		<?php do_action( 'it_exchange_super_widget_gravity_forms_checkout_info_purchase_requirement_after_form' ); ?>
	</div>

	<div class="it-exchange-cancel-element">
		<a href="#" class="it-exchange-gravity-forms-checkout-info-form-cancel"><?php _e( 'Cancel', 'it-l10n-ithemes-exchange' ); ?></a>
	</div>

	<?php do_action( 'it_exchange_super_widget_gravity_forms_checkout_info_end_wrap' ); ?>
</div>
<?php do_action( 'it_exchange_super_widget_gravity_forms_checkout_info_after_wrap' ); ?>
