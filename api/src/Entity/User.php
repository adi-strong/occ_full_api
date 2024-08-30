<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_USERNAME', fields: ['username'])]
#[ApiResource(
    types: ['https://schema.org/User',],
    operations: [
        new Get(),
        new Put(),
        new Post(),
        new Patch(),
        new GetCollection(),
    ],
    routePrefix: '/api',
    normalizationContext: ['groups' => ['user:read']],
    forceEager: false
)]
#[UniqueEntity('username', message: 'Ce Username existe déjà.')]
#[ApiFilter(OrderFilter::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'user:read',
        'continent:read',
        'country:read',
        'city:read',
    ])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\Regex('#^[a-z]+(?:[._-][a-z]+)*$#', message: 'Username invalide.')]
    #[Assert\NotBlank(message: 'Le Nom d\'utilisateur doit être renseigné.')]
    #[Assert\NotNull(message: 'Ce champ doit être renseigné.')]
    #[Assert\Length(
        min: 2,
        max: 180,
        minMessage: 'Ce champ doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Ce champ ne peut dépasser {{ limit }} caractères.'
    )]
    #[Groups([
        'user:read',
        'continent:read',
        'country:read',
        'city:read',
    ])]
    private ?string $username = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups([
        'user:read',
        'continent:read',
        'country:read',
    ])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le Mot de passe doit être renseigné.')]
    #[Assert\NotNull(message: 'Ce champ doit être renseigné.')]
    #[Assert\Length(min: 4, minMessage: 'Ce champ doit faire au moins {{ limit }} caractères.',)]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[ApiProperty(iris: ['https://schema.org/fullName'])]
    #[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
    #[Assert\NotBlank(message: 'Le Nom complet doit être renseigné.')]
    #[Assert\NotNull(message: 'Ce champ doit être renseigné.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Ce champ doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Ce champ ne peut dépasser {{ limit }} caractères.'
    )]
    #[Groups([
        'user:read',
        'continent:read',
        'country:read',
        'city:read',
    ])]
    private ?string $fullName = null;

    #[ORM\Column(length: 255)]
    #[Assert\Regex('#^\+?\d(?:[\s.-]?\d{2,3}){3,}$#', message: 'N° de Téléphone invalide.')]
    #[Assert\NotBlank(message: 'Le N° de téléphone doit être renseigné.')]
    #[Assert\NotNull(message: 'Ce champ doit être renseigné.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Ce champ doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Ce champ ne peut dépasser {{ limit }} caractères.'
    )]
    #[Groups([
        'user:read',
        'continent:read',
        'country:read',
    ])]
    private ?string $phone = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email(message: 'Adresse Email invalide.')]
    #[Assert\NotBlank(message: 'L\'adresse Email doit être renseignée.')]
    #[Assert\NotNull(message: 'Ce champ doit être renseigné.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Ce champ doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Ce champ ne peut dépasser {{ limit }} caractères.'
    )]
    #[Groups([
        'user:read',
        'continent:read',
    ])]
    private ?string $email = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'users')]
    #[Groups([
        'user:read',
    ])]
    private ?self $author = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: self::class)]
    private Collection $users;

    /**
     * @var Collection<int, Continent>
     */
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Continent::class)]
    #[Groups([
        'user:read',
    ])]
    private Collection $continents;

    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?bool $isActive = true;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->continents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
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

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
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

    public function getAuthor(): ?self
    {
        return $this->author;
    }

    public function setAuthor(?self $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(self $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->setAuthor($this);
        }

        return $this;
    }

    public function removeUser(self $user): static
    {
        if ($this->users->removeElement($user)) {
            // set the owning side to null (unless already changed)
            if ($user->getAuthor() === $this) {
                $user->setAuthor(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Continent>
     */
    public function getContinents(): Collection
    {
        return $this->continents;
    }

    public function addContinent(Continent $continent): static
    {
        if (!$this->continents->contains($continent)) {
            $this->continents->add($continent);
            $continent->setAuthor($this);
        }

        return $this;
    }

    public function removeContinent(Continent $continent): static
    {
        if ($this->continents->removeElement($continent)) {
            // set the owning side to null (unless already changed)
            if ($continent->getAuthor() === $this) {
                $continent->setAuthor(null);
            }
        }

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }
}
