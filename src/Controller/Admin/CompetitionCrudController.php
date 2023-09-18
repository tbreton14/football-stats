<?php

namespace App\Controller\Admin;

use App\Entity\Competition;
use App\Service\FffApiClient;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CompetitionCrudController extends AbstractCrudController
{

    private $competitions = array();

    public function __construct(private FffApiClient $fffApiClient)
    {
        $equipes = $this->fffApiClient->getEquipes();

        foreach ($equipes["hydra:member"] as $equipe) {
            if($equipe["category_code"] == "U15") {
                foreach($equipe["engagements"] as $compet) {
                    $this->competitions[$compet["competition"]["name"]] = $compet["competition"]["name"];
                }
            }
        }
    }

    public static function getEntityFqcn(): string
    {
        return Competition::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('category', 'Catégorie'),
            TextField::new('season', 'Saison'),
            ChoiceField::new('name', 'Nom de la compétition')->setChoices($this->competitions),
            TextField::new('codeCompetition', 'API code compétition'),
            TextField::new('numPhase', 'API num phase'),
            TextField::new('numPoule', 'API num poule'),

        ];
    }

}
