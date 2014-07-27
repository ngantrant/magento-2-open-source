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
namespace Magento\Eav\Model\Attribute\Data;

class MultilineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Eav\Model\Attribute\Data\Multiline
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $stringMock;

    protected function setUp()
    {
        $timezoneMock = $this->getMock('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $loggerMock = $this->getMock('\Magento\Framework\Logger', [], [], '', false);
        $localeResolverMock = $this->getMock('\Magento\Framework\Locale\ResolverInterface');
        $this->stringMock = $this->getMock('\Magento\Framework\Stdlib\String', [], [], '', false);

        $this->model = new Multiline($timezoneMock, $loggerMock, $localeResolverMock, $this->stringMock);
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Multiline::extractValue
     *
     * @param mixed $param
     * @param mixed $expectedResult
     * @dataProvider extractValueDataProvider
     */
    public function testExtractValue($param, $expectedResult)
    {
        $requestMock = $this->getMock('\Magento\Framework\App\RequestInterface');
        $attributeMock = $this->getMock('\Magento\Eav\Model\Attribute', [], [], '', false);

        $requestMock->expects($this->once())->method('getParam')->will($this->returnValue($param));
        $attributeMock->expects($this->once())->method('getAttributeCode')->will($this->returnValue('attributeCode'));

        $this->model->setAttribute($attributeMock);
        $this->assertEquals($expectedResult, $this->model->extractValue($requestMock));
    }

    /**
     * @return array
     */
    public function extractValueDataProvider()
    {
        return [
            [
                'param' => 'param',
                'expectedResult' => false
            ],
            [
                'param' => ['param'],
                'expectedResult' => ['param']
            ],
        ];
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Multiline::outputValue
     *
     * @param string $format
     * @param mixed $expectedResult
     * @dataProvider outputValueDataProvider
     */
    public function testOutputValue($format, $expectedResult)
    {
        $entityMock = $this->getMock('\Magento\Framework\Model\AbstractModel', [], [], '', false);
        $entityMock->expects($this->once())->method('getData')->will($this->returnValue("value1\nvalue2"));

        $attributeMock = $this->getMock('\Magento\Eav\Model\Attribute', [], [], '', false);

        $this->model->setEntity($entityMock);
        $this->model->setAttribute($attributeMock);
        $this->assertEquals($expectedResult, $this->model->outputValue($format));
    }

    /**
     * @return array
     */
    public function outputValueDataProvider()
    {
        return [
            [
                'format' => \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_ARRAY,
                'expectedResult' => ['value1', 'value2']
            ],
            [
                'format' => \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_HTML,
                'expectedResult' => 'value1<br />value2'
            ],
            [
                'format' => \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_ONELINE,
                'expectedResult' => 'value1 value2'
            ],
            [
                'format' => \Magento\Eav\Model\AttributeDataFactory::OUTPUT_FORMAT_TEXT,
                'expectedResult' => "value1\nvalue2"
            ]
        ];
    }

    /**
     * @covers \Magento\Eav\Model\Attribute\Data\Multiline::validateValue
     * @covers \Magento\Eav\Model\Attribute\Data\Text::validateValue
     *
     * @param mixed $value
     * @param array $rules
     * @param array $expectedResult
     * @dataProvider validateValueDataProvider
     */
    public function testValidateValue($value, $rules, $expectedResult)
    {
        $entityMock = $this->getMock('\Magento\Framework\Model\AbstractModel', [], [], '', false);
        $entityMock->expects($this->any())->method('getDataUsingMethod')->will($this->returnValue("value1\nvalue2"));

        $attributeMock = $this->getMock('\Magento\Eav\Model\Attribute', [], [], '', false);
        $attributeMock->expects($this->any())->method('getMultilineCount')->will($this->returnValue(2));
        $attributeMock->expects($this->any())->method('getValidateRules')->will($this->returnValue($rules));
        $attributeMock->expects($this->any())->method('getStoreLabel')->will($this->returnValue('Label'));

        $this->stringMock->expects($this->any())->method('strlen')->will($this->returnValue(5));

        $this->model->setEntity($entityMock);
        $this->model->setAttribute($attributeMock);
        $this->assertEquals($expectedResult, $this->model->validateValue($value));
    }

    /**
     * @return array
     */
    public function validateValueDataProvider()
    {
        return [
            [
                'value' => false,
                'rules' => [],
                'expectedResult' => true,
            ],
            [
                'value' => 'value',
                'rules' => [],
                'expectedResult' => true,
            ],
            [
                'value' => ['value1',  'value2'],
                'rules' => [],
                'expectedResult' => true,
            ],
            [
                'value' => 'value',
                'rules' => ['max_text_length' => 3],
                'expectedResult' => ['"Label" length must be equal or less than 3 characters.'],
            ],
            [
                'value' => 'value',
                'rules' => ['min_text_length' => 10],
                'expectedResult' => ['"Label" length must be equal or greater than 10 characters.'],
            ]
        ];
    }
}
