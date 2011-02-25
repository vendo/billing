<?php

/**
 * Interface for offsite payment drivers
 * 
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 *
 */
interface Vendo_Payment_Offsite_Driver
{
	/**
	 * Processes the the object properties with the payment gateway.
	 *
	 * @return string the URL to redirect the request to, in order to finish
	 *                payment processing
	 * @throws Payment_Exception if required fields are not set
	 */
	public function process();
}