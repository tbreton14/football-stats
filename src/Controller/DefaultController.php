<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\CategorySeason;
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

        if(!$categorySelect) {
            $categorySelect = $this->getParameter('app.default_category');
        }
        if(!$season) {
            $season = $this->getParameter('app.season_actual');
        }

        //dd($categorySelect);

        $listClub = $doctrine->getRepository(Club::class)->findAll();
        $club = $listClub[0];
        $seasonEntity = $doctrine->getRepository(Season::class)->findOneBy(["label"=>$season]);
        $category = $doctrine->getRepository(Category::class)->findOneBy(["name" => $categorySelect]);
        $categorySeason = $doctrine->getRepository(CategorySeason::class)->findOneBy(["season"=>$seasonEntity,"category"=>$category]);
        $competitions = $doctrine->getRepository(Competition::class)->findBy(["season"=>$seasonEntity,"category"=>$category]);
        $playingsUsers = $doctrine->getRepository(PlayingUser::class)->findBySeasonAndCategorie($seasonEntity->getId()->toBinary(), $category->getId()->toBinary());
        $listSeasons = $doctrine->getRepository(Season::class)->findAll();
        $listCategories = $doctrine->getRepository(Category::class)->findBySeason($seasonEntity->getId()->toBinary());

        //dd($listCategories);

        //calendrier
        $playingGlobal = [];
        foreach ($competitions as $competition) {
            if($competition->isPlayingPersonnal()) {
                $otherPlayings = null;
                $playings = $doctrine->getRepository(Playing::class)->findBy(["competition" => $competition->getId()->toBinary()], ["datePlaying"=>"ASC"]);
                array_push($playingGlobal, $playings);
            } else {
                $otherPlayings = $doctrine->getRepository(Playing::class)->findBy(["competition" => $competition->getId()->toBinary()], ["datePlaying"=>"ASC"]);
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
                    $playingUsers = $doctrine->getRepository(PlayingUser::class)->findByPlaying($playing);

                    $totalButs = 0;
                    foreach ($playingUsers as $puser) {
                        $totalButs = $totalButs + $puser['nbButs'];
                    }

                    $equipeDom = strtoupper($playing->getClubDom()) ?? null;
                    $equipeExt = strtoupper($playing->getClubExt()) ?? null;

                    $isClubDom = $equipeDom && $equipeDom == $club->getName();
                    $isClubExt = $equipeExt && $equipeExt == $club->getName();

                    $logoClub = $club->getLogo();
                    $logoClub = $_ENV["APP_SITE_URL"]."/uploads/".$logoClub;

                    $logoDom = $isClubDom
                        ? $logoClub
                        : ($playing->getLogoClubDom() ?? null);

                    $logoExt = $isClubExt
                        ? $logoClub
                        : ($playing->getLogoClubExt() ?? null);

                    $typeCompetition = null;
                    if($playing->getCompetition()->isChampionnat()) {
                        $typeCompetition = 'CH';
                    } else {
                        if($playing->getCompetition()->isTypePhase1ModeChampionnat()) {
                            $typeCompetition = 'CH';
                        } else {
                            $typeCompetition = 'CP';
                        }
                    }

                    $playingList[] = [
                        'date'        => $playing->getDatePlaying(),
                        'competitionName' => $playing->getCompetition()->getName(),
                        'competitionCode' => $playing->getCompetition()->getCodeCompetition(),
                        'competitionType' => $typeCompetition,
                        'scoreDom'    => $playing->getScoreDom(),
                        'scoreExt'    => $playing->getScoreExt(),
                        'isAmical'    => $playing->isAmical(),
                        'equipeDom'   => $playing->getClubDom(),
                        'logoDom'   => $logoDom,
                        'equipeExt'   => $playing->getClubExt(),
                        'logoExt'   => $logoExt,
                        'numJournee'   => $playing->getNumJourney(),
                        'playingUsers' => $playingUsers,
                        'externalPlayers' => $playing->getExternalPlayer(),
                        'id' => $playing->getId(),
                        'isReport' => $playing->isReport(),
                        'terrain' => null,
                        'totalButs' => $totalButs,
                    ];

                    } else {

                    $idPlaying = $playing['ma_no'];
                    $playingUsers = $doctrine->getRepository(PlayingUser::class)
                        ->findByExternalPlayingId($idPlaying);

                    $totalButs = 0;
                    foreach ($playingUsers as $puser) {
                        $totalButs = $totalButs + $puser['nbButs'];
                    }

                    $equipeDom = $playing['home']['short_name'] ?? null;
                    $equipeExt = $playing['away']['short_name'] ?? null;

                    $isClubDom = $equipeDom && $equipeDom == $club->getName();
                    $isClubExt = $equipeExt && $equipeExt == $club->getName();

                    $logoClub = $club->getLogo();
                    $logoClub = $_ENV["APP_SITE_URL"]."/uploads/".$logoClub;

                    $logoDom = $isClubDom
                        ? $logoClub
                        : ($playing['home']['club']['logo'] ?? null);

                    $logoExt = $isClubExt
                        ? $logoClub
                        : ($playing['away']['club']['logo'] ?? null);

                    $isReport = null;
                    if(!$playing['date']) {
                        $isReport = true;
                    }

                    $playingList[] = [
                        'date' => new \DateTime($playing['date']),
                        'competitionName' => $playing['competition']['name'] ?? null,
                        'competitionCode' => $playing['competition']['cp_no'] ?? null,
                        'competitionType' => $playing['phase']['type'] ?? null,
                        'scoreDom' => $playing['home_score'] ?? null,
                        'scoreExt' => $playing['away_score'] ?? null,
                        'equipeDom' => $equipeDom,
                        'logoDom' => $logoDom,
                        'equipeExt' => $equipeExt,
                        'logoExt' => $logoExt,
                        'numJournee' => $playing['poule_journee']['number'] ?? null,
                        'terrain' => isset($playing['terrain']) ? [
                            'nom'     => $playing['terrain']['name'] ?? null,
                            'adresse' => $playing['terrain']['address'] ?? null,
                            'ville'   => $playing['terrain']['city'] ?? null,
                            'codePostal' => $playing['terrain']['zip_code'] ?? null,
                            'surface' => $playing['terrain']['libelle_surface'] ?? null,
                        ] : null,
                        'playingUsers' => $playingUsers,
                        'externalPlayers' => null,
                        'isAmical' => null,
                        'id' => $idPlaying,
                        'isReport' => $isReport,
                        'totalButs' => $totalButs,
                    ];
                }


            }
        }

        usort($playingList, function ($a, $b) {
            return strcmp($a["date"]->format('Ymd'), $b["date"]->format('Ymd'));
        });


        //dd($playingGlobal);
        //dd($playingList);
        
        //Effectif
        $effectif = $doctrine->getRepository(User::class)->findBySeasonAndCategorie($seasonEntity->getId()->toBinary(), $category->getId()->toBinary(), false);
        $staff = $doctrine->getRepository(User::class)->findBySeasonAndCategorie($seasonEntity->getId()->toBinary(), $category->getId()->toBinary(), true);
        $listEffectif = [];
        $listStaff = [];
        foreach ($effectif as $key => $joueur) {
            $idUser = $joueur->getId()->toBase32();
            $listEffectif[$idUser]["id"] = $joueur->getId();
            $listEffectif[$idUser]["fullName"] = $joueur->getFullName();
            $listEffectif[$idUser]["birthDate"] = $joueur->getBirthDate();
            $listEffectif[$idUser]["userPoste"] = $joueur->getUserPoste()->getName();
            $listEffectif[$idUser]["totalMatch"] = 0;

            $listEffectif[$idUser]["totalMatch"] = $listEffectif[$idUser]["totalMatch"] + $joueur->getNbPlayingsUserBySeason($seasonEntity);
        }

        foreach ($staff as $key => $joueur) {
            $idUser = $joueur->getId()->toBase32();
            $listStaff[$idUser]["id"] = $joueur->getId();
            $listStaff[$idUser]["fullName"] = $joueur->getFullName();
            $listStaff[$idUser]["birthDate"] = $joueur->getBirthDate();
            $listStaff[$idUser]["userPoste"] = $joueur->getUserPoste()->getName();
        }

        //dd($listEffectif);

        // Buteurs+Passeurs
        $listButeurs = [];
        $listPasseurs = [];

        foreach ($playingsUsers as $playingUser) {
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

        usort($listButeurs, function ($a, $b) {
            return $b['nbButs'] <=> $a['nbButs'];
        });
        usort($listPasseurs, function ($a, $b) {
            return $b['nbPassD'] <=> $a['nbPassD'];
        });

        //dd($listPasseurs);

        //classement
        $classement = [];
        $defaultCompetition = null;

        foreach ($competitions as $competition) {

            if($competition->isDefault()){
                $defaultCompetition = $competition;
            }

            if($competition->getCodeCompetition()) {
                if($competition->getNumPhase() == 2) {
                    $numPoule = $competition->getNumPoulePhase2();
                } else {
                    $numPoule = $competition->getNumPoule();
                }
                $c = $fffApiClient->getClassementEquipe($competition->getCodeCompetition(), $competition->getNumPhase(), $numPoule);
                $classement[$competition->getCodeCompetition()] = $c["hydra:member"];
            }
        }
        
        //dd($playingList);


        // Album GooglePhotos
        /*
        $photos = [];
        $albumGoogleId = $competition->getGoogleAlbumId();
        if($albumGoogleId) {
            $photos = $googlePhotosApiService->getPhotosInAlbum($albumGoogleId);
        }
        */

        //dd($playingList);

        return $this->render('default/index.html.twig', [
            "nbTotalJoueur" => count($effectif),
            "seePassersRanking" => $categorySeason->getSeePassersRanking(),
            "seeScorersRanking" => $categorySeason->getSeeScorersRanking(),
            "listEffectif" => $listEffectif,
            "listStaff" => $listStaff,
            "listButeurs" => $listButeurs,
            "listPasseurs" => $listPasseurs,
            "listSeasons" => $listSeasons,
            "listCategories" => $listCategories,
            "classement" => $classement,
            "club" => $club,
            "photos" => null,
            "season" => $season,
            "categorySelect" => $categorySelect,
            "playingList" => $playingList,
            "competitions" => $competitions,
            "defaultCompetition" => $defaultCompetition
        ]);
    }
    

    #[Route('/ajax/user/details/{id}', name: 'ajax_app_details_user')]
    public function ajax_app_details_user(Request $request, ManagerRegistry $doctrine, $id): Response
    {
        $season = $request->get('season');
        $user = $doctrine->getRepository(User::class)->find($id);
        $seasonEntity = $doctrine->getRepository(Season::class)->findOneBy(["label"=>$season]);
        $playingsUser = $doctrine->getRepository(PlayingUser::class)->findPlayingsUserBySeason($user->getId()->toBinary(),$seasonEntity->getId()->toBinary());
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

    #[Route('/ajax/classement/{codeCompetition}/{numPhase}', name: 'ajax_app_classement')]
    public function ajax_app_classement(Request $request, FffApiClient $fffApiClient, ManagerRegistry $doctrine, $codeCompetition, $numPhase): Response
    {
        $competition = $doctrine->getRepository(Competition::class)->findOneByCodeCompetition($codeCompetition);

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

    #[Route('/ajax/resultat/journee/{codeCompetition}/{numJ}', name: 'ajax_app_resultat_journee')]
    public function ajax_app_resultat_journee(Request $request, FffApiClient $fffApiClient, ManagerRegistry $doctrine, $codeCompetition, $numJ): Response
    {
        $competition = $doctrine->getRepository(Competition::class)->findOneByCodeCompetition($codeCompetition);

        $resultatJournee = $fffApiClient->getResultatsJournee($codeCompetition, $competition->getNumPhase(), $competition->getNumPoule(), $numJ);

        return $this->render('default/resultatJournee.html.twig', [
            "resultatJournee" => $resultatJournee["hydra:member"]
        ]);
    }

}
