<?php
/**
 * Tests the order class
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 *
 * @group Vendo_Billing
 */
class Model_Order_Test extends Vendo_TestCase
{
	/**
	 * Tests to ensure that a new model's properties are empty, including it's
	 * cart contents
	 * 
	 * @return null
	 */
	public function test_new_order_is_empty()
	{
		$order = new Model_Order;
		$this->assertEquals(0, count($order));
	}

	/**
	 * Tests to ensure an added product is readable from the order
	 *
	 * @return null
	 */
	public function test_add_read_product()
	{
		// Create a new product first
		$product = new Model_Vendo_Product;
		$product->set_fields(
			array(
				'name'        => 'Unit Test',
				'price'       => '9.99',
				'description' => 'This is a unit test.',
				'order'       => '1',
			)
		);
		$product->save();
		$order = new Model_Order;
		$order->add_product($product, 1);

		$stored_product = current($order->get_products());
		$this->assertEquals($product->id, $stored_product['product']->id);
		$this->assertEquals(1, $stored_product['quantity']);

		$product->delete();
	}

	/**
	 * Tests to ensure an a quantity adjustment works
	 *
	 * @return null
	 */
	public function test_modify_quantity()
	{
		// Create a new product first
		$product = new Model_Vendo_Product;
		$product->set_fields(
			array(
				'name'        => 'Unit Test',
				'price'       => '9.99',
				'description' => 'This is a unit test.',
				'order'       => '1',
			)
		);
		$product->save();
		$order = new Model_Order;
		$order->add_product($product, 1);

		$this->assertEquals(1, count($order));

		$order->modify_quantity($product, 5);

		$this->assertEquals(5, count($order));

		$product->delete();
	}

	/**
	 * Tests to ensure saved carts have a proper save date on them
	 *
	 * @return null
	 */
	public function test_new_order_has_create_date()
	{
		$order = new Model_Order;
		$order->user_id = self::$user->id;
		$order->address_id = self::$user->address_id;
		$order->contact_id = self::$contact->id;
		$order->save();
		$this->assertEquals(time(), $order->date_created);
		$order->delete();
	}

	/**
	 * Tests to ensure a saved order can retrive it's products
	 *
	 * @return null
	 */
	public function test_saved_order_can_retrive_products()
	{
		// Create a new product first
		$product = new Model_Vendo_Product;
		$product->set_fields(
			array(
				'name'        => 'Unit Test',
				'price'       => '9.99',
				'description' => 'This is a unit test.',
				'order'       => '1',
			)
		);
		$product->save();
		$order = new Model_Order;
		$order->user_id = self::$user->id;
		$order->address_id = self::$user->address_id;
		$order->contact_id = self::$contact->id;
		$order->add_product($product, 2);

		$order->save();

		$saved_order = new Model_Order($order->id);
		$this->assertEquals(2, count($saved_order));

		$saved_product = $saved_order->get_products();
		$saved_product = $saved_product[0];

		$this->assertEquals($product->id, $saved_product->product->id);
		$this->assertEquals(2, $saved_product->quantity);

		$product->delete();
		$order->delete();
	}

	/**
	 * Tests to ensure that a saved order cannot have new products added to it
	 *
	 * @return null
	 */
	public function test_saved_order_is_fixed()
	{
		// Create a new product first
		$product = new Model_Vendo_Product;
		$product->set_fields(
			array(
				'name'        => 'Unit Test',
				'price'       => '9.99',
				'description' => 'This is a unit test.',
				'order'       => '1',
			)
		);
		$product->save();
		$order = new Model_Order;
		$order->user_id = self::$user->id;
		$order->address_id = self::$user->address_id;
		$order->contact_id = self::$contact->id;
		$order->add_product($product, 1);

		$order->save();

		try
		{
			$order->add_product($product, 1);
		}
		catch (Exception $e)
		{
			$this->assertEquals(
				'Saved orders cannot be modified!',
				$e->getMessage()
			);
			$order->delete();
			$product->delete();
			return;
		}

		$order->delete();
		$product->delete();
		$this->fail('Orders cannot be modified once saved.');
	}
}