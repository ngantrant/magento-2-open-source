<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\ImportExport\Model\Export\Config;

class SchemaLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_moduleReaderMock;

    /**
     * @var \Magento\ImportExport\Model\Export\Config\SchemaLocator
     */
    protected $_model;

    protected function setUp()
    {
        $this->_moduleReaderMock = $this->getMock('Magento\Framework\Module\Dir\Reader', [], [], '', false);

        $this->_moduleReaderMock->expects(
            $this->any()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_ImportExport'
        )->will(
            $this->returnValue('schema_dir')
        );
        $this->_model = new \Magento\ImportExport\Model\Export\Config\SchemaLocator($this->_moduleReaderMock);
    }

    public function testGetSchema()
    {
        $this->assertEquals('schema_dir/export_merged.xsd', $this->_model->getSchema());
    }

    public function testGetPerFileSchema()
    {
        $this->assertEquals('schema_dir/export.xsd', $this->_model->getPerFileSchema());
    }
}
