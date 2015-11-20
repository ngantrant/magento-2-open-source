<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\Constraint;

/**
 * Assert that Catalog Price Rule is applied in Shopping Cart.
 */
class AssertCartPriceRuleConditionIsApplied extends AssertCartPriceRuleApplying
{
    /**
     * Assert that Catalog Price Rule is applied in Shopping Cart.
     *
     * @return void
     */
    protected function assert()
    {
        $actualPrices['sub_total'] =  $this->checkoutCart->getTotalsBlock()->getSubtotal();
        $actualPrices['grand_total'] =  $this->checkoutCart->getTotalsBlock()->getGrandTotal();
        $actualPrices['discount'] =  $this->checkoutCart->getTotalsBlock()->getDiscount();

        if ($this->checkoutCart->getTotalsBlock()->isVisibleShippingPriceBlock()) {
            $actualPrices['shipping_price'] = $this->checkoutCart->getTotalsBlock()->getShippingPrice();
            $actualPrices['grand_total'] = number_format(($actualPrices['grand_total'] - $actualPrices['shipping_price']), 2);
            $expectedPrices['shipping_price'] = $this->cartPrice['shipping_price'];
        }
        $expectedPrices['sub_total'] = $this->cartPrice['sub_total'];
        $expectedPrices['grand_total'] = $this->cartPrice['grand_total'];
        $expectedPrices['discount'] = $this->cartPrice['discount'];

        \PHPUnit_Framework_Assert::assertEquals(
            $expectedPrices,
            $actualPrices,
            'Wrong total cart prices are displayed'
        );
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return "Shopping cart subtotal doesn't equal to grand total - price rule has been applied.";
    }
}
