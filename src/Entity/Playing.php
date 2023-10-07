<?php

namespace App\Entity;

use App\Repository\PlayingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

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

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $nbButCsc;

    #[ORM\ManyToOne(inversedBy: 'playing')]
    #[ORM\JoinColumn("competition_id")]
    private ?Competition $competition = null;

    #[ORM\OneToMany(mappedBy: 'playing', targetEntity: PlayingUser::class)]
    private $playingUser;

    public function __construct()
    {
        $this->playingUser = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->clubDom . "-" . $this->clubExt;
    }

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

    /**
     * @return ArrayCollection
     */
    public function getPlayingUser()
    {
        return $this->playingUser;
    }

    /**
     * @param ArrayCollection $playingUser
     */
    public function setPlayingUser($playingUser)
    {
        $this->playingUser = $playingUser;
    }

    /**
     * @return int|null
     */
    public function getNbButCsc(): ?int
    {
        return $this->nbButCsc;
    }

    /**
     * @param int|null $nbButCsc
     */
    public function setNbButCsc(?int $nbButCsc): void
    {
        $this->nbButCsc = $nbButCsc;
    }






}
