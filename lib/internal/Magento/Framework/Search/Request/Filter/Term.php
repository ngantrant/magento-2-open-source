<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Search\Request\Filter;

use Magento\Framework\Search\AbstractKeyValuePair;
use Magento\Framework\Search\Request\FilterInterface;

/**
 * Term Filter
 */
class Term extends AbstractKeyValuePair implements FilterInterface
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @param string $name
     * @param string|array $value
     * @param string $field
     */
    public function __construct($name, $value, $field)
    {
        parent::__construct($name, $value);
        $this->field = $field;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return FilterInterface::TYPE_TERM;
    }

    /**
     * Get Field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }
}
