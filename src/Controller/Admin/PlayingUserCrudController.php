<?php

namespace App\Controller\Admin;

use App\Entity\Playing;
use App\Entity\PlayingUser;
use App\Entity\User;
use App\Repository\UserRepository;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
                EasyAdminAction::new('convoc',"Ajouter convocations")
                    ->linkToCrudAction('convoc')
                    ->createAsGlobalAction()
            );
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
                "mapped" => false
            ])
            ->add('playing', EntityType::class, [
                "class" => Playing::class,
                "label" => "Liste des rencontres",
                "mapped" => false
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
            AssociationField::new('user', 'Joueurs'),
            AssociationField::new('playing', 'Rencontres'),
            IntegerField::new('nbButs', 'Nombre de buts'),
            IntegerField::new('nbPassD', 'Nombre de passe déc.'),
            IntegerField::new('nbCartonJ', 'Carton jaune'),
            IntegerField::new('nbCartonR', 'Carton rouge'),
        ];
    }

}
