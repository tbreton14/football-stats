<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }


    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnDetail();
        yield ArrayField::new('roles', '')->setSortable(false)->onlyOnIndex()
            ->setTemplatePath('admin/field/role.html.twig');
        yield EmailField::new('email');
        yield TextField::new('password', 'Mot de passe')->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répéter mot de passe']
            ]);
        yield TextField::new('firstName', 'Prénom');
        yield TextField::new('lastName', 'Nom');
        yield ArrayField::new('roles')->hideOnIndex();
        yield ChoiceField::new('poste')->setChoices([
            'Gardien' => 'gardien',
            'Défenseur' => 'défenseur',
            'Milieu' => 'milieu',
            'Attaquant' => 'attaquant',
        ])->renderExpanded();
        yield DateField::new('birthDate', 'Date de naissance');
        yield ImageField::new('profilePicture', 'Photo')->setUploadDir('public/uploads/users/');;
        yield DateTimeField::new('createdAt')->hideOnForm();
    }

}
