# Introduction
A WordPress plugin that implements the BOGO (Buy One Get One) sale scheme using the WP plugin "ADP: Advanced Dynamic Pricing for WooCommerce" with a modified behavior. 

# The Requirements
I assume that it's needed to implement the BOGO discount where the least expensive item in the cart is free when a customer buys another item. This can be implemented using the plugin ADP but with a slight difference in the behavior.

# Issues with Current Implementation of ADP
There are currently two ways to implement BOGO using ADP: With Free Products feature, and with Product Discount feature.

## Using the Free Products Feature
BOGO can be implemented using the Free Products feature like the configuration in the example below.
![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/42231357-bf58-4ccc-bbd5-136ac2a51d6b)

**Problem:** The problem with this implementation is in its behavior. Whenever a user adds a product to the cart, the plugin automatically adds a gift. While this is actually a BOGO, it's not the required behavior.

## Using the Product Discounts Feature
We can also implement BOGO using the Product Discounts feature like the configuration in the example below.
![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/f023b13a-f42f-4e66-beba-5e96556e0118)

**Problem:** This works well with two or three products in the cart, the least expensive item is marked as free. However, when more products are added, the behavior is not as intended. See the example below.

![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/9d762a68-8c31-447f-a74a-fb4bcc7bef6f)

In this example, I add 4 products to the cart as follows:

| Product | Price | Required Behavior | Current Behavior |
|---------|-------|-------------------|------------------|
| Beanie  | $20   | Paid              | Paid             |
| Beanie  | $20   | Paid              | Free             |
| Album   | $15   | Free              | Paid             |
| Album   | $15   | Free              | Free             |

### Analyzing the Problem
So, when there are more than 2 products in the cart, the products are bundled depending on the quantities set in the rule configuration. They are sorted then the discount is applied sequentially. 

Now for the same combination of products, if I change the quantities in the rule configuration to 2-2 (see the screenshot below), the problem disappears and the discount is applied correctly.
![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/058be08f-81b0-4fa2-b2b4-6340efe1f3f0)

Here's the result:

![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/f35816bd-b682-4b72-b075-78d0bab98795)

### Conclusion
If there's a way I can dynamically change these bundling quantities depending on the number of products, I can solve the problem by setting the quantities to the half of the cart size, so that the discount is only applied to the less expensive items whatever their number is. 

# The Solution

## A Filter Before Rule is Applied
After digging in the plugin docs and code, I found this hook: `adp_before_apply_rule` that can be used to modify the rule settings before it's applied. I looked it up in the code to find its parameters and how I can use it. 

## BOGO Or BxGx? 
I assum that the shop admin might want the discount scheme to be something like Buy X Get Y in future. I can implement that by useung the bundle sizes in the rule settings to defined the ratio of Paid-to-free products. 
While this makes the calculations a bit complicated, it gives the shop admin more flexibility in future discounts.

## Calculating the New Bundle Sizes
If the user sets the number of paid items to be X and the free to be Y, then the ratio of the paid items is `$paidRation = X/(X+Y)`, and therefor, the new 'paid' bundle size is `ceil($cartSize * $paidRatio)`.
Note that I use `ceil()` to work with odd number of products.

## Tests
(To be added)





