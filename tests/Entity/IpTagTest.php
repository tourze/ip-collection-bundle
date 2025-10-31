<?php

namespace IpCollectionBundle\Tests\Entity;

use IpCollectionBundle\Entity\IpTag;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(IpTag::class)]
final class IpTagTest extends AbstractEntityTestCase
{
    public function testToStringShouldReturnFormattedString(): void
    {
        $ipTag = new IpTag();
        $ipTag->setAddress('192.168.1.1');
        $ipTag->setTag('location');
        $ipTag->setValue('office');

        $this->assertEquals('192.168.1.1[location=office]', (string) $ipTag);
    }

    /**
     * 创建被测实体的一个实例.
     */
    protected function createEntity(): object
    {
        return new IpTag();
    }

    /**
     * 提供属性及其样本值的 Data Provider.
     *
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'address' => ['address', '192.168.1.1'];
        yield 'tag' => ['tag', 'location'];
        yield 'value' => ['value', 'office'];
        yield 'createTime' => ['createTime', new \DateTimeImmutable('2023-01-01 12:00:00')];
        yield 'updateTime' => ['updateTime', new \DateTimeImmutable('2023-01-01 13:00:00')];
    }
}
