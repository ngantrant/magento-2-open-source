<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Reports\Test\Page\Adminhtml\ShopCartProductReport;

/**
 * Class AssertProductInCartResult
 * Assert that product is present in Products in Carts report grid
 */
class AssertProductInCartResult extends AbstractConstraint
{
    /**
     * Assert that product is present in Products in Carts report grid by name, price, carts
     *
     * @param ShopCartProductReport $shopCartProductReport
     * @param CatalogProductSimple $product
     * @param string $carts
     * @return void
     */
    public function processAssert(ShopCartProductReport $shopCartProductReport, CatalogProductSimple $product, $carts)
    {
        $shopCartProductReport->open();
        \PHPUnit_Framework_Assert::assertTrue(
            $shopCartProductReport->getGridBlock()->isProductVisible($product, $carts),
            'Product is absent in Products in Carts report grid.'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product is present in Products in Carts report grid with correct carts number.';
    }
}
