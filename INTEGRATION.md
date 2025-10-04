# WooCommerce Subscriptions - Plugin Analysis

**Plugin Version:** 6.3.2
**Analysis Date:** 2025-10-04
**Purpose:** Document actual plugin implementation for framework accuracy

---

## Table of Contents

1. [Overview](#overview)
2. [Core Concepts](#core-concepts)
3. [Meta Keys Reference](#meta-keys-reference)
4. [Framework Verification](#framework-verification)

---

## Overview

WooCommerce Subscriptions extends `WC_Order` to create subscription objects with recurring payment schedules. The plugin uses custom post type `shop_subscription` and extends WooCommerce's order system with additional meta data for billing schedules and subscription-specific properties.

### Key Concepts

- **Subscription** = Custom post type extending WC_Order
- **Post Type** = `shop_subscription`
- **Data Store** = Uses WC Order data store with subscription-specific extensions
- **HPOS Compatible** = Supports High-Performance Order Storage

---

## Core Concepts

### WC_Subscription Class
- **Extends:** `WC_Order`
- **Post Type:** `shop_subscription`
- **Data Store:** `WCS_Subscription_Data_Store_CPT` (or HPOS variant)

### Subscription vs Order
Subscriptions inherit all order properties and add:
- Billing schedule (period/interval)
- Schedule dates (start, trial_end, next_payment, end)
- Suspension tracking
- Manual renewal requirements
- Switch data

---

## Meta Keys Reference

### Core Subscription Meta

Stored as post meta (or order meta in HPOS):

| Meta Key | Type | Description | Example |
|----------|------|-------------|---------|
| `_billing_period` | string | Billing period | `'day'`, `'week'`, `'month'`, `'year'` |
| `_billing_interval` | int | Billing interval | `1`, `2`, `3` |
| `_suspension_count` | int | Times suspended | `0`, `1`, `2` |
| `_cancelled_email_sent` | bool | Cancellation email sent | `true`, `false` |
| `_requires_manual_renewal` | bool | Requires manual renewal | `true`, `false` |
| `_trial_period` | string | Trial period unit | `'day'`, `'month'` |

### Schedule Date Meta

All dates stored as MySQL datetime strings (GMT):

| Meta Key | Description | Example |
|----------|-------------|---------|
| `_schedule_start` | Subscription start date | `'2025-01-01 00:00:00'` |
| `_schedule_trial_end` | Trial end date | `'2025-01-15 00:00:00'` |
| `_schedule_next_payment` | Next payment date | `'2025-02-01 00:00:00'` |
| `_schedule_cancelled` | Cancellation date | `'2025-06-01 00:00:00'` |
| `_schedule_end` | Subscription end date | `'2025-12-31 00:00:00'` |
| `_schedule_payment_retry` | Payment retry date | `'2025-02-03 00:00:00'` |

### Order Relationship Meta

| Meta Key | Description |
|----------|-------------|
| `_subscription_renewal_order_ids_cache` | Cached renewal order IDs |
| `_subscription_resubscribe_order_ids_cache` | Cached resubscribe order IDs |
| `_subscription_switch_order_ids_cache` | Cached switch order IDs |

### Switch Data Meta

| Meta Key | Type | Description |
|----------|------|-------------|
| `_subscription_switch_data` | array | Switch-related information |

---

## Subscription Product Meta

Products that create subscriptions use these meta keys:

| Meta Key | Type | Description | Example |
|----------|------|-------------|---------|
| `_subscription_price` | float | Recurring price | `10.00` |
| `_subscription_period` | string | Billing period | `'month'` |
| `_subscription_period_interval` | int | Billing interval | `1` |
| `_subscription_length` | int | Total length (0=indefinite) | `0`, `6`, `12` |
| `_subscription_trial_length` | int | Trial length | `0`, `7`, `14` |
| `_subscription_trial_period` | string | Trial period unit | `'day'`, `'month'` |
| `_subscription_sign_up_fee` | float | One-time sign-up fee | `5.00` |
| `_subscription_limit` | string | Purchase limit | `'no'`, `'active'`, `'any'` |
| `_subscription_one_time_shipping` | string | One-time shipping | `'yes'`, `'no'` |

---

## Subscription Statuses

All statuses include `wc-` prefix in database:

| Status Key | Display Name | Description |
|------------|--------------|-------------|
| `wc-pending` | Pending | Subscription created but not activated |
| `wc-active` | Active | Subscription is active and will renew |
| `wc-on-hold` | On hold | Subscription paused |
| `wc-cancelled` | Cancelled | Customer cancelled subscription |
| `wc-switched` | Switched | Subscription was switched to another |
| `wc-expired` | Expired | Subscription reached end date |
| `wc-pending-cancel` | Pending Cancellation | Scheduled for cancellation |

**Note:** When using `$subscription->get_status()`, the `wc-` prefix is stripped (returns `'active'` not `'wc-active'`).

---

## Framework Verification

### ✅ Verified Correct

All meta keys in the framework have been verified against the actual plugin:

#### Helper Methods
- ✅ `create_subscription()` - Uses correct post type and meta keys
- ✅ `create_subscription_product()` - Uses all correct product meta keys
- ✅ Subscription meta - Uses `_billing_period`, `_billing_interval`, etc.
- ✅ Schedule meta - Uses `_schedule_start`, `_schedule_next_payment`, etc.
- ✅ Status handling - Correctly adds/removes `wc-` prefix

#### Assertions
- ✅ `assertSubscriptionSchedule()` - Uses `get_billing_period()`, `get_billing_interval()`
- ✅ `assertSubscriptionDate()` - Uses `get_date()` method
- ✅ `assertSubscriptionActive()` - Checks status correctly (without `wc-` prefix)
- ✅ `assertSubscriptionHasTrial()` - Uses `has_trial()` method
- ✅ All other assertions use correct WC_Subscription object methods

### Important Notes

1. **Extends WC_Order** - Subscriptions have all order meta plus subscription-specific meta
2. **Status prefix** - Database stores with `wc-` prefix, getters return without it
3. **Date format** - All schedule dates are MySQL datetime strings in GMT
4. **Customer meta** - Uses `_customer_user` (same as orders)
5. **Payment method** - Uses `_payment_method` (same as orders)
6. **HPOS Compatible** - Framework works with both CPT and HPOS storage

---

## Core Classes

- **WC_Subscription** - Main subscription class (extends WC_Order)
- **WCS_Subscription_Data_Store_CPT** - Data store for CPT storage
- **WCS_Orders_Table_Subscription_Data_Store** - Data store for HPOS
- **WC_Subscriptions_Product** - Product-level subscription functions

---

## Source Files Analyzed

- `includes/class-wc-subscription.php` - Subscription class definition
- `includes/data-stores/class-wcs-subscription-data-store-cpt.php` - CPT data store
- `includes/admin/class-wc-subscriptions-admin.php` - Product meta handling
- `wcs-functions.php` - Core helper functions
- `includes/wcs-time-functions.php` - Date/time utilities

---

**Status:** ✅ Framework verified accurate against plugin v6.3.2
