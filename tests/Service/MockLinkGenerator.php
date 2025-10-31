<?php

namespace IpCollectionBundle\Tests\Service;

use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;

/**
 * Mock 的 LinkGenerator 实现,用于测试
 */
class MockLinkGenerator implements LinkGeneratorInterface
{
    public function getCurdListPage(string $entityClass): string
    {
        return 'https://example.com/admin';
    }

    public function extractEntityFqcn(string $url): ?string
    {
        return null;
    }

    public function setDashboard(string $dashboardControllerFqcn): void
    {
        // Mock实现,不需要实际逻辑
    }
}
