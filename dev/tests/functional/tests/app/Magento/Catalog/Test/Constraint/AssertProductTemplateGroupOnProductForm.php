<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Fixture\CatalogAttributeSet;
use Magento\Catalog\Test\Fixture\CatalogProductAttribute;
use Magento\Catalog\Test\Fixture\CatalogProductSimple;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductEdit;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductIndex;
use Magento\Catalog\Test\Page\Adminhtml\CatalogProductNew;
use Mtf\Constraint\AbstractConstraint;
use Mtf\Fixture\FixtureFactory;

/**
 * Class AssertProductTemplateGroupOnProductForm
 * Check that created product template displays in product template suggest container dropdown and
 * can be used for new created product
 */
class AssertProductTemplateGroupOnProductForm extends AbstractConstraint
{
    /**
     * Constraint severeness
     *
     * @var string
     */
    protected $severeness = 'high';

    /**
     * Assert that created product template:
     * 1. Displays in product template suggest container dropdown
     * 2. Can be used for new created product.
     *
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductEdit $productEdit
     * @param CatalogProductIndex $productGrid
     * @param CatalogAttributeSet $attributeSet
     * @param CatalogProductNew $newProductPage
     * @param CatalogProductAttribute $productAttributeOriginal
     * @return void
     */
    public function processAssert(
        FixtureFactory $fixtureFactory,
        CatalogProductEdit $productEdit,
        CatalogProductIndex $productGrid,
        CatalogAttributeSet $attributeSet,
        CatalogProductNew $newProductPage,
        CatalogProductAttribute $productAttributeOriginal
    ) {
        $productGrid->open();
        $productGrid->getGridPageActionBlock()->addProduct('simple');
        $productBlockForm = $newProductPage->getProductForm();

        /**@var CatalogProductSimple $catalogProductSimple */
        $productSimple = $fixtureFactory->createByCode(
            'catalogProductSimple',
            [
                'dataSet' => 'default',
                'data' => [
                    'attribute_set_id' => ['attribute_set' => $attributeSet],
                ],
            ]
        );
        $productBlockForm->fill($productSimple);

        \PHPUnit_Framework_Assert::assertTrue(
            $productEdit->getProductForm()->isTabVisible($attributeSet->getGroup()),
            "Product Group is absent on Product form tabs."
        );

        $productEdit->getProductForm()->openCustomTab($attributeSet->getGroup());
        \PHPUnit_Framework_Assert::assertTrue(
            $productEdit->getProductForm()->checkAttributeLabel($productAttributeOriginal),
            "Product Attribute is absent on Product form."
        );
    }

    /**
     * Returns a string representation of the object
     *
     * @return string
     */
    public function toString()
    {
        return 'Product Group and Product Attribute are present on the Product form.';
    }
}
