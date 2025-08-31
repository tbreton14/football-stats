<?php

namespace App\Entity;

use App\Repository\CategoryUserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryUserRepository::class)]
class CategoryUser
{
    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'categoryUsers')]
    private Collection|null $users = null;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn("category_id")]
    private ?Category $category = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $season;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn("season_id")]
    private ?Season $seasonx = null;


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

    public function getSeason(): ?string
    {
        return $this->season;
    }

    public function setSeason(?string $season): void
    {
        $this->season = $season;
    }

    public function getSeasonx(): ?Season
    {
        return $this->seasonx;
    }

    public function setSeasonx(?Season $seasonx): void
    {
        $this->seasonx = $seasonx;
    }





}
