<?php
/**
 * Controller for receiving requests from google checkout
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 */
class Controller_Google_Checkout extends Controller
{
	/**
	 * Handles requests from google checkout
	 *
	 * @return null
	 */
	public function handle()
	{
		// This all needs to be improved, it's copied from an old project of mine
		if ( ! isset($GLOBALS['HTTP_RAW_POST_DATA']) OR (isset($_SERVER['PHP_AUTH_USER']) AND $_SERVER['PHP_AUTH_USER'] != Kohana::config('google_checkout.merchant_id')) OR (isset($_SERVER['PHP_AUTH_PW']) AND $_SERVER['PHP_AUTH_PW'] != Kohana::config('google_checkout.merchant_key')))
		{
			throw new Vendo_404;
		}

		$response = $GLOBALS['HTTP_RAW_POST_DATA'];

		//If anything was sent then process the xml
		if( ! empty($response))
		{
			$XMLDocument = new DOMDocument( '1.0', 'utf-8' );
			$XMLDocument->loadXML( utf8_encode( $response ) );

			switch ($XMLDocument->documentElement->tagName)
			{
				case 'new-order-notification':
					// Process the paid order.
					$order_id = $XMLDocument->getElementsByTagName('merchant-private-data')->item(0)->nodeValue;
					$order = new Model_Order($order_id);
					$order->update_paid_status(TRUE);
					break;
			}
		}
	}
}