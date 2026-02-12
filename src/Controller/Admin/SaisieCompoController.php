<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Category;
use App\Entity\Competition;
use App\Entity\Season;
use App\Entity\User;

class SaisieCompoController extends AbstractController
{   
    private AdminUrlGenerator $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }

    #[Route(path: '/admin/choiceCompo', name: 'app_choice_compo', methods: ['POST'])]
    public function choice_compo(Request $request): Response
    {
        return $this->redirectToRoute('app_saisie_compo', [
            'category' => $request->request->get('categoryChoice')
        ]);
    }

    #[Route(path: '/admin/saisieCompo/{category}', name: 'app_saisie_compo')]
    public function saisie_compo(Request $request, ManagerRegistry $doctrine, $category): Response
    {
        $seasonEntity = $doctrine->getRepository(Season::class)->findOneBy(["label"=>$_ENV["APP_ACTUAL_SEASON"]]);
        $categoryEntity = $doctrine->getRepository(Category::class)->find($category);
        $competitions = $doctrine->getRepository(Competition::class)->findBy(["season"=>$seasonEntity,"category"=>$category]);
        $effectif = $doctrine->getRepository(User::class)->findBySeasonAndCategorie($seasonEntity->getId()->toBinary(), $category);

        $url = $this->adminUrlGenerator
            ->setController(DashboardController::class)
            ->generateUrl();

        return $this->render('admin/saisiecompo.html.twig', [
            'returnLink' => $url,
        ]);
    }
}