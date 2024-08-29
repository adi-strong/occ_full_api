<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
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
class Continent
{
    use SlugTrait, CreatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups([
        'continent:read',
        'user:read',
    ])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Groups([
        'continent:read',
        'user:read',
    ])]
    #[Assert\NotBlank(message: 'Le Nom du Continent doit être renseigné.')]
    #[Assert\NotNull(message: 'Ce champ doit être renseigné.')]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: 'Ce champ doit faire au moins {{ limit }} caractères.',
        maxMessage: 'Ce champ ne peut dépasser {{ limit }} caractères.'
    )]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'continents')]
    #[Groups([
        'continent:read',
    ])]
    private ?User $author = null;

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
}
