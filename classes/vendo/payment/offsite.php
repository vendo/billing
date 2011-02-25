<?php
/**
 * Class for processing payment information offsite for services like
 * 	Google checkout, paypal, etc. Services that redirect to a third party
 * 	website to handle the payment processing
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 */
class Vendo_Payment_Offsite
{
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
		$test_mode = FALSE
	)
	{
		$driver = Kohana::config('payment')->default['driver'];
		$class = 'Payment_Offsite_'.$driver;

		$user = $order->user;
		$driver = new $class($test_mode);

		// Make sure this driver is valid
		if ( ! ($driver instanceof Vendo_Payment_Offsite_Driver))
		{
			throw new Payment_Exception(
				get_class($driver).' is not a valid offsite payment driver!'
			);
		}

		// Build the data
		$driver->set_fields(
			array(
				'order' => $order,
			)
		);

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