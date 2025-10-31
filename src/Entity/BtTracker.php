<?php

declare(strict_types=1);

namespace IpCollectionBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Tourze\DoctrineTimestampBundle\Traits\CreateTimeAware;

#[ORM\Entity]
#[ORM\Table(name: 'ims_bt_tracker', options: ['comment' => 'BT Tracker'])]
class BtTracker implements \Stringable
{
    use CreateTimeAware;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private int $id = 0;

    #[ORM\Column(length: 40, nullable: true, options: ['comment' => '协议'])]
    #[Assert\Length(max: 40)]
    private ?string $scheme = null;

    #[ORM\Column(length: 255, options: ['comment' => '主机地址'])]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $host = null;

    #[ORM\Column(nullable: true, options: ['comment' => '端口号'])]
    #[Assert\Range(min: 1, max: 65535)]
    private ?int $port = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function setScheme(?string $scheme): void
    {
        $this->scheme = $scheme;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function setPort(?int $port): void
    {
        $this->port = $port;
    }

    public function __toString(): string
    {
        $url = '';
        if (null !== $this->scheme) {
            $url .= $this->scheme . '://';
        }
        $url .= $this->host;
        if (null !== $this->port) {
            $url .= ':' . $this->port;
        }

        return $url;
    }
}
