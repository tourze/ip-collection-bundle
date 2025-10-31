<?php

declare(strict_types=1);

namespace IpCollectionBundle\DataFixtures;

use Carbon\CarbonImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use IpCollectionBundle\Entity\IpTag;

class IpTagFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $ipTags = [
            ['192.168.1.0/24', 'aws-ipv4', '1'],
            ['10.0.0.0/8', 'aws-ipv4', '1'],
            ['172.16.0.0/12', 'aws-ipv4', '1'],
            ['2001:db8::/32', 'aws-ipv6', '1'],
            ['2001:db8:85a3::/48', 'aws-ipv6', '1'],
            ['8.8.8.0/24', 'googlecloud', '1'],
            ['8.8.4.0/24', 'googlecloud', '1'],
            ['1.1.1.0/24', 'cloudflare', '1'],
            ['1.0.0.0/24', 'cloudflare', '1'],
            ['103.21.244.0/22', 'cloudflare', '1'],
            ['103.22.200.0/22', 'cloudflare', '1'],
            ['103.31.4.0/22', 'cloudflare', '1'],
            ['104.16.0.0/12', 'cloudflare', '1'],
            ['108.162.192.0/18', 'cloudflare', '1'],
            ['131.0.72.0/22', 'cloudflare', '1'],
            ['141.101.64.0/18', 'cloudflare', '1'],
            ['162.158.0.0/15', 'cloudflare', '1'],
            ['172.64.0.0/13', 'cloudflare', '1'],
            ['173.245.48.0/20', 'cloudflare', '1'],
            ['188.114.96.0/20', 'cloudflare', '1'],
            ['190.93.240.0/20', 'cloudflare', '1'],
            ['197.234.240.0/22', 'cloudflare', '1'],
            ['198.41.128.0/17', 'cloudflare', '1'],
            ['2400:cb00::/32', 'cloudflare', '1'],
            ['2606:4700::/32', 'cloudflare', '1'],
            ['2803:f800::/32', 'cloudflare', '1'],
            ['2405:b500::/32', 'cloudflare', '1'],
            ['2405:8100::/32', 'cloudflare', '1'],
            ['2c0f:f248::/32', 'cloudflare', '1'],
            ['2a06:98c0::/29', 'cloudflare', '1'],
            ['59.24.3.173/32', 'aliyun', '1'],
            ['47.95.39.155/32', 'aliyun', '1'],
            ['39.104.62.128/32', 'aliyun', '1'],
            ['117.121.242.155/32', 'tencentcloud', '1'],
            ['49.235.92.155/32', 'tencentcloud', '1'],
            ['1.14.100.155/32', 'tencentcloud', '1'],
            ['149.129.229.155/32', 'huaweicloud', '1'],
            ['114.115.234.155/32', 'huaweicloud', '1'],
            ['121.196.246.155/32', 'huaweicloud', '1'],
        ];

        foreach ($ipTags as $index => $ipTagData) {
            $ipTag = new IpTag();
            $ipTag->setAddress($ipTagData[0]);
            $ipTag->setTag($ipTagData[1]);
            $ipTag->setValue($ipTagData[2]);
            $ipTag->setUpdateTime(CarbonImmutable::now());

            $manager->persist($ipTag);
            $this->addReference('ip-tag-' . $index, $ipTag);
        }

        $manager->flush();
    }
}
