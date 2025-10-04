<?php
/**
 * Subscription Assertions Trait
 *
 * Provides custom assertions for WooCommerce Subscriptions testing.
 *
 * @package Greys\WooCommerce\Subscriptions\Tests\Traits
 * @since   1.0.0
 */

namespace Greys\WooCommerce\Subscriptions\Tests\Traits;

/**
 * Subscription Assertions trait.
 *
 * @since 1.0.0
 */
trait Assertions {

	/**
	 * Assert subscription is active.
	 *
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionActive( $subscription, $message = '' ) {
		$this->assertEquals(
			'active',
			$subscription->get_status(),
			$message ?: 'Failed asserting subscription is active.'
		);
	}

	/**
	 * Assert subscription is pending.
	 *
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionPending( $subscription, $message = '' ) {
		$this->assertEquals(
			'pending',
			$subscription->get_status(),
			$message ?: 'Failed asserting subscription is pending.'
		);
	}

	/**
	 * Assert subscription is on-hold.
	 *
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionOnHold( $subscription, $message = '' ) {
		$this->assertEquals(
			'on-hold',
			$subscription->get_status(),
			$message ?: 'Failed asserting subscription is on-hold.'
		);
	}

	/**
	 * Assert subscription is cancelled.
	 *
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionCancelled( $subscription, $message = '' ) {
		$this->assertEquals(
			'cancelled',
			$subscription->get_status(),
			$message ?: 'Failed asserting subscription is cancelled.'
		);
	}

	/**
	 * Assert subscription is expired.
	 *
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionExpired( $subscription, $message = '' ) {
		$this->assertEquals(
			'expired',
			$subscription->get_status(),
			$message ?: 'Failed asserting subscription is expired.'
		);
	}

	/**
	 * Assert subscription has specific billing schedule.
	 *
	 * @param string $period Expected billing period.
	 * @param int    $interval Expected billing interval.
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionSchedule( $period, $interval, $subscription, $message = '' ) {
		$this->assertEquals(
			$period,
			$subscription->get_billing_period(),
			$message ?: "Failed asserting subscription billing period is {$period}."
		);

		$this->assertEquals(
			$interval,
			$subscription->get_billing_interval(),
			$message ?: "Failed asserting subscription billing interval is {$interval}."
		);
	}

	/**
	 * Assert subscription has trial.
	 *
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionHasTrial( $subscription, $message = '' ) {
		$trial_end = $subscription->get_time( 'trial_end' );
		$this->assertNotEmpty(
			$trial_end,
			$message ?: 'Failed asserting subscription has trial period.'
		);
	}

	/**
	 * Assert subscription requires manual renewal.
	 *
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionRequiresManualRenewal( $subscription, $message = '' ) {
		$this->assertTrue(
			$subscription->is_manual(),
			$message ?: 'Failed asserting subscription requires manual renewal.'
		);
	}

	/**
	 * Assert subscription has specific next payment date.
	 *
	 * @param string $expected_date Expected date (Y-m-d format).
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionNextPaymentDate( $expected_date, $subscription, $message = '' ) {
		$next_payment = $subscription->get_date( 'next_payment' );
		$this->assertEquals(
			gmdate( 'Y-m-d', strtotime( $expected_date ) ),
			gmdate( 'Y-m-d', strtotime( $next_payment ) ),
			$message ?: "Failed asserting subscription next payment date is {$expected_date}."
		);
	}

	/**
	 * Assert subscription total matches expected amount.
	 *
	 * @param float  $expected Expected total.
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionTotal( $expected, $subscription, $message = '' ) {
		$this->assertEquals(
			$expected,
			floatval( $subscription->get_total() ),
			$message ?: "Failed asserting subscription total is {$expected}."
		);
	}

	/**
	 * Assert subscription has parent order.
	 *
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionHasParentOrder( $subscription, $message = '' ) {
		$parent_id = $subscription->get_parent_id();
		$this->assertNotEmpty(
			$parent_id,
			$message ?: 'Failed asserting subscription has parent order.'
		);
	}

	/**
	 * Assert subscription has specific number of renewal orders.
	 *
	 * @param int    $expected Expected count.
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionRenewalCount( $expected, $subscription, $message = '' ) {
		$renewals = $subscription->get_related_orders( 'renewal' );
		$this->assertCount(
			$expected,
			$renewals,
			$message ?: "Failed asserting subscription has {$expected} renewal orders."
		);
	}

	/**
	 * Assert subscription end date is set.
	 *
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionHasEndDate( $subscription, $message = '' ) {
		$end_date = $subscription->get_time( 'end' );
		$this->assertNotEmpty(
			$end_date,
			$message ?: 'Failed asserting subscription has end date.'
		);
	}

	/**
	 * Assert subscription contains specific product.
	 *
	 * @param int    $product_id Product ID.
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionContainsProduct( $product_id, $subscription, $message = '' ) {
		$found = false;
		foreach ( $subscription->get_items() as $item ) {
			if ( $item->get_product_id() === $product_id ) {
				$found = true;
				break;
			}
		}

		$this->assertTrue(
			$found,
			$message ?: "Failed asserting subscription contains product ID {$product_id}."
		);
	}

	/**
	 * Assert subscription payment method.
	 *
	 * @param string $expected Expected payment method.
	 * @param object $subscription Subscription object.
	 * @param string $message Optional message.
	 * @return void
	 */
	public function assertSubscriptionPaymentMethod( $expected, $subscription, $message = '' ) {
		$this->assertEquals(
			$expected,
			$subscription->get_payment_method(),
			$message ?: "Failed asserting subscription payment method is {$expected}."
		);
	}
}
