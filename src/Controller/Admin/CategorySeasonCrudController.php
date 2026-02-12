<?php

namespace App\Controller\Admin;

use App\Entity\CategorySeason;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;

class CategorySeasonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CategorySeason::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('users', 'Joueurs'),
            AssociationField::new('category', 'Catégorie'),
            AssociationField::new('season', 'Saison'),
            BooleanField::new('seeScorersRanking', "Liste des buteurs"),
            BooleanField::new('seePassersRanking', "Liste des passeurs"),
//            TextField::new('season', 'Saison'),
        ];
    }

}
