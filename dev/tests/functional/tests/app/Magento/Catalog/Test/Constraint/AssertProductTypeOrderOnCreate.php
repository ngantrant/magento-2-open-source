<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that order and types of product on product page equals to incoming data.
 */
class AssertProductTypeOrderOnCreate extends AbstractConstraint
{
    /**
     * Assert that order and types of product on product page equals to incoming data.
     *
     * @param CatalogProductIndex $catalogProductIndex
     * @param array $menu
     * @return void
     */
    public function processAssert(CatalogProductIndex $catalogProductIndex, array $menu)
    {
        $catalogProductIndex->open();
        ksort($menu);
        \PHPUnit_Framework_Assert::assertEquals(
            implode("\n", $menu),
            $catalogProductIndex->getGridPageActionBlock()->getTypeList(),
            'Order and filling of types on product page not equals to incoming data.'
        );
    }

    /**
     * Success message is displayed.
     *
     * @return string
     */
    public function toString()
    {
        return 'Order and types of product on product page equals to incoming data.';
    }
}
