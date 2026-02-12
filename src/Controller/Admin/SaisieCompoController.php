<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\Persistence\ManagerRegistry;
use http\Env;
use App\Entity\Category;
use App\Entity\Competition;
use App\Entity\Season;
use Proxies\__CG__\App\Entity\User;

class SaisieCompoController extends AbstractController
{   

    #[Route(path: '/admin/choiceCompo', name: 'app_choice_compo', methods: ['POST'])]
    public function choice_compo(Request $request, ManagerRegistry $doctrine): Response
    {
        return $this->redirectToRoute('app_saisie_compo', [
            'category' => $request->request->get('categoryChoice')
        ]);
    }

    #[Route(path: '/admin/saisieCompo/{category}', name: 'app_saisie_compo')]
    public function saisie_compo(Request $request, ManagerRegistry $doctrine, $category): Response
    {
        $seasonEntity = $doctrine->getRepository(Season::class)->findOneBy(["label"=>$_ENV["APP_ACTUAL_SEASON"]]);
        $categoryEntity = $doctrine->getRepository(persistentObject: Category::class)->find($category);
        $competitions = $doctrine->getRepository(Competition::class)->findBy(["season"=>$seasonEntity,"category"=>$category]);
        $effectif = $doctrine->getRepository(User::class)->findBySeasonAndCategorie($seasonEntity->getId()->toBinary(), $category);

        return $this->render('admin/saisiecompo.html.twig', [
        ]);


    }
}