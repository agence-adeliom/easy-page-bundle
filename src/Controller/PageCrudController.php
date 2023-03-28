<?php

namespace Adeliom\EasyPageBundle\Controller;

use Adeliom\EasyCommonBundle\Enum\ThreeStateStatusEnum;
use Adeliom\EasyFieldsBundle\Admin\Field\EnumField;
use Adeliom\EasySeoBundle\Admin\Field\SEOField;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;

abstract class PageCrudController extends AbstractCrudController
{
    public function __construct(
        /**
         * @readonly
         */
        private AdminContextProvider $adminContextProvider
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return array_merge(parent::getSubscribedServices(), [
        ]);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->addFormTheme('@EasyMedia/form/easy-media.html.twig')

            ->setPageTitle(Crud::PAGE_INDEX, 'easy.page.admin.crud.title.page.'.Crud::PAGE_INDEX)
            ->setPageTitle(Crud::PAGE_EDIT, 'easy.page.admin.crud.title.page.'.Crud::PAGE_EDIT)
            ->setPageTitle(Crud::PAGE_NEW, 'easy.page.admin.crud.title.page.'.Crud::PAGE_NEW)
            ->setPageTitle(Crud::PAGE_DETAIL, 'easy.page.admin.crud.title.page.'.Crud::PAGE_DETAIL)
            ->setEntityLabelInSingular('easy.page.admin.crud.label.page.singular')
            ->setEntityLabelInPlural('easy.page.admin.crud.label.page.plural')
        ;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        $pages = [Crud::PAGE_INDEX, Crud::PAGE_EDIT, Crud::PAGE_NEW, Crud::PAGE_DETAIL];
        foreach ($pages as $page) {
            $pageActions = $actions->getAsDto($page)->getActions();
            foreach ($pageActions as $action) {
                $action->setLabel('easy.page.admin.crud.label.page.'.$action->getName());
                $actions->remove($page, $action->getAsConfigObject());
                $actions->add($page, $action->getAsConfigObject());
            }
        }

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        $context = $this->adminContextProvider->getContext();
        $subject = $context?->getEntity();

        yield FormField::addTab('easy.page.admin.panel.information');
        yield IdField::new('id')->hideOnForm();
        yield from $this->informationsFields($pageName, $subject);
        yield FormField::addTab('easy.page.admin.panel.publication');
        yield from $this->seoFields($pageName, $subject);
        yield from $this->metadataFields($pageName, $subject);
        yield from $this->publishFields($pageName, $subject);
    }

    /**
     * @return FieldInterface[]
     */
    public function informationsFields(string $pageName, ?EntityDto $subject): iterable
    {
        yield TextField::new('name', 'easy.page.admin.field.name')
            ->setRequired(true)
            ->setColumns(12)
        ;
    }

    /**
     * @return FieldInterface[]
     */
    public function metadataFields(string $pageName, ?EntityDto $subject): iterable
    {
        yield FormField::addPanel('easy.page.admin.panel.metadatas')->addCssClass('col-4');
        yield SlugField::new('slug', 'easy.page.admin.field.slug')
            ->setRequired(true)
            ->hideOnIndex()
            ->setTargetFieldName('name')
            ->setUnlockConfirmationMessage('easy.page.admin.field.slug_edit')
            ->setColumns(12);

        yield TextField::new('action', 'easy.page.admin.field.action')
            ->hideOnIndex()
            ->setHelp('easy.page.admin.field.action_help')
            ->setColumns(12);

        yield AssociationField::new('parent', 'easy.page.admin.field.parent')
            ->setRequired(false)
            ->setQueryBuilder(static function (QueryBuilder $queryBuilder) use ($subject) {
                $rootAlias = $queryBuilder->getAllAliases()[0];
                if ($subject instanceof EntityDto && $subject->getPrimaryKeyValue()) {
                    $queryBuilder->andWhere(sprintf('%s.id != :currentID', $rootAlias))
                        ->setParameter('currentID', $subject->getPrimaryKeyValue());
                }

                return $queryBuilder;
            })
            ->setColumns(12);
    }

    /**
     * @return FieldInterface[]
     */
    public function seoFields(string $pageName, ?EntityDto $subject): iterable
    {
        yield FormField::addPanel('easy.page.admin.panel.seo')->addCssClass('col-4');
        yield SEOField::new('seo');
    }

    /**
     * @return FieldInterface[]
     */
    public function publishFields(string $pageName, ?EntityDto $subject): iterable
    {
        yield FormField::addPanel('easy.page.admin.panel.publication')->addCssClass('col-4');
        yield EnumField::new('state', 'easy.page.admin.field.state')
            ->setEnum(ThreeStateStatusEnum::class)
            ->setRequired(true)
            ->renderExpanded(true)
            ->renderAsBadges(true);
        yield DateTimeField::new('publishDate', 'easy.page.admin.field.publishDate')->setFormat('Y-MM-dd HH:mm')
            ->setRequired(true)
            ->hideOnIndex()
            ->setColumns(6);
        yield DateTimeField::new('unpublishDate', 'easy.page.admin.field.unpublishDate')->setFormat('Y-MM-dd HH:mm')
            ->setRequired(false)
            ->hideOnIndex()
            ->setColumns(6);
    }
}
