<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SampleData\Setup;

use Magento\Framework\Setup;

/**
 * Class PostInstallSampleData
 */
class InstallData implements Setup\InstallDataInterface
{
    /**
     * @var \Magento\SampleData\Model\State
     */
    protected $state;

    /**
     * @param \Magento\SampleData\Model\State $state
     */
    public function __construct(
        \Magento\SampleData\Model\State $state
    ) {
        $this->state = $state;
    }

    /**
     * @inheritdoc
     */
    public function install(Setup\ModuleDataSetupInterface $setup, Setup\ModuleContextInterface $moduleContext)
    {
        $this->state->clearState();
    }
}
