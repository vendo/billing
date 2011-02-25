<?php
/**
 * View to render xml data for google checkout
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 */
class View_Payment_Offsite_Google_Checkout extends Kostache
{
	public $items = NULL;

	/**
	 * Returns formatted items array for the xml
	 *
	 * @return array
	 */
	public function items()
	{
		$items = array();

		foreach ($this->items as $item)
		{
			$items[] = array(
				'name' => $item['product']->name,
				'unit_price' => $item['product']->price,
				'quantity' => $item['quantity'],
			);
		}

		return $items;
	}
}