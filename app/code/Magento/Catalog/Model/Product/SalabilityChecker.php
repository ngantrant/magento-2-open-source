<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class to check that product is saleable.
 */
class SalabilityChecker
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param StoreManagerInterface $storeManager
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration = null
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->stockConfiguration = $stockConfiguration ?:\Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\CatalogInventory\Api\StockConfigurationInterface::class);
    }

    /**
     * Check if product is salable.
     *
     * @param int|string $productId
     * @param int|null $storeId
     * @return bool
     */
    public function isSalable($productId, $storeId = null): bool
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->getById($productId, false, $storeId);

        return $product->isSalable();
    }
    
    /**
     * Check if product is visible on frontend.
     *
     * @param int|string $productId
     * @param int|null $storeId
     * @return bool
     */
    public function isProductVisible($productId, $storeId = null): bool
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        try {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($productId, false, $storeId);

            return  ($this->stockConfiguration->isShowOutOfStock() || $product->isAvailable()) &&
            $product->isInStock() && $product->isVisibleInSiteVisibility();
        } catch (\Exception $e) {
            return false;
        }
    }
}