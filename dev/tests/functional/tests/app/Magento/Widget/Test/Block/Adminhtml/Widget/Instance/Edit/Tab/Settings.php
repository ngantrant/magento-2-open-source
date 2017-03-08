<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Widget\Test\Block\Adminhtml\Widget\Instance\Edit\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Mtf\Client\Element\SimpleElement;
use Magento\Mtf\Client\Locator;
use Magento\Widget\Test\Fixture\Widget;

/**
 * Widget options form.
 */
class Settings extends Tab
{
    /**
     * 'Continue' button locator.
     *
     * @var string
     */
    protected $continueButton = './/button[contains(@data-ui-id, "widget-button")]';

    /**
     * Click 'Continue' button.
     *
     * @return void
     */
    protected function clickContinue()
    {
        $this->_rootElement->find($this->continueButton, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Fill data to fields on tab.
     *
     * @param array $fields
     * @param SimpleElement|null $element
     * @return $this
     */
    public function setFieldsData(array $fields, SimpleElement $element = null)
    {
        parent::setFieldsData($fields, $element);
        $this->clickContinue();

        return $this;
    }
}
