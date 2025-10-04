<?php
/**
 * WooCommerce Subscriptions Unit Test Case
 *
 * Provides a base test case for WooCommerce Subscriptions extensions.
 *
 * @package Greys\WooCommerce\Subscriptions\Tests
 * @since   1.0.0
 */

namespace Greys\WooCommerce\Subscriptions\Tests;

use WC_Unit_Test_Case;

/**
 * WCS Unit Test Case class.
 *
 * @since 1.0.0
 */
class UnitTestCase extends WC_Unit_Test_Case {

	use Traits\Assertions;

	/**
	 * Subscription IDs created during tests.
	 *
	 * @var array
	 */
	protected $subscription_ids = [];

	/**
	 * Renewal order IDs created during tests.
	 *
	 * @var array
	 */
	protected $renewal_order_ids = [];

	/**
	 * Set up test fixtures.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		// Clear any scheduled subscription events.
		$this->clear_scheduled_subscription_events();

		// Reset subscription IDs.
		$this->subscription_ids  = [];
		$this->renewal_order_ids = [];

		// Load WC Subscription mock if not available.
		if ( ! class_exists( 'WC_Subscription' ) ) {
			require_once dirname( __FILE__ ) . '/Mocks/SubscriptionMock.php';
		}

		// Define subscription constants if not already defined.
		if ( ! defined( 'WCS_INIT_TIMESTAMP' ) ) {
			define( 'WCS_INIT_TIMESTAMP', time() );
		}
	}

	/**
	 * Tear down test fixtures.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function tearDown(): void {
		// Clean up subscriptions.
		foreach ( $this->subscription_ids as $subscription_id ) {
			wp_delete_post( $subscription_id, true );
		}

		// Clean up renewal orders.
		foreach ( $this->renewal_order_ids as $order_id ) {
			wp_delete_post( $order_id, true );
		}

		// Clear scheduled events.
		$this->clear_scheduled_subscription_events();

		parent::tearDown();
	}

	/**
	 * Clear all scheduled subscription events.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	protected function clear_scheduled_subscription_events() {
		$hooks = [
			'woocommerce_scheduled_subscription_trial_end',
			'woocommerce_scheduled_subscription_payment',
			'woocommerce_scheduled_subscription_expiration',
			'woocommerce_scheduled_subscription_end_of_prepaid_term',
			'woocommerce_subscription_payment_retry',
		];

		foreach ( $hooks as $hook ) {
			as_unschedule_all_actions( $hook );
		}
	}

	/**
	 * Get a mock subscription object.
	 *
	 * @since 1.0.0
	 * @param array $args Subscription arguments.
	 * @return \WC_Subscription|object
	 */
	protected function get_mock_subscription( $args = [] ) {
		if ( class_exists( 'WC_Subscription' ) ) {
			return new \WC_Subscription();
		}

		// Return mock if WC Subscriptions not available.
		return new Mocks\SubscriptionMock( $args );
	}

	/**
	 * Assert that a subscription has a specific status.
	 *
	 * @since 1.0.0
	 * @param string $expected_status Expected subscription status.
	 * @param object $subscription    Subscription object.
	 * @param string $message         Optional. Message to display on failure.
	 * @return void
	 */
	public function assertSubscriptionStatus( $expected_status, $subscription, $message = '' ) {
		$this->assertEquals(
			$expected_status,
			$subscription->get_status(),
			$message ?: "Failed asserting that subscription has status '{$expected_status}'."
		);
	}

	/**
	 * Assert that a subscription contains a specific product.
	 *
	 * @since 1.0.0
	 * @param int    $product_id   Product ID.
	 * @param object $subscription Subscription object.
	 * @param string $message      Optional. Message to display on failure.
	 * @return void
	 */
	public function assertSubscriptionContainsProduct( $product_id, $subscription, $message = '' ) {
		$has_product = false;
		foreach ( $subscription->get_items() as $item ) {
			if ( $item->get_product_id() === $product_id ) {
				$has_product = true;
				break;
			}
		}

		$this->assertTrue(
			$has_product,
			$message ?: "Failed asserting that subscription contains product ID {$product_id}."
		);
	}

	/**
	 * Assert that a renewal order was created.
	 *
	 * @since 1.0.0
	 * @param object $subscription Subscription object.
	 * @param string $message      Optional. Message to display on failure.
	 * @return void
	 */
	public function assertRenewalOrderCreated( $subscription, $message = '' ) {
		$renewal_orders = $subscription->get_related_orders( 'renewal' );
		$this->assertNotEmpty(
			$renewal_orders,
			$message ?: 'Failed asserting that a renewal order was created.'
		);
	}

	/**
	 * Mock a subscription date.
	 *
	 * @since 1.0.0
	 * @param object $subscription Subscription object.
	 * @param string $date_type    Date type (e.g., 'next_payment', 'trial_end').
	 * @param string $date         Date string.
	 * @return void
	 */
	protected function mock_subscription_date( $subscription, $date_type, $date ) {
		update_post_meta( $subscription->get_id(), '_schedule_' . $date_type, $date );
		$subscription->update_dates( [ $date_type => $date ] );
	}

	/**
	 * Fast-forward time for testing.
	 *
	 * @since 1.0.0
	 * @param int $seconds Number of seconds to fast-forward.
	 * @return void
	 */
	protected function fast_forward_time( $seconds ) {
		$new_time = current_time( 'timestamp' ) + $seconds;

		// Update WordPress current time.
		add_filter( 'current_time', function() use ( $new_time ) {
			return $new_time;
		} );
	}
}