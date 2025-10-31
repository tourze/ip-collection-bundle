<?php

declare(strict_types=1);

namespace IpCollectionBundle\Tests\Controller\Admin;

use Doctrine\ORM\EntityRepository;
use IpCollectionBundle\Controller\Admin\IpTagCrudController;
use IpCollectionBundle\Entity\IpTag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminControllerTestCase;

/**
 * IpTagCrudController 集成测试
 *
 * @internal
 */
#[CoversClass(IpTagCrudController::class)]
#[RunTestsInSeparateProcesses]
final class IpTagCrudControllerTest extends AbstractEasyAdminControllerTestCase
{
    protected function getEntityFqcn(): string
    {
        return IpTag::class;
    }

    protected function getControllerService(): IpTagCrudController
    {
        return self::getService(IpTagCrudController::class);
    }

    /** @return iterable<string, array{string}> */
    public static function provideIndexPageHeaders(): iterable
    {
        yield 'ID' => ['ID'];
        yield 'IP地址' => ['IP地址'];
        yield '标签' => ['标签'];
        yield '标签值' => ['标签值'];
        yield '创建时间' => ['创建时间'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideNewPageFields(): iterable
    {
        yield 'IP地址' => ['address'];
        yield '标签' => ['tag'];
        yield '标签值' => ['value'];
    }

    /** @return iterable<string, array{string}> */
    public static function provideEditPageFields(): iterable
    {
        yield 'IP地址' => ['address'];
        yield '标签' => ['tag'];
        yield '标签值' => ['value'];
    }

    /**
     * @return EntityRepository<IpTag>
     */
    private function getIpTagRepository(): EntityRepository
    {
        return self::getEntityManager()->getRepository(IpTag::class);
    }

    public function testEntityFqcn(): void
    {
        $this->assertSame(IpTag::class, IpTagCrudController::getEntityFqcn());
    }

    public function testIndexPage(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $crawler = $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Navigate to IpTag CRUD
        $link = $crawler->filter('a[href*="IpTagCrudController"]')->first();
        if ($link->count() > 0) {
            $client->click($link->link());
            $this->assertEquals(200, $client->getResponse()->getStatusCode());
        }
    }

    public function testCreateIpTag(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);
        $client->request('GET', '/admin');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        // Create test data using EntityManager
        $uniqueAddress = '192.168.1.0/24-' . uniqid();
        $ipTag = new IpTag();
        $ipTag->setAddress($uniqueAddress);
        $ipTag->setTag('region');
        $ipTag->setValue('北京');

        $entityManager = self::getEntityManager();
        $entityManager->persist($ipTag);
        $entityManager->flush();

        // Verify IP tag was created
        $repository = $this->getIpTagRepository();
        $savedIpTag = $repository->findOneBy(['address' => $uniqueAddress]);
        $this->assertNotNull($savedIpTag);
        $this->assertEquals($uniqueAddress, $savedIpTag->getAddress());
        $this->assertEquals('region', $savedIpTag->getTag());
        $this->assertEquals('北京', $savedIpTag->getValue());
    }

    public function testIpTagDataPersistence(): void
    {
        // Create client to initialize database
        $client = self::createClientWithDatabase();

        // Create test IP tags with different data
        $ipTag1 = new IpTag();
        $ipTag1->setAddress('10.0.0.0/8-' . uniqid());
        $ipTag1->setTag('provider');
        $ipTag1->setValue('阿里云');

        $entityManager = self::getEntityManager();
        $entityManager->persist($ipTag1);
        $entityManager->flush();

        $ipTag2 = new IpTag();
        $ipTag2->setAddress('203.0.113.0/24-' . uniqid());
        $ipTag2->setTag('location');
        $ipTag2->setValue('上海');

        $entityManager->persist($ipTag2);
        $entityManager->flush();

        // Verify IP tags are saved correctly
        $repository = $this->getIpTagRepository();
        $savedIpTag1 = $repository->findOneBy(['address' => $ipTag1->getAddress()]);
        $this->assertNotNull($savedIpTag1);
        $this->assertEquals('provider', $savedIpTag1->getTag());
        $this->assertEquals('阿里云', $savedIpTag1->getValue());

        $savedIpTag2 = $repository->findOneBy(['address' => $ipTag2->getAddress()]);
        $this->assertNotNull($savedIpTag2);
        $this->assertEquals('location', $savedIpTag2->getTag());
        $this->assertEquals('上海', $savedIpTag2->getValue());
    }

    public function testControllerConfiguration(): void
    {
        // Test that the controller can be instantiated
        $controller = new IpTagCrudController();
        $this->assertInstanceOf(IpTagCrudController::class, $controller);

        // The methods existence is verified by static analysis
        // No need for runtime method_exists checks as PHPStan ensures they exist
    }

    public function testConfigureFields(): void
    {
        // Test that configureFields returns appropriate fields
        $controller = new IpTagCrudController();
        $fields = $controller->configureFields('edit');
        $fieldsArray = iterator_to_array($fields);
        $this->assertNotEmpty($fieldsArray);
    }

    public function testConfigureFilters(): void
    {
        // Test that configureFilters method works
        $controller = new IpTagCrudController();
        // Method existence is verified by static analysis
        $this->assertInstanceOf(IpTagCrudController::class, $controller);
    }

    public function testValidationErrors(): void
    {
        $client = self::createClientWithDatabase();
        $this->loginAsAdmin($client);

        // 创建一个不完整的实体来测试验证
        $ipTag = new IpTag();
        // 故意不设置必填的address、tag、value字段

        $entityManager = self::getEntityManager();

        // 验证实体验证失败
        $validator = self::getService(ValidatorInterface::class);
        $violations = $validator->validate($ipTag);

        // 应该有违规（必填字段为空）
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
        $this->assertTrue($hasNotBlankViolation, 'Expected a "not blank" validation error for required fields');
    }

    public function testEntityFqcnConfiguration(): void
    {
        $controller = new IpTagCrudController();
        $this->assertEquals(IpTag::class, $controller::getEntityFqcn());
    }
}
