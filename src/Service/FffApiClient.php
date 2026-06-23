<?php

namespace App\Service;

use App\Security\User;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FffApiClient
{

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TokenStorageInterface
     */
    private $token;

    private Security $security;

    /**
     * @var StoreInterface
     */
    private $store;

    private readonly array $options;

    private array $currentRequestParams = [];

    private string $userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36';

    private function getHeaders(): array
    {
        return [
            'accept' => 'application/json, text/plain, */*',
            'accept-language' => 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7,it;q=0.6,es;q=0.5',
            'origin' => 'https://foot14.fff.fr',
            'referer' => 'https://foot14.fff.fr/',
            'sec-ch-ua' => '"Google Chrome";v="149", "Chromium";v="149", "Not)A;Brand";v="24"',
            'sec-ch-ua-mobile' => '?0',
            'sec-ch-ua-platform' => '"Linux"',
            'sec-fetch-dest' => 'empty',
            'sec-fetch-mode' => 'cors',
            'sec-fetch-site' => 'same-site',
            'user-agent' => $this->userAgent,
        ];
    }

    /**
     * Api constructor.
     */
    public function __construct(TokenStorageInterface $token, Security $security, StoreInterface $store, HttpClientInterface $client, CacheInterface $cache, LoggerInterface $logger, array $options)
    {
        $this->client = $client;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->store = $store;
        $this->token = $token;
        $this->security = $security;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $this->options = $resolver->resolve($options);

        if ($this->options['enable_cache']) {
            $this->client = new CachingHttpClient($client, $store);
        }
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('enable_cache', false);
        $resolver->setDefault('cache_time', 300);
        $resolver->setRequired(['base_url', 'club_id', 'enable_cache']);
        $resolver->setAllowedTypes('base_url', 'string');
        $resolver->setAllowedTypes('club_id', 'string');
        $resolver->setAllowedTypes('enable_cache', 'bool');
        $resolver->setAllowedTypes('cache_time', 'int');
    }

    /**
     * Récupère la réponse sous forme d'un tableau ou null si c'est une 404.
     *
     *
     * @return array|null
     */
    protected function toArrayOrNullIf404(ResponseInterface $response): ?array
    {
        try {
            return $response->toArray();
        } catch (ClientExceptionInterface $e) {
            if ($e->getCode() === 404) {
                return null;
            }
            if ($e->getCode() === 403) {
                $this->logger->error(sprintf('HTTP 403 Forbidden for URL: %s. Content: %s', $response->getInfo('url'), substr($response->getContent(false), 0, 200)));
                return ['error' => 403];
            }
            if ($e->getCode() == 401) {
                $this->cache->delete("app.managin.api.token");
                if (count($this->currentRequestParams) > 0) {
                    return $this->toArrayOrNullIf404(
                        $this->doRequest($this->currentRequestParams["method"], $this->currentRequestParams["url"], $this->currentRequestParams["options"])
                    );
                }
            }

            throw $e;
        }
    }

    /*******************************************************************************************************************
     * Fonctions
     ******************************************************************************************************************/


     /**
     * Récupère les équipes d'un club.
     *
     * @return array|null
     */
    public function getEquipes(): ?array
    {
        $response = $this->client->request('GET', $this->options['base_url'] . '/clubs/'.$this->options['club_id'].'/equipes', [
                'headers' => $this->getHeaders(),
                'extra' => [
                    'no_cache' => true,
                ]
            ]
        );

        return $this->toArrayOrNullIf404($response);
    }

    /**
     * Récupère le classement d'une équipe.
     *
     * @return array|null
     */
    public function getClassementEquipe($codeCompetition,$numPhase,$numPoule): ?array
    {
        $response = $this->client->request('GET', $this->options['base_url'] . '/compets/'.$codeCompetition.'/phases/'.$numPhase.'/poules/'.$numPoule.'/classement_journees?page=1', [
                'headers' => $this->getHeaders(),
                'extra' => [
                    'no_cache' => true,
                ]
            ]
        );

        return $this->toArrayOrNullIf404($response);
    }

    /**
     * Récupère le calendrier d'une équipe.
     *
     * @return array|null
     */
    public function getCalendrierEquipe($codeCompetition,$numPhase,$numPoule,$numClub): ?array
    {
        $response = $this->client->request('GET', $this->options['base_url'] . '/compets/'.$codeCompetition.'/phases/'.$numPhase.'/poules/'.$numPoule.'/matchs?clNo='.$numClub.'&page=1', [
                'headers' => $this->getHeaders(),
                'extra' => [
                    'no_cache' => true,
                ]
            ]
        );

        return $this->toArrayOrNullIf404($response);
    }

    /**
     * Récupère les résultats d'une journée spécifique
     *
     * @return array|null
     */
    public function getResultatsJournee($codeCompetition,$numPhase,$numPoule,$journee): ?array
    {
        $response = $this->client->request('GET', $this->options['base_url'] . '/compets/'.$codeCompetition.'/phases/'.$numPhase.'/poules/'.$numPoule.'/matchs?pjNo='.$journee, [
                'headers' => $this->getHeaders(),
                'extra' => [
                    'no_cache' => true,
                ]
            ]
        );

        return $this->toArrayOrNullIf404($response);
    }

}