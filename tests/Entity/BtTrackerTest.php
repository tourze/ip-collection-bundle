<?php

namespace IpCollectionBundle\Tests\Entity;

use IpCollectionBundle\Entity\BtTracker;
use PHPUnit\Framework\TestCase;

class BtTrackerTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $btTracker = new BtTracker();

        // 测试 scheme
        $scheme = 'http';
        $btTracker->setScheme($scheme);
        $this->assertEquals($scheme, $btTracker->getScheme());

        // 测试 host
        $host = 'tracker.example.com';
        $btTracker->setHost($host);
        $this->assertEquals($host, $btTracker->getHost());

        // 测试 port
        $port = 6969;
        $btTracker->setPort($port);
        $this->assertEquals($port, $btTracker->getPort());

        // 测试创建时间
        $createTime = new \DateTime();
        $btTracker->setCreateTime($createTime);
        $this->assertEquals($createTime, $btTracker->getCreateTime());

        // 测试ID获取方法
        $this->assertEquals(0, $btTracker->getId()); // 默认值是0，不是null
    }

    public function testFluentInterface(): void
    {
        $btTracker = new BtTracker();

        // 验证setter方法是否返回$this以支持流式接口
        $this->assertSame($btTracker, $btTracker->setScheme('https'));
        $this->assertSame($btTracker, $btTracker->setHost('example.org'));
        $this->assertSame($btTracker, $btTracker->setPort(8080));
        $this->assertSame($btTracker, $btTracker->setCreateTime(new \DateTime()));
    }
}
