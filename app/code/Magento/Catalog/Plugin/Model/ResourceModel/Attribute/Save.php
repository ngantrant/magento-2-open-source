<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Plugin\Model\ResourceModel\Attribute;

use Magento\Catalog\Model\ResourceModel\Attribute;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\PageCache\Model\Config;

class Save
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var TypeListInterface
     */
    protected $typeList;

    /**
     * @param Config $config
     * @param TypeListInterface $typeList
     */
    public function __construct(Config $config, TypeListInterface $typeList)
    {
        $this->config = $config;
        $this->typeList = $typeList;
    }

    /**
     * Invalidate full page cache after saving attribute
     *
     * @param Attribute $subject
     * @param Attribute $result
     * @return Attribute $result
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(Attribute $subject, Attribute $result)
    {
        if ($this->config->isEnabled()) {
            $this->typeList->invalidate('full_page');
        }
        return $result;
    }
}
