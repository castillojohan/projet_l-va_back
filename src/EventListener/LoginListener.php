<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;


class LoginListener
{
    public function onLogin(InteractiveLoginEvent $event) 
    {
        $user = $event->getAuthenticationToken()->getUser();
        
        if ($user && in_array('ROLE_DESACTIVATED_USER', $user->getRoles())) {
            throw new AccessDeniedHttpException(
                'Votre compte a été désactivé.',
                 null,
                  Response::HTTP_UNAUTHORIZED);

        }
        
    }
}
