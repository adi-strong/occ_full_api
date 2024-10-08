<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Controller\PostContinentAction;
use App\Repository\ContinentRepository;
use App\Traits\CreatedAtTrait;
use App\Traits\SlugTrait;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContinentRepository::class)]
#[ApiResource(
    types: ['https://schema.org/Continent'],
    operations: [
        new Get(),
        new Put(),
        new Post(controller: PostContinentAction::class),
        new Patch(),
        new Delete(),
        new GetCollection(),
    ],
    routePrefix: '/api',
    normalizationContext: ['groups' => ['continent:read']],
    forceEager: false
)]
#[UniqueEntity('name', message: 'Ce Continent existe déjà.')]
#[ApiFilter(OrderFilter::class)]
#[ORM\HasLifecycleCallbacks]
class Continent
{
    use SlugTrait, CreatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'continent:read',
        'user:read',
        'country:read',
        'city:read',
        'port:read',
    ])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
    #[Assert\NotBlank(message: 'Le Nom du Continent doit être renseigné.')]
    #[Assert\NotNull(message: 'Ce champ doit être renseigné.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Ce champ doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Ce champ ne peut dépasser {{ limit }} caractères.'
    )]
    #[Groups([
        'continent:read',
        'user:read',
        'country:read',
        'city:read',
        'port:read',
    ])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'continents')]
    #[Groups([
        'continent:read',
        'country:read',
        'city:read',
    ])]
    private ?User $author = null;

    /**
     * @var Collection<int, Country>
     */
    #[ORM\OneToMany(mappedBy: 'continent', targetEntity: Country::class)]
    #[Groups([
        'continent:read',
        'user:read',
    ])]
    private Collection $countries;

    public function __construct()
    {
        $this->countries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?UserInterface $author): static
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Country>
     */
    public function getCountries(): Collection
    {
        return $this->countries;
    }

    public function addCountry(Country $country): static
    {
        if (!$this->countries->contains($country)) {
            $this->countries->add($country);
            $country->setContinent($this);
        }

        return $this;
    }

    public function removeCountry(Country $country): static
    {
        if ($this->countries->removeElement($country)) {
            // set the owning side to null (unless already changed)
            if ($country->getContinent() === $this) {
                $country->setContinent(null);
            }
        }

        return $this;
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $slug = (new Slugify())->slugify($this->getName());
        $this->slug = $slug;
    }
}
