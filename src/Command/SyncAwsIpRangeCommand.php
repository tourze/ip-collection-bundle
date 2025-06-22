<?php

namespace IpCollectionBundle\Command;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityManagerInterface;
use IpCollectionBundle\Entity\IpTag;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tourze\DoctrineUpsertBundle\Service\UpsertManager;
use Tourze\LockCommandBundle\Command\LockableCommand;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use Yiisoft\Json\Json;

#[AsCronTask('12 */6 * * *')]
#[AsCommand(name: self::NAME, description: '同步AWS-IP地址信息')]
class SyncAwsIpRangeCommand extends LockableCommand
{
    public const NAME = 'ip-collection:sync-aws-ip-range';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UpsertManager          $upsertManager,
        private readonly HttpClientInterface    $httpClient,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $json = $this->httpClient->request('GET', 'https://ip-ranges.amazonaws.com/ip-ranges.json')->getContent();
        $json = Json::decode($json);
        foreach ($json['prefixes'] as $prefix) {
            $this->syncIPv4($prefix['ip_prefix']);
        }
        foreach ($json['ipv6_prefixes'] as $prefix) {
            $this->syncIPv6($prefix['ipv6_prefix']);
        }

        // 这里要去除那些不会再用到的IP
        $this->entityManager->createQueryBuilder()
            ->delete(IpTag::class, 'a')
            ->where('a.tag IN (:tags) AND a.value=:value AND a.updateTime<:updateTime')
            ->setParameter('tags', ['aws-ipv4', 'aws-ipv6'])
            ->setParameter('value', '1')
            ->setParameter('updateTime', CarbonImmutable::now()->subDay())
            ->getQuery()
            ->execute();

        return Command::SUCCESS;
    }

    private function syncIPv4(string $cidr): void
    {
        $ip = new IpTag();
        $ip->setAddress($cidr);
        $ip->setTag('aws-ipv4');
        $ip->setValue('1');
        $ip->setUpdateTime(CarbonImmutable::now());
        $this->upsertManager->upsert($ip, false);
    }

    private function syncIPv6(string $cidr): void
    {
        $ip = new IpTag();
        $ip->setAddress($cidr);
        $ip->setTag('aws-ipv6');
        $ip->setValue('1');
        $ip->setUpdateTime(CarbonImmutable::now());
        $this->upsertManager->upsert($ip, false);
    }
}
