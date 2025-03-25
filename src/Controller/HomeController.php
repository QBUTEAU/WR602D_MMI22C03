<?php

namespace App\Controller;

use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(EntityManagerInterface $entityManager, Security $security): Response
    {
        $user = $security->getUser();

        // Récupère tous les abonnements
        $subscriptions = $entityManager->getRepository(Subscription::class)->findAll();

        return $this->render('home/index.html.twig', [
            'subscriptions' => $subscriptions,
            'currentSubscription' => $user ? $user->getSubscription() : null,
        ]);
    }
}
