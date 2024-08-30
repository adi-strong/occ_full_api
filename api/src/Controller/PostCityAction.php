<?php

namespace App\Controller;

use App\Entity\City;
use App\Entity\Country;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class PostCityAction extends AbstractController
{
    public function __invoke(City $city): City
    {
        $slug = (new Slugify())->slugify($city->getName());

        $city
            ->setSlug($slug)
            ->setCreatedAt(new \DateTime());

        return $city;
    }
}
