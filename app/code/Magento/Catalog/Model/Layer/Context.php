<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Model\Layer;

class Context implements ContextInterface
{
    /**
     * @var ItemCollectionProviderInterface
     */
    protected $collectionProvider;

    /**
     * @var StateKeyInterface
     */
    protected $stateKey;

    /**
     * @var CollectionFilterInterface
     */
    protected $collectionFilter;

    /**
     * @param ItemCollectionProviderInterface $collectionProvider
     * @param StateKeyInterface $stateKey
     * @param CollectionFilterInterface $collectionFilter
     */
    public function __construct(
        ItemCollectionProviderInterface $collectionProvider,
        StateKeyInterface $stateKey,
        CollectionFilterInterface $collectionFilter
    ) {
        $this->collectionProvider = $collectionProvider;
        $this->stateKey = $stateKey;
        $this->collectionFilter = $collectionFilter;
    }

    /**
     * @return ItemCollectionProviderInterface
     */
    public function getCollectionProvider()
    {
        return $this->collectionProvider;
    }

    /**
     * @return StateKeyInterface
     */
    public function getStateKey()
    {
        return $this->stateKey;
    }

    /**
     * @return CollectionFilterInterface
     */
    public function getCollectionFilter()
    {
        return $this->collectionFilter;
    }
}
