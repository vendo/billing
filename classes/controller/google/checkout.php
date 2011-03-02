<?php
/**
 * Controller for receiving api requests from google checkout
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
	public function action_handle()
	{
		$response = Payment_Offsite_Google_Checkout::get_xml_response();

		//If anything was sent then process the xml
		if( ! empty($response))
		{
			// This uses the non SSL "Notification Serial Number" API
			list($key, $serial_number) = explode('=', $response);

			if ('serial-number' == $key)
			{
				// Get the xml
				$processor = new Payment_Offsite_Google_Checkout(
					Kohana::config('payment.default.test')
				);
				$xml = '<notification-history-request xmlns="http://checkout.google.com/schema/2">
				  <serial-number>'.$serial_number.'</serial-number>
				</notification-history-request>';
				$response = arr::get($_REQUEST, 'xml');
				if ( ! $response)
				{
					$response = $processor->send($xml);
				}
				Log::instance()->add(Log::INFO, 'response: '.$response);

				$XMLDocument = new DOMDocument( '1.0', 'utf-8' );
				$XMLDocument->loadXML( utf8_encode( $response ) );

				switch ($XMLDocument->documentElement->tagName)
				{
					case 'new-order-notification':
						// Assign the google order number to our order
						$order_id = $XMLDocument->getElementsByTagName(
							'merchant-private-data'
						)->item(0)->nodeValue;

						$google_id = $XMLDocument->getElementsByTagName(
							'google-order-number'
						)->item(0)->nodeValue;

						$order = new Model_Order_Google($order_id);
						$order->update_google_id($google_id);
						break;
					case 'order-state-change-notification':
						$order_number = $XMLDocument->getElementsByTagName(
							'google-order-number'
						)->item(0)->nodeValue;

						$new_state = $XMLDocument->getElementsByTagName(
							'new-financial-order-state'
						)->item(0)->nodeValue;

						if ('CHARGED' == $new_state)
						{
							// Process the paid order.
							$order = Model::factory(
								'order_google'
							);
							$order->by_google_id($order_number);
							$order->update_paid_status(TRUE);
						}
						break;
				}
			}
		}
		else
		{
			throw new Vendo_404('Invalid Request');
		}
	}
}