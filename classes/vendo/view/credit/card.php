<?php
/**
 * View class to render a form element for credit cards
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 */
abstract class Vendo_View_Credit_Card extends View_Layout
{
	// We only extend layout so we can inherit the methods
	public $render_layout = FALSE;

	protected $_partials = array(
		'months'           => 'dates/months',
		'years'            => 'dates/years',
	);

	// Holder variable for submitted credit card information
	public $credit_card;

	/**
	 * Var method overriding years() to pre-select a year
	 * 
	 * @param int $year the year to select
	 *
	 * @return array
	 */
	public function years($year = NULL)
	{
		return parent::years(
			isset($this->credit_card) ? $this->credit_card->year : NULL
		);
	}

	/**
	 * Var method overriding years() to pre-select a year
	 * 
	 * @param int $month the month to select
	 *
	 * @return array
	 */
	public function months($month = NULL)
	{
		return parent::months(
			isset($this->credit_card) ? $this->credit_card->month : NULL
		);
	}
}