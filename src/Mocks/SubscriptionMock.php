<?php
/**
 * Mock WC_Subscription class for testing.
 *
 * This mock allows testing subscription functionality when the actual
 * WooCommerce Subscriptions plugin is not available.
 *
 * @package Greys\WooCommerce\Subscriptions\Tests\Mocks
 * @since   1.0.0
 */

namespace Greys\WooCommerce\Subscriptions\Tests\Mocks;

/**
 * Mock Subscription class.
 *
 * @since 1.0.0
 */
class SubscriptionMock extends \WC_Order {

	/**
	 * Subscription data.
	 *
	 * @var array
	 */
	protected $subscription_data = [
		'status'           => 'active',
		'billing_period'   => 'month',
		'billing_interval' => 1,
		'start_date'       => '',
		'trial_end'        => '',
		'next_payment'     => '',
		'end_date'         => '',
		'is_manual'        => false,
	];

	/**
	 * Constructor.
	 *
	 * @param mixed $data Subscription data or ID.
	 */
	public function __construct( $data = [] ) {
		parent::__construct();

		if ( is_array( $data ) ) {
			$this->subscription_data = wp_parse_args( $data, $this->subscription_data );

			if ( isset( $data['status'] ) ) {
				$this->set_status( $data['status'] );
			}
			if ( isset( $data['customer_id'] ) ) {
				$this->set_customer_id( $data['customer_id'] );
			}
		}
	}

	/**
	 * Get billing period.
	 *
	 * @return string
	 */
	public function get_billing_period() {
		return $this->subscription_data['billing_period'];
	}

	/**
	 * Set billing period.
	 *
	 * @param string $period Billing period.
	 * @return void
	 */
	public function set_billing_period( $period ) {
		$this->subscription_data['billing_period'] = $period;
	}

	/**
	 * Get billing interval.
	 *
	 * @return int
	 */
	public function get_billing_interval() {
		return absint( $this->subscription_data['billing_interval'] );
	}

	/**
	 * Set billing interval.
	 *
	 * @param int $interval Billing interval.
	 * @return void
	 */
	public function set_billing_interval( $interval ) {
		$this->subscription_data['billing_interval'] = absint( $interval );
	}

	/**
	 * Get start date.
	 *
	 * @param string $format Date format.
	 * @return string
	 */
	public function get_start_date( $format = 'mysql' ) {
		return $this->subscription_data['start_date'];
	}

	/**
	 * Set start date.
	 *
	 * @param string $date Start date.
	 * @return void
	 */
	public function set_start_date( $date ) {
		$this->subscription_data['start_date'] = $date;
	}

	/**
	 * Get time for a specific date type.
	 *
	 * @param string $date_type Date type.
	 * @return int Timestamp.
	 */
	public function get_time( $date_type ) {
		if ( isset( $this->subscription_data[ $date_type ] ) && $this->subscription_data[ $date_type ] ) {
			return strtotime( $this->subscription_data[ $date_type ] );
		}
		return 0;
	}

	/**
	 * Get date.
	 *
	 * @param string $date_type Date type.
	 * @param string $format Date format.
	 * @return string
	 */
	public function get_date( $date_type, $format = 'mysql' ) {
		if ( isset( $this->subscription_data[ $date_type ] ) ) {
			return $this->subscription_data[ $date_type ];
		}
		return '';
	}

	/**
	 * Update dates.
	 *
	 * @param array $dates Array of dates to update.
	 * @return void
	 */
	public function update_dates( $dates ) {
		foreach ( $dates as $type => $date ) {
			$this->subscription_data[ $type ] = $date;
		}
	}

	/**
	 * Set trial end date.
	 *
	 * @param string $date Trial end date.
	 * @return void
	 */
	public function set_trial_end_date( $date ) {
		$this->subscription_data['trial_end'] = $date;
	}

	/**
	 * Set end date.
	 *
	 * @param string $date End date.
	 * @return void
	 */
	public function set_end_date( $date ) {
		$this->subscription_data['end_date'] = $date;
	}

	/**
	 * Check if subscription requires manual renewal.
	 *
	 * @return bool
	 */
	public function is_manual() {
		return (bool) $this->subscription_data['is_manual'];
	}

	/**
	 * Get related orders.
	 *
	 * @param string $type Order type (parent, renewal, switch, resubscribe).
	 * @return array Order IDs.
	 */
	public function get_related_orders( $type = 'all' ) {
		global $wpdb;

		$order_ids = [];

		if ( 'parent' === $type ) {
			$parent_id = $this->get_parent_id();
			if ( $parent_id ) {
				$order_ids = [ $parent_id ];
			}
		} elseif ( 'renewal' === $type ) {
			// Query for renewal orders
			$results = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT post_id FROM {$wpdb->postmeta}
					WHERE meta_key = '_subscription_renewal'
					AND meta_value = %d",
					$this->get_id()
				)
			);
			$order_ids = array_map( 'absint', $results );
		}

		return $order_ids;
	}

	/**
	 * Set created via.
	 *
	 * @param string $value Created via value.
	 * @return void
	 */
	public function set_created_via( $value ) {
		$this->set_created_via( $value );
	}

	/**
	 * Get type.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'subscription';
	}
}
