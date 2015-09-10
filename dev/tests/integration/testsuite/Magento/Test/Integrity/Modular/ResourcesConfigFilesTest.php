<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

class ResourcesConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Resource\Config\Reader
     */
    protected $_model;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $moduleDirSearch \Magento\Framework\Module\Dir\Search */
        $moduleDirSearch = $objectManager->get('Magento\Framework\Module\Dir\Search');
        $fileIteratorFactory = $objectManager->get('Magento\Framework\Config\FileIteratorFactory');
        $xmlFiles = $fileIteratorFactory->create($moduleDirSearch->collectFiles('etc/{*/resources.xml,resources.xml}'));

        $fileResolverMock = $this->getMock('Magento\Framework\Config\FileResolverInterface');
        $fileResolverMock->expects($this->any())->method('get')->will($this->returnValue($xmlFiles));
        $validationStateMock = $this->getMock('Magento\Framework\Config\ValidationStateInterface');
        $validationStateMock->expects($this->any())->method('isValidated')->will($this->returnValue(true));
        $deploymentConfigMock = $this->getMock('Magento\Framework\App\DeploymentConfig', [], [], '', false);
        $deploymentConfigMock->expects($this->any())->method('getConfiguration')->will($this->returnValue([]));
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $objectManager->create(
            'Magento\Framework\App\Resource\Config\Reader',
            [
                'fileResolver' => $fileResolverMock,
                'validationState' => $validationStateMock,
                'deploymentConfig' => $deploymentConfigMock
            ]
        );
    }

    public function testResourcesXmlFiles()
    {
        $this->_model->read('global');
    }
}
