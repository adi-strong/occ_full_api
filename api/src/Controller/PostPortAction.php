<?php

namespace App\Controller;

use App\Entity\Port;
use Cocur\Slugify\Slugify;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class PostPortAction extends AbstractController
{
    public function __invoke(Port $port): Port
    {
        $slug = (new Slugify())->slugify($port->getName());

        $port
            ->setSlug($slug)
            ->setCreatedAt(new \DateTime());

        return $port;
    }
}
