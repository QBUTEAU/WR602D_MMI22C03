<?php

namespace App\Controller;

use App\Entity\File;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HistoryController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/history', name: 'app_history')]
    public function conversionHistory(Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir votre historique.');
        }

        // Récupérer les conversions de l'utilisateur
        $files = $this->entityManager->getRepository(File::class)->findBy(
            ['user' => $user],
            ['createdAt' => 'DESC'] // Trier par date décroissante
        );

        foreach ($files as $file) {
            $file->setCreatedAt($file->getCreatedAt()->modify('+1 hour'));
        }

        return $this->render('history/index.html.twig', [
            'files' => $files,
        ]);
    }
}
