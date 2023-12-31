<?php

namespace App\Entity;

use App\Repository\CategoryUserRepository;
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

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn("user_id")]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'categories')]
    #[ORM\JoinColumn("category_id")]
    private ?Category $category = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $season;



    /*****************************************************************************************************************
    GETTERS + SETTERS
     ****************************************************************************************************************/

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
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






}
