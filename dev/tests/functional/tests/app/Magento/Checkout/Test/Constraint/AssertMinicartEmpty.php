<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Constraint;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Mtf\Constraint\AbstractConstraint;

/**
 * Assert that customer cart is empty
 */
class AssertMinicartEmpty extends AbstractConstraint
{
    /**
     * Assert that customer cart is empty
     *
     * @param CmsIndex $cmsIndex
     */
    public function processAssert(
        CmsIndex $cmsIndex
    ) {
        \PHPUnit_Framework_Assert::assertFalse(
            $cmsIndex->getCartSidebarBlock()->isItemsQtyVisible(),
            'Minicart is not empty'
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Minicart  emptiness check';
    }
}
