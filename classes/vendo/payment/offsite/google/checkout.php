<?php

/**
 * Class for processing google checkout transactions.
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 */
class Vendo_Payment_Offsite_Google_Checkout implements Vendo_Payment_Offsite_Driver
{
	// Fields required to do a transaction
	protected $_required_fields = array(
		'order' => FALSE,
		'google_API_key' => FALSE,
		'google_merchant_id' => FALSE,
		'google_sandbox_API_key' => FALSE,
		'google_sandbox_merchant_id' => FALSE,
	);

	protected $_fields = array(
		'order' => NULL,
		'google_API_key' => '',
		'google_merchant_id' => '',
		'google_sandbox_API_key' => '',
		'google_sandbox_merchant_id'=> '',
		'xml_body' => ''
	);

	protected $_test_mode = FALSE;
	protected $_xml_header = '';

	/**
	 * Sets the config for the class.
	 *
	 * @param array config passed from the library
	*/
	public function __construct($test_mode = TRUE)
	{
		$this->set_fields(
			array(
				'google_API_key' => Kohana::config(
					'payment.google_checkout.merchant_key'
				),
				'google_merchant_id' => Kohana::config(
					'payment.google_checkout.merchant_id'
				),
				'google_sandbox_API_key' => Kohana::config(
					'payment.google_checkout.sandbox_merchant_key'
				),
				'google_sandbox_merchant_id' => Kohana::config(
					'payment.google_checkout.sandbox_merchant_id'
				),
			)
		);

		$this->_test_mode = $test_mode;

		if($this->_test_mode)
		{
			$base64encoding = base64_encode(
				$this->_fields['google_sandbox_merchant_id'].
				":".$this->_fields['google_sandbox_API_key']
			);
		}
		else
		{
			$base64encoding = base64_encode(
				$this->_fields['google_merchant_id'].
				":".$this->_fields['google_API_key']
			);
		}

		$this->_xml_header = array(
			"Authorization: Basic ".$base64encoding,
			"Content-Type: application/xml;charset=UTF-8",
			"Accept: application/xml;charset=UTF-8"
		);
	}

	public function set_fields($fields)
	{
		foreach ((array) $fields as $key => $value)
		{
			$this->_fields[$key] = $value;
			if (array_key_exists($key, $this->_required_fields) and ! empty($value))
			{
				$this->_required_fields[$key] = TRUE;
			}
		}
	}

	/**
	 * Used to process any xml requests
	 * 
	 * @return string the URL to redirect to to finish the payment
	*/
	public function process()
	{
		// Check for required fields
		if (in_array(FALSE, $this->_required_fields))
		{
			$fields = array();
			foreach ($this->_required_fields as $key => $field)
			{
				if ( ! $field)
					$fields[] = $key;
			}
			throw new Payment_Exception(
				'Missing required fields in google checkout driver: '.
				implode(',', $fields)
			);
		}

		// Build the XML
		$xml = new View_Payment_Offsite_Google_Checkout;
		$xml->items = $this->_fields['order']->get_products();
		$xml->order_id = $this->_fields['order']->id;
		$this->_fields['xml_body'] = $xml->render();

		$post_url = ($this->_test_mode)
			? 'https://sandbox.google.com/checkout/api/checkout/v2/merchantCheckout/Merchant/'.$this->_fields['google_sandbox_merchant_id'] // Test mode URL
			: 'https://checkout.google.com/api/checkout/v2/merchantCheckout/Merchant/'.$this->_fields['google_merchant_id']; // Live URL

		$ch = curl_init($post_url);

		// Set custom curl options
		curl_setopt($ch, CURLOPT_POST, true);

		// Set the curl POST fields
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_xml_header);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_fields['xml_body']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Execute post and get results
		$response = curl_exec($ch);
		curl_close ($ch);

		if ( ! $response)
		{
			throw new Payment_Exception(
				'Error...',
				array(),
				$response['error_code']
			);
		}

		$xml_response = new SimpleXMLElement($response);
		return (string) $xml_response->{"redirect-url"};
	}

	/**
	 * Sends an arbitrary request to the gateway
	 *
	 * @return SimpleXMLElement
	 */
	public function send($xml)
	{
		unset($this->_required_fields['order']);
		// Check for required fields
		if (in_array(FALSE, $this->_required_fields))
		{
			$fields = array();
			foreach ($this->_required_fields as $key => $field)
			{
				if ( ! $field)
					$fields[] = $key;
			}
			throw new Payment_Exception(
				'Missing required fields in google checkout driver: '.
				implode(',', $fields)
			);
		}

		$post_url = ($this->_test_mode)
			? 'https://sandbox.google.com/checkout/api/checkout/v2/reports/Merchant/'.$this->_fields['google_sandbox_merchant_id'] // Test mode URL
			: 'https://checkout.google.com/api/checkout/v2/reports/Merchant/'.$this->_fields['google_merchant_id']; // Live URL

		$ch = curl_init($post_url);

		// Set custom curl options
		curl_setopt($ch, CURLOPT_POST, true);

		// Set the curl POST fields
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_xml_header);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Execute post and get results
		$response = curl_exec($ch);
		curl_close ($ch);

		if ( ! $response)
		{
			throw new Payment_Exception(
				'Error...',
				array(),
				$response['error_code']
			);
		}

		return $response;
	}

	/**
	 * used to get any xml response
	 * 
	 * @return (string) xml string
	 * @param $post_data string recieved from $HTTP_RAW_POST_DATA
	*/
	public static function get_xml_response()
	{
		return file_get_contents("php://input");
	}
}