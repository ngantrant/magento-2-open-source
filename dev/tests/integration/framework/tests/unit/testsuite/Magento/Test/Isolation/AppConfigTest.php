<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\TestFramework\Isolation\WorkingDirectory.
 */
namespace Magento\Test\Isolation;

class AppConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Isolation\WorkingDirectory
     */
    private $model;

    protected function setUp()
    {
        $this->model = new \Magento\TestFramework\Isolation\AppConfig();
    }

    protected function tearDown()
    {
        $this->model = null;
    }

    public function testStartTestEndTest()
    {
        $test = $this->getMockBuilder(\PHPUnit_Framework_TestCase::class)
            ->disableOriginalConstructor()
            ->getMock();
        $modelReflection = new \ReflectionClass($this->model);
        $testAppConfigProperty = $modelReflection->getProperty('testAppConfig');
        $testAppConfigProperty->setAccessible(true);
        $testAppConfigMock = $this->getMockBuilder(\Magento\TestFramework\App\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testAppConfigProperty->setValue($this->model, $testAppConfigMock);
        $testAppConfigMock->expects($this->once())
            ->method('clean');
        $this->model->startTest($test);
    }
}
