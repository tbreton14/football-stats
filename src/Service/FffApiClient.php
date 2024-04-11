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
    protected function toArrayOrNullIf404(ResponseInterface $response)
    {
        try {
            return $response->toArray();
        } catch (ClientExceptionInterface $e) {
            if ($e->getCode() === 404) {
                return null;
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
                'headers' => [
                    'Content-Type' => '',
                ],
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
        $response = $this->client->request('GET', $this->options['base_url'] . '/compets/'.$codeCompetition.'/phases/'.$numPhase.'/poules/'.$numPoule.'/classement_journees', [
                'headers' => [
                    'Content-Type' => '',
                ],
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
        $response = $this->client->request('GET', $this->options['base_url'] . '/compets/'.$codeCompetition.'/phases/'.$numPhase.'/poules/'.$numPoule.'/matchs?clNo='.$numClub, [
                'headers' => [
                    'Content-Type' => '',
                ],
                'extra' => [
                    'no_cache' => true,
                ]
            ]
        );

        return $this->toArrayOrNullIf404($response);
    }

}