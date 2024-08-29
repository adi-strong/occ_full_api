<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher) { }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('FR-fr');

        $rootPassword = 'root';
        $root = (new User())
            ->setUsername($rootPassword)
            ->setEmail($faker->email)
            ->setFullName($faker->name)
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setPhone($faker->phoneNumber);
        $root->setPassword($this->hasher->hashPassword($root, $rootPassword));
        $manager->persist($root);

        $manager->flush();
    }
}
