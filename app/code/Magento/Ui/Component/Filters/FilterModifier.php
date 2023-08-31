<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Filters;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\FilterBuilder;

/**
 * Apply modifiers to filter
 */
class FilterModifier
{
    /**
     * Filter modifier variable name
     */
    const FILTER_MODIFIER = 'filters_modifier';

    /**
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param array $allowedConditionTypes
     */
    public function __construct(
        protected readonly RequestInterface $request,
        protected readonly FilterBuilder $filterBuilder,
        protected $allowedConditionTypes = []
    ) {
        $this->allowedConditionTypes = array_merge(
            ['eq', 'neq', 'in', 'nin', 'null', 'notnull'],
            $allowedConditionTypes
        );
    }

    /**
     * Apply modifiers for filters
     *
     * @param DataProviderInterface $dataProvider
     * @param string $filterName
     * @return void
     * @throws LocalizedException
     */
    public function applyFilterModifier(DataProviderInterface $dataProvider, $filterName)
    {
        $filterModifier = $this->request->getParam(self::FILTER_MODIFIER);
        if (isset($filterModifier[$filterName]['condition_type'])) {
            $conditionType = $filterModifier[$filterName]['condition_type'];
            if (!in_array($conditionType, $this->allowedConditionTypes)) {
                throw new LocalizedException(
                    __('Condition type "%1" is not allowed', $conditionType)
                );
            }
            $value = isset($filterModifier[$filterName]['value'])
                ? $filterModifier[$filterName]['value']
                : null;
            $filter = $this->filterBuilder->setConditionType($conditionType)
                ->setField($filterName)
                ->setValue($value)
                ->create();
            $dataProvider->addFilter($filter);
        }
    }
}
