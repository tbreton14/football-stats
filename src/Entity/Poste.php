<?php

namespace App\Entity;

use App\Repository\PosteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PosteRepository::class)]
class Poste
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $name;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $zOrder;

    #[ORM\OneToMany(mappedBy: 'userPoste', targetEntity: User::class)]
    private $users;

    public function __toString(): string
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

    public function getZOrder(): ?int
    {
        return $this->zOrder;
    }

    public function setZOrder(?int $zOrder): void
    {
        $this->zOrder = $zOrder;
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @param mixed $users
     */
    public function setUsers($users): void
    {
        $this->users = $users;
    }





}
