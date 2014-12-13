<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Code;

class NameBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Code\NameBuilder
     */
    protected $nameBuilder;

    protected function setUp()
    {
        $nelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->nameBuilder = $nelper->getObject('Magento\Framework\Code\NameBuilder');
    }

    /**
     * @param array $parts
     * @param string $expected
     *
     * @dataProvider buildClassNameDataProvider
     */
    public function testBuildClassName($parts, $expected)
    {
        $this->assertEquals($expected, $this->nameBuilder->buildClassName($parts));
    }

    public function buildClassNameDataProvider()
    {
        return [
            [['Checkout', 'Controller', 'Index'], 'Checkout\Controller\Index'],
            [['checkout', 'controller', 'index'], 'Checkout\Controller\Index'],
            [
                ['magento_backend', 'block', 'system', 'store', 'edit'],
                'Magento\Backend\Block\System\Store\Edit'
            ],
            [['MyNamespace', 'MyModule'], 'MyNamespace\MyModule'],
            [['uc', 'words', 'test'], 'Uc\Words\Test'],
            [['ALL', 'CAPS', 'TEST'], 'ALL\CAPS\TEST'],
        ];
    }
}
