<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Block\Adminhtml\Catalog\Product\Composite\Fieldset\Options\Type;

/**
 * Bundle option multi select type renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Multi extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Multi
{
    /**
     * @var string
     */
    protected $_template = 'product/composite/fieldset/options/type/multi.phtml';

    /**
     * @param  string $elementId
     * @param  string $containerId
     * @return string
     */
    public function setValidationContainer($elementId, $containerId)
    {
        return '<script>
            document.getElementById(\'' .
            $elementId .
            '\').adviceContainer = \'' .
            $containerId .
            '\';
            </script>';
    }
}
