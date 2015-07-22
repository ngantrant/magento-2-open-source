<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filters\Type;

use Magento\Ui\Component\AbstractComponent;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\Api\FilterBuilder;

/**
 * Abstract class AbstractFilter
 */
abstract class AbstractFilter extends AbstractComponent
{
    /**
     * Component name
     */
    const NAME = 'filter';

    /**
     * Filter variable name
     */
    const FILTER_VAR = 'filters';

    /**
     * Filter data
     *
     * @var array
     */
    protected $filterData;

    /**
     * @var UiComponentFactory
     */
    protected $uiComponentFactory;

    /**
     * @var
     */
    protected $filterBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param FilterBuilder $filterBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        FilterBuilder $filterBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->uiComponentFactory = $uiComponentFactory;
        $this->filterBuilder = $filterBuilder;
        parent::__construct($context, $components, $data);
        $this->filterData = $this->getContext()->getFiltersParams();
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }
}
