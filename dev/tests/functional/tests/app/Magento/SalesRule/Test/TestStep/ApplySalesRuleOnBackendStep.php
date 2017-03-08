<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesRule\Test\TestStep;

use Magento\Mtf\TestStep\TestStepInterface;
use Magento\Sales\Test\Page\Adminhtml\OrderCreateIndex;
use Magento\SalesRule\Test\Fixture\SalesRule;

/**
 * Apply Sales Rule on backend.
 */
class ApplySalesRuleOnBackendStep implements TestStepInterface
{
    /**
     * Order Create Index.
     *
     * @var OrderCreateIndex
     */
    protected $orderCreateIndex;

    /**
     * SalesRule fixture.
     *
     * @var SalesRule
     */
    protected $salesRule;

    /**
     * @constructor
     * @param OrderCreateIndex $orderCreateIndex
     * @param SalesRule $salesRule
     */
    public function __construct(OrderCreateIndex $orderCreateIndex, SalesRule $salesRule = null)
    {
        $this->orderCreateIndex = $orderCreateIndex;
        $this->salesRule = $salesRule;
    }

    /**
     * Apply gift card on place order in admin.
     *
     * @return void
     */
    public function run()
    {
        if ($this->salesRule !== null) {
            $this->orderCreateIndex->getCouponsBlock()->applyCouponCode($this->salesRule);
        }
    }
}
