<?php
/**
 * Model for relating products to orders
 *
 * @package    Vendo
 * @author     Jeremy Bush
 * @copyright  (c) 2010 Jeremy Bush
 * @license    http://github.com/zombor/Vendo/raw/master/LICENSE
 */
class Vendo_Model_Order_Product extends AutoModeler_ORM
{
	protected $_table_name = 'order_products';

	protected $_data = array(
		'id' => '',
		'order_id' => '',
		'product_id' => '',
		'quantity' => '',
	);

	protected $_rules = array(
		'order_id'   => array('not_empty', 'numeric'),
		'product_id' => array('not_empty', 'numeric'),
		'quantity'   => array('not_empty', 'numeric'),
	);

	/**
	 * Override __get() to translate product to vendo_product
	 *
	 * @return mixed
	 */
	public function __get($key)
	{
		if ('product' == $key)
		{
			return db::select_array(AutoModeler::factory('vendo_product')->fields())->from(AutoModeler::factory('vendo_product')->get_table_name())->where('id', '=', $this->_data[$key.'_id'])->as_object('Model_Vendo_Product')->execute($this->_db)->current();
		}

		return parent::__get($key);
	}
}