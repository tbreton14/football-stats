<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\CategoryUser;
use App\Entity\Club;
use App\Entity\Competition;
use App\Entity\Coupons;
use App\Entity\Playing;
use App\Entity\PlayingUser;
use App\Entity\User;
use App\Service\FffApiClient;
use App\Service\ManaginApiClient;
use Doctrine\Persistence\ManagerRegistry;
use http\Env;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Uid\Uuid;
use DOMDocument;

#[Route(
    path: '/',
)]
class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index_home(Request $request, ManagerRegistry $doctrine, FffApiClient $fffApiClient): Response
    {
        $season = $request->get('season');
        $categorySelect = $request->get('category');
        $competitionSelect = $request->get('competition');
        if(!$categorySelect) {
            $categorySelect = "U15";
        }
        if(!$season) {
            $season = "2023-2024";
        }
        if(!$competitionSelect) {
            $competitionSelect = "REGIONAL 2 U15";
        }

        $club = $doctrine->getRepository(Club::class)->findAll();
        $category = $doctrine->getRepository(Category::class)->findOneBy(["name" => $categorySelect]);
        $competition = $doctrine->getRepository(Competition::class)->findOneBy(["name" => $competitionSelect,"season"=>$season, "category"=>$category->getId()->toBinary()]);
        $effectif = $doctrine->getRepository(CategoryUser::class)->findBy(["season"=>$season, "category"=>$category->getId()->toBinary()]);
        $listSeasons = $doctrine->getRepository(Competition::class)->findSeasons();
        $listCategories = $doctrine->getRepository(Competition::class)->findCategoriesBySeason(["season"=>$season]);
        $listCompetition = $doctrine->getRepository(Competition::class)->findBy(["season"=>$season, "category"=>$category->getId()->toBinary()]);

        $playingsUser = $doctrine->getRepository(PlayingUser::class)->findByCompetition($competition->getId()->toBinary());

        if($competition->isPlayingPersonnal()) {
            $playings = $doctrine->getRepository(Playing::class)->findBy(["competition" => $competition->getId()->toBinary()]);
        } else {
            if($competition->getNumPhase() == 2) {
                $playingsPhase1 = $fffApiClient->getCalendrierEquipe($competition->getCodeCompetition(), $competition->getNumPhase()-1, $competition->getNumPoule(), $_ENV['APP_API_CLUB_ID']);
                $playingsPhase1 = $playingsPhase1["hydra:member"];
            }
            $playingsPhase2 = $fffApiClient->getCalendrierEquipe($competition->getCodeCompetition(), $competition->getNumPhase(), $competition->getNumPoulePhase2(), $_ENV['APP_API_CLUB_ID']);
            $playingsPhase2 = $playingsPhase2["hydra:member"];

            $playings = array_merge($playingsPhase1,$playingsPhase2);
        }

        $listButeurs = [];
        $listPasseurs = [];

        $classement = [];
        if($competition->getCodeCompetition()) {
            if($competition->getNumPhase() == 2) {
                $numPoule = $competition->getNumPoulePhase2();
            } else {
                $numPoule = $competition->getNumPoule();
            }
            $classement = $fffApiClient->getClassementEquipe($competition->getCodeCompetition(), $competition->getNumPhase(), $numPoule);
            $classement = $classement["hydra:member"];
        }

        foreach ($playingsUser as $playingUser) {
            $idUser = $playingUser->getUser()->getId()->toBase32();
            $listButeurs[$idUser]["fullName"] = $playingUser->getUser()->getFullName();
            $listPasseurs[$idUser]["fullName"] = $playingUser->getUser()->getFullName();
            if(array_key_exists("nbButs",$listButeurs[$idUser])) {
                $listButeurs[$idUser]["nbButs"] = $listButeurs[$idUser]["nbButs"] + $playingUser->getNbButs();
            } else {
                $listButeurs[$idUser]["nbButs"] = $playingUser->getNbButs();
            }
            if(array_key_exists("nbPassD",$listPasseurs[$idUser])) {
                $listPasseurs[$idUser]["nbPassD"] = $listPasseurs[$idUser]["nbPassD"] + $playingUser->getNbPassD();
            } else {
                $listPasseurs[$idUser]["nbPassD"] = $playingUser->getNbPassD();
            }
            if(array_key_exists("nbPlaying",$listButeurs[$idUser])) {
                $listButeurs[$idUser]["nbPlaying"] = $listButeurs[$idUser]["nbPlaying"] + 1;
                $listPasseurs[$idUser]["nbPlaying"] = $listPasseurs[$idUser]["nbPlaying"] + 1;
            } else {
                $listButeurs[$idUser]["nbPlaying"] = 1;
                $listPasseurs[$idUser]["nbPlaying"] = 1;
            }

        }

        $listJoueurGar = [];
        $listJoueurDef = [];
        $listJoueurMil = [];
        $listJoueurAtt = [];

        $i=0;
        foreach ($effectif as $joueur) {
            $userJoueur = $joueur->getUser();
            if($userJoueur->getPoste() == "gardien") {
                $listJoueurGar[$i]["id"] = $userJoueur->getId();
                $listJoueurGar[$i]["fullName"] = $userJoueur->getFullName();
                $listJoueurGar[$i]["birthDate"] = $userJoueur->getBirthDate();
                $listJoueurGar[$i]["poste"] = "Gar.";
            } elseif($userJoueur->getPoste() == "défenseur") {
                $listJoueurDef[$i]["id"] = $userJoueur->getId();
                $listJoueurDef[$i]["fullName"] = $userJoueur->getFullName();
                $listJoueurDef[$i]["birthDate"] = $userJoueur->getBirthDate();
                $listJoueurDef[$i]["poste"] = "Déf.";
            } elseif($userJoueur->getPoste() == "milieu") {
                $listJoueurMil[$i]["id"] = $userJoueur->getId();
                $listJoueurMil[$i]["fullName"] = $userJoueur->getFullName();
                $listJoueurMil[$i]["birthDate"] = $userJoueur->getBirthDate();
                $listJoueurMil[$i]["poste"] = "Mil.";
            } elseif($userJoueur->getPoste() == "attaquant") {
                $listJoueurAtt[$i]["id"] = $userJoueur->getId();
                $listJoueurAtt[$i]["fullName"] = $userJoueur->getFullName();
                $listJoueurAtt[$i]["birthDate"] = $userJoueur->getBirthDate();
                $listJoueurAtt[$i]["poste"] = "Att.";
            }
            $i++;
        }

        $key_values = array_column($listButeurs, 'nbButs');
        array_multisort($key_values, SORT_DESC, $listButeurs);

        $key_values = array_column($listPasseurs, 'nbPassD');
        array_multisort($key_values, SORT_DESC, $listPasseurs);


        return $this->render('default/index.html.twig', [
            "listJoueurGar" => $listJoueurGar,
            "listJoueurDef" => $listJoueurDef,
            "listJoueurMil" => $listJoueurMil,
            "listJoueurAtt" => $listJoueurAtt,
            "listButeurs" => $listButeurs,
            "listPasseurs" => $listPasseurs,
            "listSeasons" => $listSeasons,
            "listCategories" => $listCategories,
            "listCompetition" => $listCompetition,
            "playings" => $playings,
            "playingsPersonnal" => $competition->isPlayingPersonnal(),
            "classement" => $classement,
            "effectifCategorie" => $categorySelect,
            "club" => $club[0]
        ]);
    }

    #[Route('/ajax/user/details/{id}', name: 'ajax_app_details_user')]
    public function ajax_app_details_user(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        $user = $doctrine->getRepository(User::class)->find($id);
        $nbButs = 0;
        $nbPasseD = 0;
        $nbCartonJ = 0;
        $nbCartonR = 0;

        foreach ($user->getPlayingsUser() as $playingUser) {
            $nbButs = $nbButs + $playingUser->getNbButs();
            $nbPasseD = $nbPasseD + $playingUser->getNbPassD();
            $nbCartonJ = $nbCartonJ + $playingUser->getNbCartonJ();
            $nbCartonR = $nbCartonR + $playingUser->getNbCartonR();
        }

        $detail=[];
        $detail["fullName"]=$user->getFullNameUpper();
        $detail["birthDate"]=$user->getBirthDate();
        $detail["poste"]=ucfirst(strtolower($user->getPoste()));
        $detail["nbApparition"]=count($user->getPlayingsUser());
        $detail["nbButs"]=$nbButs;
        $detail["nbPasseD"]=$nbPasseD;
        $detail["nbCartonJ"]=$nbCartonJ;
        $detail["nbCartonR"]=$nbCartonR;
        $detail["photo"]=$user->getProfilePicture();

        return $this->render('default/details.html.twig', [
            "detail" => $detail,
            "user" => $user
        ]);
    }

    #[Route('/ajax/season', name: 'ajax_app_season')]
    public function ajax_app_season(Request $request, ManagerRegistry $doctrine): Response
    {
        $listCompetitions = $doctrine->getRepository(Competition::class)->findCategoriesBySeason(["season"=>$request->get('season')]);
        $listCategories = [];

        foreach ($listCompetitions as $competition) {
            array_push($listCategories, $competition["name"]);
        }

        return new JsonResponse($listCategories);
    }

    #[Route('/ajax/category', name: 'ajax_app_category')]
    public function ajax_app_category(Request $request, ManagerRegistry $doctrine): Response
    {
        $category = $doctrine->getRepository(Category::class)->findOneBy(["name" => $request->get('category')]);
        $listCompetition = $doctrine->getRepository(Competition::class)->findBy(["season"=>$request->get('season'), "category"=>$category->getId()->toBinary()]);
        $competitions = [];

        foreach ($listCompetition as $competition) {
            array_push($competitions, $competition->getName());
        }

        return new JsonResponse($competitions);
    }

}
