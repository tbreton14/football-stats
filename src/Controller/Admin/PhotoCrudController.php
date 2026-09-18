<?php

namespace App\Controller\Admin;

use App\Entity\Photo;
use App\Entity\Season;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class PhotoCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Photo::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Photo')
            ->setEntityLabelInPlural('Galerie Photos')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function createEntity(string $entityFqcn): Photo
    {
        $photo = new Photo();
        $seasonLabel = $_ENV['APP_ACTUAL_SEASON'] ?? null;
        if ($seasonLabel) {
            $season = $this->entityManager->getRepository(Season::class)->findOneBy(['label' => $seasonLabel]);
            if ($season) {
                $photo->setSeason($season);
            }
        }
        return $photo;
    }

    public function configureFields(string $pageName): iterable
    {
        $actualSeason = $_ENV['APP_ACTUAL_SEASON'] ?? 'default';
        $uploadDir = 'public/uploads/' . $actualSeason . '/';
        $basePath = 'uploads/' . $actualSeason . '/';

        yield IdField::new('id')->hideOnForm()->hideOnIndex();
        yield TextField::new('title', 'Titre / Légende')->setRequired(false);
        yield AssociationField::new('season', 'Saison')->setRequired(true);
        yield AssociationField::new('category', 'Catégorie (optionnel)')->setRequired(false);
        yield AssociationField::new('competition', 'Compétition (optionnel)')->setRequired(false);
        yield ImageField::new('fileName', 'Photo')
            ->setBasePath($basePath)
            ->setUploadDir($uploadDir)
            ->setUploadedFileNamePattern('[randomhash].[extension]')
            ->setRequired($pageName === Crud::PAGE_NEW);
        yield DateTimeField::new('createdAt', 'Date d\'ajout')->hideOnForm();
    }
}
