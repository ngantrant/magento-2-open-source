<?php
namespace Smetana\Third\Plugin;

use Smetana\Third\Api\Data\PartnerInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Ui\DataProvider\Product\Form\ProductDataProvider;

/**
 * Class add data to product
 *
 * @package Smetana\Third\Plugin
 */
class AddProductData
{
    /**
     * Magento Core Registry instance
     *
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @param Registry $coreRegistry
     */
    public function __construct(
        Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Add data to partner name field
     *
     * @param ProductDataProvider $plugin
     * @param array $data
     *
     * @return array
     */
    public function afterGetData(ProductDataProvider $plugin, array $data): array
    {
        $product = $this->coreRegistry->registry('current_product');
        $partner = $product->getExtensionAttributes()->getPartner();

        if (!is_null($partner) && !empty($partner->getData())) {
            if ($partner->getProductId() == $product->getId()) {
                $data[$product->getId()]['product']['partner'][PartnerInterface::PARTNER_NAME] = $partner->getPartnerId();
            }
        }

        return $data;
    }
}
