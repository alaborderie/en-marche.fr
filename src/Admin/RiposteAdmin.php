<?php

namespace App\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class RiposteAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_page' => 1,
        '_per_page' => 128,
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('title', null, [
                'label' => 'Titre',
                'show_filter' => true,
            ])
            ->add('body', null, [
                'label' => 'Texte',
            ])
            ->add('sourceUrl', null, [
                'label' => 'Url',
            ])
            ->add('withNotification', null, [
                'label' => 'Avec notification',
            ])
            ->add('enabled', null, [
                'label' => 'Active',
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, [
                'label' => 'ID',
            ])
            ->add('title', null, [
                'label' => 'Titre',
            ])
            ->add('body', null, [
                'label' => 'Texte',
            ])
            ->add('sourceUrl', null, [
                'label' => 'URL',
            ])
            ->add('withNotification', null, [
                'editable' => true,
                'label' => 'Avec notification',
            ])
            ->add('enabled', null, [
                'editable' => true,
                'label' => 'Active',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'filter_emojis' => true,
            ])
            ->add('body', TextareaType::class, [
                'label' => 'Texte',
                'filter_emojis' => true,
                'attr' => ['rows' => 10],
            ])
            ->add('sourceUrl', UrlType::class, [
                'label' => 'URL',
                'required' => false,
            ])
            ->add('withNotification', null, [
                'label' => 'Avec notification',
            ])
            ->add('enabled', null, [
                'label' => 'Active',
            ])
        ;
    }
}
