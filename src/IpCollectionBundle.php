<?php

declare(strict_types=1);

namespace IpCollectionBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\DoctrineUpsertBundle\DoctrineUpsertBundle;
use Tourze\Symfony\CronJob\CronJobBundle;

class IpCollectionBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            DoctrineUpsertBundle::class => ['all' => true],
            CronJobBundle::class => ['all' => true],
        ];
    }
}
