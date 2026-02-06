<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CategoryRepository::class)]
class Category
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
    private ?string $name;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: CategorySeason::class)]
    private $categorySeasons;


    public function __construct()
    {
        $this->categorySeasons = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
    }


    /*****************************************************************************************************************
    GETTERS + SETTERS
     ****************************************************************************************************************/

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }


    /**
     * Get the value of categorySeasons
     */
    public function getCategorySeasons()
    {
        return $this->categorySeasons;
    }

    /**
     * Set the value of categorySeasons
     */
    public function setCategorySeasons($categorySeasons): self
    {
        $this->categorySeasons = $categorySeasons;

        return $this;
    }
}
