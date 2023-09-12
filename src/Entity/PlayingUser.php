<?php

namespace App\Entity;

use App\Repository\PlayingUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PlayingUserRepository::class)]
class PlayingUser
{

    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id;

    #[ORM\ManyToOne(inversedBy: 'playings')]
    #[ORM\JoinColumn("user_id")]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'playings')]
    #[ORM\JoinColumn("playing_id")]
    private ?Playing $playing = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $nbButs;

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $nbPassD;

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

    public function getPlaying(): ?Playing
    {
        return $this->playing;
    }

    public function setPlaying(?Playing $playing): void
    {
        $this->playing = $playing;
    }

    public function getNbButs(): ?int
    {
        return $this->nbButs;
    }

    public function setNbButs(?int $nbButs): void
    {
        $this->nbButs = $nbButs;
    }

    public function getNbPassD(): ?int
    {
        return $this->nbPassD;
    }

    public function setNbPassD(?int $nbPassD): void
    {
        $this->nbPassD = $nbPassD;
    }


}
