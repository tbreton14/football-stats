<?php

namespace App\Controller\Admin;

use App\Entity\Club;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;

class ClubCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Club::class;
    }


    public function configureFields(string $pageName): iterable
    {

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Nom'),
            TextField::new('adrStreet', 'Adresse (Rue)')->hideOnIndex(),
            TextField::new('adrZip', 'Code postal')->hideOnIndex(),
            TextField::new('adrCity', 'Ville'),
            CountryField::new('adrCountry', 'Pays')->hideOnIndex(),
            TextField::new('phoneContact', 'Tél. club')->hideOnIndex(),
            TextField::new('emailContact', 'Email club')->hideOnIndex(),
            UrlField::new('websiteUrl', 'URL site web'),
            UrlField::new('facebookUrl', 'URL facebook')->hideOnIndex(),
            ImageField::new('logo', 'Logo')
                ->setBasePath('public/uploads')
                ->setUploadDir('public/uploads')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->setRequired(false),
        ];

    }

}
