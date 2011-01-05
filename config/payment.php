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
		'driver' => 'Authorize',
		'curl' => array(
			CURLOPT_HEADER         => FALSE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_SSL_VERIFYPEER => TRUE,
		),
		'test' => FALSE,
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
		'md5'       => NULL,
		'duplicate_window' => 1,
	),
);