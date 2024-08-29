<?php

namespace App\Controller;

use App\Entity\Continent;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class PostContinentAction extends AbstractController
{
    public function __construct(private readonly Security $security) { }

    public function __invoke(Continent $continent): Continent
    {
        $slug = (new Slugify())->slugify($continent->getName());
        $user = $this->security->getUser();

        $continent
            ->setSlug($slug)
            ->setAuthor($user);

        return $continent;
    }
}
