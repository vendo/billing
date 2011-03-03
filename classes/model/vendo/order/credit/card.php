<?php
/**
 * Ancilary model for credit card orders
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 */
class Model_Vendo_Order_Credit_Card extends AutoModeler_ORM
{
	protected $_data = array(
		'id' => '',
		'order_id' => NULL,
	);
}