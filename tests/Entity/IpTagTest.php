<?php

namespace IpCollectionBundle\Tests\Entity;

use IpCollectionBundle\Entity\IpTag;
use PHPUnit\Framework\TestCase;

class IpTagTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $ipTag = new IpTag();

        // 测试地址
        $address = '192.168.1.1';
        $ipTag->setAddress($address);
        $this->assertEquals($address, $ipTag->getAddress());

        // 测试标签
        $tag = 'test-tag';
        $ipTag->setTag($tag);
        $this->assertEquals($tag, $ipTag->getTag());

        // 测试值
        $value = 'test-value';
        $ipTag->setValue($value);
        $this->assertEquals($value, $ipTag->getValue());

        // 测试创建时间
        $createTime = new \DateTime();
        $ipTag->setCreateTime($createTime);
        $this->assertEquals($createTime, $ipTag->getCreateTime());

        // 测试更新时间
        $updateTime = new \DateTime();
        $ipTag->setUpdateTime($updateTime);
        $this->assertEquals($updateTime, $ipTag->getUpdateTime());

        // 测试ID获取方法
        $this->assertEquals(0, $ipTag->getId()); // 默认值是0，不是null
    }

    public function testFluentInterface(): void
    {
        $ipTag = new IpTag();

        // 验证setter方法是否返回$this以支持流式接口
        $this->assertSame($ipTag, $ipTag->setAddress('127.0.0.1'));
        $this->assertSame($ipTag, $ipTag->setTag('test'));
        $this->assertSame($ipTag, $ipTag->setValue('1'));
    }
}
