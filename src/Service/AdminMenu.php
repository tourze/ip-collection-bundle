<?php

declare(strict_types=1);

namespace IpCollectionBundle\Service;

use IpCollectionBundle\Entity\BtTracker;
use IpCollectionBundle\Entity\IpTag;
use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;

#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('网络管理')) {
            $item->addChild('网络管理');
        }

        $networkMenu = $item->getChild('网络管理');
        if (null === $networkMenu) {
            return;
        }

        // 添加IP标签管理菜单
        $networkMenu
            ->addChild('IP标签管理')
            ->setUri($this->linkGenerator->getCurdListPage(IpTag::class))
            ->setAttribute('icon', 'fas fa-tags')
        ;

        // 添加BT Tracker管理菜单
        $networkMenu
            ->addChild('BT Tracker管理')
            ->setUri($this->linkGenerator->getCurdListPage(BtTracker::class))
            ->setAttribute('icon', 'fas fa-download')
        ;
    }
}
