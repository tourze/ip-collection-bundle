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

#[AsCronTask(expression: '10 */6 * * *')]
#[AsCommand(name: 'game-boost:sync-cidr', description: '同步IP地址信息')]
#[WithMonologChannel(channel: 'ip_collection')]
class SyncCidrListCommand extends LockableCommand
{
    public const NAME = 'game-boost:sync-cidr';

    public function __construct(
        private readonly UpsertManager $upsertManager,
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        /** @var EntityRepository<IpTag> */
        private readonly EntityRepository $ipTagRepository,
    ) {
        parent::__construct();
    }

    /**
     * 拿开源其他人收集的来做
     * @return \Traversable<string, string[]>
     */
    private function getProviders(): \Traversable
    {
        yield 'geoip2-cn' => [
            'https://raw.githubusercontent.com/v03413/GeoIP2-CN/refs/heads/release/China_IP_list.txt',
        ];
        yield 'ipip-china' => [
            'https://raw.githubusercontent.com/17mon/china_ip_list/refs/heads/master/china_ip_list.txt',
        ];

        yield 'aliyun' => [
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/aliyun-cidr-ipv4.txt',
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/aliyun-cidr-ipv6.txt',
        ];

        yield 'tencentcloud' => [
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/tencent-cidr-ipv4.txt',
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/tencent-cidr-ipv6.txt',
        ];

        yield 'huaweicloud' => [
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/huawei-cidr-ipv4.txt',
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/huawei-cidr-ipv6.txt',
        ];

        yield 'ucloud' => [
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/ucloud-cidr-ipv4.txt',
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/ucloud-cidr-ipv6.txt',
        ];

        yield 'ksyun' => [
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/ksyun-cidr-ipv4.txt',
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/ksyun-cidr-ipv6.txt',
        ];

        yield 'baiduyun' => [
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/baidu-cidr-ipv4.txt',
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/baidu-cidr-ipv6.txt',
        ];

        yield 'jdcloud' => [
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/jdcloud-cidr-ipv4.txt',
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/jdcloud-cidr-ipv6.txt',
        ];

        yield 'azure' => [
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/azure-cidr-ipv4.txt',
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/azure-cidr-ipv6.txt',
        ];

        yield 'googlecloud' => [
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/googlecloud-cidr-ipv4.txt',
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/googlecloud-cidr-ipv6.txt',
        ];

        yield 'googlebot' => [
            'https://raw.githubusercontent.com/lord-alfred/ipranges/main/googlebot/ipv4.txt',
            'https://raw.githubusercontent.com/lord-alfred/ipranges/main/googlebot/ipv6.txt',
        ];

        yield 'bing-bot' => [
            'https://raw.githubusercontent.com/lord-alfred/ipranges/main/bing/ipv4.txt',
        ];

        yield 'oraclecloud' => [
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/oracle-cidr-ipv4.txt',
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/oracle-cidr-ipv6.txt',
        ];

        yield 'ibmcloud' => [
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/ibmcloud-cidr-ipv4.txt',
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/ibmcloud-cidr-ipv6.txt',
        ];

        yield 'digitalocean' => [
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/digitalocean-cidr-ipv4.txt',
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/digitalocean-cidr-ipv6.txt',
        ];

        yield 'linode' => [
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/linode-cidr-ipv4.txt',
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/linode-cidr-ipv6.txt',
        ];

        yield 'cloudflare' => [
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/cloudflare-cidr-ipv4.txt',
            'https://raw.githubusercontent.com/lord-alfred/ipranges/main/cloudflare/ipv4.txt',
            'https://raw.githubusercontent.com/axpwx/IP-Data/refs/heads/master/provider/cloudflare-cidr-ipv6.txt',
            'https://raw.githubusercontent.com/lord-alfred/ipranges/main/cloudflare/ipv6.txt',
        ];

        yield 'vultr' => [
            'https://raw.githubusercontent.com/lord-alfred/ipranges/main/vultr/ipv4.txt',
            'https://raw.githubusercontent.com/lord-alfred/ipranges/main/vultr/ipv6.txt',
        ];

        yield 'telegram' => [
            'https://core.telegram.org/resources/cidr.txt',
        ];

        yield 'facebook' => [
            'https://raw.githubusercontent.com/lord-alfred/ipranges/main/facebook/ipv4_merged.txt',
            'https://raw.githubusercontent.com/lord-alfred/ipranges/main/facebook/ipv6_merged.txt',
        ];

        yield 'twitter' => [
            'https://raw.githubusercontent.com/lord-alfred/ipranges/main/twitter/ipv4_merged.txt',
            'https://raw.githubusercontent.com/lord-alfred/ipranges/main/twitter/ipv6_merged.txt',
        ];

        yield 'youtube' => [
            'https://raw.githubusercontent.com/touhidurrr/iplist-youtube/refs/heads/main/ipv4_list.txt',
            'https://raw.githubusercontent.com/touhidurrr/iplist-youtube/refs/heads/main/ipv6_list.txt',
        ];
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tags = [];
        foreach ($this->getProviders() as $source => $urlList) {
            $tags[] = $source;
            $this->syncProviderData($source, $urlList);
        }

        $this->cleanupOldData($tags);

        return Command::SUCCESS;
    }

    /**
     * @param string[] $urlList
     */
    private function syncProviderData(string $source, array $urlList): void
    {
        $text = $this->fetchContentFromUrls($source, $urlList);
        $this->processAndSaveIpData($source, $text);
    }

    /**
     * @param string[] $urlList
     */
    private function fetchContentFromUrls(string $source, array $urlList): string
    {
        $text = '';
        foreach ($urlList as $_url) {
            try {
                $this->logger->info('开始请求IP地址列表', ['source' => $source, 'url' => $_url]);
                $startTime = microtime(true);
                $response = $this->httpClient->request('GET', $_url);
                $content = $response->getContent();
                $responseTime = microtime(true) - $startTime;

                $this->logger->info('IP地址列表请求成功', [
                    'source' => $source,
                    'url' => $_url,
                    'response_time' => round($responseTime * 1000, 2) . 'ms',
                    'content_length' => strlen($content),
                ]);

                $text .= "\n" . $content;
            } catch (\Exception $e) {
                $this->logger->error('IP地址列表请求失败', [
                    'source' => $source,
                    'url' => $_url,
                    'error' => $e->getMessage(),
                    'exception' => get_class($e),
                ]);
                throw $e;
            }
        }

        return $text;
    }

    private function processAndSaveIpData(string $source, string $text): void
    {
        $text = explode("\n", $text);
        $text = array_unique($text);
        foreach ($text as $cidr) {
            $cidr = trim($cidr);
            if ('' === $cidr) {
                continue;
            }

            $ip = new IpTag();
            $ip->setAddress($cidr);
            $ip->setTag($source);
            $ip->setValue('1');
            $ip->setUpdateTime(CarbonImmutable::now());
            $this->upsertManager->upsert($ip);
        }
    }

    /**
     * @param string[] $tags
     */
    private function cleanupOldData(array $tags): void
    {
        // 这里要去除那些不会再用到的IP
        $this->ipTagRepository->createQueryBuilder('a')
            ->delete()
            ->where('a.tag IN (:tags) AND a.value=:value AND a.updateTime<:updateTime')
            ->setParameter('tags', $tags)
            ->setParameter('value', '1')
            ->setParameter('updateTime', CarbonImmutable::now()->subDay())
            ->getQuery()
            ->execute()
        ;
    }
}
