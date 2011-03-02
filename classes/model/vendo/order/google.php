<?php
/**
 * Extended order model to hold a google order ID
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 */
class Model_Vendo_Order_Google extends Model_Vendo_Order
{
	protected $_data = array(
		'id' => '',
		'user_id' => NULL,
		'contact_id' => '',
		'date_created' => '',
		'address_id' => '',
		'paid' => FALSE,
		'google_order_id' => '',
	);

	/**
	 * Loads this model by google order id
	 *
	 * @return Model_Vendo_Order_Google
	 */
	public function by_google_id($order_number)
	{
		$this->load(
			db::select_array(
				array_keys($this->_data)
			)->where('google_order_id', '=', $order_number)
		);
	}

	/**
	 * Assigns a google id to this model
	 *
	 * @return null
	 */
	public function update_google_id($google_id)
	{
		DB::update('orders')->set(
			array('google_order_id' => $google_id)
		)->where('id', '=', $this->id)->execute($this->db);
	}
}