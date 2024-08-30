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
use App\Controller\PostPortAction;
use App\Repository\PortRepository;
use App\Traits\CreatedAtTrait;
use App\Traits\IsDeletedTrait;
use App\Traits\SlugTrait;
use Cocur\Slugify\Slugify;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PortRepository::class)]
#[ApiResource(
    types: ['https://schema.org/Port'],
    operations: [
        new Get(),
        new Put(),
        new Post(controller: PostPortAction::class),
        new Patch(),
        new Delete(),
        new GetCollection(),
    ],
    routePrefix: '/api',
    normalizationContext: ['groups' => ['port:read']],
    forceEager: false
)]
#[ApiFilter(OrderFilter::class)]
#[ORM\HasLifecycleCallbacks]
class Port
{
    use CreatedAtTrait, SlugTrait, IsDeletedTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'port:read',
        'city:read',
        'country:read',
        'continent:read',
        'user:read',
    ])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[ApiProperty(iris: ['https://schema.org/name'])]
    #[ApiFilter(SearchFilter::class, strategy: 'ipartial')]
    #[Assert\NotBlank(message: 'Le Nom du Port doit être renseigné.')]
    #[Assert\NotNull(message: 'Ce champ doit être renseigné.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Ce champ doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Ce champ ne peut dépasser {{ limit }} caractères.'
    )]
    #[Groups([
        'port:read',
        'city:read',
        'country:read',
        'continent:read',
        'user:read',
    ])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([
        'port:read',
        'city:read',
        'country:read',
        'continent:read',
        'user:read',
    ])]
    private ?string $latitude = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups([
        'port:read',
        'city:read',
        'country:read',
        'continent:read',
        'user:read',
    ])]
    private ?string $longitude = null;

    #[ORM\ManyToOne(inversedBy: 'ports')]
    #[Assert\NotBlank(message: 'La Ville doit être renseignée.')]
    #[Assert\NotNull(message: 'Ce champ doit être renseigné.')]
    #[Groups([
        'port:read',
    ])]
    private ?City $city = null;

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

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
    }

    #[ORM\PreUpdate]
    public function onUpdate(): void
    {
        $slug = (new Slugify())->slugify($this->getName());
        $this->slug = $slug;
    }
}
