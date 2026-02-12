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
use EasyCorp\Bundle\EasyAdminBundle\Config\Asset;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;




class DashboardController extends AbstractDashboardController
{

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $seasonEntity = $this->em->getRepository(Season::class)->findOneBy(["label"=>$_ENV["APP_ACTUAL_SEASON"]]);
        $categories = $this->em->getRepository(Category::class)->findBySeason($seasonEntity->getId()->toBinary());
    
        return $this->render('admin/dashboard/index.html.twig', [
            'categories' => $categories,
        ]);

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());
 
        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirect('...');
        // }
 
        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
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
            MenuItem::linkToCrud('Postes', 'fas fa-list', Poste::class),
            MenuItem::linkToCrud('Championnats', 'fas fa-list', Competition::class),
            MenuItem::linkToCrud('Joueurs', 'fas fa-users', User::class),
            MenuItem::linkToCrud('Joueurs-Catégorie', 'fas fa-users-gear', CategorySeason::class),
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
    public function configureActions(): Actions
    {
        $choiceAction = Action::new('choiceCompo', 'Saisie Compo')
            ->linkToRoute('app_choice_compo');

        return Actions::new()
            ->add(Crud::PAGE_INDEX, $choiceAction);
    }

}