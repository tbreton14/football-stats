<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{

    use TimestampableEntity;
    use SoftDeleteableEntity;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id;


    #[ORM\Column(length: 180, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: false)]
    private ?string $firstName;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName;

    #[Vich\UploadableField(mapping: "profile_pictures", fileNameProperty: "profilePicture")]
    public ?File $file = null;

    public ?string $profilePictureUrl = null;

    /**
     * @var \DateTime|null
     */
    #[Assert\Type(\DateTime::class)]
    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $birthDate;


    #[ORM\Column(length: 255, nullable: true)]
    public ?string $profilePicture = null;

    #[Assert\Length(max: 255)]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poste;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: CategoryUser::class)]
    private $categories;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: PlayingUser::class)]
    private $playingsUser;

    #[ORM\ManyToOne(inversedBy: 'users')]
    #[ORM\JoinColumn("poste_id", nullable: true)]
    private ?Poste $userPoste = null;

    #[ORM\ManyToMany(targetEntity: CategoryUser::class, mappedBy: 'users')]
    private Collection $categoryUsers;

    #[ORM\ManyToMany(targetEntity: Summon::class, mappedBy: 'users')]
    private Collection $summonUsers;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->playingsUser = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->firstName." ".$this->lastName;
    }


    /*****************************************************************************************************************
    GETTERS + SETTERS
     ****************************************************************************************************************/

    public function getNbPlayingsUserBySeason($season): int
    {
        $total=0;
        foreach ($this->playingsUser as $playingUser) {
            if ($playingUser->getPlaying()->getCompetition()->getSeasonx() == $season) {
                $total++;
            }
        }
        return $total;
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getUuid(): ?Uuid
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRole($role)
    {
        return in_array($role,$this->getRoles());
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file): void
    {
        $this->file = $file;
    }

    public function getProfilePictureUrl(): ?string
    {
        return $this->profilePictureUrl;
    }

    public function setProfilePictureUrl(?string $profilePictureUrl): void
    {
        $this->profilePictureUrl = $profilePictureUrl;
    }

    public function getBirthDate(): ?\DateTime
    {
        return $this->birthDate;
    }

    public function setBirthDate(?\DateTime $birthDate): void
    {
        $this->birthDate = $birthDate;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function setProfilePicture(?string $profilePicture): void
    {
        $this->profilePicture = $profilePicture;
    }

    public function getPoste(): ?string
    {
        return $this->poste;
    }

    public function setPoste(?string $poste): void
    {
        $this->poste = $poste;
    }

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function setCategories($categories): void
    {
        $this->categories = $categories;
    }

    /**
     * @return ArrayCollection
     */
    public function getPlayingsUser()
    {
        return $this->playingsUser;
    }

    /**
     * @param ArrayCollection $playingsUser
     */
    public function setPlayingsUser($playingsUser)
    {
        $this->playingsUser = $playingsUser;
    }

    public function getFullName() {
        return ucfirst(strtolower($this->firstName))." ".ucfirst(strtolower($this->lastName));
    }

    public function getFullNameUpper() {
        return ucfirst(strtolower($this->firstName))." ".strtoupper($this->lastName);
    }

    public function getUserPoste(): ?Poste
    {
        return $this->userPoste;
    }

    public function setUserPoste(?Poste $userPoste): void
    {
        $this->userPoste = $userPoste;
    }

    public function getCategoryUsers(): Collection
    {
        return $this->categoryUsers;
    }

    public function setCategoryUsers(Collection $categoryUsers): void
    {
        $this->categoryUsers = $categoryUsers;
    }

    public function getSummonUsers(): Collection
    {
        return $this->summonUsers;
    }

    public function setSummonUsers(Collection $summonUsers): void
    {
        $this->summonUsers = $summonUsers;
    }




}
