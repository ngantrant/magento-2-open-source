<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Controller;

use Magento\Framework\Composer\ComposerInformation;
use Magento\Framework\Module\PackageInfo;
use Magento\Framework\Module\PackageInfoFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Setup\Controller\ModuleGrid;
use Magento\Setup\Model\ObjectManagerProvider;
use Magento\Framework\Module\FullModuleList;
use Magento\Framework\Module\ModuleList;

/**
 * Class ModuleGridTest
 */
class ModuleGridTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComposerInformation|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composerInformationMock;

    /**
     * @var FullModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fullModuleListMock;

    /**
     * @var ModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $moduleListMock;

    /**
     * @var PackageInfoFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packageInfoFactoryMock;

    /**
     * Module package info
     *
     * @var PackageInfo|\PHPUnit_Framework_MockObject_MockObject
     */
    private $packageInfoMock;
    
    /**
     * @var ObjectManagerProvider|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerProvider;

    /**
     * Controller
     *
     * @var ModuleGrid
     */
    private $controller;
    
    /**
     * @var array
     */
    private $moduleData = [];

    /**
     * @var array
     */
    private $allComponentData = [];

    public function setUp()
    {
        $this->moduleData = [
            'magento/sample-module-one' => [
                'name' => 'magento/sample-module-one',
                'type' => 'magento2-module',
                'version' => '1.0.0'
            ]
        ];
        $this->allComponentData = [
            'magento/sample-module-one' => [
                'name' => 'magento/sample-module-one',
                'type' => 'magento2-module',
                'version' => '1.0.0'
            ],
            'magento/sample-module-two' => [
                'name' => 'magento/sample-module-two',
                'type' => 'magento2-module',
                'version' => '1.0.0'
            ]
        ];

        $this->composerInformationMock = $this->getMockBuilder(ComposerInformation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerProvider = $this->getMockBuilder(ObjectManagerProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->packageInfoFactoryMock = $this->getMockBuilder(PackageInfoFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->moduleListMock = $this->getMockBuilder(ModuleList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->moduleListMock->expects(static::any())
            ->method('has')
            ->willReturn(true);

        $this->fullModuleListMock = $this->getMockBuilder(FullModuleList::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->fullModuleListMock->expects(static::any())
            ->method('getNames')
            ->willReturn(array_keys($this->allComponentData));
        
        $this->packageInfoMock = $this->getMockBuilder(PackageInfo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new ModuleGrid(
            $this->composerInformationMock,
            $this->fullModuleListMock,
            $this->moduleListMock,
            $this->objectManagerProvider
        );
    }

    public function testIndexAction()
    {
        $viewModel = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $viewModel);
        $this->assertTrue($viewModel->terminate());
    }

    public function testModulesAction()
    {
        $objectManager = $this->getMock(ObjectManagerInterface::class);
        $this->objectManagerProvider->expects($this->once())
            ->method('get')
            ->willReturn($objectManager);
        $objectManager->expects(static::once())
            ->method('get')
            ->willReturnMap([
                [PackageInfoFactory::class, $this->packageInfoFactoryMock],
            ]);
        $this->packageInfoFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->packageInfoMock);

        $this->packageInfoMock->expects(static::exactly(2))
            ->method('getModuleName')
            ->willReturnMap([
                ['magento/sample-module-one', 'Sample_Module_One'],
                ['magento/sample-module-two', 'Sample_Module_Two'],
            ]);

        $this->packageInfoMock->expects(static::exactly(2))
            ->method('getRequiredBy')
            ->willReturn([]);
        $this->packageInfoMock
            ->method('getPackageName')
            ->willReturnMap([
                    ['magento/sample-module-one', $this->allComponentData['magento/sample-module-one']['name']],
                    ['magento/sample-module-two', $this->allComponentData['magento/sample-module-two']['name']],
                ]);
        $this->packageInfoMock->expects(static::exactly(2))
            ->method('getVersion')
            ->willReturnMap([
                ['magento/sample-module-one', $this->allComponentData['magento/sample-module-one']['version']],
                ['magento/sample-module-two', $this->allComponentData['magento/sample-module-two']['version']],
            ]);
        $this->moduleListMock->expects(static::exactly(2))
            ->method('has')
            ->willReturn(true);
        $this->composerInformationMock->expects(static::once())
            ->method('getInstalledMagentoPackages')
            ->willReturn($this->moduleData);

        $jsonModel = $this->controller->modulesAction();
        $this->assertInstanceOf('Zend\View\Model\JsonModel', $jsonModel);
        $variables = $jsonModel->getVariables();
        $this->assertArrayHasKey('success', $variables);
        $this->assertTrue($variables['success']);
        $expected = [
            [
                'name' => 'magento/sample-module-one',
                'type' => 'module',
                'version' => '1.0.0',
                'vendor' => 'magento',
                'moduleName' => 'Sample_Module_One',
                'enable' => true,
                'requiredBy' => []
            ],
            [
                'name' => 'magento/sample-module-two',
                'type' => 'module',
                'version' => '1.0.0',
                'vendor' => 'magento',
                'moduleName' => 'Sample_Module_Two',
                'enable' => true,
                'requiredBy' => []
            ]
        ];
        $this->assertEquals($expected, $variables['modules']);
        $this->assertArrayHasKey('total', $variables);
        $this->assertEquals(2, $variables['total']);
    }
}
