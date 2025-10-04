<?php
/**
 * WooCommerce Subscriptions Helper - Recurring Cart
 *
 * Helper functions for managing recurring cart calculations in tests.
 *
 * @package Greys\WooCommerce\Subscriptions\Tests\Helpers
 * @since   1.0.0
 */

namespace Greys\WooCommerce\Subscriptions\Tests\Helpers;

/**
 * Recurring Cart helper class.
 *
 * @since 1.0.0
 */
class RecurringCart {

	/**
	 * Calculate recurring cart totals.
	 *
	 * @param array $items Cart items.
	 * @return array Calculated totals.
	 */
	public static function calculate_totals( $items = [] ) {
		$subtotal = 0;
		$total = 0;

		foreach ( $items as $item ) {
			$product = wc_get_product( $item['product_id'] );
			if ( ! $product ) {
				continue;
			}

			$quantity = isset( $item['quantity'] ) ? $item['quantity'] : 1;
			$price = $product->get_price();

			$subtotal += $price * $quantity;
			$total += $price * $quantity;
		}

		return [
			'subtotal' => $subtotal,
			'total' => $total,
		];
	}

	/**
	 * Get recurring cart from subscription.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return array Recurring cart data.
	 */
	public static function get_recurring_cart( $subscription ) {
		$cart_data = [];

		foreach ( $subscription->get_items() as $item ) {
			$cart_data[] = [
				'product_id' => $item->get_product_id(),
				'quantity' => $item->get_quantity(),
				'subtotal' => $item->get_subtotal(),
				'total' => $item->get_total(),
			];
		}

		return $cart_data;
	}

	/**
	 * Calculate recurring shipping.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return float Shipping total.
	 */
	public static function calculate_recurring_shipping( $subscription ) {
		$shipping_total = 0;

		foreach ( $subscription->get_shipping_methods() as $shipping_method ) {
			$shipping_total += floatval( $shipping_method->get_total() );
		}

		return $shipping_total;
	}

	/**
	 * Calculate recurring taxes.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return float Tax total.
	 */
	public static function calculate_recurring_taxes( $subscription ) {
		return floatval( $subscription->get_total_tax() );
	}

	/**
	 * Get recurring total.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return float Recurring total.
	 */
	public static function get_recurring_total( $subscription ) {
		return floatval( $subscription->get_total() );
	}
}
