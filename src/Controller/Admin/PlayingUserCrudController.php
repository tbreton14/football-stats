<?php

namespace App\Controller\Admin;

use App\Entity\PlayingUser;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PlayingUserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PlayingUser::class;
    }

    /*
    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id'),
            TextField::new('title'),
            TextEditorField::new('description'),
        ];
    }
    */
}
