<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SLB_PaymentMethod_Paystack extends SLN_PaymentMethod_Abstract {

	public function getFields() {
		return array(
			'pay_paystack_secret_key',
			'pay_paystack_public_key',
		);
	}

	public function dispatchThankYou(SLN_Shortcode_Salon_ThankyouStep $shortcode, SLN_Wrapper_Booking $booking = null){
		$data = $_REQUEST;
		if ($data['mode'] === $this->getMethodKey()) {
			$op = explode('-', $data['sln_booking_id']);
			$booking_id = $op[0];
			$booking = $this->plugin->createBooking($booking_id);
			if(!isset($data['action']) || $data['action'] === 'cancel'){
				$this->setError( __('Your payment has not been completed', 'salon-booking-system') );
				return $this->getError();
			} elseif($data['action'] === 'ok'){
				$result = $this->verifyTransaction($booking, $data['ref']);

				if ($result) {
					$shortcode->goToThankyou();
				}
				else {
					$this->setError( __('Your payment has not been completed', 'salon-booking-system') );
					return $this->getError();
				}
			} else {
				return $this->getError();
			}
		} else {
			throw new Exception('payment method mode not managed');
		}
	}

	/**
	 * @param SLN_Wrapper_Booking $booking
	 * @param $args
	 */
	private function verifyTransaction($booking, $ref) {
		$sKey = $this->getApiParam('secret_key');
		$args = array(
			'headers' => array(
				'Authorization' => "Bearer {$sKey}"
			)
		);
		$response = wp_remote_get("https://api.paystack.co/transaction/verify/{$ref}", $args);
		$body     = json_decode(wp_remote_retrieve_body($response));

		if (!empty($body) && isset($body->data) && $body->data->status === 'success') {
			$booking->markPaid($ref);

			return true;
		}

		return false;
	}

	public function getApiParam( $param ) {
		return $this->plugin->getSettings()->get( "pay_paystack_$param" );
	}

	public function renderPayButton( $data ) {
		ob_start();

		extract( $data );

		$plugin = $this->plugin;

		include dirname( __FILE__ ) . '/templates/pay.php';

		return ob_get_clean();
	}

	public function renderSettingsFields( $data ) {
		ob_start();

		extract( $data );

		$plugin = $this->plugin;
		include dirname( __FILE__ ) . '/templates/settings.php';

		return ob_get_clean();
	}
}
