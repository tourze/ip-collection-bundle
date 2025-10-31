<?php

declare(strict_types=1);

namespace IpCollectionBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use IpCollectionBundle\Entity\BtTracker;

#[AdminCrud(
    routePath: '/ip-collection/bt-tracker',
    routeName: 'ip_collection_bt_tracker'
)]
final class BtTrackerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BtTracker::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('BT Tracker')
            ->setEntityLabelInPlural('BT Tracker管理')
            ->setPageTitle(Crud::PAGE_INDEX, 'BT Tracker列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建BT Tracker')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑BT Tracker')
            ->setPageTitle(Crud::PAGE_DETAIL, 'BT Tracker详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['scheme', 'host', 'port'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();

        yield TextField::new('scheme', '协议')
            ->setMaxLength(40)
            ->setRequired(false)
            ->setHelp('如：http、https、udp等，为空时自动识别')
            ->hideOnIndex()
        ;

        yield TextField::new('host', '主机地址')
            ->setMaxLength(255)
            ->setRequired(true)
            ->setHelp('Tracker服务器的域名或IP地址')
        ;

        yield IntegerField::new('port', '端口号')
            ->setRequired(false)
            ->setHelp('Tracker服务端口，范围：1-65535')
            ->hideOnIndex()
        ;

        // 在列表页显示完整URL
        if (Crud::PAGE_INDEX === $pageName) {
            yield TextField::new('__toString', 'Tracker地址')
                ->setHelp('完整的 Tracker 地址')
            ;
        }

        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('scheme', '协议'))
            ->add(TextFilter::new('host', '主机地址'))
            ->add(NumericFilter::new('port', '端口号'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
