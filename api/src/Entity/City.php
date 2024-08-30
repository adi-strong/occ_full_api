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
use App\Controller\PostCityAction;
use App\Repository\CityRepository;
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

#[ORM\Entity(repositoryClass: CityRepository::class)]
#[ApiResource(
    types: ['https://schema.org/City'],
    operations: [
        new Get(),
        new Put(),
        new Post(controller: PostCityAction::class),
        new Patch(),
        new Delete(),
        new GetCollection(),
    ],
    routePrefix: '/api',
    normalizationContext: ['groups' => ['city:read']],
    forceEager: false
)]
#[UniqueEntity('name', message: 'Cette Ville existe déjà.')]
#[ApiFilter(OrderFilter::class)]
#[ORM\HasLifecycleCallbacks]
class City
{
    use CreatedAtTrait, SlugTrait, IsDeletedTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'city:read',
        'country:read',
        'continent:read',
        'user:read',
        'port:read',
    ])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
    #[Assert\NotBlank(message: 'Le Nom de la ville doit être renseignée.')]
    #[Assert\NotNull(message: 'Ce champ doit être renseigné.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Ce champ doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Ce champ ne peut dépasser {{ limit }} caractères.'
    )]
    #[Groups([
        'city:read',
        'country:read',
        'continent:read',
        'user:read',
        'port:read',
    ])]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'cities')]
    #[Assert\NotBlank(message: 'Le Pays doit être renseigné.')]
    #[Assert\NotNull(message: 'Ce champ doit être renseigné.')]
    #[Groups([
        'city:read',
        'port:read',
    ])]
    private ?Country $country = null;

    /**
     * @var Collection<int, Port>
     */
    #[ORM\OneToMany(mappedBy: 'city', targetEntity: Port::class, cascade: ['remove'])]
    #[Groups([
        'city:read',
        'country:read',
        'continent:read',
        'user:read',
    ])]
    private Collection $ports;

    public function __construct()
    {
        $this->ports = new ArrayCollection();
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

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection<int, Port>
     */
    public function getPorts(): Collection
    {
        return $this->ports;
    }

    public function addPort(Port $port): static
    {
        if (!$this->ports->contains($port)) {
            $this->ports->add($port);
            $port->setCity($this);
        }

        return $this;
    }

    public function removePort(Port $port): static
    {
        if ($this->ports->removeElement($port)) {
            // set the owning side to null (unless already changed)
            if ($port->getCity() === $this) {
                $port->setCity(null);
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
