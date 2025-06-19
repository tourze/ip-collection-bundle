<?php

namespace IpCollectionBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;

#[ORM\Entity]
#[ORM\Table(name: 'ims_bt_tracker', options: ['comment' => 'BT Tracker'])]
class BtTracker
{
    use CreateTimeAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[ORM\Column(length: 40, nullable: true)]
    private ?string $scheme = null;

    #[ORM\Column(length: 255)]
    private ?string $host = null;

    #[ORM\Column(nullable: true)]
    private ?int $port = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function setScheme(?string $scheme): static
    {
        $this->scheme = $scheme;

        return $this;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host): static
    {
        $this->host = $host;

        return $this;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(?int $port): static
    {
        $this->port = $port;

        return $this;
    }
}
