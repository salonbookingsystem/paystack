<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="col-xs-12"><h2 class="sln-box-title"><?php _e('Paystack account informations','salon-booking-system');?></h2></div>

<div class="col-xs-12">
	<div class="col-xs-12 col-sm-4 sln-input--simple">
		<?php $adminSettings->row_input_text('pay_paystack_secret_key', __('Secret key', 'salon-booking-system')); ?>
		<p class="sln-input-help"><?php _e('-','salon-booking-system');?></p>
	</div>
	<div class="col-xs-12 col-sm-4 sln-input--simple">
		<?php $adminSettings->row_input_text('pay_paystack_public_key', __('Public key', 'salon-booking-system')); ?>
		<p class="sln-input-help"><?php _e('-','salon-booking-system');?></p>
	</div>
	<div class="col-xs-12 col-sm-4 sln-box-maininfo align-top">
		<p class="sln-input-help"><?php _e('To use this payment method you need an account with Paystack.','salon-booking-system');?></p>
	</div>
</div>