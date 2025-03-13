<?php

namespace App\Controller;

use App\Entity\Subscription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class SubscriptionController extends AbstractController
{
    
    #[Route('/subscription/change', name: 'app_subscription_change')]
public function changeSubscription(
    EntityManagerInterface $entityManager,
    Request $request,
    Security $security
): Response {
    $user = $security->getUser();

    if (!$user) {
        return $this->redirectToRoute('app_login'); // Redirige si non connecté
    }

    // Liste des abonnements et rôles associés
    $subscriptions = [
        1 => ['name' => 'Standard', 'price' => 0.00, 'role' => 'ROLE_USER'],
        2 => ['name' => 'Expert', 'price' => 4.99, 'role' => 'ROLE_EXPERT'],
        3 => ['name' => 'Premium', 'price' => 9.99, 'role' => 'ROLE_PREMIUM'],
    ];

    if ($request->isMethod('POST')) {
        $subscriptionId = (int) $request->request->get('subscription');

        if (array_key_exists($subscriptionId, $subscriptions)) {
            $subscription = $entityManager->getRepository(Subscription::class)->find($subscriptionId);

            if ($subscription) {
                // Mise à jour de l'abonnement
                $user->setSubscription($subscription);
                
                // Mise à jour du rôle
                $newRole = [$subscriptions[$subscriptionId]['role']];
                $user->setRoles($newRole);

                // Sauvegarde en base de données
                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_profil'); // Redirige vers le profil après modification
            }
        }
    }

    return $this->render('subscription/index.html.twig', [
        'subscriptions' => $subscriptions,
        'currentSubscription' => $user->getSubscription() ? $user->getSubscription()->getId() : null,
    ]);
}

}
