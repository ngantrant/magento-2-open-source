<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Quote\Test\Unit\Model\Quote;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\Checks\Composite;
use Magento\Payment\Model\Checks\SpecificationFactory;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Model\Quote;

use Magento\Quote\Model\Quote\Payment;

class PaymentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Payment
     */
    private $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|SpecificationFactory
     */
    private $specificationFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ManagerInterface
     */
    private $eventManager;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->specificationFactory = $this->getMockBuilder(
            SpecificationFactory::class
        )->disableOriginalConstructor()
            ->getMock();
        $this->eventManager = $this->getMock(ManagerInterface::class);
        $serializer = $this->getMockBuilder(\Magento\Framework\Serialize\Serializer\Json::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $serializer->expects($this->any())
            ->method('unserialize')
            ->willReturnCallback(function ($value) {
                return json_decode($value, true);
            });

        $this->model = $objectManager->getObject(
            Payment::class,
            [
                'methodSpecificationFactory' => $this->specificationFactory,
                'eventDispatcher' => $this->eventManager,
                'serializer' => $serializer
            ]
        );
    }

    /**
     * @param int|string|null $databaseValue
     * @param int|string|null $expectedValue
     * @dataProvider yearValueDataProvider
     */
    public function testGetCcExpYearReturnsValidValue($databaseValue, $expectedValue)
    {
        $this->model->setData('cc_exp_year', $databaseValue);
        static::assertEquals($expectedValue, $this->model->getCcExpYear());
    }

    /**
     * @return array
     */
    public function yearValueDataProvider()
    {
        return [
            [null, null],
            [0, null],
            ['0', 0],
            [1939, 1939],
        ];
    }

    /**
     * @param array $data
     * @param array $convertedData
     * @param array $dataToAssign
     * @param array $checks
     * @dataProvider importDataPositiveCheckDataProvider
     */
    public function testImportDataPositiveCheck(
        array $data,
        array $convertedData,
        array $dataToAssign,
        array $checks
    ) {
        $quoteId = 1;
        $storeId = 1;

        $paymentMethod = $this->getMock(MethodInterface::class);
        $quote = $this->getMockBuilder(Quote::class)
            ->disableOriginalConstructor()
            ->getMock();
        $methodSpecification = $this->getMockBuilder(Composite::class)
            ->disableOriginalConstructor()
            ->getMock();

        $quote->expects(static::once())
            ->method('getId')
            ->willReturn($quoteId);

        $this->model->setQuote($quote);
        $this->model->setMethodInstance($paymentMethod);
        $this->eventManager->expects(static::once())
            ->method('dispatch')
            ->with(
                'sales_quote_payment_import_data_before',
                [
                    'payment' => $this->model,
                    'input' => new DataObject($convertedData)
                ]
            );
        $quote->expects(static::once())
            ->method('getStoreId')
            ->willReturn($storeId);

        $quote->expects(static::once())
            ->method('collectTotals');

        $this->specificationFactory->expects(static::once())
            ->method('create')
            ->with($checks)
            ->willReturn($methodSpecification);

        $paymentMethod->expects(static::once())
            ->method('isAvailable')
            ->with($quote)
            ->willReturn(true);
        $methodSpecification->expects(static::once())
            ->method('isApplicable')
            ->with($paymentMethod, $quote)
            ->willReturn(true);

        $paymentMethod->expects(static::once())
            ->method('assignData')
            ->with(new DataObject($dataToAssign));
        $paymentMethod->expects(static::once())
            ->method('validate');

        $this->model->importData($data);
    }

    /**
     * @covers \Magento\Quote\Model\Quote\Payment::getAdditionalData()
     * @dataProvider getAdditionalDataDataProvider
     * @param mixed $expected
     * @param mixed $additionalData
     */
    public function testGetAdditionalData($expected, $additionalData)
    {
        $this->model->setData(Payment::KEY_ADDITIONAL_DATA, $additionalData);
        $this->assertSame($expected, $this->model->getAdditionalData());
    }

    /**
     * @return array
     */
    public function getAdditionalDataDataProvider()
    {
        return [
            // Variation #1
            [
                //$expected
                ['field1' => 'value1', 'field2' => 'value2'],
                //$additionalData
                ['field1' => 'value1', 'field2' => 'value2'],
            ],
            // Variation #2
            [
                //$expected
                ['field1' => 'value1', 'field2' => 'value2'],
                //$additionalData
                '{"field1":"value1","field2":"value2"}',
            ],
            // Variation #3
            [
                //$expected
                null,
                //$additionalData
                '{"field1":field2":"value2"}',
            ],
            // Variation #4
            [
                //$expected
                null,
                //$additionalData
                123,
            ],
        ];
    }

    /**
     * @return array
     */
    public function importDataPositiveCheckDataProvider()
    {
        return [
            [
                [
                    PaymentInterface::KEY_METHOD => 'payment_method_code',
                    'cc_number' => '1111',
                    'cc_type' => 'VI',
                    'cc_owner' => 'John Doe'
                ],
                [
                    PaymentInterface::KEY_METHOD => 'payment_method_code',
                    PaymentInterface::KEY_PO_NUMBER => null,
                    PaymentInterface::KEY_ADDITIONAL_DATA => [
                        'cc_number' => '1111',
                        'cc_type' => 'VI',
                        'cc_owner' => 'John Doe'
                    ],
                    'checks' => []
                ],
                [
                    PaymentInterface::KEY_METHOD => 'payment_method_code',
                    PaymentInterface::KEY_PO_NUMBER => null,
                    PaymentInterface::KEY_ADDITIONAL_DATA => [
                        'cc_number' => '1111',
                        'cc_type' => 'VI',
                        'cc_owner' => 'John Doe'
                    ],
                    'checks' => []
                ],
                []
            ],
            [
                [
                    PaymentInterface::KEY_METHOD => 'payment_method_code',
                    'cc_number' => '1111',
                    'cc_type' => 'VI',
                    'cc_owner' => 'John Doe',
                    'checks' => ['check_code1', 'check_code2']
                ],
                [
                    PaymentInterface::KEY_METHOD => 'payment_method_code',
                    PaymentInterface::KEY_PO_NUMBER => null,
                    PaymentInterface::KEY_ADDITIONAL_DATA => [
                        'cc_number' => '1111',
                        'cc_type' => 'VI',
                        'cc_owner' => 'John Doe'
                    ],
                    'checks' => ['check_code1', 'check_code2']
                ],
                [
                    PaymentInterface::KEY_METHOD => 'payment_method_code',
                    PaymentInterface::KEY_PO_NUMBER => null,
                    PaymentInterface::KEY_ADDITIONAL_DATA => [
                        'cc_number' => '1111',
                        'cc_type' => 'VI',
                        'cc_owner' => 'John Doe'
                    ],
                    'checks' => ['check_code1', 'check_code2']
                ],
                ['check_code1', 'check_code2']
            ]
        ];
    }
}
