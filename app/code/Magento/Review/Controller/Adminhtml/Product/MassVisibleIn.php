<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Review\Controller\Adminhtml\Product as ProductController;

class MassVisibleIn extends ProductController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');
        if (!is_array($reviewsIds)) {
            $this->messageManager->addError(__('Please select review(s).'));
        } else {
            try {
                $stores = $this->getRequest()->getParam('stores');
                foreach ($reviewsIds as $reviewId) {
                    $model = $this->reviewFactory->create()->load($reviewId);
                    $model->setSelectStores($stores);
                    $model->save();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($reviewsIds))
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while updating these review(s).')
                );
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('review/*/pending');
        return $resultRedirect;
    }
}
