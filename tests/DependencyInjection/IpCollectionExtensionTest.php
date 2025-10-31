<?php

namespace IpCollectionBundle\Tests\DependencyInjection;

use IpCollectionBundle\DependencyInjection\IpCollectionExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Tourze\PHPUnitSymfonyUnitTest\AbstractDependencyInjectionExtensionTestCase;

/**
 * @internal
 */
#[CoversClass(IpCollectionExtension::class)]
final class IpCollectionExtensionTest extends AbstractDependencyInjectionExtensionTestCase
{
    public function testGetAlias(): void
    {
        $extension = new IpCollectionExtension();
        $alias = $extension->getAlias();
        $this->assertEquals('ip_collection', $alias);
    }

    public function testExtendsSymfonyExtension(): void
    {
        $extension = new IpCollectionExtension();
        $this->assertInstanceOf(Extension::class, $extension);
    }
}
