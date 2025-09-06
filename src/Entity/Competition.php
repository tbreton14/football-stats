<?php

namespace App\Entity;

use App\Repository\CompetitionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompetitionRepository::class)]
class Competition
{

    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $season;

    #[ORM\ManyToOne(inversedBy: 'competitions')]
    #[ORM\JoinColumn("season_id")]
    private ?Season $seasonx = null;

    #[ORM\ManyToOne(inversedBy: 'competitions')]
    #[ORM\JoinColumn("category_id")]
    private ?Category $category = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $name;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $codeCompetition;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numPhase;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numPoule;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $numPoulePhase2;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $playingPersonnal;

    #[ORM\OneToMany(mappedBy: 'competition', targetEntity: CategoryUser::class)]
    private $categories;

    #[ORM\OneToOne(mappedBy: 'competition', targetEntity: Playing::class)]
    private $playing;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $googleAlbumId;


    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name." (".$this->seasonx.")";
    }

    /*****************************************************************************************************************
    GETTERS + SETTERS
     ****************************************************************************************************************/

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getSeason(): ?string
    {
        return $this->season;
    }

    public function setSeason(?string $season): void
    {
        $this->season = $season;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function setCategories(Collection $categories): void
    {
        $this->categories = $categories;
    }

    public function getCodeCompetition(): ?string
    {
        return $this->codeCompetition;
    }

    public function setCodeCompetition(?string $codeCompetition): void
    {
        $this->codeCompetition = $codeCompetition;
    }

    public function getNumPhase(): ?string
    {
        return $this->numPhase;
    }

    public function setNumPhase(?string $numPhase): void
    {
        $this->numPhase = $numPhase;
    }

    public function getNumPoule(): ?string
    {
        return $this->numPoule;
    }

    public function setNumPoule(?string $numPoule): void
    {
        $this->numPoule = $numPoule;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }

    /**
     * @return mixed
     */
    public function getPlayingPersonnal()
    {
        return $this->playingPersonnal;
    }

    /**
     * @param mixed $playingPersonnal
     */
    public function setPlayingPersonnal($playingPersonnal): void
    {
        $this->playingPersonnal = $playingPersonnal;
    }

    public function isPlayingPersonnal(): ?bool
    {
        return $this->playingPersonnal;
    }

    public function getNumPoulePhase2(): ?string
    {
        return $this->numPoulePhase2;
    }

    public function setNumPoulePhase2(?string $numPoulePhase2): void
    {
        $this->numPoulePhase2 = $numPoulePhase2;
    }

    public function getGoogleAlbumId(): ?string
    {
        return $this->googleAlbumId;
    }

    public function setGoogleAlbumId(?string $googleAlbumId): void
    {
        $this->googleAlbumId = $googleAlbumId;
    }

    public function getSeasonx(): ?Season
    {
        return $this->seasonx;
    }

    public function setSeasonx(?Season $seasonx): void
    {
        $this->seasonx = $seasonx;
    }

    /**
     * @return mixed
     */
    public function getPlaying()
    {
        return $this->playing;
    }

    /**
     * @param mixed $playing
     */
    public function setPlaying($playing): void
    {
        $this->playing = $playing;
    }








}
