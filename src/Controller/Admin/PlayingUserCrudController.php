<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Entity\Competition;
use App\Entity\Playing;
use App\Entity\PlayingUser;
use App\Entity\Scorer;
use App\Entity\Season;
use App\Entity\Summon;
use App\Entity\User;
use App\Repository\CategoryUserRepository;
use App\Repository\PlayingRepository;
use App\Repository\UserRepository;
use App\Repository\ZquestionRepository;
use App\Service\FffApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action as EasyAdminAction;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class PlayingUserCrudController extends AbstractCrudController
{

    private AdminUrlGenerator $adminUrlGenerator;
    private TranslatorInterface $translator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator, TranslatorInterface $translator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->translator = $translator;
    }

    public static function getEntityFqcn(): string
    {
        return PlayingUser::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions

            ->add(Crud::PAGE_INDEX,
                EasyAdminAction::new('convoc',"Ajouter convocations CP")
                    ->linkToCrudAction('convoc')
                    ->createAsGlobalAction()
            )

            ->add(Crud::PAGE_INDEX,
                EasyAdminAction::new('convoc_cnp',"Ajouter convocations CNP")
                    ->linkToCrudAction('convoc_cnp')
                    ->createAsGlobalAction()
            )

            ->add(Crud::PAGE_INDEX,
                EasyAdminAction::new('addScorer',"Ajouter buteurs")
                    ->linkToCrudAction('addScorer')
                    ->createAsGlobalAction()
            )
            ;
    }

    public function addScorer(AdminContext $context, EntityManagerInterface $entityManager, FffApiClient $fffApiClient): Response
    {

        $request = $context->getRequest();

        $url = $this->adminUrlGenerator
            ->setController(PlayingUserCrudController::class)
            ->setAction(EasyAdminAction::INDEX)
            ->generateUrl();

        $categorySelect = $this->getParameter('app.default_category');
        $category = $entityManager->getRepository(Category::class)->findOneBy(["name" => $categorySelect]);
        $season = $this->getParameter('app.season_actual');
        $competitions = $entityManager->getRepository(Competition::class)->findBy(["season"=>$season, "category"=>$category->getId()->toBinary()]);

        $allPlayings = [];
        foreach ($competitions as $competition) {
            $playingsPhase1 = $fffApiClient->getCalendrierEquipe($competition->getCodeCompetition(), $competition->getNumPhase(), $competition->getNumPoule(), $_ENV['APP_API_CLUB_ID']);
            $playings = $playingsPhase1["hydra:member"];
            usort($playings, function ($a, $b) {
                return strcmp($a["date"], $b["date"]);
            });
            $allPlayings = array_merge($allPlayings, $playings);
        }

        $playingList = [];

        foreach ($allPlayings as $playing) {
            $playingList[$playing["home"]["short_name"]." - ".$playing["away"]["short_name"]] = $playing["ma_no"];
        }

        $form = $this->createFormBuilder(null, [
            'label' => false
        ])
            ->add('user', EntityType::class, [
                "class" => User::class,
                "multiple" => false,
                "expanded" => true,
                "label" => "Liste des joueurs",
                "mapped" => false,
                "query_builder" => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
//                        ->leftJoin("u.categories","uc")
//                        ->leftJoin("uc.category","c")
                        ->leftJoin("u.userPoste","p")
//                        ->andWhere("uc.season = :season")
//                        ->setParameter("season", $_ENV["APP_ACTUAL_SEASON"])
//                        ->andWhere("c.name = :nameCategory")
//                        ->setParameter("nameCategory", "U16")
                        ->orderBy('p.zOrder','asc');
                }
            ])
            ->add('playing', ChoiceType::class, [
                "label" => "Liste des rencontres",
                "mapped" => false,
                "choices" => $playingList
            ])
            ->add('nbGoal', IntegerType::class, [
                "label" => "Nombre de buts"
            ])
            ->add('sp', CheckboxType::class, [
                "label" => "Sur penalty ?"
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $idPlaying = $form->get("playing")->getData();
            $user = $form->get("user")->getData();
            $seasonEntity = $entityManager->getRepository(Season::class)->findOneBy(["label"=>$_ENV["APP_ACTUAL_SEASON"]]);

            $scorer = new Scorer();
            $scorer->setUser($user);
            $scorer->setIdPlaying($idPlaying);
            $scorer->setNbGoal($form->get("nbGoal")->getData());
            $scorer->setSeason($seasonEntity);
            $entityManager->persist($scorer);
            $entityManager->flush();

            return $this->redirect($url);

        }

        return $this->render('admin/addScorer.html.twig', [
            "form" => $form->createView(),
            "returnLink" => $url
        ]);

    }

    public function convoc_cnp(AdminContext $context, EntityManagerInterface $entityManager, FffApiClient $fffApiClient): Response
    {

        $request = $context->getRequest();

        $url = $this->adminUrlGenerator
            ->setController(PlayingUserCrudController::class)
            ->setAction(EasyAdminAction::INDEX)
            ->generateUrl();

        $categorySelect = $this->getParameter('app.default_category');
        $category = $entityManager->getRepository(Category::class)->findOneBy(["name" => $categorySelect]);
        $season = $this->getParameter('app.season_actual');
        $competitions = $entityManager->getRepository(Competition::class)->findBy(["season"=>$season, "category"=>$category->getId()->toBinary()]);

        $allPlayings = [];
        foreach ($competitions as $competition) {
            $playingsPhase1 = $fffApiClient->getCalendrierEquipe($competition->getCodeCompetition(), $competition->getNumPhase(), $competition->getNumPoule(), $_ENV['APP_API_CLUB_ID']);
            $playings = $playingsPhase1["hydra:member"];
            usort($playings, function ($a, $b) {
                return strcmp($a["date"], $b["date"]);
            });
            $allPlayings = array_merge($allPlayings, $playings);
        }

        $playingList = [];

        foreach ($allPlayings as $playing) {
            $playingList[$playing["home"]["short_name"]." - ".$playing["away"]["short_name"]] = $playing["ma_no"];
        }

        $form = $this->createFormBuilder(null, [
            'label' => false
        ])
            ->add('users', EntityType::class, [
                "class" => User::class,
                "multiple" => true,
                "expanded" => true,
                "label" => "Liste des joueurs",
                "mapped" => false,
                "query_builder" => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
//                        ->leftJoin("u.categories","uc")
//                        ->leftJoin("uc.category","c")
                        ->leftJoin("u.userPoste","p")
//                        ->andWhere("uc.season = :season")
//                        ->setParameter("season", $_ENV["APP_ACTUAL_SEASON"])
//                        ->andWhere("c.name = :nameCategory")
//                        ->setParameter("nameCategory", "U16")
                        ->orderBy('p.zOrder','asc');
                }
            ])
            ->add('playing', ChoiceType::class, [
                "label" => "Liste des rencontres",
                "mapped" => false,
                "choices" => $playingList
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $idPlaying = $form->get("playing")->getData();
            $users = $form->get("users")->getData();
            $seasonEntity = $entityManager->getRepository(Season::class)->findOneBy(["label"=>$_ENV["APP_ACTUAL_SEASON"]]);

            $summon = new Summon();
            $summon->setUsers($users);
            $summon->setIdPlaying($idPlaying);
            $summon->setSeason($seasonEntity);
            $entityManager->persist($summon);
            $entityManager->flush();

            return $this->redirect($url);

        }

        return $this->render('admin/convoc.html.twig', [
            "form" => $form->createView(),
            "returnLink" => $url
        ]);

    }


    public function convoc(AdminContext $context, EntityManagerInterface $entityManager): Response
    {

        $request = $context->getRequest();

        $url = $this->adminUrlGenerator
            ->setController(PlayingUserCrudController::class)
            ->setAction(EasyAdminAction::INDEX)
            ->generateUrl();

        $form = $this->createFormBuilder(null, [
            'label' => false
        ])
            ->add('users', EntityType::class, [
                "class" => User::class,
                "multiple" => true,
                "expanded" => true,
                "label" => "Liste des joueurs",
                "mapped" => false,
                "query_builder" => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
//                        ->leftJoin("u.categories","uc")
//                        ->leftJoin("uc.category","c")
                        ->leftJoin("u.userPoste","p")
//                        ->andWhere("uc.season = :season")
//                        ->setParameter("season", $_ENV["APP_ACTUAL_SEASON"])
//                        ->andWhere("c.name = :nameCategory")
//                        ->setParameter("nameCategory", "U16")
                        ->orderBy('p.zOrder','asc');
                }
            ])
            ->add('playing', EntityType::class, [
                "class" => Playing::class,
                "label" => "Liste des rencontres",
                "mapped" => false,
                "query_builder" => function (PlayingRepository $er) {
                    return $er->createQueryBuilder('pl')
                        ->leftJoin("pl.competition","c")
                        ->andWhere("c.season = :season")
                        ->setParameter("season", $_ENV["APP_ACTUAL_SEASON"])
                        ->orderBy('c.name','asc')
                        ->addOrderBy('pl.datePlaying','asc');
                }
            ])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $playing = $form->get("playing")->getData();
            $users = $form->get("users")->getData();

            foreach ($users as $user) {
                $playingUser = new PlayingUser();
                $playingUser->setUser($user);
                $playingUser->setPlaying($playing);
                $entityManager->persist($playingUser);
            }

            $entityManager->flush();
            return $this->redirect($url);
        }

        return $this->render('admin/convoc.html.twig', [
            "form" => $form->createView(),
            "returnLink" => $url
        ]);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('user')
            ->add('playing')
            ;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user', 'Joueurs')->setFormTypeOptions([
                "query_builder" => function (UserRepository $er) {
                    return $er->createQueryBuilder('u')
//                        ->leftJoin("u.categories","uc")
//                        ->leftJoin("u.categoryUsers","cus")
//                        ->leftJoin("uc.category","c")
                        ->leftJoin("u.userPoste","p")
//                        ->andWhere("uc.seasonx = :season")
//                        ->setParameter("season", $_ENV["APP_ACTUAL_SEASON"])
//                        ->andWhere("c.name = :nameCategory")
//                        ->setParameter("nameCategory", "U16")
                        ->orderBy('p.zOrder','asc');
                }
            ]),
//            AssociationField::new('user', 'Joueurs')->setFormTypeOptions([
//                "query_builder" => function (CategoryUserRepository $er) {
//                    return $er->createQueryBuilder('cu')
//                        ->leftJoin("cu.users","u")
//                        ->leftJoin("cu.category","c")
//                        ->leftJoin("u.userPoste","p")
//                        ->andWhere("cu.seasonx = :season")
//                        ->setParameter("season", $_ENV["APP_ACTUAL_SEASON"])
//                        ->andWhere("c.name = :nameCategory")
//                        ->setParameter("nameCategory", "U16")
//                        ->orderBy('p.zOrder','asc');
//                }
//            ]),
            AssociationField::new('playing', 'Rencontres')->setFormTypeOptions([
                "query_builder" => function (PlayingRepository $er) {
                    return $er->createQueryBuilder('pl')
                        ->leftJoin("pl.competition","c")
                        ->andWhere("c.seasonx = :season")
                        ->setParameter("season", $_ENV["APP_ACTUAL_SEASON"])
                        ->orderBy('c.name','asc')
                        ->addOrderBy('pl.datePlaying','asc');
                }
            ]),
            IntegerField::new('nbButs', 'Nombre de buts'),
            IntegerField::new('nbPassD', 'Nombre de passe déc.'),
            IntegerField::new('nbCartonJ', 'Carton jaune'),
            IntegerField::new('nbCartonR', 'Carton rouge'),
        ];
    }

}
