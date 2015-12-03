<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Category\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Catalog\Block\Adminhtml\Category\AbstractCategory;

/**
 * Class ResetButton
 */
class ResetButton extends AbstractCategory implements ButtonProviderInterface
{
    /**
     * Reset button
     *
     * @return array
     */
    public function getButtonData()
    {
        $category = $this->getCategory();
        $categoryId = (int)$category->getId();

        if (!$category->isReadonly() && $this->hasStoreRootCategory()) {
            $resetPath = $categoryId ? 'catalog/*/edit' : 'catalog/*/add';
            return [
                'id' => 'reset',
                'label' => __('Reset'),
                'on_click' => "categoryReset('"
                    . $this->getUrl($resetPath, $this->getDefaultUrlParams())
                    . "',true)",
                'class' => 'reset',
                'sort_order' => 20
            ];
        }

        return [];
    }

    /**
     * @return array
     */
    protected function getDefaultUrlParams()
    {
        return ['_current' => true, '_query' => ['isAjax' => null]];
    }
}
