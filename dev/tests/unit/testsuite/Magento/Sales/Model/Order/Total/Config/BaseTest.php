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
namespace Magento\Sales\Model\Order\Total\Config;

use Magento\TestFramework\Helper\ObjectManager;

class BaseTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Model\Order\Total\Config\Base */
    protected $object;

    /** @var \Magento\App\Cache\Type\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $configCacheType;

    /** @var \Magento\Logger|\PHPUnit_Framework_MockObject_MockObject */
    protected $logger;

    /** @var \Magento\Sales\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $salesConfig;

    /** @var \Magento\Sales\Model\Order\TotalFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $orderTotalFactory;

    protected function setUp()
    {
        $this->configCacheType = $this->getMock('Magento\App\Cache\Type\Config', [], [], '', false);
        $this->logger = $this->getMock('Magento\Logger', [], [], '', false);
        $this->salesConfig = $this->getMock('Magento\Sales\Model\Config', [], [], '', false);
        $this->orderTotalFactory = $this->getMock('Magento\Sales\Model\Order\TotalFactory', [], [], '', false);

        $objectManager = new ObjectManager($this);
        $this->object = $objectManager->getObject('Magento\Sales\Model\Order\Total\Config\Base', [
            'configCacheType' => $this->configCacheType,
            'logger' => $this->logger,
            'salesConfig' => $this->salesConfig,
            'orderTotalFactory' => $this->orderTotalFactory,
        ]);
    }

    public function testGetTotalModels()
    {
        $total = $this->getMockForAbstractClass('Magento\Sales\Model\Order\Total\AbstractTotal');
        $this->salesConfig->expects($this->once())->method('getGroupTotals')->will(
            $this->returnValue([
                'some_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1903],
                'other_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1723],
            ])
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with('Magento\Sales\Model\Order\Total\AbstractTotal')
            ->will($this->returnValue($total));

        $this->configCacheType->expects($this->once())->method('save')
            ->with('a:2:{i:0;s:10:"other_code";i:1;s:9:"some_code";}', 'sorted_collectors');

        $this->assertSame(
            ['other_code' => $total, 'some_code' => $total],
            $this->object->getTotalModels()
        );
    }

    /**
     * @expectedException Magento\Model\Exception
     * @expectedExceptionMessage The total model should be extended from \Magento\Sales\Model\Order\Total\AbstractTotal.
     */
    public function testGetTotalModelsInvalidTotalModel()
    {
        $this->salesConfig->expects($this->once())->method('getGroupTotals')->will(
            $this->returnValue([
                'some_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1903],
                'other_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1723],
            ])
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with('Magento\Sales\Model\Order\Total\AbstractTotal')
            ->will($this->returnValue($this));

        $this->object->getTotalModels();
    }

    public function testGetTotalUnserializeCachedCollectorCodes()
    {
        $total = $this->getMockForAbstractClass('Magento\Sales\Model\Order\Total\AbstractTotal');
        $this->salesConfig->expects($this->any())->method('getGroupTotals')->will(
            $this->returnValue([
                'some_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1903],
                'other_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1723],
            ])
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with('Magento\Sales\Model\Order\Total\AbstractTotal')
            ->will($this->returnValue($total));

        $this->configCacheType->expects($this->once())->method('load')->with('sorted_collectors')
            ->will($this->returnValue('a:2:{i:0;s:10:"other_code";i:1;s:9:"some_code";}'));
        $this->configCacheType->expects($this->never())->method('save');

        $this->assertSame(
            ['other_code' => $total, 'some_code' => $total],
            $this->object->getTotalModels()
        );
    }

    public function testGetTotalModelsSortingSubroutine()
    {
        $total = $this->getMockForAbstractClass('Magento\Sales\Model\Order\Total\AbstractTotal');
        $this->salesConfig->expects($this->once())->method('getGroupTotals')->will(
            $this->returnValue([
                'some_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1903],
                'other_code' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1112],
                'equal_order' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 1112],
                'big_order' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal', 'sort_order' => 3000],
                'no_order' => ['instance' => 'Magento\Sales\Model\Order\Total\AbstractTotal'],
            ])
        );

        $this->orderTotalFactory->expects($this->any())->method('create')
            ->with('Magento\Sales\Model\Order\Total\AbstractTotal')
            ->will($this->returnValue($total));

        $this->assertSame(
            [
                'no_order' => $total,
                'equal_order' => $total,
                'other_code' => $total,
                'some_code' => $total,
                'big_order' => $total,
            ],
            $this->object->getTotalModels()
        );
    }
}
