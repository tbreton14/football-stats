<?php

namespace App\Entity;

use App\Repository\SummonRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SummonRepository::class)]
class Summon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $idPlaying = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'summonUsers')]
    private Collection|null $users = null;

    #[ORM\ManyToOne(inversedBy: 'summons')]
    #[ORM\JoinColumn("season_id")]
    private ?Season $season = null;

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

    public function getUsers(): ?Collection
    {
        return $this->users;
    }

    public function setUsers(?Collection $users): void
    {
        $this->users = $users;
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
