<?php

declare(strict_types=1);

namespace IpCollectionBundle\Command;

use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityRepository;
use IpCollectionBundle\Entity\IpTag;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Tourze\DoctrineUpsertBundle\Service\UpsertManager;
use Tourze\LockCommandBundle\Command\LockableCommand;
use Tourze\Symfony\CronJob\Attribute\AsCronTask;
use Yiisoft\Json\Json;

#[AsCronTask(expression: '12 */6 * * *')]
#[AsCommand(name: 'ip-collection:sync-aws-ip-range', description: '同步AWS-IP地址信息')]
#[WithMonologChannel(channel: 'ip_collection')]
class SyncAwsIpRangeCommand extends LockableCommand
{
    public const NAME = 'ip-collection:sync-aws-ip-range';

    public function __construct(
        private readonly UpsertManager $upsertManager,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        /** @var EntityRepository<IpTag> */
        private readonly EntityRepository $ipTagRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $url = 'https://ip-ranges.amazonaws.com/ip-ranges.json';
        $this->logger->info('开始同步AWS IP地址信息', ['url' => $url]);

        try {
            $startTime = microtime(true);
            $response = $this->httpClient->request('GET', $url);
            $json = $response->getContent();
            $responseTime = microtime(true) - $startTime;

            $this->logger->info('AWS IP地址信息请求成功', [
                'url' => $url,
                'response_time' => round($responseTime * 1000, 2) . 'ms',
                'content_length' => strlen($json),
            ]);

            $json = Json::decode($json);
        } catch (\Exception $e) {
            $this->logger->error('AWS IP地址信息请求失败', [
                'url' => $url,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            throw $e;
        }

        // 类型检查：确保返回的是数组且包含必需的字段
        assert(is_array($json), 'AWS IP ranges JSON response must be an array');
        assert(isset($json['prefixes']) && is_array($json['prefixes']), 'AWS IP ranges JSON must contain prefixes array');
        assert(isset($json['ipv6_prefixes']) && is_array($json['ipv6_prefixes']), 'AWS IP ranges JSON must contain ipv6_prefixes array');

        foreach ($json['prefixes'] as $prefix) {
            assert(is_array($prefix) && isset($prefix['ip_prefix']) && is_string($prefix['ip_prefix']), 'Each prefix must contain ip_prefix as string');
            $this->syncIPv4($prefix['ip_prefix']);
        }
        foreach ($json['ipv6_prefixes'] as $prefix) {
            assert(is_array($prefix) && isset($prefix['ipv6_prefix']) && is_string($prefix['ipv6_prefix']), 'Each ipv6 prefix must contain ipv6_prefix as string');
            $this->syncIPv6($prefix['ipv6_prefix']);
        }

        // 这里要去除那些不会再用到的IP
        $this->ipTagRepository->createQueryBuilder('a')
            ->delete()
            ->where('a.tag IN (:tags) AND a.value=:value AND a.updateTime<:updateTime')
            ->setParameter('tags', ['aws-ipv4', 'aws-ipv6'])
            ->setParameter('value', '1')
            ->setParameter('updateTime', CarbonImmutable::now()->subDay())
            ->getQuery()
            ->execute()
        ;

        return Command::SUCCESS;
    }

    private function syncIPv4(string $cidr): void
    {
        $ip = new IpTag();
        $ip->setAddress($cidr);
        $ip->setTag('aws-ipv4');
        $ip->setValue('1');
        $ip->setUpdateTime(CarbonImmutable::now());
        $this->upsertManager->upsert($ip);
    }

    private function syncIPv6(string $cidr): void
    {
        $ip = new IpTag();
        $ip->setAddress($cidr);
        $ip->setTag('aws-ipv6');
        $ip->setValue('1');
        $ip->setUpdateTime(CarbonImmutable::now());
        $this->upsertManager->upsert($ip);
    }
}
