<?php

namespace IpCollectionBundle\Tests\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use IpCollectionBundle\Command\SyncBtPublicTrackerCommand;
use IpCollectionBundle\Entity\BtTracker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SyncBtPublicTrackerCommandTest extends TestCase
{
    private MockObject&HttpClientInterface $httpClient;
    private MockObject&EntityManagerInterface $entityManager;
    private SyncBtPublicTrackerCommand $command;
    private MockObject&ResponseInterface $response;
    private MockObject&QueryBuilder $queryBuilder;
    private MockObject&Query $query;
    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->queryBuilder = $this->createMock(QueryBuilder::class);
        $this->query = $this->createMock(Query::class);

        $this->command = new SyncBtPublicTrackerCommand(
            $this->httpClient,
            $this->entityManager
        );

        $application = new Application();
        $application->add($this->command);

        $this->commandTester = new CommandTester($this->command);
    }

    public function testExecute(): void
    {
        $sampleTrackers = "udp://tracker1.example.com:6969/announce\n\nhttp://tracker2.example.com:80/announce";

        // 设置 HTTP 客户端的模拟行为
        $this->httpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://raw.githubusercontent.com/ngosang/trackerslist/refs/heads/master/trackers_all.txt'
            )
            ->willReturn($this->response);

        $this->response->expects($this->once())
            ->method('getContent')
            ->willReturn($sampleTrackers);

        // 模拟事务
        $this->entityManager->expects($this->once())
            ->method('wrapInTransaction')
            ->willReturnCallback(function ($callback) {
                $callback();
                return true;
            });

        // 模拟查询构建器和删除操作
        $this->entityManager->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('from')
            ->with(BtTracker::class, 'a')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('delete')
            ->willReturn($this->queryBuilder);

        $this->queryBuilder->expects($this->once())
            ->method('getQuery')
            ->willReturn($this->query);

        $this->query->expects($this->once())
            ->method('execute');

        // 模拟插入两个新的 Tracker
        $this->entityManager->expects($this->exactly(2))
            ->method('persist')
            ->with($this->callback(function ($tracker) {
                return $tracker instanceof BtTracker;
            }));

        $this->entityManager->expects($this->exactly(2))
            ->method('flush');

        // 执行命令
        $this->commandTester->execute([]);

        // 断言命令执行成功
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
