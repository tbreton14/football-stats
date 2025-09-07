<?php

namespace App\Controller\Admin;

use App\Entity\Scorer;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ScorerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Scorer::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('idPlaying'),
            AssociationField::new('user', 'Joueur'),
            AssociationField::new('season', 'Saison'),
            IntegerField::new('nbGoal'),
            BooleanField::new('sp'),
        ];
    }

}
