<?php

declare(strict_types=1);

namespace IpCollectionBundle\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminCrud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use IpCollectionBundle\Entity\IpTag;

#[AdminCrud(
    routePath: '/ip-collection/ip-tag',
    routeName: 'ip_collection_ip_tag'
)]
final class IpTagCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return IpTag::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('IP标签')
            ->setEntityLabelInPlural('IP标签管理')
            ->setPageTitle(Crud::PAGE_INDEX, 'IP标签列表')
            ->setPageTitle(Crud::PAGE_NEW, '创建IP标签')
            ->setPageTitle(Crud::PAGE_EDIT, '编辑IP标签')
            ->setPageTitle(Crud::PAGE_DETAIL, 'IP标签详情')
            ->setDefaultSort(['createTime' => 'DESC'])
            ->setSearchFields(['address', 'tag', 'value'])
            ->showEntityActionsInlined()
            ->setFormThemes(['@EasyAdmin/crud/form_theme.html.twig'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')->onlyOnIndex();
        yield TextField::new('address', 'IP地址')
            ->setMaxLength(60)
            ->setRequired(true)
            ->setHelp('支持单个IP或IP段格式')
        ;
        yield TextField::new('tag', '标签')
            ->setMaxLength(60)
            ->setRequired(true)
            ->setHelp('标签分类名称，如：region、provider等')
        ;
        yield TextField::new('value', '标签值')
            ->setMaxLength(64)
            ->setRequired(true)
            ->setHelp('标签的具体值，如：北京、阿里云等')
        ;
        yield DateTimeField::new('createTime', '创建时间')
            ->hideOnForm()
        ;
        yield DateTimeField::new('updateTime', '更新时间')
            ->onlyOnDetail()
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('address', 'IP地址'))
            ->add(TextFilter::new('tag', '标签'))
            ->add(TextFilter::new('value', '标签值'))
            ->add(DateTimeFilter::new('createTime', '创建时间'))
        ;
    }
}
