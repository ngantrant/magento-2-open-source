<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Reports\Model\Event;

/**
 * Event type model
 *
 * @method \Magento\Reports\Model\Resource\Event\Type _getResource()
 * @method \Magento\Reports\Model\Resource\Event\Type getResource()
 * @method string getEventName()
 * @method \Magento\Reports\Model\Event\Type setEventName(string $value)
 * @method int getCustomerLogin()
 * @method \Magento\Reports\Model\Event\Type setCustomerLogin(int $value)
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Type extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Reports\Model\Resource\Event\Type');
    }
}
