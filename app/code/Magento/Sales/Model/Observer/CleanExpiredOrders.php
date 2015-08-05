<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Observer;

use Magento\Store\Model\StoresConfig;
use Magento\Sales\Model\Order;

class CleanExpiredOrders
{
    /**
     * @var StoresConfig
     */
    protected $storesConfig;

    /**
     * @var \Magento\Sales\Model\Resource\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timeZone;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param StoresConfig $storesConfig
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timeZone
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\Resource\Order\CollectionFactory $collectionFactory
     */
    public function __construct(
        StoresConfig $storesConfig,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timeZone,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Resource\Order\CollectionFactory $collectionFactory
    ) {
        $this->storesConfig = $storesConfig;
        $this->timeZone = $timeZone;
        $this->logger = $logger;
        $this->orderCollectionFactory = $collectionFactory;
    }

    /**
     * Clean expired quotes (cron process)
     *
     * @return void
     */
    public function execute()
    {
        $lifetimes = $this->storesConfig->getStoresConfigByPath('sales/orders/delete_pending_after');
        foreach ($lifetimes as $storeId => $lifetime) {
            /** @var $orders \Magento\Sales\Model\Resource\Order\Collection */
            $orders = $this->orderCollectionFactory->create();
            $orders->addFieldToFilter('store_id', $storeId);
            $orders->addFieldToFilter('status', Order::STATE_PENDING_PAYMENT);
            $time = $this->timeZone->date(time() - $lifetime * 60, $this->timeZone->getConfigTimezone());
            $orders->addFieldToFilter('updated_at', ['to' => $time]);
            try {
                $orders->walk('cancel');
                $orders->walk('save');
            } catch (\Exception $e) {
                $this->logger->error('Error cancelling deprecated orders: ' . $e->getMessage());
            }
        }
    }
}
