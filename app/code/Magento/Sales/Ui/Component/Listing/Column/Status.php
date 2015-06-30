<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Model\Resource\Order\Status\CollectionFactory;

/**
 * Class Status
 */
class Status extends Column
{
    /**
     * @var string[]
     */
    protected $statuses;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param CollectionFactory $collectionFactory
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        CollectionFactory $collectionFactory,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array & $dataSource)
    {
        if (null === $this->statuses) {
            /** @var \Magento\Sales\Model\Order\Status $status */
            foreach ($this->collectionFactory->create()->getItems() as $status) {
                $this->statuses[$status->getStatus()] = $status->getLabel();
            }
        }
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->statuses[$item[$this->getData('name')]];
            }
        }
    }
}
