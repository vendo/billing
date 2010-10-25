<?php
/**
 * Config array for processing payment information
 *
 * @package    Vendo
 * @author     Jeremy Bush
 * @copyright  (c) 2010 Jeremy Bush
 * @license    http://github.com/zombor/Vendo/raw/master/LICENSE
 */

return array(
	'default' => array(
		'driver' => 'Authorize',
		'curl' => array(
			CURLOPT_HEADER         => false,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => true,
		),
		'test' => false,
	),
	'authorize' => array(
		'login_id'  => '',
		'trans_key' => '',
		'md5'       => '',
		'duplicate_window' => 1,
	),
	'authorize_test' => array(
		'login_id'  => '',
		'trans_key' => '',
		'md5'       => null,
		'duplicate_window' => 1,
	),
);