<?php

namespace IpCollectionBundle\Tests;

use IpCollectionBundle\IpCollectionBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IpCollectionBundleTest extends TestCase
{
    public function testBundleExtends(): void
    {
        $bundle = new IpCollectionBundle();
        $this->assertInstanceOf(Bundle::class, $bundle);
    }
}
