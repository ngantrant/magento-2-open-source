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
namespace Magento\Install\Controller\Wizard;

class Locale extends \Magento\Install\Controller\Wizard
{
    /**
     * Localization settings
     *
     * @return void
     */
    public function execute()
    {
        $this->_checkIfInstalled();
        $this->_actionFlag->set('', self::FLAG_NO_DISPATCH_BLOCK_EVENT, true);
        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);

        $this->_prepareLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->getLayout()->addBlock('Magento\Install\Block\Locale', 'install.locale', 'content');
        $this->_view->getLayout()->getBlock('install.locale')->setLocaleCode($this->_session->getLocale());
        $this->_view->renderLayout();
    }
}
