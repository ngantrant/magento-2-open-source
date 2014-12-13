<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureInterface;

/**
 * Class AssertProductNotInGrid
 * Assert that Product absence on grid
 */
class AssertProductNotInGrid extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'low';

    /**
     * Assert that product cannot be found by name and sku
     *
     * @param FixtureInterface|FixtureInterface[] $product
     * @param CatalogProductIndex $productGrid
     * @return void
     */
    public function processAssert($product, CatalogProductIndex $productGrid)
    {
        $products = is_array($product) ? $product : [$product];
        foreach ($products as $product) {
            $filter = ['sku' => $product->getSku(), 'name' => $product->getName()];
            $productGrid->open();
            \PHPUnit_Framework_Assert::assertFalse(
                $productGrid->getProductGrid()->isRowVisible($filter),
                "Product with sku \"{$filter['sku']}\" and name \"{$filter['name']}\" is attend in Products grid."
            );
        }
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Assertion that product is absent in products grid.';
    }
}
