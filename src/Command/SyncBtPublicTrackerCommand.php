<?php

declare(strict_types=1);

namespace IpCollectionBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use IpCollectionBundle\Entity\BtTracker;
use League\Uri\Uri;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

#[AsCronTask(expression: '33 6 * * *')]
#[AsCommand(name: 'bt:sync-public-tracker', description: '收集公共的Tracker地址')]
#[WithMonologChannel(channel: 'ip_collection')]
class SyncBtPublicTrackerCommand extends Command
{
    public const NAME = 'bt:sync-public-tracker';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface $logger,
        /** @var EntityRepository<BtTracker> */
        private readonly EntityRepository $btTrackerRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = 'https://raw.githubusercontent.com/ngosang/trackerslist/refs/heads/master/trackers_all.txt';
        $this->logger->info('开始收集公共Tracker地址', ['url' => $url]);

        try {
            $startTime = microtime(true);
            $response = $this->httpClient->request('GET', $url);
            $trackers = $response->getContent();
            $responseTime = microtime(true) - $startTime;

            $this->logger->info('Tracker地址列表请求成功', [
                'url' => $url,
                'response_time' => round($responseTime * 1000, 2) . 'ms',
                'content_length' => strlen($trackers),
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Tracker地址列表请求失败', [
                'url' => $url,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            throw $e;
        }
        $trackers = explode("\n", $trackers);

        $this->entityManager->wrapInTransaction(function () use ($trackers): void {
            // 先删除所有，再重新添加
            $this->btTrackerRepository->createQueryBuilder('a')
                ->delete()
                ->getQuery()
                ->execute()
            ;
            foreach ($trackers as $tracker) {
                $tracker = trim($tracker);
                if ('' === $tracker) {
                    continue;
                }
                $this->sync($tracker);
            }
        });

        return Command::SUCCESS;
    }

    private function sync(string $line): void
    {
        $uri = Uri::new($line);

        $host = $uri->getHost();
        if (null === $host) {
            return;
        }

        $tracker = new BtTracker();
        $tracker->setScheme($uri->getScheme());
        $tracker->setHost($host);
        $tracker->setPort($uri->getPort());
        $this->entityManager->persist($tracker);
        $this->entityManager->flush();
    }
}
