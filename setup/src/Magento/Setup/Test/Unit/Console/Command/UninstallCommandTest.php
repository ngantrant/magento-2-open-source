<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Console\Command;

use Magento\Setup\Console\Command\UninstallCommand;
use Magento\Setup\Model\Installer;
use Magento\Setup\Model\InstallerFactory;
use Symfony\Component\Console\Tester\CommandTester;

class UninstallCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var InstallerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $installerFactory;

    /**
     * @var Installer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $installer;

    /**
     * @var UninstallCommand|\PHPUnit_Framework_MockObject_MockObject
     */
    private $command;

    public function setUp()
    {
        $this->installerFactory = $this->getMock(\Magento\Setup\Model\InstallerFactory::class, [], [], '', false);
        $this->installer = $this->getMock(\Magento\Setup\Model\Installer::class, [], [], '', false);
        $this->command = new UninstallCommand($this->installerFactory);
    }

    public function testExecuteInteractionYes()
    {
        $this->installer->expects($this->once())->method('uninstall');
        $this->installerFactory->expects($this->once())->method('create')->will($this->returnValue($this->installer));

        $this->checkInteraction(true);
    }

    public function testExecuteInteractionNo()
    {
        $this->installer->expects($this->exactly(0))->method('uninstall');
        $this->installerFactory->expects($this->exactly(0))->method('create');

        $this->checkInteraction(false);
    }

    public function checkInteraction($answer)
    {
        $question = $this->getMock(\Symfony\Component\Console\Helper\QuestionHelper::class, [], [], '', false);
        $question
            ->expects($this->once())
            ->method('ask')
            ->will($this->returnValue($answer));

        /** @var \Symfony\Component\Console\Helper\HelperSet|\PHPUnit_Framework_MockObject_MockObject $helperSet */
        $helperSet = $this->getMock(\Symfony\Component\Console\Helper\HelperSet::class, [], [], '', false);
        $helperSet
            ->expects($this->once())
            ->method('get')
            ->with('question')
            ->will($this->returnValue($question));
        $this->command->setHelperSet($helperSet);

        $tester = new CommandTester($this->command);
        $tester->execute([]);
    }
}
