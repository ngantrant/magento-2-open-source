<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Weee\Test\Unit\Helper;

use Magento\Weee\Helper\Data as WeeeHelper;

/**
 * @SuppressWarnings(PHPMD.TooManyMethods)
 */
class DataTest extends \PHPUnit_Framework_TestCase
{
    const ROW_AMOUNT_INVOICED = '200';
    const BASE_ROW_AMOUNT_INVOICED = '400';
    const TAX_AMOUNT_INVOICED = '20';
    const BASE_TAX_AMOUNT_INVOICED = '40';
    const ROW_AMOUNT_REFUNDED = '100';
    const BASE_ROW_AMOUNT_REFUNDED = '201';
    const TAX_AMOUNT_REFUNDED = '10';
    const BASE_TAX_AMOUNT_REFUNDED = '21';

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * @var \Magento\Weee\Model\Tax
     */
    protected $weeeTax;

    /**
     * @var \Magento\Weee\Helper\Data
     */
    protected $helperData;

    protected function setUp()
    {
        $this->product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $weeeConfig = $this->getMock('Magento\Weee\Model\Config', [], [], '', false);
        $weeeConfig->expects($this->any())->method('isEnabled')->will($this->returnValue(true));
        $this->weeeTax = $this->getMock('Magento\Weee\Model\Tax', [], [], '', false);
        $this->weeeTax->expects($this->any())->method('getWeeeAmount')->will($this->returnValue('11.26'));
        $arguments = [
            'weeeConfig' => $weeeConfig,
            'weeeTax' => $this->weeeTax,
        ];
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->helperData = $helper->getObject('Magento\Weee\Helper\Data', $arguments);
    }

    public function testGetAmount()
    {
        $this->product->expects($this->any())->method('hasData')->will($this->returnValue(false));
        $this->product->expects($this->any())->method('getData')->will($this->returnValue(11.26));

        $this->assertEquals('11.26', $this->helperData->getAmount($this->product));
    }

    /**
     * @return \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    private function setupOrderItem()
    {
        $orderItem = $this->getMockBuilder('\Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->setMethods(['__wakeup'])
            ->getMock();

        $orderItem->setData(
            'weee_tax_applied',
            \Zend_Json::encode(
                [
                    [
                        WeeeHelper::KEY_WEEE_AMOUNT_INVOICED => self::ROW_AMOUNT_INVOICED,
                        WeeeHelper::KEY_BASE_WEEE_AMOUNT_INVOICED => self::BASE_ROW_AMOUNT_INVOICED,
                        WeeeHelper::KEY_WEEE_TAX_AMOUNT_INVOICED => self::TAX_AMOUNT_INVOICED,
                        WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_INVOICED => self::BASE_TAX_AMOUNT_INVOICED,
                        WeeeHelper::KEY_WEEE_AMOUNT_REFUNDED => self::ROW_AMOUNT_REFUNDED,
                        WeeeHelper::KEY_BASE_WEEE_AMOUNT_REFUNDED => self::BASE_ROW_AMOUNT_REFUNDED,
                        WeeeHelper::KEY_WEEE_TAX_AMOUNT_REFUNDED => self::TAX_AMOUNT_REFUNDED,
                        WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_REFUNDED => self::BASE_TAX_AMOUNT_REFUNDED,
                    ],
                    [
                        WeeeHelper::KEY_WEEE_AMOUNT_INVOICED => self::ROW_AMOUNT_INVOICED,
                        WeeeHelper::KEY_BASE_WEEE_AMOUNT_INVOICED => self::BASE_ROW_AMOUNT_INVOICED,
                        WeeeHelper::KEY_WEEE_TAX_AMOUNT_INVOICED => self::TAX_AMOUNT_INVOICED,
                        WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_INVOICED => self::BASE_TAX_AMOUNT_INVOICED,
                        WeeeHelper::KEY_WEEE_AMOUNT_REFUNDED => self::ROW_AMOUNT_REFUNDED,
                        WeeeHelper::KEY_BASE_WEEE_AMOUNT_REFUNDED => self::BASE_ROW_AMOUNT_REFUNDED,
                        WeeeHelper::KEY_WEEE_TAX_AMOUNT_REFUNDED => self::TAX_AMOUNT_REFUNDED,
                        WeeeHelper::KEY_BASE_WEEE_TAX_AMOUNT_REFUNDED => self::BASE_TAX_AMOUNT_REFUNDED,
                    ],
                ]
            )
        );
        return $orderItem;
    }

    public function testGetWeeeAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getWeeeAmountInvoiced($orderItem);
        $this->assertEquals(self::ROW_AMOUNT_INVOICED, $value);
    }

    public function testGetBaseWeeeAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getBaseWeeeAmountInvoiced($orderItem);
        $this->assertEquals(self::BASE_ROW_AMOUNT_INVOICED, $value);
    }

    public function testGetWeeeTaxAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getWeeeTaxAmountInvoiced($orderItem);
        $this->assertEquals(self::TAX_AMOUNT_INVOICED, $value);
    }

    public function testGetWeeeBaseTaxAmountInvoiced()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getBaseWeeeTaxAmountInvoiced($orderItem);
        $this->assertEquals(self::BASE_TAX_AMOUNT_INVOICED, $value);
    }

    public function testGetWeeeAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getWeeeAmountRefunded($orderItem);
        $this->assertEquals(self::ROW_AMOUNT_REFUNDED, $value);
    }

    public function testGetBaseWeeeAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getBaseWeeeAmountRefunded($orderItem);
        $this->assertEquals(self::BASE_ROW_AMOUNT_REFUNDED, $value);
    }

    public function testGetWeeeTaxAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getWeeeTaxAmountRefunded($orderItem);
        $this->assertEquals(self::TAX_AMOUNT_REFUNDED, $value);
    }

    public function testGetBaseWeeeTaxAmountRefunded()
    {
        $orderItem = $this->setupOrderItem();
        $value = $this->helperData->getBaseWeeeTaxAmountRefunded($orderItem);
        $this->assertEquals(self::BASE_TAX_AMOUNT_REFUNDED, $value);
    }

    public function testGetWeeAttributesForBundle()
    {
        $weeObject = new \Magento\Framework\DataObject(
            [
                'code' => 'fpt',
                'amount' => '15.0000',
            ]
        );
        $testArray = ['fpt' => $weeObject];

        $this->weeeTax->expects($this->any())
            ->method('getProductWeeeAttributes')
            ->will($this->returnValue([$weeObject]));

        $productSimple=$this->getMock('\Magento\Catalog\Model\Product\Type\Simple', [], [], '', false);

        $productInstance=$this->getMock('\Magento\Bundle\Model\Product\Type', [], [], '', false);
        $productInstance->expects($this->any())
            ->method('getSelectionsCollection')
            ->will($this->returnValue([$productSimple]));

        $store=$this->getMock('\Magento\Store\Model\Store', [], [], '', false);


        $product=$this->getMock(
            '\Magento\Bundle\Model\Product',
            ['getTypeInstance', 'getStoreId', 'getStore', 'getTypeId'],
            [],
            '',
            false
        );
        $product->expects($this->any())
            ->method('getTypeInstance')
            ->will($this->returnValue($productInstance));

        $product->expects($this->any())
            ->method('getStoreId')
            ->will($this->returnValue(1));

        $product->expects($this->any())
            ->method('getStore')
            ->will($this->returnValue($store));

        $product->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue('bundle'));

        $registry=$this->getMock('Magento\Framework\Registry', [], [], '', false);
        $registry->expects($this->any())
            ->method('registry')
            ->with('current_product')
            ->will($this->returnValue($product));

        $this->assertEquals($testArray, $this->helperData->getWeeAttributesForBundle($product));
    }

    public function testGetAppliedSimple()
    {
        $testArray = ['key' => 'value'];
        $itemProductSimple=$this->getMock('\Magento\Quote\Model\Quote\Item', ['getWeeeTaxApplied'], [], '', false);
        $itemProductSimple->expects($this->any())
            ->method('getHasChildren')
            ->will($this->returnValue(false));

        $itemProductSimple->expects($this->any())
            ->method('getWeeeTaxApplied')
            ->will($this->returnValue(\Zend_Json::encode($testArray)));

        $this->assertEquals($testArray, $this->helperData->getApplied($itemProductSimple));
    }

    public function testGetAppliedBundle()
    {
        $testArray1 = ['key1' => 'value1'];
        $testArray2 = ['key2' => 'value2'];

        $testArray = array_merge($testArray1, $testArray2);

        $itemProductSimple1=$this->getMock('\Magento\Quote\Model\Quote\Item', ['getWeeeTaxApplied'], [], '', false);
        $itemProductSimple2=$this->getMock('\Magento\Quote\Model\Quote\Item', ['getWeeeTaxApplied'], [], '', false);

        $itemProductSimple1->expects($this->any())
            ->method('getWeeeTaxApplied')
            ->will($this->returnValue(\Zend_Json::encode($testArray1)));

        $itemProductSimple2->expects($this->any())
            ->method('getWeeeTaxApplied')
            ->will($this->returnValue(\Zend_Json::encode($testArray2)));

        $itemProductBundle=$this->getMock(
            '\Magento\Quote\Model\Quote\Item',
            ['getHasChildren', 'isChildrenCalculated', 'getChildren'],
            [],
            '',
            false
        );
        $itemProductBundle->expects($this->any())
            ->method('getHasChildren')
            ->will($this->returnValue(true));
        $itemProductBundle->expects($this->any())
            ->method('isChildrenCalculated')
            ->will($this->returnValue(true));
        $itemProductBundle->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue([$itemProductSimple1, $itemProductSimple2]));


        $this->assertEquals($testArray, $this->helperData->getApplied($itemProductBundle));
    }

    public function testGetAppliedAmountSimple()
    {
        $testResult = 2;
        $itemProductSimple=$this->getMock(
            '\Magento\Quote\Model\Quote\Item',
            ['getWeeeTaxAppliedAmount'],
            [],
            '',
            false
        );
        $itemProductSimple->expects($this->any())
            ->method('getHasChildren')
            ->will($this->returnValue(false));

        $itemProductSimple->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->will($this->returnValue(\Zend_Json::encode($testResult)));

        $this->assertEquals($testResult, $this->helperData->getAppliedAmount($itemProductSimple));
    }

    public function getAppliedAmountBundle()
    {
        $testAmount1 = 1;
        $testAmount2 = 2;

        $testArray = $testAmount1 + $testAmount2;

        $itemProductSimple1=$this->getMock(
            '\Magento\Quote\Model\Quote\Item',
            ['getWeeeTaxAppliedAmount'],
            [],
            '',
            false
        );
        $itemProductSimple2=$this->getMock(
            '\Magento\Quote\Model\Quote\Item',
            ['getWeeeTaxAppliedAmount'],
            [],
            '',
            false
        );

        $itemProductSimple1->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->will($this->returnValue(\Zend_Json::encode($testAmount1)));

        $itemProductSimple2->expects($this->any())
            ->method('getWeeeTaxAppliedAmount')
            ->will($this->returnValue(\Zend_Json::encode($testAmount2)));

        $itemProductBundle=$this->getMock(
            '\Magento\Quote\Model\Quote\Item',
            ['getHasChildren', 'isChildrenCalculated', 'getChildren'],
            [],
            '',
            false
        );
        $itemProductBundle->expects($this->any())
            ->method('getHasChildren')
            ->will($this->returnValue(true));
        $itemProductBundle->expects($this->any())
            ->method('isChildrenCalculated')
            ->will($this->returnValue(true));
        $itemProductBundle->expects($this->any())
            ->method('getChildren')
            ->will($this->returnValue([$itemProductSimple1, $itemProductSimple2]));

        $this->assertEquals($testArray, $this->helperData->getAppliedAmount($itemProductBundle));
    }
}
