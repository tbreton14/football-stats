<?php

namespace App\Controller\Admin;

use App\Entity\CategoryUser;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;

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
            AssociationField::new('user', 'Joueurs'),
            AssociationField::new('category', 'Catégorie'),
            AssociationField::new('competition', 'Compétition'),
        ];
    }

}
