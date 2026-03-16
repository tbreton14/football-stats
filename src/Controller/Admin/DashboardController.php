<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\CategorySeason;
use App\Entity\CategoryUser;
use App\Entity\Club;
use App\Entity\Competition;
use App\Entity\Playing;
use App\Entity\PlayingUser;
use App\Entity\Poste;
use App\Entity\Scorer;
use App\Entity\Season;
use App\Entity\Summon;
use App\Entity\User;
use App\Service\GooglePhotosApi;
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryRepository;
use App\Service\FffApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;




class DashboardController extends AbstractDashboardController
{

    private EntityManagerInterface $em;
    private AdminUrlGenerator $adminUrlGenerator;
    private GooglePhotosApi $googlePhotosApiService;
    private CacheInterface $cache;

    public function __construct(
        EntityManagerInterface $em,
        AdminUrlGenerator $adminUrlGenerator,
        GooglePhotosApi $googlePhotosApiService,
        CacheInterface $cache
    ) {
        $this->em = $em;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->googlePhotosApiService = $googlePhotosApiService;
        $this->cache = $cache;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $seasonEntity = $this->em->getRepository(Season::class)->findOneBy(["label"=>$_ENV["APP_ACTUAL_SEASON"]]);
        $categories = $this->em->getRepository(Category::class)->findBySeason($seasonEntity->getId()->toBinary());
    
        return $this->render('admin/dashboard/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route(path: '/admin/choiceCompo', name: 'app_choice_compo', methods: ['POST'])]
    public function choice_compo(Request $request): Response
    {
        return $this->redirectToRoute('app_saisie_compo', [
            'category' => $request->request->get('categoryChoice')
        ]);
    }

    #[Route(path: '/admin/saisieCompo/{category}', name: 'app_saisie_compo')]
    public function saisie_compo(Request $request, $category, FffApiClient $fffApiClient): Response
    {
        $seasonEntity = $this->em->getRepository(Season::class)->findOneBy(["label"=>$_ENV["APP_ACTUAL_SEASON"]]);
        $categoryEntity = $this->em->getRepository(Category::class)->find($category);
        $competitions = $this->em->getRepository(Competition::class)->findBy(["season"=>$seasonEntity,"category"=>$categoryEntity]);
        $effectif = $this->em->getRepository(User::class)->findBySeasonAndCategorie($seasonEntity->getId()->toBinary(), $categoryEntity->getId()->toBinary(), false);
  
        //calendrier
        $playingGlobal = [];
        foreach ($competitions as $competition) {
            if($competition->isPlayingPersonnal()) {
                $otherPlayings = null;
                $playings = $this->em->getRepository(Playing::class)->findBy(["competition" => $competition->getId()->toBinary()], ["datePlaying"=>"ASC"]);
                array_push($playingGlobal, $playings);
            } else {
                $otherPlayings = $this->em->getRepository(Playing::class)->findBy(["competition" => $competition->getId()->toBinary()], ["datePlaying"=>"ASC"]);
                if($otherPlayings){
                    array_push($playingGlobal, $otherPlayings);
                }
                if($competition->getNumPhase() == 2) {
                    $playingsPhase1 = $fffApiClient->getCalendrierEquipe($competition->getCodeCompetition(), $competition->getNumPhase()-1, $competition->getNumPoule(), $_ENV['APP_API_CLUB_ID']);
                    $playingsPhase1 = $playingsPhase1["hydra:member"];
                    $playingsPhase2 = $fffApiClient->getCalendrierEquipe($competition->getCodeCompetition(), $competition->getNumPhase(), $competition->getNumPoulePhase2(), $_ENV['APP_API_CLUB_ID']);
                    $playingsPhase2 = $playingsPhase2["hydra:member"];

                    $playings = array_merge($playingsPhase1, $playingsPhase2);
                    array_push($playingGlobal, $playings);
                } else {
                    
                    if($competition->getCodeCompetition()) {
                        $playingsPhase1 = $fffApiClient->getCalendrierEquipe($competition->getCodeCompetition(), $competition->getNumPhase(), $competition->getNumPoule(), $_ENV['APP_API_CLUB_ID']);
                        $playings = $playingsPhase1["hydra:member"];
                        usort($playings, function ($a, $b) {
                            return strcmp($a["date"], $b["date"]);
                        });
                        array_push($playingGlobal, $playings);
                    }
                }
            }
        }

        $playingList = [];
        //dd($playings);

        foreach ($playingGlobal as $playings) {
            foreach ($playings as $playing) {

                if ($playing instanceof Playing) {
                
                    $playingList[] = [
                        'date'        => $playing->getDatePlaying(),
                        'equipeDom'   => $playing->getClubDom(),
                        'equipeExt'   => $playing->getClubExt(),
                        'id' => $playing->getId()
                    ];

                } else {

                    $idPlaying = $playing['ma_no'];
                    $equipeDom = $playing['home']['short_name'] ?? null;
                    $equipeExt = $playing['away']['short_name'] ?? null;

                    $playingList[] = [
                        'date' => new \DateTime($playing['date']),
                        'equipeDom' => $equipeDom,
                        'equipeExt' => $equipeExt,
                        'id' => $idPlaying,
                    ];
                }


            }
        }

        usort($playingList, function ($a, $b) {
            return strcmp($a["date"]->format('Ymd'), $b["date"]->format('Ymd'));
        });

        $url = $this->adminUrlGenerator
            ->setController(DashboardController::class)
            ->generateUrl();

        if($request->getMethod() == 'POST') {
            $rencontreId = $request->request->get('rencontreChoice');
            $joueurs = $request->request->all('effectifSelected');

            $nbButs = $request->request->all('nbButs');
            $nbPassD = $request->request->all('nbPassD');
            $sp = $request->request->all('sp');
            $nbCartonJ = $request->request->all('nbcartonJ');
            $nbCartonR = $request->request->all('nbcartonR');

            try {
                $playing = $this->em->getRepository(Playing::class)->find($rencontreId);
            } catch(\Exception $e) {
                $playing = $rencontreId;
            }

            if (!$playing || empty($joueurs)) {
                $this->addFlash('danger', "Sélection obligatoire d'au moins 1 joueur.");
                return $this->redirectToRoute('app_saisie_compo', ['category'=>$category]);
            }

            foreach ($joueurs as $joueurId) {

                $user = $this->em->getRepository(User::class)->find($joueurId);

                if (!$user) {
                    continue;
                }

                if ($playing instanceof Playing) {
                    $playingUser = $this->em->getRepository(PlayingUser::class)
                        ->findOneBy([
                            'playing' => $playing,
                            'user' => $user
                        ]);
                } else {
                    $playingUser = $this->em->getRepository(PlayingUser::class)
                        ->findOneBy([
                            'external_playing_id' => $playing,
                            'user' => $user
                        ]);
                }

                if (!$playingUser) {
                    $playingUser = new PlayingUser();

                    if ($playing instanceof Playing) {
                        $playingUser->setPlaying($playing);
                    } else {
                        $playingUser->setExternalPlayingId($playing);
                    }

                    $playingUser->setUser($user);
                }

                $playingUser->setNbButs((int) $nbButs[$joueurId] ?? 0);
                $playingUser->setNbPassD((int) $nbPassD[$joueurId] ?? 0);
                $playingUser->setSp((int) $sp[$joueurId] ?? 0);
                $playingUser->setNbCartonJ((int) $nbCartonJ[$joueurId] ?? 0);
                $playingUser->setNbCartonR((int) $nbCartonR[$joueurId] ?? 0);

                $playingUser->setSeason($seasonEntity);

                $this->em->persist($playingUser);
            }

            $this->em->flush();
            $this->addFlash('success', "Saisie compo ajouté !");
            return $this->redirectToRoute('admin');
        }    

        return $this->render('admin/saisiecompo.html.twig', [
            'returnLink' => $url,
            'effectif' => $effectif,
            'playingList' => $playingList,
            'category' => $category
        ]);
    }

    #[Route('/admin/ajax/playingusers', name: 'admin_ajax_playingusers')]
    public function admin_ajax_playingusers(Request $request): Response
    {
        $rencontreId = $request->request->get('rencontreChoice');
         try {
            $playing = $this->em->getRepository(Playing::class)->find($rencontreId);
        } catch(\Exception $e) {
            $playing = $rencontreId;
        }

        if ($playing instanceof Playing) {
            $playingUsers = $playing->getPlayingUsers();
        } else {
             $playingUsers = $this->em->getRepository(PlayingUser::class)
                ->findBy([
                    'external_playing_id' => $playing,
                ]);
        }

        $ids = [];

        foreach ($playingUsers as $playingUser) {
            $ids[$playingUser->getUser()->getId()->toRfc4122()]["nbButs"] = $playingUser->getNbButs();
            $ids[$playingUser->getUser()->getId()->toRfc4122()]["sp"] = $playingUser->getSp();
            $ids[$playingUser->getUser()->getId()->toRfc4122()]["nbPassD"] = $playingUser->getNbPassD();
            $ids[$playingUser->getUser()->getId()->toRfc4122()]["nbCartonJ"] = $playingUser->getNbCartonJ();
            $ids[$playingUser->getUser()->getId()->toRfc4122()]["nbCartonJR"] = $playingUser->getNbCartonR();
        }

        return new JsonResponse($ids);
    }

    #[Route(path: '/admin/google/authenticate', name: "admin_google_authenticate")]
    public function indexTestGooglePhoto(Request $request): Response
    {
        $urlToRedirect = $this->googlePhotosApiService->generateGoogleRedirectUrl();
        return $this->redirect($urlToRedirect);
    }

    #[Route(path: '/admin/google/oauth/redirect', name: "admin_google_redirect")]
    public function testGooglePhotoRedirect(Request $request): Response
    {
        $code = $request->query->get("code");
        if (!$code) {
            throw new BadRequestHttpException("Missing params");
        }

        $token = $this->googlePhotosApiService->handleRedirect($code);

        if (!empty($token["error"])) {
            throw new BadRequestHttpException("Cannot get access token from google, try to authenticate again");
        }

        // Delete old access token
        $this->cache->delete("google_access_token");

        // Only to set access token in cache
        $this->cache->get("google_access_token", function (ItemInterface $item) use ($token) {
            $item->expiresAfter($token["expires_in"] - 100);
            return $token["access_token"];
        });

        return new Response($token["refresh_token"]);
    }

    #[Route(path: "/admin/google/albums/list", name: "admin_google_album_list")]
    public function getAlbums(Request $request): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(DashboardController::class)
            ->generateUrl();

        try {
            $albums = $this->googlePhotosApiService->getAlbums();
            return $this->render('admin/dashboard/google_albums.html.twig', [
                "albums" => $albums,
                "returnLink" => $url
            ]);
        } catch (\Exception $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }

    public function configureDashboard(): Dashboard
    {
        // the name visible to end users
        $logoSrc = "/images/logo-usonm.jpg";

        return Dashboard::new()

            ->setTitle("<img src=".$logoSrc.">")

            // by default EasyAdmin displays a black square as its default favicon;
            // use this method to display a custom favicon: the given path is passed
            // "as is" to the Twig asset() function:
            // <link rel="shortcut icon" href="{{ asset('...') }}">
            ->setFaviconPath('favicon.svg')

            ->setLocales(['fr'])

            // the domain used by default is 'messages'
            //->setTranslationDomain('my-custom-domain')

            // there's no need to define the "text direction" explicitly because
            // its default value is inferred dynamically from the user locale
            //->setTextDirection('ltr')

            // set this option if you prefer the page content to span the entire
            // browser width, instead of the default design which sets a max width
            //->renderContentMaximized()

            // set this option if you prefer the sidebar (which contains the main menu)
            // to be displayed as a narrow column instead of the default expanded design
            //->renderSidebarMinimized()

            // by default, users can select between a "light" and "dark" mode for the
            // backend interface. Call this method if you prefer to disable the "dark"
            // mode for any reason (e.g. if your interface customizations are not ready for it)
            ->disableDarkMode()

            // by default, all backend URLs are generated as absolute URLs. If you
            // need to generate relative URLs instead, call this method
            //->generateRelativeUrls()

            ;

    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),
            MenuItem::section('Google'),
            MenuItem::linkToRoute("Get refresh token google", "fa fa-home", "admin_google_authenticate"),
            MenuItem::linkToRoute("Albums google", "fa fa-home", "admin_google_album_list"),
            MenuItem::section('Données'),
            MenuItem::linkToCrud('Rencontres', 'fas fa-futbol', Playing::class),
            MenuItem::linkToCrud('Joueurs-Rencontres', 'fas fa-list-check', PlayingUser::class),

            MenuItem::section('Paramètres / Listes'),
            MenuItem::linkToCrud('Clubs', 'fas fa-building', Club::class),
            MenuItem::linkToCrud('Saisons', 'fas fa-list', Season::class),
            MenuItem::linkToCrud('Catégories', 'fas fa-list', Category::class),
            MenuItem::linkToCrud('Catégorie - Saison', 'fas fa-users-gear', CategorySeason::class),
            MenuItem::linkToCrud('Postes', 'fas fa-list', Poste::class),
            MenuItem::linkToCrud('Championnats', 'fas fa-list', Competition::class),
            MenuItem::linkToCrud('Joueurs', 'fas fa-users', User::class),
//          MenuItem::linkToCrud('Buteurs', 'fas fa-futbol', Scorer::class),
//          MenuItem::linkToCrud('Convocations', 'fas fa-list', Summon::class),

        ];
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addCssFile(Asset::new('build/easy-admin-custom.css')) // See webpack.config.js for the SCSS file location
            ->addWebpackEncoreEntry('easy-admin-custom')
            ;
    }
    /*public function configureActions(): Actions
    {
        $choiceAction = Action::new('choiceCompo', 'Saisie Compo')
            ->linkToRoute('app_choice_compo');

        return Actions::new()
            ->add(Crud::PAGE_INDEX, $choiceAction);
    }*/


}