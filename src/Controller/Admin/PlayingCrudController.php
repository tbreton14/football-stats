<?php

namespace App\Controller\Admin;

use App\Entity\Playing;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class PlayingCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Playing::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('clubDom', 'Equipe domicile'),
            TextField::new('clubExt', 'Equipe extérieur'),
            TextField::new('logoClubDom', 'Logo équipe domicile')->hideOnIndex(),
            TextField::new('logoClubExt', 'Logo équipe extérieur')->hideOnIndex(),
            DateTimeField::new('datePlaying', 'Date de la rencontre'),
            IntegerField::new('scoreDom', 'Score domicile'),
            IntegerField::new('scoreExt', 'Score extérieur'),
            IntegerField::new('nbButCsc', 'Nb buts csc')->hideOnIndex(),
            AssociationField::new('competition', 'Compétition')->hideOnIndex(),

        ];
    }

}
