# IP Collection Bundle

[![最新版本](https://img.shields.io/packagist/v/tourze/ip-collection-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/ip-collection-bundle)
[![PHP 版本](https://img.shields.io/badge/php-%5E8.1-blue?style=flat-square)](https://php.net)
[![许可证](https://img.shields.io/badge/license-MIT-green?style=flat-square)](LICENSE)
[![下载量](https://img.shields.io/packagist/dt/tourze/ip-collection-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/ip-collection-bundle)
[![构建状态](https://img.shields.io/badge/build-passing-brightgreen?style=flat-square)](#)
[![代码覆盖率](https://img.shields.io/badge/coverage-100%25-brightgreen?style=flat-square)](#)

[English](README.md) | [中文](README.zh-CN.md)

这是一个 Symfony Bundle，用于从各种来源（包括 AWS、云服务提供商和 BT tracker）收集和管理 IP 地址信息。

## 功能

- 同步各种公共的 IP 地址列表
- 收集 BT Tracker 地址
- 同步 AWS IP 地址范围
- IP 地址标记和管理
- 通过定时任务自动同步

## 安装

### 要求

此包需要以下依赖：

- PHP 8.1 或更高版本
- Symfony 7.3+
- Doctrine ORM 3.0+
- 附加包：
  - `league/uri`: URI 操作库
  - `nesbot/carbon`: 日期操作库
  - `yiisoft/json`: JSON 编码/解码
  - `tourze/doctrine-upsert-bundle`: 数据库 upsert 操作
  - `tourze/symfony-lock-command-bundle`: 命令锁定
  - `tourze/symfony-cron-job-bundle`: 定时任务调度

### 通过 Composer 安装

```bash
composer require tourze/ip-collection-bundle
```

## 配置

将包添加到你的 `config/bundles.php`：

```php
return [
    // ...
    IpCollectionBundle\IpCollectionBundle::class => ['all' => true],
];
```

## 使用方法

### 命令

Bundle 提供了以下控制台命令：

- `game-boost:sync-cidr` - 从各种云服务提供商和数据源同步 IP 地址列表
- `ip-collection:sync-aws-ip-range` - 同步 AWS IP 地址范围
- `bt:sync-public-tracker` - 收集公共的 BT Tracker 地址

所有命令都配置了自动定时调度，也可以手动执行。

#### 手动执行

```bash
# 同步 AWS IP 范围（每6小时运行一次）
php bin/console ip-collection:sync-aws-ip-range

# 从各种云服务提供商同步 CIDR 列表（每6小时运行一次）
php bin/console game-boost:sync-cidr

# 收集 BT tracker 地址（每日早上6:33运行）
php bin/console bt:sync-public-tracker
```

### 实体

Bundle 包含以下实体：

- `IpTag` - 存储 IP 地址的标签信息
- `BtTracker` - 存储 BT Tracker 信息

### 高级用法

#### 自定义 IP 来源

你可以扩展 `SyncCidrListCommand` 类来添加自己的 IP 数据源：

```php
class CustomSyncCommand extends SyncCidrListCommand
{
    protected function getProviders(): \Traversable
    {
        // 添加你的自定义提供商
        yield 'custom-source' => [
            'https://example.com/ip-list.txt',
        ];
        
        // 包含默认提供商
        yield from parent::getProviders();
    }
}
```

#### 使用 IP 标签

```php
use IpCollectionBundle\Entity\IpTag;
use Doctrine\ORM\EntityManagerInterface;

// 按标签查找 IP
$repository = $entityManager->getRepository(IpTag::class);
$awsIps = $repository->findBy(['tag' => 'aws-ipv4']);
$chinaIps = $repository->findBy(['tag' => 'geoip2-cn']);
$aliyunIps = $repository->findBy(['tag' => 'aliyun']);

// 检查 IP 是否属于特定提供商
$isAwsIp = $repository->findOneBy([
    'address' => '203.0.113.0/24',
    'tag' => 'aws-ipv4'
]) !== null;
```

#### 使用 BT Tracker

```php
use IpCollectionBundle\Entity\BtTracker;

// 获取所有活跃的 tracker
$trackerRepository = $entityManager->getRepository(BtTracker::class);
$trackers = $trackerRepository->findAll();

// 按协议筛选
$udpTrackers = array_filter($trackers, fn(BtTracker $tracker) => 
    str_starts_with($tracker->getUrl(), 'udp://')
);
```

## 测试

在项目根目录运行以下命令来执行测试：

```bash
vendor/bin/phpunit packages/ip-collection-bundle/tests
```

## 贡献

请查看 [CONTRIBUTING.md](../../CONTRIBUTING.md) 了解如何为此项目做贡献。

## 更新日志

请查看 [CHANGELOG.md](CHANGELOG.md) 了解最近的变更信息。

## 许可证

该项目采用 MIT 许可证 - 详情请参阅 [LICENSE](LICENSE) 文件。 