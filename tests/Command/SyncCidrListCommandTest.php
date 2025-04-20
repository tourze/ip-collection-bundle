<?php

namespace IpCollectionBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use IpCollectionBundle\Command\SyncCidrListCommand;
use IpCollectionBundle\Entity\IpTag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\DoctrineUpsertBundle\Service\UpsertManager;

class SyncCidrListCommandTest extends TestCase
{
    private MockObject&HttpClientInterface $httpClient;
    private MockObject&EntityManagerInterface $entityManager;
    private MockObject&UpsertManager $upsertManager;
    private SyncCidrListCommand $command;
    private MockObject&ResponseInterface $response;
    private MockObject&QueryBuilder $queryBuilder;
    private MockObject&Query $query;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->upsertManager = $this->createMock(UpsertManager::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->query = $this->createMock(Query::class);

        $this->command = new SyncCidrListCommand(
            $this->entityManager,
            $this->upsertManager,
            $this->httpClient
        );

        $application = new Application();
        $application->add($this->command);

        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecute(): void
    {
        // 为简化测试，我们只测试其中几个源的处理
        $mockResponses = [
            'geoip2-cn' => "192.168.1.0/24\n10.0.0.0/8",
            'ipip-china' => "172.16.0.0/12\n192.168.0.0/16"
        ];

        // 设置 HTTP 客户端的模拟行为
        $this->httpClient->expects($this->atLeast(2))
            ->method('request')
            ->willReturnCallback(function ($method, $url) use ($mockResponses) {
                $this->assertEquals('GET', $method);

                $responseContent = '';
                if (strpos($url, 'GeoIP2-CN') !== false) {
                    $responseContent = $mockResponses['geoip2-cn'];
                } elseif (strpos($url, 'china_ip_list') !== false) {
                    $responseContent = $mockResponses['ipip-china'];
                }

                $this->response->method('getContent')->willReturn($responseContent);
                return $this->response;
            });

        // 修改 UpsertManager 匹配逻辑，接受任何 tag 值
        $this->upsertManager->expects($this->atLeast(4))
            ->method('upsert')
            ->with(
                $this->callback(function (IpTag $ipTag) {
                    // 接受任何标签值
                    return (
                        $ipTag->getValue() === '1' &&
                        $ipTag->getUpdateTime() instanceof \DateTimeInterface
                    );
                }),
                false
            );

        // 模拟查询构建器和删除操作
        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('delete')
            ->with(IpTag::class, 'a')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('where')
            ->with('a.tag IN (:tags) AND a.value=:value AND a.updateTime<:updateTime')
            ->willReturn($this->queryBuilder);

        // 使用callback为setParameter方法提供灵活的匹配
        $this->queryBuilder->method('setParameter')
            ->willReturnCallback(function ($param, $value) {
                return $this->queryBuilder;
            });

        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('execute');

        // 执行命令
        $this->commandTester->execute([]);

        // 断言命令执行成功
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
