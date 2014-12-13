<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CatalogInventory\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface Stock
 */
interface StockInterface extends ExtensibleDataInterface
{
    const STOCK_ID = 'stock_id';

    const WEBSITE_ID = 'website_id';

    const STOCK_NAME = 'stock_name';

    /**
     * Retrieve stock identifier
     *
     * @return int
     */
    public function getStockId();

    /**
     * Retrieve website identifier
     *
     * @return int
     */
    public function getWebsiteId();

    /**
     * Retrieve stock name
     *
     * @return string
     */
    public function getStockName();
}
