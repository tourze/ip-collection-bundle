<?php

namespace IpCollectionBundle\Tests\Command;

use IpCollectionBundle\Command\SyncBtPublicTrackerCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(SyncBtPublicTrackerCommand::class)]
#[RunTestsInSeparateProcesses]
final class SyncBtPublicTrackerCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(SyncBtPublicTrackerCommand::class);

        return new CommandTester($command);
    }

    public function testCommandName(): void
    {
        $this->assertEquals('bt:sync-public-tracker', SyncBtPublicTrackerCommand::NAME);

        $command = self::getService(SyncBtPublicTrackerCommand::class);
        $this->assertEquals('bt:sync-public-tracker', $command->getName());
    }

    public function testCommandDescription(): void
    {
        $command = self::getService(SyncBtPublicTrackerCommand::class);
        $this->assertEquals('收集公共的Tracker地址', $command->getDescription());
    }
}
