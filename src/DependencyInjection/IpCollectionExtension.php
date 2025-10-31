<?php

declare(strict_types=1);

namespace IpCollectionBundle\DependencyInjection;

use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class IpCollectionExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
