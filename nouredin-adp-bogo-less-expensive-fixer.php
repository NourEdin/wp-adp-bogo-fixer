<?php
use ADP\BaseVersion\Includes\Core\Rule\Rule;
use ADP\BaseVersion\Includes\Core\RuleProcessor\RuleProcessor;
use ADP\BaseVersion\Includes\Core\Cart\Cart;
use \ADP\BaseVersion\Includes\Core\Rule\PackageRule;

/**
 * Plugin Name: Advanced Dynamic Pricing for WooCommerce - BOGO less expensive fixer
 * Version: 1.0.0
 * Author: NourEdin
 * Author URI: https://profiles.wordpress.org/nouredin/
 * Description: Applies BOGO to the least expensive items in the cart using Advanced Dynamic Pricing for WooCommerce plugin
 * Requires Plugins: woocommerce, advanced-dynamic-pricing-for-woocommerce
 */

if (!defined('ABSPATH'))
    die('Access Denied.');

/**
 * Filters a PackageRule to apply the BOGO product bundling in a different way than the default.
 * By default, the cart products are sorted then divided into bundles, which doesn't guarantee the least expensive products are the free ones.
 * To fix that, after the products are sorted, they are handled as a single large bundle, and the package qty options will be used to determine how many
 * free products will be given for how many purchased ones. Finally, the package qty options will be modified to reflect which products will be free.
 *
 * E.g. 1. If user choice of qty is 1-1 (Buy one, get one free), then, half of the cart will be free products (after sorting and considering duplicate ones).
 * However, if user choice is 2-1 (Buy two, get one free), then third of the cart products will be free.
 *
 * To implement the behavior of considering free-paid ration, we need to know the total number of products in the cart and the individual package quantity set by the user.
 * E.g. 2. If user choice is 1-1, and the cart has 9 products, then 4 will be free.
 * E.g. 3. If user choice is 2-1 and the cart has 9 products, then 3 will be free.
 * Rule: If user choice is nPaid-nFree and the cart has nCart products, then the number of free products is floor(nCart * nFree/(nPaid+nFree) )
 *
 *
 * @param Rule $rule
 * @param RuleProcessor $processor
 * @param Cart $cart
 * @return Rule
 */
function adp_brc_fix_bogo_product_bundling(Rule $rule, RuleProcessor $processor, Cart $cart): Rule
{
    //We are only interested in package rules, and the logic only applies to those starts with "BOGO" in the title.
    if (get_class($rule) === PackageRule::class && $rule->getTitle() == 'BOGO') {
        //Count cart items, considering duplicate products (ie. quantity >1)
        $cartSize = 0;
        if ($cart && !empty($cart->getItems())) {
            foreach ($cart->getItems() as $item) {
                $cartSize += $item->getQty();
            }
        }

        //Find Paid-to-free ratio.
        $packages = $rule->getPackages();

        if ($rule->getApplyFirstTo() == PackageRule::APPLY_FIRST_TO_CHEAP) {
            $freePkgIndex = 0;
            $paidPkgIndex = 1;
        } else {
            $freePkgIndex = 1;
            $paidPkgIndex = 0;
        }

        //Calculate the new quantities
        $totalPackageQty = $packages[$freePkgIndex]->getQty() + $packages[$paidPkgIndex]->getQty();

        $freeRatio = $packages[$freePkgIndex]->getQty() / $totalPackageQty;
        $paidRatio = $packages[$paidPkgIndex]->getQty() / $totalPackageQty;
        $freeQty = floor($cartSize * $freeRatio);
        $paidQty = ceil($cartSize * $paidRatio);

        //Update the rule
        $packages[$freePkgIndex]->setQty($freeQty);
        $packages[$freePkgIndex]->setQtyEnd($freeQty);

        $packages[$paidPkgIndex]->setQtyEnd($paidQty);
        $packages[$paidPkgIndex]->setQtyEnd($paidQty);

    }

    return $rule;
}
add_filter('adp_before_apply_rule', 'adp_brc_fix_bogo_product_bundling', 10, 3);