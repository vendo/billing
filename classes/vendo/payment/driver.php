<?php

/**
 * Interface for defining payment drivers
 * 
 * How to write drivers
 * --------------
 * 
 * Payment drivers should not be used independantly, but it is possible.
 * 
 * Drivers should have a set of required fields which it checks in the process
 * method. If not all required fields are met, the method should throw a
 * Payment_Exception.
 * 
 * Drivers should also have a method to convert Payment type constants into
 * values that the gateway can understand. This method should be ran in the
 * __set() method when the transaction type is set. This method is not defined
 * in this interface because the method should be protected.
 * 
 * Driver fields
 * ----------------
 * 
 * Drivers should always define/translate the following fields:
 * 
 *  * type       - the transaction type. Should be assigned as Payment::TYPE_*
 *  * card_num   - the credit card number
 *  * exp_date   - the expiration date of the card. Should be set as unix time
 *  * card_code  - the cvv2 code of the card
 *  * amount     - the amount to process
 *  * verified   - is the card cvv2 verified?
 *  * first_name - first name of the card holder
 *  * last_name  - last name of the card holder
 *  * address    - address of the card holder
 *  * city       - city of the card holder
 *  * state      - state of the card holder
 *  * zip        - zip of the card holder
 * 
 * Drivers are allowed to define additional fields, but they should never be
 * required. Also keep in mind that some fields will only be required based on
 * the state of other fields. For example, card_code should only be required
 * if the verified flag is set to FALSE. The driver itself needs to define this.
 * 
 * Processing responses
 * --------------
 * 
 * All drivers should have a method that converts the gateway's response into
 * values that Vendo can understand (Payment::STATUS_ID_*)
 * 
 * Using a driver
 * ---------------
 * 
 * While drivers should not be used independantly, they can be if required.
 * 
 * Simply instantiate the specific object:
 * 
 * $auth_net = new Payment_Authorize;
 * 
 * assign properties:
 * 
 * $auth_net->card_num = '5454545454545454';  
 * $auth_net->exp = date();
 * 
 * and process the object:
 * 
 * $result = $auth_net->process();
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 *
 */
interface Vendo_Payment_Driver
{
	/**
	 * Determines if this object is in test mode or not
	 *
	 * @return bool
	 */
	public function test_mode();

	/**
	 * Processes the the object properties with the payment gateway and returns 
	 * a status object
	 *
	 * @return object Payment_Transaction with the transaction details
	 * @throws Payment_Exception if required fields are not set
	 */
	public function process();

	/**
	 * Sets the credit card
	 * 
	 * @param Model_Credit_Card $card the card object to set
	 *
	 * @return null
	 */
	public function set_credit_card(Model_Credit_Card $card);

	/**
	 * Sets the transaction amount
	 * 
	 * @param float $amount the amount to set
	 *
	 * @return null
	 */
	public function set_amount($amount);

	/**
	 * Sets the buyer's name
	 * 
	 * @param string $first the first name to set
	 * @param string $last  the last name to set
	 *
	 * @return null
	 */
	public function set_name($first, $last);

	/**
	 * Sets the buyer's address
	 * 
	 * @param Model_Vendo_Address $address the address model to set
	 *
	 * @return null
	 */
	public function set_address(Model_Vendo_Address $address);
}