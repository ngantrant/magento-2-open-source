<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Backend system config datetime field renderer
 */
class Notification extends \Magento\Backend\Block\System\Config\Form\Field
{
    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setValue($this->_cache->load('admin_notifications_lastcheck'));
        $format = $this->_localeDate->getDateTimeFormat(
            \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM
        );
        return $this->_localeDate->date(intval($element->getValue()))->toString($format);
    }
}
