<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var QuoteSetupFactory
     */
    private $quoteSetupFactory;

    /**
     * @var ConvertSerializedDataToJsonFactory
     */
    private $convertSerializedDataToJsonFactory;

    /**
     * Constructor
     *
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param ConvertSerializedDataToJsonFactory $convertSerializedDataToJsonFactory
     */
    public function __construct(
        QuoteSetupFactory $quoteSetupFactory,
        ConvertSerializedDataToJsonFactory $convertSerializedDataToJsonFactory
    ) {
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->convertSerializedDataToJsonFactory = $convertSerializedDataToJsonFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '2.0.4', '<')) {
            $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
            $this->convertSerializedDataToJsonFactory->create(['quoteSetup' => $quoteSetup])
                ->convert();
        }
    }
}
