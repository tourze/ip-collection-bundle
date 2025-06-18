<?php

namespace IpCollectionBundle\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Tourze\DoctrineIndexedBundle\Attribute\IndexColumn;
use Tourze\DoctrineTimestampBundle\Traits\TimestampableAware;
use Tourze\EasyAdmin\Attribute\Action\Creatable;
use Tourze\EasyAdmin\Attribute\Action\Deletable;
use Tourze\EasyAdmin\Attribute\Action\Editable;
use Tourze\EasyAdmin\Attribute\Column\ExportColumn;
use Tourze\EasyAdmin\Attribute\Column\ListColumn;
use Tourze\EasyAdmin\Attribute\Field\FormField;
use Tourze\EasyAdmin\Attribute\Permission\AsPermission;

/**
 * 为IP/IP段打上标签信息
 */
#[AsPermission(title: 'IP标签')]
#[Creatable]
#[Editable]
#[Deletable]
#[ORM\Entity]
#[ORM\Table(name: 'ims_ip_tag', options: ['comment' => 'IP标签'])]
#[ORM\UniqueConstraint(name: 'ims_ip_tag_idx_uniq', columns: ['address', 'tag', 'value'])]
class IpTag
{
    use TimestampableAware;
    #[ListColumn(order: -1)]
    #[ExportColumn]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER, options: ['comment' => 'ID'])]
    private ?int $id = 0;

    #[IndexColumn]
    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 60, options: ['comment' => 'IP地址'])]
    private string $address;

    #[IndexColumn]
    #[ListColumn]
    #[FormField]
    #[ORM\Column(length: 60, options: ['comment' => '标签'])]
    private string $tag;

    #[IndexColumn]
    #[ListColumn]
    #[FormField]
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
    }}
