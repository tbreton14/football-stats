<?php

namespace App\Controller;

use App\Entity\CategoryUser;
use App\Entity\Coupons;
use App\Entity\Playing;
use App\Entity\PlayingUser;
use App\Entity\User;
use App\Service\ManaginApiClient;
use Doctrine\Persistence\ManagerRegistry;
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
    public function index_home(Request $request, ManagerRegistry $doctrine): Response
    {
        $effectif = $doctrine->getRepository(CategoryUser::class)->findByCategoryName("U15");
        $playingsUser = $doctrine->getRepository(PlayingUser::class)->findAll();
        $playings = $doctrine->getRepository(Playing::class)->findAll();
        $listButeurs = [];
        $listPasseurs = [];

        foreach ($playingsUser as $playingUser) {
            $idUser = $playingUser->getUser()->getId()->toBase32();
            $listButeurs[$idUser]["fullName"] = $playingUser->getUser()->getFullName();
            $listPasseurs[$idUser]["fullName"] = $playingUser->getUser()->getFullName();
            if(array_key_exists("nbButs",$listButeurs[$idUser])) {
                $listButeurs[$idUser]["nbButs"] = $listButeurs[$idUser]["nbButs"] + $playingUser->getNbButs();
            } else {
                $listButeurs[$idUser]["nbButs"] = $playingUser->getNbButs();
            }
            if(array_key_exists("nbPassD",$listButeurs[$idUser])) {
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
            if($joueur["user"]["poste"] == "gardien") {
                $listJoueurGar[$i]["id"] = $joueur["user"]["id"];
                $listJoueurGar[$i]["fullName"] = ucfirst(strtolower($joueur["user"]["firstName"]))." ".ucfirst(strtolower($joueur["user"]["lastName"]));
                $listJoueurGar[$i]["birthDate"] = $joueur["user"]["birthDate"];
                $listJoueurGar[$i]["poste"] = "Gar.";
            } elseif($joueur["user"]["poste"] == "défenseur") {
                $listJoueurDef[$i]["id"] = $joueur["user"]["id"];
                $listJoueurDef[$i]["fullName"] = ucfirst(strtolower($joueur["user"]["firstName"]))." ".ucfirst(strtolower($joueur["user"]["lastName"]));
                $listJoueurDef[$i]["birthDate"] = $joueur["user"]["birthDate"];
                $listJoueurDef[$i]["poste"] = "Déf.";
            } elseif($joueur["user"]["poste"] == "milieu") {
                $listJoueurMil[$i]["id"] = $joueur["user"]["id"];
                $listJoueurMil[$i]["fullName"] = ucfirst(strtolower($joueur["user"]["firstName"]))." ".ucfirst(strtolower($joueur["user"]["lastName"]));
                $listJoueurMil[$i]["birthDate"] = $joueur["user"]["birthDate"];
                $listJoueurMil[$i]["poste"] = "Mil.";
            } elseif($joueur["user"]["poste"] == "attaquant") {
                $listJoueurAtt[$i]["id"] = $joueur["user"]["id"];
                $listJoueurAtt[$i]["fullName"] = ucfirst(strtolower($joueur["user"]["firstName"]))." ".ucfirst(strtolower($joueur["user"]["lastName"]));
                $listJoueurAtt[$i]["birthDate"] = $joueur["user"]["birthDate"];
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
            "playings" => $playings
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

}
