<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * AdminNotification Inbox model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\AdminNotification\Model\ResourceModel\Grid;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends \Magento\AdminNotification\Model\ResourceModel\Inbox\Collection
{
    /**
     * Add remove filter
     *
     * @return \Magento\AdminNotification\Model\ResourceModel\Grid\Collection|AbstractCollection
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->addRemoveFilter();
        return $this;
    }
}
