<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\CategoryUser;
use App\Entity\Club;
use App\Entity\Competition;
use App\Entity\Playing;
use App\Entity\PlayingUser;
use App\Entity\Scorer;
use App\Entity\Season;
use App\Entity\Summon;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\FffApiClient;
use App\Service\GooglePhotosApi;
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

#[Route(
    path: '/',
)]
class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index_home(Request $request, ManagerRegistry $doctrine, FffApiClient $fffApiClient, GooglePhotosApi $googlePhotosApiService): Response
    {
        $season = $request->get('season');
        $categorySelect = $request->get('category');
        $competitionSelect = $request->get('competition');
        if(!$categorySelect) {
            $categorySelect = $this->getParameter('app.default_category');
        }
        if(!$season) {
            $season = $this->getParameter('app.season_actual');
        }
        if(!$competitionSelect) {
            $competitionSelect = $this->getParameter('app.default_competition');
        }

        $seasonEntity = $doctrine->getRepository(Season::class)->findOneBy(["label"=>$season]);

        $club = $doctrine->getRepository(Club::class)->findAll();
        $category = $doctrine->getRepository(Category::class)->findOneBy(["name" => $categorySelect]);
        $competition = $doctrine->getRepository(Competition::class)->findOneBy(["name" => $competitionSelect,"seasonx"=>$seasonEntity, "category"=>$category->getId()->toBinary()]);
        $categoryUser = $doctrine->getRepository(CategoryUser::class)->findUsersBySeasonAndCategorie($seasonEntity->getId()->toBinary(), $category->getId()->toBinary());
        $effectif = $categoryUser[0]->getUsers();
        $listSeasons = $doctrine->getRepository(Season::class)->findBy([],['label'=>'DESC']);
        $listCategories = $doctrine->getRepository(Competition::class)->findCategoriesBySeason(["season"=>$seasonEntity]);
        $listCompetition = $doctrine->getRepository(Competition::class)->findBy(["seasonx"=>$seasonEntity, "category"=>$category->getId()->toBinary()]);

        $playingsUser = $doctrine->getRepository(PlayingUser::class)->findByCompetition($competition->getId()->toBinary());

        $scorers = $doctrine->getRepository(Scorer::class)->findBy(["season"=>$seasonEntity]);
        $summons = $doctrine->getRepository(Summon::class)->findBy(["season"=>$seasonEntity]);

        if($competition->isPlayingPersonnal()) {
            $otherPlayings = null;
            $playings = $doctrine->getRepository(Playing::class)->findBy(["competition" => $competition->getId()->toBinary()], ["datePlaying"=>"ASC"]);
        } else {
            $otherPlayings = $doctrine->getRepository(Playing::class)->findBy(["competition" => $competition->getId()->toBinary()], ["datePlaying"=>"ASC"]);
            if($competition->getNumPhase() == 2) {
                $playingsPhase1 = $fffApiClient->getCalendrierEquipe($competition->getCodeCompetition(), $competition->getNumPhase()-1, $competition->getNumPoule(), $_ENV['APP_API_CLUB_ID']);
                $playingsPhase1 = $playingsPhase1["hydra:member"];
                $playingsPhase2 = $fffApiClient->getCalendrierEquipe($competition->getCodeCompetition(), $competition->getNumPhase(), $competition->getNumPoulePhase2(), $_ENV['APP_API_CLUB_ID']);
                $playingsPhase2 = $playingsPhase2["hydra:member"];

                $playings = array_merge($playingsPhase1, $playingsPhase2);
            } else {
                $playingsPhase1 = $fffApiClient->getCalendrierEquipe($competition->getCodeCompetition(), $competition->getNumPhase(), $competition->getNumPoule(), $_ENV['APP_API_CLUB_ID']);
                $playings = $playingsPhase1["hydra:member"];
                usort($playings, function($a,$b) {
                    return strcmp($a["date"], $b["date"]);
                });

                //Ajout des scorers et de la composition
                foreach($playings as $key => $playing) {
                    $i=0;
                    $playings[$key]["scorers"]=[];
                    $playings[$key]["compo"]=[];
                    foreach($scorers as $scorer) {
                        if($scorer->getIdPlaying() == $playing["ma_no"]) {
                            for($j=1; $j<=$scorer->getNbGoal(); $j++) {
                                $playings[$key]["scorers"][$i] = $scorer->getUser()->getFullName();
                                $i++;
                            }
                        }
                    }

                    foreach($summons as $summon) {
                        if($summon->getIdPlaying() == $playing["ma_no"]) {
                            $j=0;
                            foreach ($summon->getUsers() as $user) {
                                $playings[$key]["compo"][$j] = $user->getFullName();
                                $j++;
                            }
                        }
                    }
                }

//                dd($playings);
            }
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

        /***
         * Calcul des buteurs
         */
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

        if(!$competition->isPlayingPersonnal()) {
            foreach($scorers as $scorer) {
                $idUser = $scorer->getUser()->getId()->toBase32();

                if(!array_key_exists($idUser,$listButeurs)) {
                    $listButeurs[$idUser]=[];
                }

                if(!array_key_exists("fullName",$listButeurs[$idUser])) {
                    $listButeurs[$idUser]["fullName"] = $scorer->getUser()->getFullName();
                }

                if(array_key_exists("nbButs",$listButeurs[$idUser])) {
                    $listButeurs[$idUser]["nbButs"] = $listButeurs[$idUser]["nbButs"] + $scorer->getNbGoal();
                } else {
                    $listButeurs[$idUser]["nbButs"] = $scorer->getNbGoal();
                }

                foreach ($summons as $summon) {
                    foreach ($summon->getUsers() as $user) {
                        $idSummonUser = $user->getId()->toBase32();
                        if($idSummonUser == $idUser ) {
                            if (array_key_exists("nbPlaying", $listButeurs[$idUser])) {
                                $listButeurs[$idUser]["nbPlaying"] = $listButeurs[$idUser]["nbPlaying"] + 1;
                            } else {
                                $listButeurs[$idUser]["nbPlaying"] = 1;
                            }
                        }
                    }
                }
            }
        }

        $key_values = array_column($listButeurs, 'nbButs');
        array_multisort($key_values, SORT_DESC, $listButeurs);

        $key_values = array_column($listPasseurs, 'nbPassD');
        array_multisort($key_values, SORT_DESC, $listPasseurs);

        /***
         * Effectif
         */
        $listEffectif = [];
        foreach ($effectif as $key => $joueur) {
            $idUser = $joueur->getId()->toBase32();
            $listEffectif[$idUser]["id"] = $joueur->getId();
            $listEffectif[$idUser]["fullName"] = $joueur->getFullName();
            $listEffectif[$idUser]["birthDate"] = $joueur->getBirthDate();
            $listEffectif[$idUser]["userPoste"] = $joueur->getUserPoste()->getName();
            $listEffectif[$idUser]["totalMatch"] = 0;

            foreach ($joueur->getSummonUsers() as $summonUser) {
                if($summonUser->getSeason()==$seasonEntity) {
                    $listEffectif[$idUser]["totalMatch"] = $listEffectif[$idUser]["totalMatch"] + 1;
                }
            };

            $listEffectif[$idUser]["totalMatch"] = $listEffectif[$idUser]["totalMatch"] + $joueur->getNbPlayingsUserBySeason($seasonEntity);
        }


        // Album GooglePhotos
        $photos = [];
        $albumGoogleId = $competition->getGoogleAlbumId();
        if($albumGoogleId) {
            $photos = $googlePhotosApiService->getPhotosInAlbum($albumGoogleId);
        }

        return $this->render('default/index.html.twig', [
            "nbTotalJoueur" => count($effectif),
            "effectif" => $effectif,
//            "listJoueurGar" => $listJoueurGar,
//            "listJoueurDef" => $listJoueurDef,
//            "listJoueurMil" => $listJoueurMil,
//            "listJoueurAtt" => $listJoueurAtt,
            "listEffectif" => $listEffectif,
            "listButeurs" => $listButeurs,
            "listPasseurs" => $listPasseurs,
            "listSeasons" => $listSeasons,
            "listCategories" => $listCategories,
            "listCompetition" => $listCompetition,
            "otherPlayings" => $otherPlayings,
            "playings" => $playings,
            "playingsPersonnal" => $competition->isPlayingPersonnal(),
            "classement" => $classement,
            "effectifCategorie" => $categorySelect,
            "club" => $club[0],
            "numPhase" => $competition->getNumPhase(),
            "idCompetition" => $competition->getId(),
            "photos" => $photos,
            "season" => $season,
            "categorySelect" => $categorySelect,
            "competitionSelect" => $competitionSelect
        ]);
    }

    #[Route('/ajax/user/details/{id}', name: 'ajax_app_details_user')]
    public function ajax_app_details_user(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        $season = $request->get('season');
        $user = $doctrine->getRepository(User::class)->find($id);
        $playingsUser = $doctrine->getRepository(PlayingUser::class)->findPlayingsUserBySeason($user,$season);
        $nbButs = 0;
        $nbPasseD = 0;
        $nbCartonJ = 0;
        $nbCartonR = 0;

//        dd($playingsUser);

        foreach ($playingsUser as $playingUser) {
            $nbButs = $nbButs + $playingUser->getNbButs();
            $nbPasseD = $nbPasseD + $playingUser->getNbPassD();
            $nbCartonJ = $nbCartonJ + $playingUser->getNbCartonJ();
            $nbCartonR = $nbCartonR + $playingUser->getNbCartonR();
        }

        $detail=[];
        $detail["fullName"]=$user->getFullNameUpper();
        $detail["birthDate"]=$user->getBirthDate();
        $detail["poste"]=$user->getUserPoste();
        $detail["nbApparition"]=count($playingsUser);
        $detail["nbButs"]=$nbButs;
        $detail["nbPasseD"]=$nbPasseD;
        $detail["nbCartonJ"]=$nbCartonJ;
        $detail["nbCartonR"]=$nbCartonR;
        $detail["photo"]=$user->getProfilePicture();
        $detail["season"]=$season;

        return $this->render('default/details.html.twig', [
            "detail" => $detail,
            "user" => $user
        ]);
    }

    #[Route('/ajax/season', name: 'ajax_app_season')]
    public function ajax_app_season(Request $request, ManagerRegistry $doctrine): Response
    {
        $season = $doctrine->getRepository(Season::class)->findOneBy(["label"=>$request->get('season')]);
        $listCompetitions = $doctrine->getRepository(Competition::class)->findCategoriesBySeason(["season"=>$season]);
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
        $season = $doctrine->getRepository(Season::class)->findOneBy(["label"=>$request->get('season')]);
        $listCompetition = $doctrine->getRepository(Competition::class)->findBy(["seasonx"=>$season, "category"=>$category->getId()->toBinary()]);
        $competitions = [];

        foreach ($listCompetition as $competition) {
            array_push($competitions, $competition->getName());
        }

        return new JsonResponse($competitions);
    }

    #[Route('/ajax/classement/{idCompetition}/{numPhase}', name: 'ajax_app_classement')]
    public function ajax_app_classement(Request $request, FffApiClient $fffApiClient, ManagerRegistry $doctrine, $idCompetition, $numPhase): Response
    {
        $competition = $doctrine->getRepository(Competition::class)->find($idCompetition);

        if($numPhase == 2) {
            $numPoule = $competition->getNumPoulePhase2();
        } else {
            $numPoule = $competition->getNumPoule();
        }

        $classement = $fffApiClient->getClassementEquipe($competition->getCodeCompetition(), $numPhase, $numPoule);
        $classement = $classement["hydra:member"];

        return $this->render('default/classement.html.twig', [
            "classement" => $classement
        ]);
    }

    #[Route('/ajax/resultat/journee/{idCompetition}/{numJ}', name: 'ajax_app_resultat_journee')]
    public function ajax_app_resultat_journee(Request $request, FffApiClient $fffApiClient, ManagerRegistry $doctrine, $idCompetition, $numJ): Response
    {
        $competition = $doctrine->getRepository(Competition::class)->find($idCompetition);

        $resultatJournee = $fffApiClient->getResultatsJournee($competition->getCodeCompetition(), $competition->getNumPhase(), $competition->getNumPoule(), $numJ);

        return $this->render('default/resultatJournee.html.twig', [
            "resultatJournee" => $resultatJournee["hydra:member"]
        ]);
    }

}
