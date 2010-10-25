<?php
/**
 * Credit Card model class. This class is not persistable.
 *
 * @package    Vendo
 * @author     Jeremy Bush
 * @copyright  (c) 2010 Jeremy Bush
 * @license    http://github.com/zombor/Vendo/raw/master/LICENSE
 */
class Vendo_Model_Credit_Card
{
	protected $card_number;

	// Must be set as MMYYYY
	protected $exp_date;
	protected $month;
	protected $year;

	protected $card_code;

	protected $contact;

	protected $address;

	/**
	 * Constructor to set class properties
	 *
	 * @return null
	 */
	public function __construct(
		$card_number,
		$exp_date,
		$card_code,
		Model_Contact $contact,
		Model_Address $address
	)
	{
		$this->card_number = $card_number;
		$this->exp_date = $exp_date;
		$this->card_code = $card_code;
		$this->contact = $contact;
		$this->address = $address;

		$this->month = substr($exp_date, 0, 2);
		$this->year  = substr($exp_date, 2, 4);
	}

	/**
	 * Magic __get method to obtain class properties
	 *
	 * @return mixed
	 */
	public function __get($key)
	{
		if (isset($this->$key))
		{
			return $this->$key;
		}

		throw new Kohana_Exception(
			'Key :key does not exist!',
			array(':key' => $key)
		);
	}

	/**
	 * Runs validation on this object
	 *
	 * @return true on success, error array on failure
	 */
	public function validate()
	{
		$validate = Validate::factory(
			array(
				'credit_card_number' => $this->card_number,
				'credit_card_exp_date' => $this->exp_date,
				'credit_card_code' => $this->card_code,
			)
		)
		->rule('credit_card_number', 'not_empty')
		->rule('credit_card_number', 'credit_card')
		->rule('credit_card_exp_date', 'not_empty')
		->rule('credit_card_exp_date', 'min_length', array(6))
		->rule('credit_card_code', 'not_empty');

		if ( ! $validate->check(TRUE))
		{
			return $validate->errors('form_errors');
		}

		return TRUE;
	}
}