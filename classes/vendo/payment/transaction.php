<?php

/**
 * Class for processing authorize.net transactions
 *
 * Class for abstracting payment gateway responses
 * 
 * How to use
 * --------------
 * 
 * This class takes values assigned by payment drivers and abstracts them into
 * a consistant way Vendo can use them.
 * 
 * The payment driver assigns these values, and the Order classes uses the
 * values. This object is *not* persisted.
 * 
 * Properties
 * --------------
 * 
 * This class has the following properties:
 * 
 *  * response_code        - a Payment::STATUS_ID_* constant that represents the
 *                           response from the gateway
 *  * reason_text          - the raw text returned from the gateway for the
 *                           transaction
 *  * gateway_reference_id - the transaction id from the gateway for this
 *                           transaction
 *  * total                - the total amount of this transaction
 *  * approval_code        - the approval code returned by the gateway
 *  * avs_verified         - is this transaction address verified?
 *  * cvv2_verified        - is this transaction cvv2 verified?
 *  * vendor_id            - the gateway vendor this transaction was sent to
 *  * md5_verified         - has this transaction been checked for MitM attacks?
 *
 * @package    Vendo
 * @author     Jeremy Bush
 * @copyright  (c) 2010 Jeremy Bush
 * @license    http://github.com/zombor/Vendo/raw/master/LICENSE
 *
 */
class Payment_Transaction
{
	protected $data = array(
		// The Payment raw response code. See Payment class constants
		'raw_response_code' => '',
		// The Payment raw reason code. See Payment class constants
		'raw_reason_code' => '',
		// The Payment response code. See Payment class constants
		'response_code' => '',
		// The raw reason text reported by the payment gateway
		'reason_text' => '',
		// The transaction id/reference id reported by the gateway
		'gateway_reference_id' => '',
		// The total amount of the transaction
		'total' => '',
		// The approval code reported by the gateway
		'approval' => '',
		// Is this transaction avs verified?
		'avs_verified' => '',
		// Is this transaction cvv2 verified?
		'cvv2_verified' => '',
		// The vendor that gave us this data?
		'vendor_id' => '',
		// Is this transaction valid?
		'md5_verified' => false,
	);

	/**
	 * Magic set method.
	 *
	 * @param string $key the key to set
	 * @param mixed  $val the value to set to
	 *
	 * @return void
	 */
	public function __set($key, $val)
	{
		if (array_key_exists($key, $this->data))
			$this->data[$key] = $val;
	}

	/**
	 * Magic get method.
	 *
	 * @param string $key the key to get
	 *
	 * @return void
	 */
	public function __get($key)
	{
		if (array_key_exists($key, $this->data))
			return $this->data[$key];
	}
}
