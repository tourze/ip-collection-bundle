# IP Collection Bundle

[![Latest Version](https://img.shields.io/packagist/v/tourze/ip-collection-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/ip-collection-bundle)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue?style=flat-square)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green?style=flat-square)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/ip-collection-bundle.svg?style=flat-square)](https://packagist.org/packages/tourze/ip-collection-bundle)
[![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen?style=flat-square)](#)

[English](README.md) | [中文](README.zh-CN.md)

A Symfony Bundle for collecting and managing IP address information from various sources including AWS, cloud providers, and BT trackers.

## Features

- Synchronize various public IP address lists
- Collect BT Tracker addresses
- Synchronize AWS IP address ranges
- IP address tagging and management
- Automated synchronization with cron jobs

## Installation

### Requirements

This bundle requires the following dependencies:

- PHP 8.1 or higher
- Symfony 7.3+
- Doctrine ORM 3.0+
- Additional packages:
  - `league/uri`: URI manipulation library
  - `nesbot/carbon`: Date manipulation library
  - `yiisoft/json`: JSON encoding/decoding
  - `tourze/doctrine-upsert-bundle`: Database upsert operations
  - `tourze/symfony-lock-command-bundle`: Command locking
  - `tourze/symfony-cron-job-bundle`: Cron job scheduling

### Install via Composer

```bash
composer require tourze/ip-collection-bundle
```

## Configuration

Add the bundle to your `config/bundles.php`:

```php
return [
    // ...
    IpCollectionBundle\IpCollectionBundle::class => ['all' => true],
];
```

## Usage

### Commands

The Bundle provides the following console commands:

- `game-boost:sync-cidr` - Synchronize IP address lists from various cloud providers and sources
- `ip-collection:sync-aws-ip-range` - Synchronize AWS IP address ranges  
- `bt:sync-public-tracker` - Collect public BT Tracker addresses

All commands are configured with automatic cron scheduling and can also be executed manually.

#### Manual Execution

```bash
# Synchronize AWS IP ranges (runs every 6 hours)
php bin/console ip-collection:sync-aws-ip-range

# Sync CIDR lists from various cloud providers (runs every 6 hours)
php bin/console game-boost:sync-cidr

# Collect BT tracker addresses (runs daily at 6:33 AM)
php bin/console bt:sync-public-tracker
```

### Entities

The Bundle includes the following entities:

- `IpTag` - Stores IP address tag information
- `BtTracker` - Stores BT Tracker information

### Advanced Usage

#### Customizing IP Sources

You can extend the `SyncCidrListCommand` class to add your own IP data sources:

```php
class CustomSyncCommand extends SyncCidrListCommand
{
    protected function getProviders(): \Traversable
    {
        // Add your custom providers here
        yield 'custom-source' => [
            'https://example.com/ip-list.txt',
        ];
        
        // Include default providers
        yield from parent::getProviders();
    }
}
```

#### Working with IP Tags

```php
use IpCollectionBundle\Entity\IpTag;
use Doctrine\ORM\EntityManagerInterface;

// Find IPs by tag
$repository = $entityManager->getRepository(IpTag::class);
$awsIps = $repository->findBy(['tag' => 'aws-ipv4']);
$chinaIps = $repository->findBy(['tag' => 'geoip2-cn']);
$aliyunIps = $repository->findBy(['tag' => 'aliyun']);

// Check if an IP belongs to a specific provider
$isAwsIp = $repository->findOneBy([
    'address' => '203.0.113.0/24',
    'tag' => 'aws-ipv4'
]) !== null;
```

#### Working with BT Trackers

```php
use IpCollectionBundle\Entity\BtTracker;

// Get all active trackers
$trackerRepository = $entityManager->getRepository(BtTracker::class);
$trackers = $trackerRepository->findAll();

// Filter by protocol
$udpTrackers = array_filter($trackers, fn(BtTracker $tracker) => 
    str_starts_with($tracker->getUrl(), 'udp://')
);
```

## Testing

Run the following command in the project root directory to execute tests:

```bash
vendor/bin/phpunit packages/ip-collection-bundle/tests
```

## Contributing

Please see [CONTRIBUTING.md](../../CONTRIBUTING.md) for details on how to contribute to this project.

## Changelog

Please see [CHANGELOG.md](CHANGELOG.md) for information about recent changes.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
