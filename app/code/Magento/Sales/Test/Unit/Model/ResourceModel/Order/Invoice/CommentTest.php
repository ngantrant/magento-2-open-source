<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Invoice;

/**
 * Class CommentTest
 */
class CommentTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment
     */
    protected $commentResource;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\Comment|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $commentModelMock;

    /**
     * @var \Magento\Framework\App\ResourceConnection|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $appResourceMock;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $connectionMock;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\Comment\Validator|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $validatorMock;

    /**
     * @var \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $entitySnapshotMock;

    /**
     * Set up
     */
    protected function setUp()
    {
        $this->commentModelMock = $this->createMock(\Magento\Sales\Model\Order\Invoice\Comment::class);
        $this->appResourceMock = $this->createMock(\Magento\Framework\App\ResourceConnection::class);
        $this->connectionMock = $this->createMock(\Magento\Framework\DB\Adapter\Pdo\Mysql::class);
        $this->validatorMock = $this->createMock(\Magento\Sales\Model\Order\Invoice\Comment\Validator::class);
        $this->entitySnapshotMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot::class
        );
        $this->appResourceMock->expects($this->any())
            ->method('getConnection')
            ->will($this->returnValue($this->connectionMock));
        $this->connectionMock->expects($this->any())
            ->method('describeTable')
            ->will($this->returnValue([]));
        $this->connectionMock->expects($this->any())
            ->method('insert');
        $this->connectionMock->expects($this->any())
            ->method('lastInsertId');
        $this->commentModelMock->expects($this->any())->method('hasDataChanges')->will($this->returnValue(true));
        $this->commentModelMock->expects($this->any())->method('isSaveAllowed')->will($this->returnValue(true));

        $relationProcessorMock = $this->createMock(
            \Magento\Framework\Model\ResourceModel\Db\ObjectRelationProcessor::class
        );

        $contextMock = $this->createMock(\Magento\Framework\Model\ResourceModel\Db\Context::class);
        $contextMock->expects($this->once())->method('getResources')->willReturn($this->appResourceMock);
        $contextMock->expects($this->once())->method('getObjectRelationProcessor')->willReturn($relationProcessorMock);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->commentResource = $objectManager->getObject(
            \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment::class,
            [
                'context' => $contextMock,
                'validator' => $this->validatorMock,
                'entitySnapshot' => $this->entitySnapshotMock
            ]
        );
    }

    /**
     * Test _beforeSaveMethod via save()
     */
    public function testSave()
    {
        $this->entitySnapshotMock->expects($this->once())
            ->method('isModified')
            ->with($this->commentModelMock)
            ->willReturn(true);
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($this->commentModelMock))
            ->will($this->returnValue([]));
        $this->commentModelMock->expects($this->any())->method('getData')->willReturn([]);
        $this->commentResource->save($this->commentModelMock);
        $this->assertTrue(true);
    }

    /**
     * Test _beforeSaveMethod via save() with failed validation
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Cannot save comment:
     */
    public function testSaveValidationFailed()
    {
        $this->entitySnapshotMock->expects($this->once())
            ->method('isModified')
            ->with($this->commentModelMock)
            ->willReturn(true);
        $this->validatorMock->expects($this->once())
            ->method('validate')
            ->with($this->equalTo($this->commentModelMock))
            ->will($this->returnValue(['warning message']));
        $this->commentResource->save($this->commentModelMock);
        $this->assertTrue(true);
    }
}
