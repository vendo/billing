<?php
/**
 * Class for storing both saved and unsaved user orders (shopping cart)
 *
 * @package   Vendo
 * @author    Jeremy Bush <contractfrombelow@gmail.com>
 * @copyright (c) 2010-2011 Jeremy Bush
 * @license   ISC License http://github.com/zombor/Vendo/raw/master/LICENSE
 */
class Model_Vendo_Order extends AutoModeler_ORM implements Countable
{
	protected $_table_name = 'orders';

	protected $_data = array(
		'id' => '',
		'user_id' => NULL,
		'contact_id' => '',
		'date_created' => '',
		'address_id' => '',
		'paid' => FALSE,
		'order_type_id' => NULL,
	);

	protected $_rules = array(
		'user_id' => array(
			array('numeric'),
		),
		'contact_id' => array(
			array('not_empty'),
			array('numeric'),
		),
		'address_id' => array(
			array('not_empty'),
			array('numeric'),
		),
		'order_type_id' => array(
			array('not_empty'),
			array('numeric'),
		),
	);

	protected $_order_products = array();

	// This is for saved orders
	protected $_has_many = array(
		'order_products'
	);

	public $credit_card;

	const TYPE_CREDIT_CARD = 1;
	const TYPE_GOOGLE_CHECKOUT = 2;

	protected $_ancilary_models = array(
		NULL => 'order_credit_card',
		Model_Order::TYPE_CREDIT_CARD => 'order_credit_card',
		Model_Order::TYPE_GOOGLE_CHECKOUT => 'order_google',
	);

	/**
	 * Overload constructor to set the order products on object load
	 * 
	 * @param int $id the id to load
	 * 
	 * @return null
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if ($this->id)
		{
			$this->_order_products = $this->find_related(
				'order_products'
			);
		}
	}

	/**
	 * Overload __get to return empty address objects
	 * 
	 * @param mixed $key the key to get
	 *
	 * @return mixed
	 */
	public function __get($key)
	{
		if ($key == 'address' AND ! $this->_data['address_id'])
		{
			return new Model_Vendo_Address;
		}
		else if ($key == 'address')
		{
			return new Model_Vendo_Address($this->address_id);
		}

		return parent::__get($key);
	}

	/**
	 * Override save() to set date_created if this is a new object
	 * 
	 * @param object $validation a validation object to save with
	 *
	 * @return int
	 */
	public function save($validation = NULL)
	{
		if ($this->id)
		{
			throw new Kohana_Exception('Saved orders cannot be modified!');
		}

		if ( ! $this->id)
		{
			$this->date_created = time();
		}

		// Save the related items if this is a new item
		if ($status = parent::save($validation))
		{
			foreach ($this->_order_products as $product)
			{
				$order_product = new Model_Order_Product;
				$order_product->order_id   = $this->id;
				$order_product->product_id = $product['product']->id;
				$order_product->quantity   = $product['quantity'];
				$order_product->save();
			}
		}

		return $status;
	}

	/**
	 * Updates the paid status on this order
	 *
	 * @return null
	 */
	public function update_paid_status($paid)
	{
		$this->paid = TRUE;
		return parent::save($validation);
	}

	/**
	 * Overload delete() method to prevent deletion if the order is saved.
	 *
	 * @return mixed
	 */
	public function delete()
	{
		if ($this->id)
		{
			throw new Kohana_Exception('Saved orders cannot be modified!');
		}

		return parent::delete();
	}

	/**
	 * Obtains all orders, optionally with a limit and offset (page)
	 *
	 * @return Database_Result
	 */
	public static function get_orders($limit = NULL, $offset = NULL)
	{
		$query = NULL;

		if ($offset)
		{
			$query = db::select()->offset($offset);
		}
		return Model::factory('order')->load(
			$query,
			$limit
		);
	}

	/**
	 * Adds an item to the user's cart. This does not persist the relationship
	 * in the database
	 * 
	 * @param Model_Vendo_Product $product  the product model to add
	 * @param int                 $quantity the number of items to add
	 * 
	 * @return bool
	 */
	public function add_product(Model_Vendo_Product $product, $quantity = 1)
	{
		if ($this->id)
		{
			throw new Kohana_Exception('Saved orders cannot be modified!');
		}

		if ( ! isset($this->_order_products[$product->id]))
		{
			$this->_order_products[$product->id] = array(
				'product' => $product,
				'quantity' => $quantity,
			);
		}
		else
		{
			$this->_order_products[$product->id]['quantity']+=$quantity;
		}

		return TRUE;
	}

	/**
	 * Empties the cart
	 *
	 * @return null
	 */
	public function destroy()
	{
		if ($this->id)
		{
			throw new Kohana_Exception('Saved orders cannot be modified!');
		}

		$this->_order_products = array();
	}

	/**
	 * Returns all the products for this order
	 *
	 * @return array
	 */
	public function get_products()
	{
		return $this->_order_products;
	}

	/**
	 * Directly modifies the quantity of a product in the order
	 * 
	 * @param Model_Vendo_Product $product      the product to modify
	 * @param int                 $new_quantity the new num to set
	 *
	 * @return null
	 */
	public function modify_quantity(Model_Vendo_Product $product, $new_quantity)
	{
		if ($this->id)
		{
			throw new Kohana_Exception('Saved orders cannot be modified!');
		}

		if (0 == $new_quantity)
		{
			unset($this->_order_products[$product->id]);
		}
		else
		{
			$this->_order_products[$product->id]['quantity'] = $new_quantity;
		}
	}

	// Countable Interface
	/**
	 * Counts the number of items in this order. Saved orders comes from the
	 * database (read-only), and non-saved orders come from $_order_products.
	 *
	 * @return integer
	 */
	public function count()
	{
		$total = 0;

		foreach ($this->_order_products as $product)
		{
			$total+=$product['quantity'];
		}

		return $total;
	}

	/**
	 * Calculates the total price for this cart, rounded to 2 decimal places
	 *
	 * @return float
	 */
	public function amount()
	{
		$total = 0;

		foreach ($this->get_products() as $product)
		{
			$total+=$product['quantity']*$product['product']->price;
		}

		return round($total, 2);
	}

	/**
	 * Retrieves the ancilary model for this order
	 *
	 * @return object ancilary model that relates to this one
	 */
	public function get_ancilary_model()
	{
		$model = Model::factory($this->_ancilary_models[$this->order_type_id]);
		$model->load(db::select()->where('order_id', '=', $this->id));
		return $model;
	}
}