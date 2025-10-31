<?php

declare(strict_types=1);

namespace IpCollectionBundle\Tests;

use IpCollectionBundle\IpCollectionBundle;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(IpCollectionBundle::class)]
#[RunTestsInSeparateProcesses]
final class IpCollectionBundleTest extends AbstractBundleTestCase
{
}
