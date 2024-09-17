<?php

namespace App\Controller\Admin;

use App\Service\GooglePhotosApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route(
    path: '/admin/google',
    name: "admin_google_"
)]
class AuthenticateGoogleController extends AbstractController
{
    public function __construct(
        private readonly GooglePhotosApi $googlePhotosApiService,
        private readonly CacheInterface $cache
    )
    {
    }

    #[Route(path: '/authenticate', name: "authenticate")]
    public function indexTestGooglePhoto(Request $request): Response
    {
        $urlToRedirect = $this->googlePhotosApiService->generateGoogleRedirectUrl();
        return $this->redirect($urlToRedirect);
    }

    #[Route(path: '/oauth/redirect', name: "redirect", schemes: ['https'])]
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

    #[Route(path: "/albums/list", name: "album_list")]
    public function getAlbums(Request $request): Response
    {
        try {
            $albums = $this->googlePhotosApiService->getAlbums();
            return $this->render('admin/dashboard/google_albums.html.twig', [
                "albums" => $albums
            ]);
        } catch (\Exception $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }
}