<?php

namespace App\Controller\Admin;

use App\Entity\Club;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

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
            TextField::new('adrStreet', 'Adresse (Rue)'),
            TextField::new('adrZip', 'Code postal'),
            TextField::new('adrCity', 'Ville'),
            CountryField::new('adrCountry', 'Pays'),
            TextField::new('phoneContact', 'Tél. club'),
            TextField::new('emailContact', 'Email club'),
            UrlField::new('websiteUrl', 'URL site web'),
            UrlField::new('facebookUrl', 'URL facebook'),
        ];

    }

}
