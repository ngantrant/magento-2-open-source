<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Ui\Component\Listing\Column;

use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class CustomerGroup
 */
class CustomerGroup extends Column
{
    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param GroupRepositoryInterface $groupRepository
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        GroupRepositoryInterface $groupRepository,
        array $components = [],
        array $data = []
    ) {
        $this->groupRepository = $groupRepository;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                try {
                    $item[$this->getData('name')] = $this->groupRepository->getById($item[$this->getData('name')])
                        ->getCode();
                } catch (NoSuchEntityException $exception) {
                    $item[$this->getData('name')] = __('Group was removed');
                } catch (\Exception $exception) {
                    $item[$this->getData('name')] = '';
                }
            }
        }

        return $dataSource;
    }
}
