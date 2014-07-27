<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\ConfigurableProduct\Test\Block\Adminhtml\Product\Edit\Tab\Super;

use Mtf\Client\Element;
use Mtf\Client\Element\Locator;
use Magento\Catalog\Test\Block\Adminhtml\Product\Edit\Tab\Options as CatalogOptions;

/**
 * Class Options
 * Attribute options row form
 */
class Options extends CatalogOptions
{
    /**
     * CSS selector name item
     *
     * @var string
     */
    protected $nameSelector = 'td[data-column="name"]';

    /**
     * XPath selector percent label
     *
     * @var string
     */
    protected $percentSelector = '//button[span[contains(text(),"%")]]';

    /**
     * Getting options data form on the product form
     *
     * @param array|null $fields [optional]
     * @param Element|null $element [optional]
     * @return array
     */
    public function getDataOptions(array $fields = null, Element $element = null)
    {
        $element = $element === null ? $this->_rootElement : $element;
        $mapping = $this->dataMapping($fields);
        $data = $this->_getData($mapping, $element);

        $data['is_percent'] = 'No';
        $percentElement = $element->find($this->percentSelector, Locator::SELECTOR_XPATH);
        if ($percentElement->isVisible()) {
            $data['is_percent'] = 'Yes';
        }

        $nameElement = $element->find($this->nameSelector);
        if ($nameElement->isVisible()) {
            $data['name'] = $nameElement->getText();
        }

        return $data;
    }
}
