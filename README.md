# IP Collection Bundle

[English](README.md) | [中文](README.zh-CN.md)

A Symfony Bundle for collecting and managing IP address information.

## Features

- Synchronize various public IP address lists
- Collect BT Tracker addresses
- Synchronize AWS IP address ranges

## Installation

```bash
composer require tourze/ip-collection-bundle
```

## Testing

Run the following command in the project root directory to execute tests:

```bash
vendor/bin/phpunit packages/ip-collection-bundle/tests
```

## Usage

### Commands

The Bundle provides the following console commands:

- `game-boost:sync-cidr` - Synchronize IP address information
- `ip-collection:sync-aws-ip-range` - Synchronize AWS IP address ranges
- `bt:sync-public-tracker` - Collect public BT Tracker addresses

These commands can be executed automatically through scheduled tasks or run manually.

### Entities

The Bundle includes the following entities:

- `IpTag` - Stores IP address tag information
- `BtTracker` - Stores BT Tracker information
