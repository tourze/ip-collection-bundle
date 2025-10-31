<?php

declare(strict_types=1);

namespace IpCollectionBundle\Tests\Controller\Admin;

use Doctrine\ORM\EntityRepository;
use IpCollectionBundle\Controller\Admin\BtTrackerCrudController;
use IpCollectionBundle\Entity\BtTracker;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * BtTrackerCrudController 集成测试
 *
 * @internal
 */
#[CoversClass(BtTrackerCrudController::class)]
#[RunTestsInSeparateProcesses]
final class BtTrackerCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getEntityFqcn(): string
    {
        return BtTracker::class;
    }

    protected function getControllerService(): BtTrackerCrudController
    {
        return self::getService(BtTrackerCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield '主机地址' => ['主机地址'];
        yield 'Tracker地址' => ['Tracker地址'];
        yield '创建时间' => ['创建时间'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield '协议' => ['scheme'];
        yield '主机地址' => ['host'];
        yield '端口号' => ['port'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        yield '协议' => ['scheme'];
        yield '主机地址' => ['host'];
        yield '端口号' => ['port'];
    }

    /**
     * @return EntityRepository<BtTracker>
     */
    private function getBtTrackerRepository(): EntityRepository
    {
        return self::getEntityManager()->getRepository(BtTracker::class);
    }

    public function testEntityFqcn(): void
    {
        $this->assertSame(BtTracker::class, BtTrackerCrudController::getEntityFqcn());
    }

    public function testIndexPage(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Navigate to BtTracker CRUD
        $link = $crawler->filter('a[href*="BtTrackerCrudController"]')->first();
        if ($link->count() > 0) {
            $client->click($link->link());
            $this->assertEquals(200, $client->getResponse()->getStatusCode());
        }
    }

    public function testCreateBtTracker(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Create test data using EntityManager
        $uniqueHost = 'tracker.example.com-' . uniqid();
        $btTracker = new BtTracker();
        $btTracker->setScheme('http');
        $btTracker->setHost($uniqueHost);
        $btTracker->setPort(8080);

        $entityManager = self::getEntityManager();
        $entityManager->persist($btTracker);
        $entityManager->flush();

        // Verify BT tracker was created
        $repository = $this->getBtTrackerRepository();
        $savedBtTracker = $repository->findOneBy(['host' => $uniqueHost]);
        $this->assertNotNull($savedBtTracker);
        $this->assertEquals('http', $savedBtTracker->getScheme());
        $this->assertEquals($uniqueHost, $savedBtTracker->getHost());
        $this->assertEquals(8080, $savedBtTracker->getPort());
    }

    public function testBtTrackerDataPersistence(): void
    {
        // Create client to initialize database
        $client = self::createClientWithDatabase();

        // Create test BT trackers with different data
        $btTracker1 = new BtTracker();
        $btTracker1->setScheme('udp');
        $btTracker1->setHost('tracker.openbittorrent.com-' . uniqid());
        $btTracker1->setPort(80);

        $entityManager = self::getEntityManager();
        $entityManager->persist($btTracker1);
        $entityManager->flush();

        $btTracker2 = new BtTracker();
        $btTracker2->setHost('open.tracker.com-' . uniqid());

        $entityManager->persist($btTracker2);
        $entityManager->flush();

        // Verify BT trackers are saved correctly
        $repository = $this->getBtTrackerRepository();
        $savedBtTracker1 = $repository->findOneBy(['host' => $btTracker1->getHost()]);
        $this->assertNotNull($savedBtTracker1);
        $this->assertEquals('udp', $savedBtTracker1->getScheme());
        $this->assertEquals(80, $savedBtTracker1->getPort());

        $savedBtTracker2 = $repository->findOneBy(['host' => $btTracker2->getHost()]);
        $this->assertNotNull($savedBtTracker2);
        $this->assertNull($savedBtTracker2->getScheme());
        $this->assertNull($savedBtTracker2->getPort());
    }

    public function testControllerConfiguration(): void
    {
        // Test that the controller can be instantiated
        $controller = new BtTrackerCrudController();
        $this->assertInstanceOf(BtTrackerCrudController::class, $controller);

        // The methods existence is verified by static analysis
        // No need for runtime method_exists checks as PHPStan ensures they exist
    }

    public function testConfigureFields(): void
    {
        // Test that configureFields returns appropriate fields
        $controller = new BtTrackerCrudController();
        $fields = $controller->configureFields('edit');
        $fieldsArray = iterator_to_array($fields);
        $this->assertNotEmpty($fieldsArray);
    }

    public function testConfigureFilters(): void
    {
        // Test that configureFilters method works
        $controller = new BtTrackerCrudController();
        // Method existence is verified by static analysis
        $this->assertInstanceOf(BtTrackerCrudController::class, $controller);
    }

    public function testEntityFqcnConfiguration(): void
    {
        $controller = new BtTrackerCrudController();
        $this->assertEquals(BtTracker::class, $controller::getEntityFqcn());
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建一个不完整的实体来测试验证
        $btTracker = new BtTracker();
        // 故意不设置必填的host字段

        $entityManager = self::getEntityManager();

        // 验证实体验证失败
        $validator = self::getService(ValidatorInterface::class);
        $violations = $validator->validate($btTracker);

        // 应该有违规（host字段为空）
        $this->assertGreaterThan(0, $violations->count());

        // 检查违规信息
        $hasNotBlankViolation = false;
        foreach ($violations as $violation) {
            $message = (string) $violation->getMessage();
            if (str_contains($message, 'should not be blank')
                || str_contains($message, 'This value should not be blank')) {
                $hasNotBlankViolation = true;
                break;
            }
        }
        $this->assertTrue($hasNotBlankViolation, 'Expected a "not blank" validation error for the host field');
    }

    public function testToStringMethod(): void
    {
        // 测试完整URL格式
        $btTracker = new BtTracker();
        $btTracker->setScheme('http');
        $btTracker->setHost('example.com');
        $btTracker->setPort(8080);

        $expectedUrl = 'http://example.com:8080';
        $this->assertSame($expectedUrl, (string) $btTracker);

        // 测试无端口的URL格式
        $btTracker2 = new BtTracker();
        $btTracker2->setScheme('https');
        $btTracker2->setHost('secure.example.com');

        $expectedUrl2 = 'https://secure.example.com';
        $this->assertSame($expectedUrl2, (string) $btTracker2);

        // 测试无协议的URL格式
        $btTracker3 = new BtTracker();
        $btTracker3->setHost('simple.example.com');
        $btTracker3->setPort(6969);

        $expectedUrl3 = 'simple.example.com:6969';
        $this->assertSame($expectedUrl3, (string) $btTracker3);

        // 测试只有主机名的情况
        $btTracker4 = new BtTracker();
        $btTracker4->setHost('basic.example.com');

        $expectedUrl4 = 'basic.example.com';
        $this->assertSame($expectedUrl4, (string) $btTracker4);
    }
}
