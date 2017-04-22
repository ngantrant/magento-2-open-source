<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\App\Config;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Information Expert in store groups handling
 *
 * @package Magento\Store\Model
 */
class GroupRepository implements \Magento\Store\Api\GroupRepositoryInterface
{
    /**
     * @var GroupFactory
     */
    protected $groupFactory;

    /**
     * @var \Magento\Store\Api\Data\GroupInterface[]
     */
    protected $entities = [];

    /**
     * @var bool
     */
    protected $allLoaded = false;

    /**
     * @var \Magento\Store\Model\ResourceModel\Group\CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var Config
     */
    private $appConfig;

    /**
     * @param GroupFactory $groupFactory
     * @param \Magento\Store\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory
     */
    public function __construct(
        GroupFactory $groupFactory,
        \Magento\Store\Model\ResourceModel\Group\CollectionFactory $groupCollectionFactory
    ) {
        $this->groupFactory = $groupFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function get($id)
    {
        if (isset($this->entities[$id])) {
            return $this->entities[$id];
        }

        $groupData = [];
        $groups = $this->getAppConfig()->get('scopes', 'groups', []);
        if ($groups) {
            foreach ($groups as $data) {
                if (isset($data['group_id']) && $data['group_id'] == $id) {
                    $groupData = $data;
                    break;
                }
            }
        }
        $group = $this->groupFactory->create([
            'data' => $groupData
        ]);

        if (null === $group->getId()) {
            throw new NoSuchEntityException();
        }
        $this->entities[$id] = $group;
        return $group;
    }

    /**
     * {@inheritdoc}
     */
    public function getList()
    {
        if (!$this->allLoaded) {
            $groups = $this->getAppConfig()->get('scopes', 'groups', []);
            foreach ($groups as $data) {
                $group = $this->groupFactory->create([
                    'data' => $data
                ]);
                $this->entities[$group->getId()] = $group;
            }
            $this->allLoaded = true;
        }

        return $this->entities;
    }

    /**
     * {@inheritdoc}
     */
    public function clean()
    {
        $this->entities = [];
        $this->allLoaded = false;
    }

    /**
     * Retrieve application config.
     *
     * @deprecated
     * @return Config
     */
    private function getAppConfig()
    {
        if (!$this->appConfig) {
            $this->appConfig = ObjectManager::getInstance()->get(Config::class);
        }
        return $this->appConfig;
    }
}
