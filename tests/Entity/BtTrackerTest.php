<?php

namespace IpCollectionBundle\Tests\Entity;

use IpCollectionBundle\Entity\BtTracker;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(BtTracker::class)]
final class BtTrackerTest extends AbstractEntityTestCase
{
    public function testToStringShouldReturnTrackerUrl(): void
    {
        $btTracker = new BtTracker();
        $btTracker->setScheme('https');
        $btTracker->setHost('tracker.example.com');
        $btTracker->setPort(6969);

        $this->assertEquals('https://tracker.example.com:6969', (string) $btTracker);
    }

    public function testToStringWithoutPortShouldReturnTrackerUrl(): void
    {
        $btTracker = new BtTracker();
        $btTracker->setScheme('http');
        $btTracker->setHost('tracker.example.com');

        $this->assertEquals('http://tracker.example.com', (string) $btTracker);
    }

    public function testToStringWithNullSchemeShouldReturnTrackerUrl(): void
    {
        $btTracker = new BtTracker();
        $btTracker->setHost('tracker.example.com');

        $this->assertEquals('tracker.example.com', (string) $btTracker);
    }

    /**
     * 创建被测实体的一个实例.
     */
    protected function createEntity(): object
    {
        return new BtTracker();
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'scheme' => ['scheme', 'https'];
        yield 'host' => ['host', 'tracker.example.com'];
        yield 'port' => ['port', 6969];
        yield 'createTime' => ['createTime', new \DateTimeImmutable('2023-01-01 12:00:00')];
    }
}
