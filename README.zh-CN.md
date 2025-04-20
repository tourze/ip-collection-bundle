# IP Collection Bundle

这是一个 Symfony Bundle，用于收集和管理 IP 地址信息。

## 功能

- 同步各种公共的 IP 地址列表
- 收集 BT Tracker 地址
- 同步 AWS IP 地址范围

## 安装

```bash
composer require tourze/ip-collection-bundle
```

## 测试

在项目根目录运行以下命令来执行测试：

```bash
vendor/bin/phpunit packages/ip-collection-bundle/tests
```

## 使用方法

### 命令

Bundle 提供了以下控制台命令：

- `game-boost:sync-cidr` - 同步 IP 地址信息
- `ip-collection:sync-aws-ip-range` - 同步 AWS IP 地址范围
- `bt:sync-public-tracker` - 收集公共的 BT Tracker 地址

这些命令可以通过定时任务自动执行，也可以手动运行。

### 实体

Bundle 包含以下实体：

- `IpTag` - 存储 IP 地址的标签信息
- `BtTracker` - 存储 BT Tracker 信息 