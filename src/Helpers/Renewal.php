<?php
/**
 * WooCommerce Subscriptions Helper - Renewal
 *
 * Helper functions for creating and managing renewal orders in tests.
 *
 * @package Greys\WooCommerce\Subscriptions\Tests\Helpers
 * @since   1.0.0
 */

namespace Greys\WooCommerce\Subscriptions\Tests\Helpers;

/**
 * Renewal helper class.
 *
 * @since 1.0.0
 */
class Renewal {

	/**
	 * Create a renewal order for a subscription.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @param array            $args Optional renewal order arguments.
	 * @return \WC_Order
	 */
	public static function create_renewal_order( $subscription, $args = [] ) {
		$defaults = [
			'status' => 'pending',
		];

		$args = wp_parse_args( $args, $defaults );

		// Use WCS function if available
		if ( function_exists( 'wcs_create_renewal_order' ) ) {
			$renewal_order = wcs_create_renewal_order( $subscription );
		} else {
			// Manual renewal order creation
			$renewal_order = wc_create_order();

			// Copy items from subscription
			foreach ( $subscription->get_items() as $item ) {
				$renewal_item = new \WC_Order_Item_Product();
				$renewal_item->set_product( $item->get_product() );
				$renewal_item->set_quantity( $item->get_quantity() );
				$renewal_item->set_subtotal( $item->get_subtotal() );
				$renewal_item->set_total( $item->get_total() );

				$renewal_order->add_item( $renewal_item );
			}

			// Set customer
			$renewal_order->set_customer_id( $subscription->get_customer_id() );

			// Link to subscription
			update_post_meta( $renewal_order->get_id(), '_subscription_renewal', $subscription->get_id() );

			// Calculate totals
			$renewal_order->calculate_totals();
		}

		// Set status
		$renewal_order->set_status( $args['status'] );
		$renewal_order->save();

		return $renewal_order;
	}

	/**
	 * Process renewal payment.
	 *
	 * @param \WC_Order $renewal_order Renewal order.
	 * @return void
	 */
	public static function process_renewal_payment( $renewal_order ) {
		$renewal_order->payment_complete();
	}

	/**
	 * Fail renewal payment.
	 *
	 * @param \WC_Order $renewal_order Renewal order.
	 * @param string    $reason Optional failure reason.
	 * @return void
	 */
	public static function fail_renewal_payment( $renewal_order, $reason = '' ) {
		$renewal_order->update_status( 'failed', $reason );
	}

	/**
	 * Get renewal orders for a subscription.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return array Array of renewal order IDs.
	 */
	public static function get_renewal_orders( $subscription ) {
		return $subscription->get_related_orders( 'renewal' );
	}

	/**
	 * Trigger early renewal.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @return \WC_Order|false Renewal order or false on failure.
	 */
	public static function trigger_early_renewal( $subscription ) {
		if ( function_exists( 'wcs_create_renewal_order' ) ) {
			return wcs_create_renewal_order( $subscription );
		}

		return self::create_renewal_order( $subscription );
	}

	/**
	 * Schedule next payment.
	 *
	 * @param \WC_Subscription $subscription Subscription object.
	 * @param string           $date Next payment date.
	 * @return void
	 */
	public static function schedule_next_payment( $subscription, $date ) {
		$subscription->update_dates( [ 'next_payment' => $date ] );

		// Schedule action if using Action Scheduler
		if ( function_exists( 'as_schedule_single_action' ) ) {
			$timestamp = strtotime( $date );
			as_schedule_single_action(
				$timestamp,
				'woocommerce_scheduled_subscription_payment',
				[ 'subscription_id' => $subscription->get_id() ]
			);
		}
	}
}
