<?php
/**
 * Init file for payment module, add routes
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 */

Route::set(
	'google checkout handler',
	'billing/google-checkout/handle.html'
)->defaults(
	array(
		'controller' => 'google_checkout',
		'action' => 'handle',
	)
);