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

namespace Magento\Checkout\Test\TestStep;

use Magento\Checkout\Test\Page\CheckoutOnepage;
use Mtf\TestStep\TestStepInterface;

/**
 * Class SelectPaymentMethodStep
 * Selecting payment method
 */
class SelectPaymentMethodStep implements TestStepInterface
{
    /**
     * Onepage checkout page
     *
     * @var CheckoutOnepage
     */
    protected $checkoutOnepage;

    /**
     * Payment information
     *
     * @var string
     */
    protected $payment;

    /**
     * @constructor
     * @param CheckoutOnepage $checkoutOnepage
     * @param array $payment
     */
    public function __construct(CheckoutOnepage $checkoutOnepage, array $payment)
    {
        $this->checkoutOnepage = $checkoutOnepage;
        $this->payment = $payment;
    }

    /**
     * Run step that selecting payment method
     *
     * @return void
     */
    public function run()
    {
        if ($this->payment['method'] !== 'free') {
            $this->checkoutOnepage->getPaymentMethodsBlock()->selectPaymentMethod($this->payment);
        }
        $this->checkoutOnepage->getPaymentMethodsBlock()->clickContinue();
    }
}
