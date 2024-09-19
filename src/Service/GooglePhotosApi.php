<?php

namespace App\Service;

use Google\Auth\Credentials\UserRefreshCredentials;
use Google\Client;
use Google\Photos\Library\V1\PhotosLibraryClient;
use Google\Photos\Types\MediaItem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class GooglePhotosApi
{
    public function __construct(
        private readonly CacheInterface $cache,
        private readonly ParameterBagInterface $parameterBag,
        private readonly UrlGeneratorInterface $urlGenerator,
        private Client $client
    )
    {
        $this->client->addScope("https://www.googleapis.com/auth/photoslibrary.readonly");
        $this->client->setAccessType("offline");
        $this->client->setRedirectUri(
            $this->urlGenerator->generate($this->parameterBag->get("app.google_redirect_uri"), [], UrlGeneratorInterface::ABSOLUTE_URL)
        );
    }

    /**
     * Get google authenticate url to redirect to
     * @param string|null $redirectUrl
     * @return string
     */
    public function generateGoogleRedirectUrl(string $redirectUrl = null): string
    {
        if ($redirectUrl) {
            $this->client->setRedirectUri($redirectUrl);
        }
        return $this->client->createAuthUrl();
    }

    public function handleRedirect(string $code): array
    {
        return $this->client->fetchAccessTokenWithAuthCode($code);
    }

    /**
     * Get google access token from cache and try to use the refresh token if not exist or expired
     * @return string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getAccessTokenFromCache(): string
    {
        return $this->cache->get("google_access_token", function (ItemInterface $item) {
            $refreshToken = $this->parameterBag->get("app.google_refresh_token");
            if (!$refreshToken) {
                throw new BadRequestHttpException("Refresh token not found");
            }
            $token = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
            if (!empty($token["error"])) {
                throw new BadRequestHttpException("Error while refreshing token, refresh token is probably invalid");
            }
            $item->expiresAfter($token["expires_in"] - 100);
            return $token["access_token"];
        });
    }

    private function getGooglePhotoClient(): PhotosLibraryClient
    {
        return new PhotosLibraryClient([
            "credentials" => new UserRefreshCredentials($this->client->getScopes(), [
                "client_id" => $this->client->getClientId(),
                "client_secret" => $this->client->getClientSecret(),
                "refresh_token" => $this->getAccessTokenFromCache()
            ])
        ]);
    }

    public function getAlbums(): array
    {
        $photosLibraryClient = $this->getGooglePhotoClient();

        $customArray = [];

        $response = $photosLibraryClient->listAlbums();
        foreach ($response->iteratePages() as $page) {
            foreach ($page as $item) {
                $customArray[] = [
                    "id" => $item->getId(),
                    "name" => $item->getTitle()
                ];
            }
        }
        return $customArray;
    }

    public function getPhotosInAlbum(string $albumId): array
    {
        $response = $this->getGooglePhotoClient()->searchMediaItems([
            "albumId" => $albumId,
            "pageSize" => 100
        ]);

        $customArray = [];
        foreach ($response->iteratePages() as $page) {

            /** @var MediaItem $elem */
            foreach ($page as $elem) {
                $customArray[] = [
                    "id" => $elem->getId(),
                    "product_url" => $elem->getProductUrl(),
                    "base_url" => $elem->getBaseUrl(),
                    "file_name" => $elem->getFilename(),
                    "mime_type" => $elem->getMimeType(),
                ];
            }
        }
        return $customArray;
    }
}