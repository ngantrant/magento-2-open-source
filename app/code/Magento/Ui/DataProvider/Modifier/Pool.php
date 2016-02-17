<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Modifier;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Pool
 */
class Pool
{
    /**
     * @var array
     */
    protected $modifiers = [];

    /**
     * @var array
     */
    protected $modifiersInstances = [];

    /**
     * @var ModifierFactory
     */
    protected $factory;

    /**
     * @param ModifierFactory $factory
     * @param array $modifiers
     */
    public function __construct(
        ModifierFactory $factory,
        array $modifiers
    ) {
        $this->factory = $factory;
        $this->modifiers = $this->sort($modifiers);
    }

    /**
     * Retrieve modifiers
     *
     * @return array
     */
    public function getModifiers()
    {
        return $this->modifiers;
    }

    /**
     * Retrieve modifiers instantiated
     *
     * @return ModifierInterface[]
     * @throws LocalizedException
     */
    public function getModifiersInstances()
    {
        if ($this->modifiersInstances) {
            return $this->modifiersInstances;
        }

        foreach ($this->modifiers as $modifier) {
            if (empty($modifier['class'])) {
                throw new LocalizedException(__('Parameter "class" must be present.'));
            }

            if (empty($modifier['sortOrder'])) {
                throw new LocalizedException(__('Parameter "sortOrder" must be present.'));
            }

            $this->modifiersInstances[$modifier['class']] = $this->factory->create($modifier['class']);
        }

        return $this->modifiersInstances;
    }

    /**
     * Sorting modifiers according to sort order
     *
     * @param array $data
     * @return array
     */
    protected function sort(array $data)
    {
        usort($data, function (array $a, array $b) {
            $a['sortOrder'] = $this->getSortOrder($a);
            $b['sortOrder'] = $this->getSortOrder($b);

            if ($a['sortOrder'] == $b['sortOrder']) {
                return 0;
            }

            return ($a['sortOrder'] < $b['sortOrder']) ? -1 : 1;
        });

        return $data;
    }

    /**
     * Retrieve sort order from array
     *
     * @param array $variable
     * @return int
     */
    protected function getSortOrder(array $variable)
    {
        return !empty($variable['sortOrder']) ? $variable['sortOrder'] : 0;
    }
}
