<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Manager;

/**
 * Observer model
 */
class AddSwatchAttributeTypeObserver implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param Manager $moduleManager
     */
    public function __construct(Manager $moduleManager)
    {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        if (!$this->moduleManager->isOutputEnabled('Magento_Swatches')) {
            return;
        }

        /** @var \Magento\Framework\DataObject $response */
        $response = $observer->getEvent()->getResponse();
        $types = $response->getTypes();
        $types[] = [
            'value' => \Magento\Swatches\Model\Swatch::SWATCH_TYPE_VISUAL_ATTRIBUTE_FRONTEND_INPUT,
            'label' => __('Visual Swatch'),
            'hide_fields' => [
                'is_unique',
                'is_required',
                'frontend_class',
                '_scope',
                '_default_value',
                '_front_fieldset',
            ],
        ];
        $types[] = [
            'value' => \Magento\Swatches\Model\Swatch::SWATCH_TYPE_TEXTUAL_ATTRIBUTE_FRONTEND_INPUT,
            'label' => __('Text Swatch'),
            'hide_fields' => [
                'is_unique',
                'is_required',
                'frontend_class',
                '_scope',
                '_default_value',
                '_front_fieldset',
            ],
        ];

        $response->setTypes($types);
    }
}
