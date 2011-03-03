<?php
/**
 * Ancilary model for google checkout orders
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 */
class Model_Vendo_Order_Google extends AutoModeler_ORM
{
	protected $_table_name = 'order_googles';

	protected $_data = array(
		'id' => '',
		'order_id' => NULL,
		'google_order_id' => '',
	);

	/**
	 * Loads an order model by google order id
	 *
	 * @return Model_Vendo_Order
	 */
	public function by_google_id($order_number)
	{
		$this->load(
			db::select_array(
				array_keys($this->_data)
			)->where('google_order_id', '=', $order_number)
		);

		return $this->order;
	}

	/**
	 * Assigns a google id to this model
	 *
	 * @return null
	 */
	public function update_google_id($google_id)
	{
		$this->google_order_id = $google_id;
		return $this->save();
	}
}