<?php

namespace App\EventListener;

use ApiPlatform\Symfony\Security\Exception\AccessDeniedException;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

final class JWTCreatedListener
{
    public function onJWTCreated(JWTCreatedEvent $event): void
    {
        $payload = $event->getData();
        $user = $event->getUser();

        $payload['id'] = $user->getId();
        $payload['username'] = $user->getUserIdentifier();
        $payload['email'] = $user->getEmail();
        $payload['roles'] = $user->getRoles();
        $payload['fullName'] = $user->getFullName();
        $payload['phone'] = $user->getPhone();

        if ($user->isActive() === true) $event->setData($payload);
        else throw new AccessDeniedException('Access denied.');
    }
}
