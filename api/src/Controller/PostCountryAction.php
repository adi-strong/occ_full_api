<?php

namespace App\Controller;

use App\Entity\Country;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class PostCountryAction extends AbstractController
{
    public function __invoke(Country $country): Country
    {
        $slug = (new Slugify())->slugify($country->getName());

        $country
            ->setSlug($slug)
            ->setCreatedAt(new \DateTime());

        return $country;
    }
}
