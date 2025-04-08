<?php

namespace IpCollectionBundle\Command;

use Doctrine\ORM\EntityManagerInterface;
use IpCollectionBundle\Entity\BtTracker;
use League\Uri\Uri;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;

#[AsCronTask('33 6 * * *')]
#[AsCommand(name: 'bt:sync-public-tracker', description: '收集公共的Tracker地址')]
class SyncBtPublicTrackerCommand extends Command
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $response = $this->httpClient->request('GET', 'https://raw.githubusercontent.com/ngosang/trackerslist/refs/heads/master/trackers_all.txt');
        $trackers = $response->getContent();
        $trackers = explode("\n", $trackers);

        $this->entityManager->wrapInTransaction(function () use ($trackers) {
            // 先删除所有，再重新添加
            $this->entityManager->createQueryBuilder()
                ->from(BtTracker::class, 'a')
                ->delete()
                ->getQuery()
                ->execute();
            foreach ($trackers as $tracker) {
                $tracker = trim($tracker);
                if (empty($tracker)) {
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

        $tracker = new BtTracker();
        $tracker->setScheme($uri->getScheme());
        $tracker->setHost($uri->getHost());
        $tracker->setPort($uri->getPort());
        $this->entityManager->persist($tracker);
        $this->entityManager->flush();
    }
}
