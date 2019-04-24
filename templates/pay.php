<?php
/** @var SLB_PaymentMethod_Paystack $paymentMethod */
/** @var SLN_Plugin $plugin */
/** @var SLN_Wrapper_Booking $booking */

$ref      = $booking->getUniqueId();

$pKey     = $paymentMethod->getApiParam('public_key');

$deposit  = $booking->getDeposit();
$amount   = number_format((float) ($deposit > 0 ? $deposit : $booking->getAmount()), 2, '.', '');
$amount   = str_replace('.', '', $amount);
$amount   = (float) $amount;

$currency = $plugin->getSettings()->getCurrency();

$email    = $booking->getEmail();


$callback_url = $booking->getPayUrl() . '&mode=' . $paymentMethod->getMethodKey();

$label        = ($deposit > 0 ? sprintf(__('Pay %s as a deposit with %s', 'salon-booking-system'), $plugin->format()->money($deposit), $paymentMethod->getMethodLabel()) :
                       sprintf(__('Pay with %s', 'salon-booking-system'), $paymentMethod->getMethodLabel()));
?>

<form method="post" action="" id="paystack_payment_form">
    <script src="https://js.paystack.co/v1/inline.js"></script>
    <button type="button" class="button-alt" onclick="payWithPaystack()"><?php echo $label; ?></button>
</form>

<script>
    function payWithPaystack(){
        var handler = PaystackPop.setup({
            key: '<?php echo $pKey ?>',
            email: '<?php echo $email; ?>',
            amount: <?php echo $amount; ?>,
            currency: '<?php echo $currency; ?>',
            ref: "<?php echo $ref; ?>",
            callback: function(response){
                var url = '<?php echo $callback_url; ?>' + '&action=ok&ref=' + response.reference;
                jQuery('#paystack_payment_form').attr('action', url).submit();
            },
        });
        handler.openIframe();
    }
</script>
