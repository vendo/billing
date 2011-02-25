<?php

/**
 * Class for processing google checkout transactions.
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 */
class Vendo_Payment_Offsite_Google_Checkout implements Payment_Offsite_Driver
{
	// Fields required to do a transaction
	private $required_fields = array(
		'xml_body' => FALSE,
		'products' => FALSE,
	);

	private $fields = array(
		'products' => array(),
		'google_API_key' => '',
		'google_merchant_id' => '',
		'google_sandbox_API_key' => '',
		'google_sandbox_merchant_id'=> '',
		'xml_body' => ''
	);

	private $test_mode = FALSE;

	/**
	 * Sets the config for the class.
	 *
	 * @param array config passed from the library
	*/
	public function __construct($test_mode = TRUE)
	{
		$this->fields['google_API_key'] = Kohana::config('google_checkout.merchant_key');
		$this->fields['google_merchant_id'] = Kohana::config('google_checkout.merchant_id');
		$this->fields['google_sandbox_API_key'] = '';
		$this->fields['google_sandbox_merchant_id'] = '';

		$this->curl_config = array(CURLOPT_HEADER         => FALSE,
			                       CURLOPT_RETURNTRANSFER => TRUE,
			                       CURLOPT_SSL_VERIFYPEER => FALSE);
		$this->test_mode = $test_mode;

		if($this->test_mode)
		{
			$base64encoding = base64_encode(
				$this->fields['google_sandbox_merchant_id'].
				":".$this->fields['google_sandbox_API_key']
			);
		}
		else
		{
			$base64encoding = base64_encode(
				$this->fields['google_merchant_id'].
				":".$this->fields['google_API_key']
			);
		}

		$this->xml_header = array(
			"Authorization: Basic ".$base64encoding,
			"Content-Type: application/xml;charset=UTF-8",
			"Accept: application/xml;charset=UTF-8"
		);
	}

	public function set_fields($fields)
	{
		foreach ((array) $fields as $key => $value)
		{
			$this->fields[$key] = $value;
			if (array_key_exists($key, $this->required_fields) and ! empty($value))
			{
				$this->required_fields[$key] = TRUE;
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
		$xml->items = $this->fields['products'];
		$this->xml_body = $xml->render();

		$post_url = ($this->test_mode)
			? 'https://sandbox.google.com/checkout/api/checkout/v2/merchantCheckout/Merchant/'.$this->fields['google_sandbox_merchant_id'] // Test mode URL
			: 'https://checkout.google.com/api/checkout/v2/merchantCheckout/Merchant/'.$this->fields['google_merchant_id']; // Live URL

		$ch = curl_init($post_url);

		// Set custom curl options
		curl_setopt($ch, CURLOPT_POST, true);

		// Set the curl POST fields
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->xml_header);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->fields['xml_body']);
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
		return $xml_response->{"redirect-url"};
	}

	/**
	 * used to get any xml response
	 * 
	 * @return (string) xml string
	 * @param $post_data string recieved from $HTTP_RAW_POST_DATA
	*/
	public function get_xml_response($post_data)
	{
		return isset($post_data) ? $post_data : file_get_contents("php://input");
	}
}