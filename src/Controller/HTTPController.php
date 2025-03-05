<?php

namespace App\Controller;

use App\Service\SymfonyDocs;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HTTPController extends AbstractController
{
    private SymfonyDocs $symfonyDocs;

    public function __construct(SymfonyDocs $symfonyDocs)
    {
        $this->symfonyDocs = $symfonyDocs;
    }

    #[Route('/github-docs', name: 'github_docs', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $content = $this->symfonyDocs->fetchGitHubInformation();
        return new JsonResponse($content);
    }

    #[Route('/test-service', name: 'test_service')]
    public function testService(SymfonyDocs $symfonyDocs): JsonResponse
    {
        $content = $symfonyDocs->fetchGitHubInformation();
        return new JsonResponse($content);
    }
}
