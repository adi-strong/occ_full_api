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
use App\Controller\PostCountryAction;
use App\Repository\CountryRepository;
use App\Traits\CreatedAtTrait;
use App\Traits\IsDeletedTrait;
use App\Traits\SlugTrait;
use Cocur\Slugify\Slugify;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[ApiResource(
    types: ['https://schema.org/Country'],
    operations: [
        new Get(),
        new Put(),
        new Post(controller: PostCountryAction::class),
        new Patch(),
        new Delete(),
        new GetCollection(),
    ],
    routePrefix: '/api',
    normalizationContext: ['groups' => ['country:read']],
    forceEager: false
)]
#[UniqueEntity('name', message: 'Ce Pays existe déjà.')]
#[ApiFilter(OrderFilter::class)]
#[ORM\HasLifecycleCallbacks]
class Country
{
    use CreatedAtTrait, SlugTrait, IsDeletedTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'country:read',
        'continent:read',
        'user:read',
        'city:read',
        'port:read',
    ])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
    #[Assert\NotBlank(message: 'Le Nom du Pays doit être renseigné.')]
    #[Assert\NotNull(message: 'Ce champ doit être renseigné.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Ce champ doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Ce champ ne peut dépasser {{ limit }} caractères.'
    )]
    #[Groups([
        'country:read',
        'continent:read',
        'user:read',
        'city:read',
        'port:read',
    ])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups([
        'country:read',
        'continent:read',
        'user:read',
        'city:read',
        'port:read',
    ])]
    private ?string $postalCode = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Groups([
        'country:read',
        'continent:read',
        'user:read',
        'city:read',
        'port:read',
    ])]
    private ?string $abbreviation = null;

    #[ORM\ManyToOne(inversedBy: 'countries')]
    #[Assert\NotBlank(message: 'Le Continent doit être renseigné.')]
    #[Assert\NotNull(message: 'Ce champ doit être renseigné.')]
    #[Groups([
        'country:read',
        'city:read',
        'port:read',
    ])]
    private ?Continent $continent = null;

    /**
     * @var Collection<int, City>
     */
    #[ORM\OneToMany(mappedBy: 'country', targetEntity: City::class, cascade: ['remove'])]
    #[Groups([
        'country:read',
        'continent:read',
        'user:read',
    ])]
    private Collection $cities;

    public function __construct()
    {
        $this->cities = new ArrayCollection();
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

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    public function setAbbreviation(?string $abbreviation): static
    {
        $this->abbreviation = $abbreviation;

        return $this;
    }

    public function getContinent(): ?Continent
    {
        return $this->continent;
    }

    public function setContinent(?Continent $continent): static
    {
        $this->continent = $continent;

        return $this;
    }

    /**
     * @return Collection<int, City>
     */
    public function getCities(): Collection
    {
        return $this->cities;
    }

    public function addCity(City $city): static
    {
        if (!$this->cities->contains($city)) {
            $this->cities->add($city);
            $city->setCountry($this);
        }

        return $this;
    }

    public function removeCity(City $city): static
    {
        if ($this->cities->removeElement($city)) {
            // set the owning side to null (unless already changed)
            if ($city->getCountry() === $this) {
                $city->setCountry(null);
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
