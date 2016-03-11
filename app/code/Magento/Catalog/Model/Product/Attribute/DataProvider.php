<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Attribute;

use Magento\Catalog\Model\Category;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Eav\Model\Entity\Attribute as EavAttribute;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Element\DataType\Text;

/**
 * Data provider for the form of adding new product attribute.
 */
class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param StoreRepositoryInterface $storeRepository
     * @param ArrayManager $arrayManager
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        StoreRepositoryInterface $storeRepository,
        ArrayManager $arrayManager,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->storeRepository = $storeRepository;
        $this->arrayManager = $arrayManager;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return [];
    }

    /**
     * @return array
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        $meta['advanced_fieldset']['children'] = $this->arrayManager->set('attribute_code/arguments/data/config', [], [
            'notice' => __(
                'This is used internally. Make sure you don\'t use spaces or more than %1 symbols.',
                EavAttribute::ATTRIBUTE_CODE_MAX_LENGTH
            ),
            'validation' => [
                'max_text_length' => EavAttribute::ATTRIBUTE_CODE_MAX_LENGTH
            ]
        ]);

        $sortOrder = 1;

        foreach ($this->storeRepository->getList() as $store) {
            if (!$store->getId()) {
                continue;
            }

            $storeId = $store->getId();

            $meta['manage-titles']['children'] = [
                'frontend_label[' . $store->getId() . ']' => $this->arrayManager->set('arguments/data/config', [], [
                    'formElement' => Input::NAME,
                    'componentType' => Field::NAME,
                    'label' => $store->getName(),
                    'dataType' => Text::NAME,
                    'dataScope' => 'frontend_label[' . $storeId . ']'
                ]),
            ];

            $meta['attribute_options_select_container']['children']['attribute_options_select']['children']['record']['children']['value_option_' . $storeId] = $this->arrayManager->set(
                'arguments/data/config',
                [],
                [
                    'dataType' => 'text',
                    'formElement' => 'input',
                    'component' => 'Magento_Catalog/js/form/element/input',
                    'template' => 'Magento_Catalog/form/element/input',
                    'prefixName' => 'option.value',
                    'prefixElementName' => 'option_',
                    'suffixName' => (string)$storeId,
                    'label' => $store->getName(),
                    'sortOrder' => $sortOrder,
                    'componentType' => Field::NAME,
                ]);
            $meta['attribute_options_multiselect_container']['children']['attribute_options_multiselect']['children']['record']['children']['value_option_' . $storeId] = $this->arrayManager->set(
                'arguments/data/config',
                [],
                [
                    'dataType' => 'text',
                    'formElement' => 'input',
                    'component' => 'Magento_Catalog/js/form/element/input',
                    'template' => 'Magento_Catalog/form/element/input',
                    'prefixName' => 'option.value',
                    'prefixElementName' => 'option_',
                    'suffixName' => (string)$storeId,
                    'label' => $store->getName(),
                    'sortOrder' => $sortOrder,
                    'componentType' => Field::NAME,
                ]);

            ++$sortOrder;
        }

        $meta['attribute_options_select_container']['children']['attribute_options_select']['children']['record']['children']['action_delete'] = $this->arrayManager->set(
            'arguments/data/config',
            [],
            [
                'componentType' => 'actionDelete',
                'dataType' => 'text',
                'fit' => true,
                'sortOrder' => $sortOrder,
            ]
        );
        $meta['attribute_options_multiselect_container']['children']['attribute_options_multiselect']['children']['record']['children']['action_delete'] = $this->arrayManager->set(
            'arguments/data/config',
            [],
            [
                'componentType' => 'actionDelete',
                'dataType' => 'text',
                'fit' => true,
                'sortOrder' => $sortOrder,
            ]
        );

        return $meta;
    }
}
