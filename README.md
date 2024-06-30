# Introduction
A WordPress plugin that implements the BOGO (Buy One Get One) sale scheme using the WP plugin "ADP: Advanced Dynamic Pricing for WooCommerce" with a modified behavior. 

# How to Use
Download the plugin zip file and upload it to your WordPress installation.

# The Client Needs
it's needed to implement the BOGO discount where the least expensive item in the cart is free when a customer buys another item. This can be implemented using the plugin ADP but with a slight difference in the behavior.

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

## BOGO Or BxGy? 
I assume that the shop admin might want in the future a discount scheme like Buy X Get Y. I can implement that by easily using the bundle sizes in the rule settings to defined the ratio of Paid-to-free products. 
While this makes the calculations a bit complicated, it gives the shop admin more flexibility in future discounts.

## Calculating the New Bundle Sizes
If the user sets the number of paid items to be X and the free to be Y, then the ratio of the paid items is `$paidRation = X/(X+Y)`, and therefor, the new 'paid' bundle size is `ceil($cartSize * $paidRatio)`.
Note that I use `ceil()` to work with odd number of products.

## The Rule Configuration
I use the following configuration for the rule. However, the bundle quantities can be anything depending on the needed discount. For Buy-One-Get-One, it can be 1:1.

![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/f023b13a-f42f-4e66-beba-5e96556e0118)

**Important:** To make it possible for the user to enable or disable the new behavior, the rule mist start with "bogo" (case-insensitive).

## Tests

### Odd Number of Products

1. A single product.

![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/b76aae40-f8d7-4ac2-b021-e47e53fba3ec)

2. Three products, all the same.

![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/d9aa0981-f358-48ce-b59f-4a2eb129245d)

3. Three products, all different.

![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/b77425e9-446c-4103-9157-504ae853d68a)


4. Three products, two identical and one is less expensive.

![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/da33bd1d-cc65-4eb9-8f39-26beaec128cd)


5. Three products, two identical and one is more expensive.

![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/45f196e9-333e-4b43-b93b-be1cb53ffbff)



### Even Number of Products

1. Two different products.

![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/2dc8dd41-fcbf-49d2-aee0-6d261dd93a21)


2. Two same products.

![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/c6368e96-b0ec-48c3-8c4a-97de00d9511f)


3. Four different products.

![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/0fa68a59-a195-4ae0-8433-a06ce4feda29)


4. Four products, two items of different products.

![image](https://github.com/NourEdin/wp-adp-bogo-fixer/assets/7292410/b8ccaa96-87b1-4a62-a04d-8ebb9ef43b99)





