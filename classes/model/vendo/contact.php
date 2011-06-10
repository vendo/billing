<?php
/**
 * Class for storing generic contact information
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 */
class Model_Vendo_Contact extends AutoModeler_ORM
{
	protected $_table_name = 'contacts';

	protected $_data = array(
		'id' => '',
		'email' => '',
		'first_name' => '',
		'last_name' => '',
		'address_id' => '',
	);

	protected $_rules = array(
		'email' => array(
			array('not_empty'),
			array('email'),
		),
		'first_name' => array(
			array('not_empty'),
		)
		'last_name' => array(
			array('not_empty'),
		),
		'address_id' => array(
			array('not_empty'),
			array('numeric'),
		),
	);

	/**
	 * Overload __get to return empty address objects
	 * 
	 * @param mixed $key the key to get
	 *
	 * @return mixed
	 */
	public function __get($key)
	{
		if ($key == 'address' AND ! $this->_data['address_id'])
		{
			return new Model_Vendo_Address;
		}
		else if ($key == 'address')
		{
			return new Model_Vendo_Address($this->address_id);
		}

		return parent::__get($key);
	}
}