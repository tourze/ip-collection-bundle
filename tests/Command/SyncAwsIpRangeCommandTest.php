<?php

namespace IpCollectionBundle\Tests\Command;

use IpCollectionBundle\Command\SyncAwsIpRangeCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(SyncAwsIpRangeCommand::class)]
#[RunTestsInSeparateProcesses]
final class SyncAwsIpRangeCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(SyncAwsIpRangeCommand::class);

        return new CommandTester($command);
    }

    public function testCommandName(): void
    {
        $this->assertEquals('ip-collection:sync-aws-ip-range', SyncAwsIpRangeCommand::NAME);

        $command = self::getService(SyncAwsIpRangeCommand::class);
        $this->assertEquals('ip-collection:sync-aws-ip-range', $command->getName());
    }

    public function testCommandDescription(): void
    {
        $command = self::getService(SyncAwsIpRangeCommand::class);
        $this->assertEquals('同步AWS-IP地址信息', $command->getDescription());
    }
}
