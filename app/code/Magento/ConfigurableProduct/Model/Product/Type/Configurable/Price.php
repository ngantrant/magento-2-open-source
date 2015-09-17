<?php
/**
 * Product type price model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\Type\Configurable;

use Magento\Framework\Pricing\PriceCurrencyInterface;

class Price extends \Magento\Catalog\Model\Product\Type\Price
{
    /**
     * Get product final price
     *
     * @param   float $qty
     * @param   \Magento\Catalog\Model\Product $product
     * @return  float
     */
    public function getFinalPrice($qty, $product)
    {
        if ($qty === null && $product->getCalculatedFinalPrice() !== null) {
            return $product->getCalculatedFinalPrice();
        }
        //TODO: MAGETWO-23739 catalogrule price must get from simple product.
        if ($product->getCustomOption('simple_product')) {
            $product->setSelectedConfigurableOption($product->getCustomOption('simple_product')->getProduct());
            /** @var \Magento\Catalog\Model\Product $selectedProduct */
            $selectedProduct= $product->getCustomOption('simple_product')->getProduct();
            $finalPrice = $selectedProduct->getPrice();
        } else {
            $finalPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        }
        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        $finalPrice = max(0, $finalPrice);
        $product->setFinalPrice($finalPrice);

        return $finalPrice;
    }
}
