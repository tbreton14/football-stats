<?php

namespace App\Entity;

use App\Repository\CategorySeasonRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\SoftDeleteable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategorySeasonRepository::class)]
#[SoftDeleteable(fieldName: 'deletedAt', timeAware: false, hardDelete: true)]
class CategorySeason
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'categorySeasons')]
    private Collection|null $users = null;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn("category_id")]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn("season_id")]
    private ?Season $season = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $seeScorersRanking;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $seePasserssRanking;


    /*****************************************************************************************************************
    GETTERS + SETTERS
     ****************************************************************************************************************/

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getUsers(): ?Collection
    {
        return $this->users;
    }

    public function setUsers(?Collection $users): void
    {
        $this->users = $users;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): void
    {
        $this->category = $category;
    }


    public function getSeason(): ?Season
    {
        return $this->season;
    }

    public function setSeason(?Season $season): void
    {
        $this->season = $season;
    }


    /**
     * Get the value of seeScorersRanking
     */
    public function getSeeScorersRanking()
    {
        return $this->seeScorersRanking;
    }

    /**
     * Set the value of seeScorersRanking
     */
    public function setSeeScorersRanking($seeScorersRanking): self
    {
        $this->seeScorersRanking = $seeScorersRanking;

        return $this;
    }

    /**
     * Get the value of seePasserssRanking
     */
    public function getSeePasserssRanking()
    {
        return $this->seePasserssRanking;
    }

    /**
     * Set the value of seePasserssRanking
     */
    public function setSeePasserssRanking($seePasserssRanking): self
    {
        $this->seePasserssRanking = $seePasserssRanking;

        return $this;
    }
}
