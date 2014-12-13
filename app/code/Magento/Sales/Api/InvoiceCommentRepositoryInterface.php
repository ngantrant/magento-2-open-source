<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Api;

/**
 * Interface RepositoryInterface
 */
interface InvoiceCommentRepositoryInterface
{
    /**
     * @param \Magento\Framework\Api\SearchCriteria $criteria
     * @return \Magento\Sales\Api\Data\InvoiceSearchResultInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteria $criteria);

    /**
     * Load entity
     *
     * @param int $id
     * @return \Magento\Sales\Api\Data\InvoiceCommentInterface
     */
    public function get($id);

    /**
     * Delete entity
     *
     * @param \Magento\Sales\Api\Data\InvoiceCommentInterface $entity
     * @return bool
     */
    public function delete(\Magento\Sales\Api\Data\InvoiceCommentInterface $entity);

    /**
     * Perform persist operations for one entity
     *
     * @param \Magento\Sales\Api\Data\InvoiceCommentInterface $entity
     * @return \Magento\Sales\Api\Data\InvoiceCommentInterface
     */
    public function save(\Magento\Sales\Api\Data\InvoiceCommentInterface $entity);
}
