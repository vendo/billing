<?php
/**
 * Class for processing payment information
 *
 * @package    Vendo
 * @author     Jeremy Bush
 * @copyright  (c) 2010 Jeremy Bush
 * @license    http://github.com/zombor/Vendo/raw/master/LICENSE
 */
class Payment
{
	const TYPE_AVS_ONLY_REQUEST             = 1;
	const TYPE_PRE_AUTH_REQUEST             = 2;
	const TYPE_CARD_CREDIT_REQUEST          = 3;
	const TYPE_FORCE_REQUEST                = 4;
	const TYPE_LEGACY                       = 5;
	const TYPE_POST_AUTH_REQUEST            = 6;
	const TYPE_CHARGEBACK_REPORT_REQUEST    = 7;
	const TYPE_CARD_SALE_REQUEST            = 8;
	const TYPE_ID_BASED_CREDIT_REQUEST      = 9;
	const TYPE_ID_BASED_FORCE_REQUEST       = 10;
	const TYPE_ID_BASED_VOID_REQUEST        = 11;
	const TYPE_ID_BASED_SALE_REQUEST        = 12;
	const TYPE_SETTLEMENT_REQUEST           = 13;
	const TYPE_CARD_DISPUTE                 = 14;
	const TYPE_VOID                         = 15;

	const VENDOR_ID_AUTHORIZE               = 1;

	const STATUS_ID_INCOMPLETE              = 0;
	const STATUS_ID_SUCCESSFUL              = 1;
	const STATUS_ID_EXPIRED_CARD            = 2;
	const STATUS_ID_NOT_ON_FILE             = 3;
	const STATUS_ID_INVALID_CARD            = 4;
	const STATUS_ID_DECLINED                = 5;
	const STATUS_ID_CALL_AUTH_CENTER        = 6;
	const STATUS_ID_DECLINE_CVV2            = 7;
	const STATUS_ID_PICK_UP_CARD            = 8;
	const STATUS_ID_PLEASE_RETRY            = 9;
	const STATUS_ID_MAX_MONTLY_EXCEEDED     = 10;
	const STATUS_ID_REQUEST_EXCEEDS_BALANCE = 11;
	const STATUS_ID_SEQUENCE_ERROR          = 12;
	const STATUS_ID_AVS_MISMATCH            = 13;
	const STATUS_ID_CARD_CODE_MISMATCH      = 14;
	const STATUS_ID_DUPLICATE               = 15;
	const STATUS_ID_TRANS_ID_INVALID        = 16;
	const STATUS_ID_INVALID_SETTLEMENT      = 17;
	const STATUS_ID_AWAITING_SETTLEMENT     = 18;
	const STATUS_ID_INVALID_TRANSACTION     = 19;
	const STATUS_ID_VOIDED                  = 20;
	const STATUS_ID_UNKNOWN                 = 100;

	// Errors that can occur
	const CONNECTION_ERROR                  = 1;

	/**
	 * Processes an order
	 *
	 * @param object $order            The Model_Order object to process
	 * @param string $transaction_type The transaction type to process
	 * @param bool   $test_mode        set to true to force test mode. false
	 *                                 will use config value. Determines which
	 *                                 gateway to use.
	 * @param bool   $test_flag        set to true to set test request flag
	 *
	 * @return object Payment_Transaction
	 */
	public static function process(
		Model_Order $order,
		$transaction_type = Payment::TYPE_CARD_SALE_REQUEST,
		$test_mode = FALSE,
		$test_flag = FALSE
	)
	{
		$driver = Kohana::config('payment')->default['driver'];
		$class = 'Payment_'.$driver;

		$user = $order->user;
		$driver = new $class($test_mode);

		// Make sure this driver is valid
		if ( ! ($driver instanceof Payment_Driver))
		{
			throw new Payment_Exception(
				$driver.' is not a valid payment driver!'
			);
		}

		$driver->type = $transaction_type;
		if ($test_flag)
		{
			$authorize->test_request = 'TRUE';
		}

		$driver->set_credit_card($order->credit_card);
		$driver->set_amount($order->amount());
		$driver->set_name($user->first_name, $user->last_name);
		$driver->set_address($credit_card->address);

		try
		{
			return $driver->process();
		}
		catch (Payment_Exception $e) // Payment failed
		{
			throw $e;
		}
	}
}
