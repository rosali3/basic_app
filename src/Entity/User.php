<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Controller\User\GetCurrentController;
use App\Controller\User\RegistrationController;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Collection;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource

(types: ['https://schema.org/Book'],
    //normalizationContext: ['groups' => ['book:read']],
    //denormalizationContext: ['groups' => ['book:write']],
operations: [
    new Post(
        uriTemplate: 'user/register',
        controller: RegistrationController::class,
        denormalizationContext: ['groups' => 'createUser']
    ),
    new Get (
        uriTemplate: 'users/get-current',
        controller: GetCurrentController::class,
        normalizationContext: ['groups' => 'image'],
        denormalizationContext: ['groups' => 'find'],
        security: 'is_granted ("ROLE_ADMIN")'
    ),
    new GetCollection(
        uriTemplate: 'api/images/user',
        //controller: TaskController::class,
        normalizationContext: ['groups' => 'image'],
        security: 'is_granted ("ROLE_USER")',
    ),
    new Delete(),
    new Patch()
])]

class User extends BaseEntity implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['createUser', 'find'])]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(['createUser'])]
    private ?string $password = null;

//    #[ORM\OneToMany(mappedBy: "User", targetEntity: Image::class, orphanRemoval: true)]
    #[ORM\ManyToOne(targetEntity: Image::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups('image')]
    #[ApiProperty(types: ['https://schema.org/image'])]
    public ?Image $image = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string // id не всегда инт
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

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }
    /** @return ?\DateTimeInterface */
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /** @return ?\DateTimeInterface */
    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    #[ORM\PrePersist]
    public function dateCreate(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = $this->createdAt;
    }

    #[ORM\PreUpdate]
    public function dateUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
