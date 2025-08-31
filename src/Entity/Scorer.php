<?php

namespace App\Entity;

use App\Repository\ScorerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScorerRepository::class)]
class Scorer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $idPlaying = null;

    #[ORM\ManyToOne(inversedBy: 'scorersUser')]
    #[ORM\JoinColumn("user_id")]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'scorersUser')]
    #[ORM\JoinColumn("season_id")]
    private ?Season $season = null;

    #[ORM\Column]
    private ?int $nbGoal = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $sp;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdPlaying(): ?int
    {
        return $this->idPlaying;
    }

    public function setIdPlaying(int $idPlaying): static
    {
        $this->idPlaying = $idPlaying;

        return $this;
    }

    public function getNbGoal(): ?int
    {
        return $this->nbGoal;
    }

    public function setNbGoal(int $nbGoal): static
    {
        $this->nbGoal = $nbGoal;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): void
    {
        $this->season = $season;
    }



}
