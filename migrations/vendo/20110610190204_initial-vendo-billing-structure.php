<?php defined('SYSPATH') or die('No direct script access.');

/**
 * initial vendo billing structure
 */
class Migration_Vendo_20110610190204 extends Minion_Migration_Base {

	/**
	 * Run queries needed to apply this migration
	 *
	 * @param Kohana_Database Database connection
	 */
	public function up(Kohana_Database $db)
	{
		$db->query(NULL, 'CREATE TABLE `contacts` (
		  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  `email` varchar(50) NOT NULL,
		  `first_name` varchar(50) NOT NULL,
		  `last_name` varchar(50) NOT NULL,
		  `address_id` bigint(20) unsigned DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

		$db->query(NULL, 'CREATE TABLE `orders` (
		  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  `user_id` bigint(20) unsigned DEFAULT NULL,
		  `date_created` bigint(20) unsigned NOT NULL,
		  `address_id` bigint(20) unsigned DEFAULT NULL,
		  `contact_id` bigint(20) unsigned DEFAULT NULL,
		  `paid` tinyint(3) unsigned NOT NULL,
		  `order_type_id` smallint(5) unsigned DEFAULT NULL,
		  PRIMARY KEY (`id`),
		  KEY `fk_order_user` (`user_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

		$db->query(NULL, 'CREATE TABLE `order_credit_cards` (
		  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  `order_id` bigint(20) unsigned NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `order_id` (`order_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1');

		$db->query(NULL, 'CREATE TABLE `order_googles` (
		  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  `order_id` bigint(20) unsigned NOT NULL,
		  `google_order_id` bigint(20) unsigned NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `order_id` (`order_id`),
		  KEY `google_order_id` (`google_order_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1');

		$db->query(NULL, 'CREATE TABLE `order_products` (
		  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		  `order_id` bigint(20) unsigned NOT NULL,
		  `product_id` bigint(20) unsigned NOT NULL,
		  `quantity` mediumint(9) NOT NULL,
		  PRIMARY KEY (`id`),
		  KEY `fk_order_products_order` (`order_id`),
		  KEY `fk_order_products_product` (`product_id`)
		) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');

		$db->query(NULL, 'ALTER TABLE `orders`
		  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE');

		$db->query(NULL, 'ALTER TABLE `order_credit_cards`
		  ADD CONSTRAINT `order_credit_cards_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE');

		$db->query(NULL, 'ALTER TABLE `order_googles`
		  ADD CONSTRAINT `order_googles_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE');

		$db->query(NULL, 'ALTER TABLE `order_products`
		  ADD CONSTRAINT `fk_order_products_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
		  ADD CONSTRAINT `fk_order_products_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE');
	}

	/**
	 * Run queries needed to remove this migration
	 *
	 * @param Kohana_Database Database connection
	 */
	public function down(Kohana_Database $db)
	{
		$db->query(
			NULL, 'SET foreign_key_checks = 0'
		);

		$db->query(NULL, 'DROP TABLE `contacts`');
		$db->query(NULL, 'DROP TABLE `orders`');
		$db->query(NULL, 'DROP TABLE `order_googles`');
		$db->query(NULL, 'DROP TABLE `order_products`');
		$db->query(NULL, 'DROP TABLE `order_credit_cards`');

		$db->query(
			NULL, 'SET foreign_key_checks = 1'
		);
	}
}
