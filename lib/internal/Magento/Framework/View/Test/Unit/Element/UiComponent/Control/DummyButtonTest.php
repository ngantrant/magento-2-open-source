<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Element\UiComponent\Control;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\View\Element\UiComponent\Control\DummyButton;

/**
 * Class DummyButtonTest
 */
class DummyButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Checks that button data for button dummy is empty array
     */
    public function testGetButtonData()
    {
        $objectManagerHelper = new ObjectManagerHelper($this);
        $dummyButton = $objectManagerHelper->getObject(DummyButton::class);
        $this->assertSame([], $dummyButton->getButtonData());
    }
}
