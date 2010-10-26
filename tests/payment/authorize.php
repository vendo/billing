<?php
/**
 * Tests the order class
 *
 * @package    Vendo
 * @author     Jeremy Bush
 * @copyright  (c) 2010 Jeremy Bush
 * @license    http://github.com/zombor/Vendo/raw/master/LICENSE
 *
 * @group Vendo_Billing
 */
class Payment_Authorize_Test extends PHPUnit_Framework_TestCase
{
	/**
	 * Tests basic payments
	 *
	 * @return null
	 */
	public function test_payments()
	{
		// We can't do anything if the config isn't filled out
		if ( ! Kohana::config('payment.default.authorize_test.login_id') OR
			! Kohana::config('payment.default.authorize_test.trans_key'))
		{
			$this->markTestSkipped();
			return;
		}

		// Force test mode
		$authorize = new Payment_Authorize(true);

		// Make sure it's in test mode
		$this->assertSame(true, $authorize->test_mode());

		$authorize->card_num = '5424000000000015';
		$authorize->exp_date = strtotime('+1 year');
		$authorize->card_code = 626;
		$authorize->amount = 12.34;

		// AVS Info
		$authorize->address = '1234 Main St';
		$authorize->city = 'Nowhere';
		$authorize->state = 'CA';
		$authorize->zip = '90210';

		try
		{
			$result = $authorize->process();
		}
		catch (Payment_Exception $e) // Skip these tests if we can't connect
		{
			if ($e->getCode() == Vendo_Payment::CONNECTION_ERROR)
			{
				$this->markTestSkipped();
				return;
			}

			throw $e;
		}

		$this->assertSame(true, $result instanceof Payment_Transaction);
		$this->assertSame(1, $result->response_code);
		$this->assertSame('12.34', $result->total);
		$this->assertSame(Payment::VENDOR_ID_AUTHORIZE, $result->vendor_id);

		// Force errors to be returned
		$authorize->card_num = '4222222222222';
		$authorize->test_request = 'TRUE';
		$authorize->amount = 4;
		$result = $authorize->process();
		$this->assertSame(8, $result->response_code);

		// Expired Card
		$authorize->amount = 8;
		$result = $authorize->process();
		$this->assertSame(2, $result->response_code);

		$authorize->amount = 10;
		$result = $authorize->process();
		$this->assertSame(4, $result->response_code);

		$authorize->amount = 3;
		$result = $authorize->process();
		$this->assertSame(5, $result->response_code);

		$authorize->amount = 13;
		$result = $authorize->process();
		$this->assertSame(100, $result->response_code);
	}
}