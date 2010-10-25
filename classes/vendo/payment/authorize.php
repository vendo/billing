<?php

/**
 * Class for processing authorize.net transactions
 *
 * @package    Vendo
 * @author     Jeremy Bush
 * @copyright  (c) 2010 Jeremy Bush
 * @license    http://github.com/zombor/Vendo/raw/master/LICENSE
 */
class Vendo_Payment_Authorize implements Vendo_Payment_Driver
{
	// Gateway url values
	const LIVE_GATEWAY = 'https://secure.authorize.net/gateway/transact.dll';
	const TEST_GATEWAY =
		'https://certification.authorize.net/gateway/transact.dll';

	// The gateway url to process with
	protected $_gateway = null;

	const DEV_GATEWAY_ID = 12345;

	/* Fields required to do a transaction
	 * anything here is required, and if the value is false, it means the value
	 * hasn't been set yet and needs to be before processing
	 */
	private $_required_fields = array
	(
		'x_login'           => false,
		'x_tran_key'        => false,
		'x_version'         => true,
		'x_delim_char'      => true,
		'x_type'            => true,
		'x_method'          => true,
		'x_trans_id'        => true,
		'x_relay_response'  => true,
		'x_card_num'        => false,
		'x_exp_date'        => false,
		'x_card_code'       => false,
		'x_amount'          => false,
		'x_address'         => true,
		'x_city'            => true,
		'x_state'           => true,
		'x_zip'             => true,
	);

	// Default required values
	private $_authnet_values = array
	(
		'x_test_request'    => 'FALSE',

		'x_version'         => '3.1',
		'x_delim_char'      => '|',
		'x_delim_data'      => 'TRUE',
		'x_duplicate_window' => 1,
		'x_type'            => 'AUTH_CAPTURE', 
		'x_method'          => 'CC',
		'x_relay_response'  => 'FALSE',

		'x_login'           => '',
		'x_tran_key'        => '',
		'x_card_num'        => '',
		'x_exp_date'        => '',
		'x_card_code'       => '',
		'x_amount'          => '',
		'x_trans_id'        => 0,
		'x_first_name'      => '',
		'x_last_name'       => '',

		'x_address'         => '',
		'x_city'            => '',
		'x_state'           => '',
		'x_zip'             => '',
		'x_country'         => 'US',
	);

	/**
	 * Magic set method. Used to set special class variables and required
	 * statuses
	 *
	 * @param string $key the key to set
	 * @param mixed  $val the value to set to
	 *
	 * @return void
	 */
	public function __set($key, $val)
	{
		if (array_key_exists('x_'.$key, $this->_authnet_values))
		{
			if ('exp_date' == $key)
				$val = date('mY', $val);

			$this->_authnet_values['x_'.$key] = $val;

			// Satisfy the required attribute if the value is valid
			if ($val AND array_key_exists('x_'.$key, $this->_required_fields))
				$this->_required_fields['x_'.$key] = true;

			// Convert type constants to strings that auth.net understands
			if ('type' == $key)
				$this->process_type();
		}
		else
			$this->$key = $val;
	}

	/**
	 * Constructor. Sets up test mode, login keys, etc.
	 *
	 * @param bool $test_mode set to true to force test mode. false will use
	 *                        config value
	 */
	public function __construct($test_mode = false)
	{
		$this->curl_config = Kohana::config('payment')->default['curl'];

		// Always use the "live" mode of the gateway
		$this->_authnet_values['x_test_request'] = 'FALSE';

		if ( ! $test_mode)
		{
			$test_mode = Kohana::config('payment')->default['test'];
		}

		$key = $test_mode ? 'authorize_test' : 'authorize';

		$config = Kohana::config('payment')->$key;
		$this->login    = $config['login_id'];
		$this->tran_key = $config['trans_key'];
		$this->duplicate_window = $config['duplicate_window'];
		$this->_config  = $config;

		$this->_gateway = $test_mode ? self::TEST_GATEWAY : self::LIVE_GATEWAY;
	}

	/**
	 * Determines if this object is in test mode or not
	 *
	 * @return bool
	 */
	public function test_mode()
	{
		return self::TEST_GATEWAY == $this->_gateway;
	}

	/**
	 * Sets credit card information
	 *
	 * @return null
	 */
	public function set_credit_card(Model_Credit_Card $credit_card)
	{
		$this->card_num = $credit_card->card_number;
		$this->exp_date = $credit_card->exp_date;
		$this->card_code = $credit_card->card_code;
	}

	/**
	 * Sets the transaction amount
	 *
	 * @return null
	 */
	public function set_amount($amount)
	{
		number_format($amount, 2, '.', '');
	}

	/**
	 * Sets the buyer's name
	 *
	 * @return null
	 */
	public function set_name($first_name, $last_name)
	{
		$this->first_name = $first_name;
		$this->last_name = $last_name;
	}

	/**
	 * Sets the buyer's address for AVS
	 *
	 * @return null
	 */
	public function set_address(Model_Address $address)
	{
		$this->address = $address->address1;
		$this->city = $address->city;
		$this->state = $address->state;
		$this->zip = $address->zipcode;
	}

	/**
	 * Processes the the object properties with authorize.net and returns 
	 * a status object
	 *
	 * @return object Payment_Transaction with the transaction details
	 */
	public function process()
	{
		// Check this object for any special rules
		$this->process_values();

		// Check for required fields
		if (in_array(false, $this->_required_fields))
		{
			$fields = array();
			foreach ($this->_required_fields as $key => $field)
			{
				if ( ! $field)
					$fields[] = $key;
			}
			throw new Payment_Exception(
				'Missing required fields in auth.net driver: '.
				implode(',', $fields)
			);
		}

		$fields = '';
		foreach ( $this->_authnet_values as $key => $value )
		{
			$fields .= $key.'='.urlencode($value).'&';
		}

		$ch = curl_init($this->_gateway);

		// Set custom curl options
		curl_setopt_array($ch, $this->curl_config);

		// Set the curl POST fields
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim($fields, '& '));

		//execute post and get results
		$response = curl_exec($ch);

		curl_close($ch);

		if ( ! $response)
		{
			throw new Payment_Exception(
				'Gateway connection error...',
				array(),
				Payment::CONNECTION_ERROR
			);
		}

		// Im passing the $fields
		return $this->process_response($fields, $response);
	}

	/**
	 * Processes the values array for any special rules. This lets us set
	 * special required fields for specific transaction types, etc.
	 *
	 * @return void
	 */
	protected function process_values()
	{
		// We need a trans_id for this kind of transaction
		if (
			'PRIOR_AUTH_CAPTURE' == $this->_authnet_values['x_type']
			AND ! isset($this->_authnet_values['x_trans_id'])
		)
		{
			$this->_required_fields['x_trans_id'] = FALSE;
		}
		elseif (
			isset($this->_authnet_values['x_trans_id'])
			AND ! $this->_authnet_values['x_trans_id']
		)
		{
			unset(
				$this->_authnet_values['x_trans_id'],
				$this->_required_fields['x_trans_id']
			);
		}

		// You need a trans key to run a credit or void
		if (in_array(
				$this->_authnet_values['x_type'],
				array('CREDIT', 'VOID', 'PRIOR_AUTH_CAPTURE')
			)
			AND ! isset($this->_authnet_values['x_trans_id'])
		)
		{
			$this->_required_fields['x_trans_id'] = FALSE;
		}
		if (in_array(
				$this->_authnet_values['x_type'],
				array('CREDIT', 'VOID', 'PRIOR_AUTH_CAPTURE')
			)
			AND ! isset($this->_authnet_values['x_tran_key'])
		)
		{
			$this->_required_fields['x_tran_key'] = FALSE;
		}

		// You need an auth code for a capture_only request
		if ('CAPTURE_ONLY' == $this->_authnet_values['x_type']
			AND ! isset($this->_authnet_values['x_auth_code'])
		)
		{
			$this->_required_fields['x_auth_code'] = FALSE;
		}
	}

	/**
	 * Processes the response from the object and creates a proper
	 * transaction object
	 *
	 * @param array $raw_request  the array sent to authorize.net
	 * @param array $raw_response the array returned by authorize.net
	 *
	 * @return object Payment_Transaction with the transaction details
	 */
	protected function process_response($raw_request, $raw_response)
	{
		$transaction = new Payment_Transaction;
		$response = explode(
			$this->_authnet_values['x_delim_char'],
			$raw_response
		);
		switch ($response[0])
		{
			case 1:
				$response_code = Payment::STATUS_ID_SUCCESSFUL;
				break;
			case 2:
				switch ($response[2])
				{
					case 4:
						$response_code = Payment::STATUS_ID_PICK_UP_CARD;
						break;
					case 37:
						$response_code = Payment::STATUS_ID_INVALID_CARD;
						break;
					case 27:
					case 127:
						$response_code = Payment::STATUS_ID_AVS_MISMATCH;
						break;
					case 65:
						$response_code = Payment::STATUS_ID_CARD_CODE_MISMATCH;
						break;
					default:
						$response_code = Payment::STATUS_ID_DECLINED;
				}
				break;
			case 3:
				switch ($response[2])
				{
					case 8:
						$response_code = Payment::STATUS_ID_EXPIRED_CARD;
						break;
					case 6:
					case 10:
					case 37:
						$response_code = Payment::STATUS_ID_INVALID_CARD;
						break;
					case 11:
						$response_code = Payment::STATUS_ID_DUPLICATE;
						break;
					case 15:
					case 16:
						$response_code = Payment::STATUS_ID_TRANS_ID_INVALID;
						break;
					case 47:
						$response_code = Payment::STATUS_ID_INVALID_SETTLEMENT;
						break;
					case 50:
						$response_code = Payment::STATUS_ID_AWAITING_SETTLEMENT;
						break;
					case 78:
						$response_code = Payment::STATUS_ID_CARD_CODE_MISMATCH;
						break;
					case 128:
						$response_code = Payment::STATUS_ID_INVALID_TRANSACTION;
					default:
						$response_code = Payment::STATUS_ID_UNKNOWN;
						break;
				}
				break;
		}

		$transaction->raw_response_code = $response[0];
		$transaction->raw_reason_code = $response[2];
		$transaction->response_code = $response_code;
		$transaction->reason_text = $response[3];
		$transaction->gateway_reference_id =
			($this->test_mode()) ? self::DEV_GATEWAY_ID : $response[6];

		// Use our value for the total if we are issuing a test request.
		// We must do this because Authorize.net returns 0.00 for test requests.
		$transaction->total =
			$this->_authnet_values['x_test_request'] == 'TRUE'
			? $this->_authnet_values['x_amount']
			: $response[9];

		$transaction->approval = $response[4];
		$transaction->avs_verified = 
			($response[5] == 'X' OR $response[5] == 'Y') ? 'Y' : 'N';
		$transaction->cvv2_verified
			= $response[38] == 'M' ? 'Y' : 'N';

		$transaction->vendor_id = Payment::VENDOR_ID_AUTHORIZE;

		// Calculate and verify the md5 hash for verification
		$md5 = md5(
			$this->_config['md5'].
			$this->_authnet_values['x_login'].
			$response[6].
			number_format($this->_authnet_values['x_amount'], 2)
		);

		if (strtoupper($md5) != $response[37])
		{
			throw new Payment_Exception(
				'MD5 Mismatch!'
			);
		}

		$transaction->md5_verified =
			strtolower($md5) == strtolower($response[37]);

		return $transaction;
	}

	/**
	 * Converts Payment transaction type constants to strings auth.net
	 * can understand
	 *
	 * @return void
	 */
	protected function process_type()
	{
		switch($this->_authnet_values['x_type'])
		{
			case Payment::TYPE_AVS_ONLY_REQUEST:
			case Payment::TYPE_PRE_AUTH_REQUEST:
				$this->_authnet_values['x_type'] = 'AUTH_ONLY';
				break;
			case Payment::TYPE_FORCE_REQUEST:
			case Payment::TYPE_CARD_SALE_REQUEST:
			case Payment::TYPE_ID_BASED_FORCE_REQUEST:
			case Payment::TYPE_ID_BASED_SALE_REQUEST:
				$this->_authnet_values['x_type'] = 'AUTH_CAPTURE';
				break;
			case Payment::TYPE_POST_AUTH_REQUEST:
				$this->_authnet_values['x_type'] = 'PRIOR_AUTH_CAPTURE';
				break;
			case Payment::TYPE_CARD_CREDIT_REQUEST:
			case Payment::TYPE_ID_BASED_CREDIT_REQUEST:
				$this->_authnet_values['x_type'] = 'CREDIT';
				break;
			case Payment::TYPE_ID_BASED_VOID_REQUEST:
			case Payment::TYPE_VOID:
				$this->_authnet_values['x_type'] = 'VOID';
				break;
			case Payment::TYPE_LEGACY:
			case Payment::TYPE_SETTLEMENT_REQUEST:
			case Payment::TYPE_CARD_DISPUTE:
			case Payment::TYPE_CHARGEBACK_REPORT_REQUEST:
				break;
		}
	}
}