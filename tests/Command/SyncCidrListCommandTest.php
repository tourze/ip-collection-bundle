<?php

namespace IpCollectionBundle\Tests\Command;

use IpCollectionBundle\Command\SyncCidrListCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Console\Tester\CommandTester;
use Tourze\PHPUnitSymfonyKernelTest\AbstractCommandTestCase;

/**
 * @internal
 */
#[CoversClass(SyncCidrListCommand::class)]
#[RunTestsInSeparateProcesses]
final class SyncCidrListCommandTest extends AbstractCommandTestCase
{
    protected function onSetUp(): void
    {
    }

    protected function getCommandTester(): CommandTester
    {
        $command = self::getService(SyncCidrListCommand::class);

        return new CommandTester($command);
    }

    public function testCommandName(): void
    {
        $this->assertEquals('game-boost:sync-cidr', SyncCidrListCommand::NAME);

        $command = self::getService(SyncCidrListCommand::class);
        $this->assertEquals('game-boost:sync-cidr', $command->getName());
    }

    public function testCommandDescription(): void
    {
        $command = self::getService(SyncCidrListCommand::class);
        $this->assertEquals('同步IP地址信息', $command->getDescription());
    }

    public function testGetProvidersMethod(): void
    {
        $command = self::getService(SyncCidrListCommand::class);

        $reflection = new \ReflectionClass($command);
        $getProvidersMethod = $reflection->getMethod('getProviders');
        $getProvidersMethod->setAccessible(true);

        $result = $getProvidersMethod->invoke($command);
        $this->assertInstanceOf(\Traversable::class, $result);
        $providers = iterator_to_array($result);

        $this->assertIsArray($providers);
        $this->assertArrayHasKey('geoip2-cn', $providers);
        $this->assertArrayHasKey('aliyun', $providers);
        $this->assertArrayHasKey('cloudflare', $providers);

        $this->assertIsArray($providers['geoip2-cn']);
        $this->assertNotEmpty($providers['geoip2-cn']);
        $geoip2CnFirstUrl = $providers['geoip2-cn'][0];
        $this->assertIsString($geoip2CnFirstUrl);
        $this->assertStringContainsString('https://', $geoip2CnFirstUrl);
    }
}
