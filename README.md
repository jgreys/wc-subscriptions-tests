# WooCommerce Subscriptions PHPUnit Framework

A comprehensive PHPUnit testing framework for WooCommerce Subscriptions extensions. This package provides test helpers, mock classes, and assertion traits to make testing WooCommerce Subscriptions functionality easier and more reliable.

## Features

- 🎯 **Complete Test Suite**: Base test case class with subscription-specific functionality
- 🛠️ **Helper Classes**: Create subscriptions, renewal orders, and recurring carts easily
- 🎭 **Mock Objects**: Full mock implementations when WC Subscriptions isn't available
- ✅ **Custom Assertions**: Subscription-specific assertion methods
- 📦 **Easy Integration**: Works seamlessly with existing WooCommerce test setups

## Requirements

- PHP >= 7.4
- PHPUnit ^9.6
- WooCommerce PHPUnit Framework (`greys/woocommerce-phpunit-framework`)
- WordPress Test Suite

## Installation

```bash
composer require --dev greys/woocommerce-subscriptions-phpunit-framework
```

## Quick Start

### 1. Extend the Base Test Case

```php
<?php
use Greys\WooCommerce\Subscriptions\Tests\UnitTestCase;
use Greys\WooCommerce\Subscriptions\Tests\Helpers\Subscription;

class My_Subscription_Test extends UnitTestCase {

    public function test_subscription_creation() {
        // Arrange - Create a subscription using helper.
        $subscription = Subscription::create_subscription( array(
            'status'         => 'active',
            'billing_period' => 'month',
            'billing_interval' => 1,
        ) );

        // Act - Add to tracked subscriptions for cleanup.
        $this->subscription_ids[] = $subscription->get_id();

        // Assert - Use custom assertions.
        $this->assertSubscriptionActive( $subscription );
        $this->assertSubscriptionSchedule( 'month', 1, $subscription );
    }
}
```

### 2. Create Test Subscriptions

```php
// Simple subscription
$subscription = Subscription::create_subscription();

// Subscription with trial
$subscription = Subscription::create_subscription( array(
    'trial_end' => gmdate( 'Y-m-d H:i:s', strtotime( '+7 days' ) ),
) );

// Add products
$product = Subscription::create_subscription_product( array(
    'name'          => 'Monthly Plan',
    'regular_price' => '29.99',
) );

Subscription::add_product( $subscription, $product->get_id() );
```

### 3. Test Renewal Orders

```php
use Greys\WooCommerce\Subscriptions\Tests\Helpers\Renewal;

public function test_renewal_order_creation() {
    // Arrange.
    $subscription = Subscription::create_subscription();

    // Act.
    $renewal_order = Renewal::create_renewal_order( $subscription );

    // Assert.
    $this->assertRenewalOrderCreated( $subscription );
    $this->assertEquals( 'pending', $renewal_order->get_status() );
}
```

### 4. Test Recurring Cart

```php
use Greys\WooCommerce\Subscriptions\Tests\Helpers\RecurringCart;

public function test_recurring_cart() {
    // Arrange.
    $product = Subscription::create_subscription_product();

    // Act.
    $cart_key = RecurringCart::add_subscription_to_cart(
        $product->get_id(),
        2 // quantity
    );

    // Assert.
    $this->assertNotEmpty( $cart_key );
    $this->assertEquals( 2, WC()->cart->get_cart_contents_count() );
}
```

## Available Helpers

### Subscription

- `create_subscription( $args )` - Create a test subscription
- `create_subscription_product( $args )` - Create a subscription product
- `add_product( $subscription, $product_id, $args )` - Add product to subscription
- `update_status( $subscription, $new_status )` - Update subscription status
- `process_payment( $subscription, $success )` - Process subscription payment
- `create_customer( $args )` - Create a customer with subscription capabilities

### Renewal

- `create_renewal_order( $subscription, $args )` - Create renewal order
- `process_renewal_payment( $renewal_order, $success )` - Process renewal payment
- `create_failed_renewal_order( $subscription, $reason )` - Create failed renewal
- `get_renewal_orders( $subscription )` - Get all renewal orders
- `schedule_renewal( $subscription, $date )` - Schedule renewal payment
- `trigger_renewal_retry( $renewal_order, $retry_count )` - Trigger payment retry

### RecurringCart

- `add_subscription_to_cart( $product_id, $quantity, $cart_data )` - Add subscription to cart
- `create_recurring_cart( $args )` - Create mock recurring cart
- `add_item_to_recurring_cart( $cart, $product_id, $quantity, $total )` - Add item to recurring cart
- `calculate_recurring_totals( $cart )` - Calculate cart totals
- `clear_recurring_carts()` - Clear all recurring carts
- `create_mixed_cart( $products )` - Create cart with mixed products

## Custom Assertions

The framework provides subscription-specific assertions via traits:

```php
// Status assertions
$this->assertSubscriptionActive( $subscription );
$this->assertSubscriptionOnHold( $subscription );
$this->assertSubscriptionCancelled( $subscription );

// Schedule assertions
$this->assertSubscriptionSchedule( 'month', 1, $subscription );
$this->assertSubscriptionDate( 'next_payment', '2024-02-01', $subscription );

// Content assertions
$this->assertSubscriptionContainsProduct( $product_id, $subscription );
$this->assertSubscriptionItemCount( 3, $subscription );
$this->assertSubscriptionTotal( 99.99, $subscription );

// Trial assertions
$this->assertSubscriptionHasTrial( $subscription );
$this->assertSubscriptionNoTrial( $subscription );

// Payment assertions
$this->assertSubscriptionPaymentMethod( 'stripe', $subscription );
$this->assertSubscriptionManual( $subscription );
$this->assertSubscriptionFailedPaymentCount( 2, $subscription );

// Relationship assertions
$this->assertSubscriptionHasParentOrder( $subscription );
$this->assertSubscriptionHasRenewalOrders( $subscription, 3 );
$this->assertRenewalOrderCreated( $subscription );
```

## Mock Classes

When WooCommerce Subscriptions is not available, the framework provides mock classes:

### WC_Subscription_Mock

A full mock implementation of WC_Subscription that extends WC_Order:

```php
$mock_subscription = new \WCS\Testing\Mocks\WC_Subscription_Mock();
$mock_subscription->set_billing_period( 'month' );
$mock_subscription->set_billing_interval( 1 );
$mock_subscription->update_dates( array(
    'start'        => gmdate( 'Y-m-d H:i:s' ),
    'next_payment' => gmdate( 'Y-m-d H:i:s', strtotime( '+1 month' ) ),
) );
```

## Advanced Usage

### Testing Payment Failures

```php
public function test_payment_failure_handling() {
    // Arrange.
    $subscription = Subscription::create_subscription( array(
        'status' => 'active',
    ) );

    // Act.
    Subscription::process_payment( $subscription, false );

    // Assert.
    $this->assertSubscriptionOnHold( $subscription );
    $this->assertSubscriptionFailedPaymentCount( 1, $subscription );
}
```

### Testing Subscription Trials

```php
public function test_trial_period() {
    // Arrange.
    $trial_end = gmdate( 'Y-m-d H:i:s', strtotime( '+14 days' ) );
    $subscription = Subscription::create_subscription( array(
        'trial_end' => $trial_end,
    ) );

    // Assert.
    $this->assertSubscriptionHasTrial( $subscription );
    $this->assertSubscriptionDate( 'trial_end', $trial_end, $subscription );
}
```

### Testing Renewal Retries

```php
public function test_renewal_retry_mechanism() {
    // Arrange.
    $subscription = Subscription::create_subscription();
    $failed_renewal = Renewal::create_failed_renewal_order(
        $subscription,
        'Insufficient funds'
    );

    // Act.
    Renewal::trigger_renewal_retry( $failed_renewal, 1 );

    // Assert.
    $this->assertSubscriptionOnHold( $subscription );
    $retry_scheduled = as_next_scheduled_action(
        'woocommerce_subscription_payment_retry',
        array( $failed_renewal->get_id() )
    );
    $this->assertNotFalse( $retry_scheduled );
}
```

### Testing Mixed Cart (Subscription + Regular Products)

```php
public function test_mixed_cart_checkout() {
    // Arrange.
    $regular_product = WC_Helper_Product::create_simple_product();
    $subscription_product = Subscription::create_subscription_product();

    // Act.
    $cart = RecurringCart::create_mixed_cart( array(
        array( 'id' => $regular_product->get_id(), 'quantity' => 1 ),
        array( 'id' => $subscription_product->get_id(), 'quantity' => 1, 'is_subscription' => true ),
    ) );

    // Assert.
    $this->assertCount( 2, $cart );
    $this->assertTrue( WC()->cart->contains_subscription() );
}
```

## Bootstrap Configuration

Add to your test bootstrap file:

```php
// Load WC Subscriptions mock if not available
if ( ! class_exists( 'WC_Subscription' ) ) {
    require_once __DIR__ . '/vendor/greys/woocommerce-subscriptions-phpunit-framework/src/mocks/class-wc-subscription-mock.php';
}

// Define WCS constants if needed
if ( ! defined( 'WCS_INIT_TIMESTAMP' ) ) {
    define( 'WCS_INIT_TIMESTAMP', time() );
}
```

## Best Practices

1. **Always track subscription IDs** for cleanup in `tearDown()`
2. **Use helpers instead of direct database manipulation**
3. **Clear scheduled actions** after each test
4. **Mock external payment gateways** to avoid API calls
5. **Test both success and failure scenarios**
6. **Use realistic test data** (proper date formats, valid amounts)

## Troubleshooting

### "Class WC_Subscription not found"

The framework automatically loads mock classes when WC Subscriptions isn't available. Ensure the mock is loaded in your bootstrap file.

### Scheduled actions not clearing

Use the base test case which automatically clears subscription-related scheduled actions:

```php
class MyTest extends UnitTestCase {
    // Scheduled actions are automatically cleared
}
```

### Payment gateway errors

Mock payment gateways to avoid external dependencies:

```php
PaymentGateway::mock_gateway( 'stripe', array(
    'supports' => array( 'subscriptions', 'subscription_cancellation' ),
) );
```

## Contributing

Contributions are welcome! Please:

1. Write tests for new features
2. Follow WordPress coding standards
3. Update documentation for API changes
4. Add examples for complex features

## License

GPL-3.0-or-later

## Credits

Built with ❤️ for the WooCommerce community by Greys.

Inspired by and built upon:
- WooCommerce Core Testing Framework
- WordPress Testing Suite
- PHPUnit best practices

## Support

For issues, questions, or feature requests, please open an issue on GitHub.