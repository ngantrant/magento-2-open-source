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
namespace Magento\View\Layout\Argument\Interpreter;

use Magento\ObjectManager;
use Magento\Data\Argument\InterpreterInterface;

/**
 * Interpreter that returns invocation result of a helper method
 */
class HelperMethod implements InterpreterInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var NamedParams
     */
    private $paramsInterpreter;

    /**
     * @param ObjectManager $objectManager
     * @param NamedParams $paramsInterpreter
     */
    public function __construct(ObjectManager $objectManager, NamedParams $paramsInterpreter)
    {
        $this->objectManager = $objectManager;
        $this->paramsInterpreter = $paramsInterpreter;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function evaluate(array $data)
    {
        if (!isset($data['helper']) || substr_count($data['helper'], '::') != 1) {
            throw new \InvalidArgumentException('Helper method name in format "\Class\Name::methodName" is expected.');
        }
        $helperMethod = $data['helper'];
        list($helperClass, $methodName) = explode('::', $helperMethod, 2);
        if (!method_exists($helperClass, $methodName)) {
            throw new \InvalidArgumentException("Helper method '{$helperMethod}' does not exist.");
        }
        $methodParams = $this->paramsInterpreter->evaluate($data);
        $methodParams = array_values($methodParams);
        // Use positional argument binding instead of named binding
        $helperInstance = $this->objectManager->get($helperClass);
        return call_user_func_array(array($helperInstance, $methodName), $methodParams);
    }
}
