<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class RedirectIfNotAuthenticatedListener
{
    private AuthorizationCheckerInterface $authChecker;
    private RouterInterface $router;

    public function __construct(AuthorizationCheckerInterface $authChecker, RouterInterface $router)
    {
        $this->authChecker = $authChecker;
        $this->router = $router;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        // Laisser l'accès à ces routes
        $publicRoutes = ['app_login', 'app_register'];

        if (!in_array($route, $publicRoutes) && !$this->authChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            $event->setResponse(new RedirectResponse($this->router->generate('app_login')));
        }
    }
}
