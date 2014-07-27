<?php
/**
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

namespace Magento\Customer\Test\Block\Account\Dashboard;

use Mtf\Block\Block;

/**
 * Class Address
 * Customer Dashboard Address Book block
 */
class Address extends Block
{
    /**
     * Default Billing Address Edit link
     *
     * @var string
     */
    protected $defaultBillingAddressEdit = '[data-ui-id=default-billing-edit-link]';

    /**
     * Shipping address block selector
     *
     * @var string
     */
    protected $shippingAddressBlock = '.shipping address';

    /**
     * Billing address block selector
     *
     * @var string
     */
    protected $billingAddressBlock = '.billing address';

    /**
     * Edit Default Billing Address
     *
     * @return void
     */
    public function editBillingAddress()
    {
        $this->_rootElement->find($this->defaultBillingAddressEdit)->click();
    }

    /**
     * Returns Default Billing Address Text
     *
     * @return array|string
     */
    public function getDefaultBillingAddressText()
    {
        return $this->_rootElement->find($this->billingAddressBlock)->getText();
    }

    /**
     * Returns Default Shipping Address Text
     *
     * @return array|string
     */
    public function getDefaultShippingAddressText()
    {
        return $this->_rootElement->find($this->shippingAddressBlock)->getText();
    }
}
