<?php

namespace App\Controller\Admin;

use App\Entity\Summon;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class SummonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Summon::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IntegerField::new('idPlaying'),
            AssociationField::new('users', 'Joueurs'),
            AssociationField::new('season', 'Saison'),
        ];
    }

}
