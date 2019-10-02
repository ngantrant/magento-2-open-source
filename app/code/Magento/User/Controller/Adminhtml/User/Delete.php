<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Controller\Adminhtml\User;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\User\Block\User\Edit\Tab\Main as UserEdit;
use Magento\User\Controller\Adminhtml\User;

/**
 * Class Delete
 */
class Delete extends User implements HttpPostActionInterface
{
    /**
     * Execute delete admin user action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\User\Model\User */
        $currentUser = $this->_objectManager->get(\Magento\Backend\Model\Auth\Session::class)->getUser();
        $userId = (int)$this->getRequest()->getPost('user_id');

        if ($userId) {
            if ($currentUser->getId() == $userId) {
                $this->messageManager->addErrorMessage(__('You cannot delete your own account.'));
                $this->_redirect('adminhtml/*/edit', ['user_id' => $userId]);
                return;
            }
            try {
                $currentUserPassword = (string)$this->getRequest()->getPost(UserEdit::CURRENT_USER_PASSWORD_FIELD);
                if (empty($currentUserPassword)) {
                    throw new AuthenticationException(
                        __('The password entered for the current user is invalid. Verify the password and try again.')
                    );
                }
                $currentUser->performIdentityCheck($currentUserPassword);
                /** @var \Magento\User\Model\User $model */
                $model = $this->_userFactory->create();
                $model->setId($userId);
                $model->delete();
                $this->messageManager->addSuccessMessage(__('You deleted the user.'));
                $this->_redirect('adminhtml/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('adminhtml/*/edit', ['user_id' => $this->getRequest()->getParam('user_id')]);
                return;
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a user to delete.'));
        $this->_redirect('adminhtml/*/');
    }
}
