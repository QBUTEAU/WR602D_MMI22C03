<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Subscription;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Exception;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            return $this->render('registration/index.html.twig', [
                'registrationForm' => $form->createView(),
            ]);
        }

        $hashedPassword = $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData());
        $user->setPassword($hashedPassword);
        $user->setRoles(['ROLE_USER']);

        $subscription = $entityManager->getRepository(Subscription::class)->find(1);

        if (!$subscription) {
            $subscription = $entityManager->getRepository(Subscription::class)->find(4);
        }

        if (!$subscription) {
            throw new Exception("Aucun abonnement par défaut (ID 1 ou 4) n'a été trouvé.");
        }

        $user->setSubscription($subscription);
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_login');
    }
}
