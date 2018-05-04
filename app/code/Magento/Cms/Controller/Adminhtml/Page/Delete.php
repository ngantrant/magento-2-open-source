<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Controller\Adminhtml\Page;

use Magento\Backend\App\Action\Context;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Cms::page_delete';
    
    /**
<<<<<<< HEAD
     * @var \Magento\Cms\Model\Page
     */
    private $cmsPage;
    
    /**
     * @param Context $context
     * @param \Magento\Cms\Model\Page $cmsPage
     */
    public function __construct(
        Context $context,
        \Magento\Cms\Model\Page $cmsPage
    ) {
        parent::__construct($context);
        $this->cmsPage = $cmsPage ?: \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Cms\Model\Page::class);
=======
     * @var \Magento\Cms\Api\PageRepositoryInterface
     */
    private $cmsPageRepository;
    
    /**
     * @param Context $context
     * @param \Magento\Cms\Api\PageRepositoryInterface $cmsPageRepository
     */
    public function __construct(
        Context $context,
        \Magento\Cms\Api\PageRepositoryInterface $cmsPageRepository = null
    ) {
        parent::__construct($context);
        $this->cmsPageRepository = $cmsPageRepository ?: \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Cms\Api\PageRepositoryInterface::class);
>>>>>>> 01a0528039bc1d52a299ef5f8caec22326036f6c
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('page_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $title = "";
            try {
                // init model and delete
<<<<<<< HEAD
                $model = $this->cmsPage->load($id);
=======
                $model = $this->cmsPageRepository->getById($id);
>>>>>>> 01a0528039bc1d52a299ef5f8caec22326036f6c
                $title = $model->getTitle();
                $this->cmsPageRepository->delete($model);
                // display success message
                $this->messageManager->addSuccess(__('The page has been deleted.'));
                // go to grid
                $this->_eventManager->dispatch(
                    'adminhtml_cmspage_on_delete',
                    ['title' => $title, 'status' => 'success']
                );
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_cmspage_on_delete',
                    ['title' => $title, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['page_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a page to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
