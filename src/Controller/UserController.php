<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\File; // 🔥 Importation de File pour éviter l'erreur
use Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function profil(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté

        // Vérification que l'utilisateur est bien connecté
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir votre profil.');
        }

        // Compter le nombre de PDF générés par cet utilisateur
        $pdfCount = $entityManager->getRepository(File::class)->count(['user' => $user]);

        return $this->render('user/profil.html.twig', [
            'user' => $user,
            'pdfCount' => $pdfCount, // 🔥 Nombre de PDFs générés
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: ['GET'])]
    public function logout()
    {
        // Symfony gère la déconnexion automatiquement via security.yaml
        throw new Exception('This should never be reached!');
    }
}
