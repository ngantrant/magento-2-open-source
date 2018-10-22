<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product;

use Magento\Framework\DB\Select;

/**
 * Interface BaseSelectProcessorInterface
 * @api
 */
interface BaseSelectProcessorInterface
{
    /**
     * Product table alias
     */
    const PRODUCT_TABLE_ALIAS = 'child';

    /**
     * @param Select $select
     * @param int $productId
     * @return Select
     */
    public function process(Select $select, $productId);
}
