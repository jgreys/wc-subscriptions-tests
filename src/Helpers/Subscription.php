<?php
/**
 * WooCommerce Subscriptions Helper - Subscription
 *
 * Helper functions for creating and managing subscriptions in tests.
 *
 * @package Greys\WooCommerce\Subscriptions\Tests\Helpers
 * @since   1.0.0
 */

namespace Greys\WooCommerce\Subscriptions\Tests\Helpers;

use WC_Product_Subscription;
use WC_Product_Variable_Subscription;

/**
 * Subscription helper class.
 *
 * @since 1.0.0
 */
class Subscription {

	/**
	 * Create a subscription.
	 *
	 * @param array $args Subscription arguments.
	 * @return \WC_Subscription
	 */
	public static function create_subscription( $args = [] ) {
		$defaults = [
			'status'            => 'active',
			'billing_period'    => 'month',
			'billing_interval'  => 1,
			'start_date'        => current_time( 'mysql' ),
			'customer_id'       => 0,
			'created_via'       => 'unit-test',
		];

		$args = wp_parse_args( $args, $defaults );

		if ( ! class_exists( 'WC_Subscription' ) ) {
			return new \Greys\WooCommerce\Subscriptions\Tests\Mocks\SubscriptionMock( $args );
		}

		$subscription = new \WC_Subscription();
		$subscription->set_status( $args['status'] );
		$subscription->set_billing_period( $args['billing_period'] );
		$subscription->set_billing_interval( $args['billing_interval'] );
		$subscription->set_start_date( $args['start_date'] );

		if ( $args['customer_id'] ) {
			$subscription->set_customer_id( $args['customer_id'] );
		}

		$subscription->set_created_via( $args['created_via'] );

		// Set trial if provided
		if ( isset( $args['trial_end'] ) ) {
			$subscription->set_trial_end_date( $args['trial_end'] );
		}

		// Set next payment if provided
		if ( isset( $args['next_payment'] ) ) {
			$subscription->update_dates( [ 'next_payment' => $args['next_payment'] ] );
		}

		// Set end date if provided
		if ( isset( $args['end_date'] ) ) {
			$subscription->set_end_date( $args['end_date'] );
		}

		$subscription->save();

		return $subscription;
	}

	/**
	 * Create a subscription product.
	 *
	 * @param array $args Product arguments.
	 * @return \WC_Product_Subscription
	 */
	public static function create_subscription_product( $args = [] ) {
		$defaults = [
			'name'              => 'Test Subscription Product',
			'regular_price'     => '10.00',
			'subscription_price' => '',
			'subscription_period' => 'month',
			'subscription_period_interval' => 1,
			'subscription_length' => 0,
		];

		$args = wp_parse_args( $args, $defaults );

		$product = new WC_Product_Subscription();
		$product->set_name( $args['name'] );
		$product->set_regular_price( $args['regular_price'] );

		// Set subscription meta
		$product->update_meta_data( '_subscription_price', $args['subscription_price'] ?: $args['regular_price'] );
		$product->update_meta_data( '_subscription_period', $args['subscription_period'] );
		$product->update_meta_data( '_subscription_period_interval', $args['subscription_period_interval'] );
		$product->update_meta_data( '_subscription_length', $args['subscription_length'] );

		// Set trial if provided
		if ( isset( $args['trial_length'] ) && isset( $args['trial_period'] ) ) {
			$product->update_meta_data( '_subscription_trial_length', $args['trial_length'] );
			$product->update_meta_data( '_subscription_trial_period', $args['trial_period'] );
		}

		// Set sign-up fee if provided
		if ( isset( $args['sign_up_fee'] ) ) {
			$product->update_meta_data( '_subscription_sign_up_fee', $args['sign_up_fee'] );
		}

		$product->save();

		return $product;
	}

	/**
	 * Add a product to a subscription.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @param int              $product_id Product ID.
	 * @param array            $args Item arguments.
	 * @return int Item ID.
	 */
	public static function add_product( $subscription, $product_id, $args = [] ) {
		$defaults = [
			'quantity' => 1,
			'subtotal' => '',
			'total'    => '',
		];

		$args = wp_parse_args( $args, $defaults );

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			return 0;
		}

		$item = new \WC_Order_Item_Product();
		$item->set_product( $product );
		$item->set_quantity( $args['quantity'] );

		$subtotal = $args['subtotal'] ?: $product->get_price();
		$total = $args['total'] ?: $subtotal;

		$item->set_subtotal( $subtotal );
		$item->set_total( $total );

		$subscription->add_item( $item );
		$subscription->calculate_totals();
		$subscription->save();

		return $item->get_id();
	}

	/**
	 * Create a variable subscription product.
	 *
	 * @param array $args Product arguments.
	 * @param array $variations Variation data.
	 * @return \WC_Product_Variable_Subscription
	 */
	public static function create_variable_subscription( $args = [], $variations = [] ) {
		$defaults = [
			'name' => 'Test Variable Subscription',
		];

		$args = wp_parse_args( $args, $defaults );

		$product = new WC_Product_Variable_Subscription();
		$product->set_name( $args['name'] );
		$product->save();

		// Create variations if provided
		foreach ( $variations as $variation_args ) {
			self::create_subscription_variation( $product->get_id(), $variation_args );
		}

		return $product;
	}

	/**
	 * Create a subscription variation.
	 *
	 * @param int   $parent_id Parent product ID.
	 * @param array $args Variation arguments.
	 * @return \WC_Product_Subscription_Variation
	 */
	public static function create_subscription_variation( $parent_id, $args = [] ) {
		$defaults = [
			'regular_price' => '10.00',
			'attributes'    => [],
		];

		$args = wp_parse_args( $args, $defaults );

		$variation = new \WC_Product_Subscription_Variation();
		$variation->set_parent_id( $parent_id );
		$variation->set_regular_price( $args['regular_price'] );
		$variation->set_attributes( $args['attributes'] );

		$variation->save();

		return $variation;
	}

	/**
	 * Update subscription status.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @param string           $new_status New status.
	 * @param string           $note Optional note.
	 * @return void
	 */
	public static function update_status( $subscription, $new_status, $note = '' ) {
		$subscription->update_status( $new_status, $note );
	}

	/**
	 * Process payment for subscription.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return void
	 */
	public static function process_payment( $subscription ) {
		$subscription->payment_complete();
	}
}
