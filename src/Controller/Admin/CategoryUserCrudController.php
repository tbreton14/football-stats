<?php

namespace App\Controller\Admin;

use App\Entity\CategoryUser;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CategoryUserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CategoryUser::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('users', 'Joueurs'),
            AssociationField::new('category', 'Catégorie'),
            AssociationField::new('seasonx', 'Saison'),
//            TextField::new('season', 'Saison'),
        ];
    }

}
