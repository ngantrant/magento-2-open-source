<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Connect\Controller\Adminhtml\Extension\Custom;

class Create extends \Magento\Connect\Controller\Adminhtml\Extension\Custom
{
    /**
     * Create new Extension Package
     *
     * @return void
     */
    public function execute()
    {
        $session = $this->_objectManager->get('Magento\Connect\Model\Session');
        try {
            $post = $this->getRequest()->getPost();
            $session->setCustomExtensionPackageFormData($post);
            $ext = $this->_objectManager->create('Magento\Connect\Model\Extension');
            $ext->setData($post);
            $packageVersion = $this->getRequest()->getPost('version_ids');
            if (is_array($packageVersion)) {
                if (in_array(\Magento\Framework\Connect\Package::PACKAGE_VERSION_2X, $packageVersion)) {
                    $ext->createPackage();
                }
                if (in_array(\Magento\Framework\Connect\Package::PACKAGE_VERSION_1X, $packageVersion)) {
                    $ext->createPackageV1x();
                }
            }
            $this->_redirect('adminhtml/*');
        } catch (\Magento\Framework\Model\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('adminhtml/*');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong creating the package.'));
            $this->_redirect('adminhtml/*');
        }
    }
}
