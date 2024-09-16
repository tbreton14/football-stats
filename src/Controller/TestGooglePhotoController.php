<?php

namespace App\Controller;

use App\Service\GooglePhotosApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Google;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Google\Auth\Credentials\UserRefreshCredentials;

#[Route(
    path: '/test-google-photo',
    name: "test_google_photo_"
)]
class TestGooglePhotoController extends AbstractController
{
    public function __construct(
        private readonly GooglePhotosApi $googlePhotosApiService,
        private readonly ParameterBagInterface $parameterBag
    )
    {
    }

    #[Route(path: "/albums/{albumId}", name: "album_view")]
    public function testGooglePhotoGetAlbum(Request $request, string $albumId): Response
    {
        try {
//            $photos = $this->googlePhotosApiService->getPhotosInAlbum($albumId);
            $photos = $this->googlePhotosApiService->getPhotosInAlbum(
                $this->parameterBag->get("app.google_album_id")
            );
            return $this->json($photos);
        } catch (\Exception $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }
}