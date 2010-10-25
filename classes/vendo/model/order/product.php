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
}