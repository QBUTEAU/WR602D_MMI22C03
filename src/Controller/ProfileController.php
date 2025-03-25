<?php

namespace App\Controller;

use App\Form\ChangePasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/profile/delete', name: 'app_delete_profile', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteProfile(
        EntityManagerInterface $entityManager,
        Security $security,
        Request $request
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        // Vérification du token CSRF
        if (!$this->isCsrfTokenValid('delete_profile', $request->request->get('_token'))) {
            $this->addFlash('danger', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_profil');
        }

        // Empêcher un admin de se supprimer
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('danger', 'Un administrateur ne peut pas supprimer son compte.');
            return $this->redirectToRoute('app_profil');
        }

        // Déconnexion avant suppression
        $security->logout(false);

        // Suppression du compte
        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte a été supprimé avec succès.');

        return $this->redirectToRoute('app_home');
    }
}
