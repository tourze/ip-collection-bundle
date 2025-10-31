<?php

declare(strict_types=1);

namespace IpCollectionBundle\Tests\Service;

use IpCollectionBundle\Service\AdminMenu;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\MenuFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * AdminMenu 单元测试
 *
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private ItemInterface $item;

    public function testInvokeMethod(): void
    {
        // 测试 AdminMenu 的 __invoke 方法正常工作
        $this->expectNotToPerformAssertions();

        try {
            $adminMenu = self::getService(AdminMenu::class);
            ($adminMenu)($this->item);
        } catch (\Throwable $e) {
            Assert::fail('AdminMenu __invoke method should not throw exception: ' . $e->getMessage());
        }
    }

    protected function onSetUp(): void
    {
        // 使用真实的Knp Menu组件，避免手工实现的规则问题
        $factory = new MenuFactory();
        $this->item = $factory->createItem('test');
    }
}
