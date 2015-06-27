<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Test\Unit\Model\Cron;

use Magento\Setup\Model\Cron\JobDbRollback;

class JobDBRollbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JobDbRollback
     */
    private $jobDbRollback;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Setup\BackupRollbackFactory
     */
    private $backupRollbackFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\Setup\BackupRollback
     */
    private $backupRollback;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\App\Filesystem\DirectoryList
     */
    private $dirList;

    public function setup()
    {
        $this->backupRollbackFactory = $this->getMock(
            'Magento\Framework\Setup\BackupRollbackFactory',
            [],
            [],
            '',
            false
        );
        $this->backupRollback = $this->getMock('\Magento\Framework\Setup\BackupRollback', [], [], '', false);
        $this->dirList = $this->getMock('Magento\Framework\App\Filesystem\DirectoryList', [], [], '', false);
        $status = $this->getMock('Magento\Setup\Model\Cron\Status', [], [], '', false);
        $output = $this->getMockForAbstractClass('Symfony\Component\Console\Output\OutputInterface', [], '', false);

        $this->jobDbRollback = new JobDbRollback(
            $this->dirList,
            $this->backupRollbackFactory,
            $output,
            $status,
            'setup:rollback',
            []
        );
    }

    public function testExecute()
    {
        $this->backupRollbackFactory->expects($this->once())->method('create')->willReturn($this->backupRollback);
        $this->backupRollback->expects($this->once())->method('dbRollback');
        $this->dirList->expects($this->once())->method('getPath')->willReturn('some/path');
        $this->jobDbRollback->execute();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Could not complete
     */
    public function testExceptionOnExecute()
    {
        $this->backupRollbackFactory->expects($this->once())->method('create')->willThrowException(new \Exception);
        $this->jobDbRollback->execute();
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage No available backup file found.
     */
    public function testExceptionOnFindFile()
    {
        $this->backupRollbackFactory->expects($this->once())->method('create')->willReturn($this->backupRollback);
        $this->dirList->expects($this->once())->method('getPath')->willReturn('');
        $this->jobDbRollback->execute();
    }
}

// functions to override native php functions
namespace Magento\Setup\Model\Cron;

function scandir($inputDir)
{
    if ($inputDir == 'some/path/backups') {
        return ['file1_code', 'file2_db'];
    } else {
        return [];
    }
}
