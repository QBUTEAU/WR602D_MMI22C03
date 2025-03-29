<?php

// src/Controller/SecurityController.php
namespace App\Controller;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/login.html.twig', [
            'error' => $authenticationUtils->getLastAuthenticationError(),
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function forgotPassword(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createFormBuilder()
        ->add('email', EmailType::class, [
        'required' => true,
        'label' => 'Votre adresse-mail :'
        ])
        ->add('submit', SubmitType::class, ['label' => 'Envoyer'])
        ->getForm();


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()['email'];
            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

            if (!$user) {
                $this->addFlash('error', 'Aucun compte ne correspond à cet e-mail.');
                return $this->redirectToRoute('app_forgot_password');
            }

            // Redirige vers le formulaire de changement de mot de passe avec l'ID de l'utilisateur
            return $this->redirectToRoute('app_reset_password', ['id' => $user->getId()]);
        }

        return $this->render('security/forgot_password.html.twig', [
        'form' => $form->createView()
        ]);
    }

    #[Route('/reset-password/{id}', name: 'app_reset_password')]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        int $id
    ): Response {
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        $form = $this->createFormBuilder()
        ->add('newPassword', PasswordType::class, ['label' => 'Nouveau mot de passe'])
        ->add('confirmPassword', PasswordType::class, ['label' => 'Confirmez le mot de passe'])
        ->add('submit', SubmitType::class, ['label' => 'Modifier'])
        ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if ($data['newPassword'] !== $data['confirmPassword']) {
                $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                return $this->redirectToRoute('app_reset_password', ['id' => $id]);
            }

            // Met à jour le mot de passe de l'utilisateur
            $hashedPassword = $passwordHasher->hashPassword($user, $data['newPassword']);
            $user->setPassword($hashedPassword);
            $entityManager->flush();

            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
        'form' => $form->createView()
        ]);
    }



    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {
    }
}
