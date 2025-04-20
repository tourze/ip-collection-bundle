<?php

namespace IpCollectionBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use IpCollectionBundle\Command\SyncAwsIpRangeCommand;
use IpCollectionBundle\Entity\IpTag;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\DoctrineUpsertBundle\Service\UpsertManager;

class SyncAwsIpRangeCommandTest extends TestCase
{
    private MockObject&HttpClientInterface $httpClient;
    private MockObject&EntityManagerInterface $entityManager;
    private MockObject&UpsertManager $upsertManager;
    private SyncAwsIpRangeCommand $command;
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

        $this->command = new SyncAwsIpRangeCommand(
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
        // 准备测试数据
        $sampleJsonResponse = json_encode([
            'prefixes' => [
                ['ip_prefix' => '203.0.113.0/24'],
                ['ip_prefix' => '198.51.100.0/24']
            ],
            'ipv6_prefixes' => [
                ['ipv6_prefix' => '2001:db8::/32'],
                ['ipv6_prefix' => '2001:db8:1::/48']
            ]
        ]);

        // 设置 HTTP 客户端的模拟行为
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://ip-ranges.amazonaws.com/ip-ranges.json'
            )
            ->willReturn($this->response);

        $this->response->expects($this->once())
            ->method('getContent')
            ->willReturn($sampleJsonResponse);

        // 模拟 UpsertManager
        $this->upsertManager->expects($this->exactly(4))
            ->method('upsert')
            ->with(
                $this->callback(function (IpTag $ipTag) {
                    return (
                        in_array($ipTag->getTag(), ['aws-ipv4', 'aws-ipv6']) &&
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

        // 使用consecutive方法模拟连续调用
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
