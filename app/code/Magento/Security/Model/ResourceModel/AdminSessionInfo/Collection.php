<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Security\Model\ResourceModel\AdminSessionInfo;

/**
 * Admin Session Info collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * @var \Magento\Security\Helper\SecurityConfig
     */
    protected $securityConfig;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Security\Helper\SecurityConfig $securityConfig
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Security\Helper\SecurityConfig $securityConfig,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->securityConfig = $securityConfig;
        $this->dateTime = $dateTime;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Magento\Security\Model\AdminSessionInfo',
            'Magento\Security\Model\ResourceModel\AdminSessionInfo'
        );
    }

    /**
     * Filter by user
     *
     * @param int $userId
     * @param int $status
     * @param null|string $sessionIdToExclude
     * @return $this
     */
    public function filterByUser(
        $userId,
        $status = \Magento\Security\Model\AdminSessionInfo::LOGGED_IN,
        $sessionIdToExclude = null
    ) {
        $this->addFieldToFilter('user_id', $userId);
        $this->addFieldToFilter('status', $status);
        if (null !== $sessionIdToExclude) {
            $this->addFieldToFilter('session_id', ['neq' => $sessionIdToExclude]);
        }
        return $this;
    }

    /**
     * Filter expired sessions
     *
     * @param int $sessionLifeTime
     * @return $this
     */
    public function filterExpiredSessions($sessionLifeTime)
    {
        $this->addFieldToFilter(
            'updated_at',
            ['gt' => $this->dateTime->formatDate($this->securityConfig->getCurrentTimestamp() - $sessionLifeTime)]
        );
        return $this;
    }
}
