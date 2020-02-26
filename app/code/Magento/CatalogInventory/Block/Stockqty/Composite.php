<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Block\Stockqty;

use Magento\Catalog\Model\Product;

/**
 * Product stock qty block for abstract composite product
 */
abstract class Composite extends AbstractStockqty
{
    /**
     * Child products cache
     *
     * @var Product[]
     */
    private $_childProducts;

    /**
     * Retrieve child products
     *
     * @return Product[]
     */
    abstract protected function _getChildProducts();

    /**
     * Retrieve child products (using cache)
     *
     * @return Product[]
     */
    public function getChildProducts()
    {
        if ($this->_childProducts === null) {
            $this->_childProducts = $this->_getChildProducts();
        }
        return $this->_childProducts;
    }

    /**
     * Retrieve id of details table placeholder in template
     *
     * @return string
     */
    public function getDetailsPlaceholderId()
    {
        return $this->getPlaceholderId() . '-details';
    }

    /**
     *  Get stock qty of child products
     *
     * @return float|int
     */
    public function getChildStockQtyLeft()
    {
        $childStockQty = 0;

        foreach ($this->getChildProducts() as $childProduct) {
            $childStockQty += $childProductStockQty = $this->getProductStockQty($childProduct);
        }

        return $childStockQty;
    }
}
