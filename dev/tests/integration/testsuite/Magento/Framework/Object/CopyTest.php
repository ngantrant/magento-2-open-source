<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Object;

class CopyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Object\Copy
     */
    protected $_service;

    protected function setUp()
    {
        $this->_service = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Object\Copy');
    }

    public function testCopyFieldset()
    {
        $fieldset = 'sales_copy_order';
        $aspect = 'to_edit';
        $data = ['customer_email' => 'admin@example.com', 'customer_group_id' => '1'];
        $source = new \Magento\Framework\Object($data);
        $target = new \Magento\Framework\Object();
        $expectedTarget = new \Magento\Framework\Object($data);
        $expectedTarget->setDataChanges(true);
        // hack for assertion

        $this->assertNull($this->_service->copyFieldsetToTarget($fieldset, $aspect, 'invalid_source', []));
        $this->assertNull($this->_service->copyFieldsetToTarget($fieldset, $aspect, [], 'invalid_target'));
        $this->assertEquals(
            $target,
            $this->_service->copyFieldsetToTarget('invalid_fieldset', $aspect, $source, $target)
        );
        $this->assertSame($target, $this->_service->copyFieldsetToTarget($fieldset, $aspect, $source, $target));
        $this->assertEquals($expectedTarget, $target);
    }

    public function testCopyFieldsetArrayTarget()
    {
        $fieldset = 'sales_copy_order';
        $aspect = 'to_edit';
        $data = ['customer_email' => 'admin@example.com', 'customer_group_id' => '1'];
        $source = new \Magento\Framework\Object($data);
        $target = [];
        $expectedTarget = $data;

        $this->assertEquals(
            $target,
            $this->_service->copyFieldsetToTarget('invalid_fieldset', $aspect, $source, $target)
        );
        $this->assertEquals(
            $expectedTarget,
            $this->_service->copyFieldsetToTarget($fieldset, $aspect, $source, $target)
        );
    }
}
