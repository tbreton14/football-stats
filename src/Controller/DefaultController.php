<?php

namespace App\Controller;

use App\Service\ManaginApiClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(
    path: '/',
)]
class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index_home(Request $request): Response
    {
        return $this->render('default/index.html.twig', [

        ]);
    }

}
