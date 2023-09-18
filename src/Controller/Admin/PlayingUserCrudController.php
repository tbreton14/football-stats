<?php

namespace App\Controller\Admin;

use App\Entity\PlayingUser;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;

class PlayingUserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PlayingUser::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user', 'Joueurs'),
            AssociationField::new('playing', 'Rencontres'),
            IntegerField::new('nbButs', 'Nombre de buts'),
            IntegerField::new('nbPassD', 'Nombre de passe déc.'),
            IntegerField::new('nbCartonJ', 'Carton jaune'),
            IntegerField::new('nbCartonR', 'Carton rouge'),
        ];
    }

}
