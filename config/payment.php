<?php
/**
 * Config array for processing payment information
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 */

return array(
	'default' => array(
		'driver' => 'Google_Checkout',
		'test' => TRUE,
	),
	'google_checkout' => array(
		'merchant_key' => '9nMm_15FVH_ZHB3Lxi_8ZQ',
		'merchant_id' => '966660456209745',
		'sandbox_merchant_key' => '9nMm_15FVH_ZHB3Lxi_8ZQ',
		'sandbox_merchant_id' => '966660456209745',
	)
);