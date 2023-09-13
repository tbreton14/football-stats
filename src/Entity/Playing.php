<?php

namespace App\Entity;

use App\Repository\PlayingRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PlayingRepository::class)]
class Playing
{

    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $clubDom;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $clubExt;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoClubDom;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $logoClubExt;

    #[Assert\Type(\DateTime::class)]
    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTime $datePlaying;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $scoreDom;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $scoreExt;

    #[ORM\OneToOne(inversedBy: 'playing')]
    private ?Competition $competition = null;


    /*****************************************************************************************************************
    GETTERS + SETTERS
     ****************************************************************************************************************/

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getClubDom(): ?string
    {
        return $this->clubDom;
    }

    public function setClubDom(?string $clubDom): void
    {
        $this->clubDom = $clubDom;
    }

    public function getClubExt(): ?string
    {
        return $this->clubExt;
    }

    public function setClubExt(?string $clubExt): void
    {
        $this->clubExt = $clubExt;
    }

    public function getLogoClubDom(): ?string
    {
        return $this->logoClubDom;
    }

    public function setLogoClubDom(?string $logoClubDom): void
    {
        $this->logoClubDom = $logoClubDom;
    }

    public function getLogoClubExt(): ?string
    {
        return $this->logoClubExt;
    }

    public function setLogoClubExt(?string $logoClubExt): void
    {
        $this->logoClubExt = $logoClubExt;
    }

    public function getDatePlaying(): ?\DateTime
    {
        return $this->datePlaying;
    }

    public function setDatePlaying(?\DateTime $datePlaying): void
    {
        $this->datePlaying = $datePlaying;
    }

    public function getScoreDom(): ?int
    {
        return $this->scoreDom;
    }

    public function setScoreDom(?int $scoreDom): void
    {
        $this->scoreDom = $scoreDom;
    }

    public function getScoreExt(): ?int
    {
        return $this->scoreExt;
    }

    public function setScoreExt(?int $scoreExt): void
    {
        $this->scoreExt = $scoreExt;
    }

    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    public function setCompetition(?Competition $competition): void
    {
        $this->competition = $competition;
    }


}
