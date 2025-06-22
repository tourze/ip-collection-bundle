<?php

namespace IpCollectionBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;

/**
 * 为IP/IP段打上标签信息
 */
#[ORM\Entity]
#[ORM\Table(name: 'ims_ip_tag', options: ['comment' => 'IP标签'])]
#[ORM\UniqueConstraint(name: 'ims_ip_tag_idx_uniq', columns: ['address', 'tag', 'value'])]
class IpTag implements \Stringable
{
    use TimestampableAware;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[IndexColumn]
    #[ORM\Column(length: 60, options: ['comment' => 'IP地址'])]
    private string $address;

    #[IndexColumn]
    #[ORM\Column(length: 60, options: ['comment' => '标签'])]
    private string $tag;

    #[IndexColumn]
    #[ORM\Column(length: 64, options: ['comment' => '值'])]
    private string $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function setTag(string $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s[%s=%s]', $this->address, $this->tag, $this->value);
    }
}
