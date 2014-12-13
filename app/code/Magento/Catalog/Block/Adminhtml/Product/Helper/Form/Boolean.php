<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Helper\Form;

/**
 * Product form boolean field helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Boolean extends \Magento\Framework\Data\Form\Element\Select
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setValues([['label' => __('No'), 'value' => 0], ['label' => __('Yes'), 'value' => 1]]);
    }
}
