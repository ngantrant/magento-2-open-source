<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\Constraint;

use Magento\Customer\Test\Fixture\CustomerGroupInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex;
use Mtf\Constraint\AbstractConstraint;

/**
 * Class AssertCustomerGroupInGrid
 */
class AssertCustomerGroupInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that customer group in grid
     *
     * @param CustomerGroupInjectable $customerGroup
     * @param CustomerGroupIndex $customerGroupIndex
     * @return void
     */
    public function processAssert(
        CustomerGroupInjectable $customerGroup,
        CustomerGroupIndex $customerGroupIndex
    ) {
        $customerGroupIndex->open();
        $filter = ['code' => $customerGroup->getCustomerGroupCode()];
        \PHPUnit_Framework_Assert::assertTrue(
            $customerGroupIndex->getCustomerGroupGrid()->isRowVisible($filter),
            'Group with type \'' . $customerGroup->getCustomerGroupCode() . '\'is absent in customer groups grid.'
        );
    }

    /**
     * Success assert of  customer group in grid
     *
     * @return string
     */
    public function toString()
    {
        return 'Customer group in grid.';
    }
}
