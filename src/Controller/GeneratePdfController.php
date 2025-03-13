<?php

namespace App\Controller;

use App\Entity\File;
use App\Service\GotenbergService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use DateTimeImmutable;

class GeneratePdfController extends AbstractController
{
    private $pdfService;
    private $entityManager;

    public function __construct(GotenbergService $pdfService, EntityManagerInterface $entityManager)
    {
        $this->pdfService = $pdfService;
        $this->entityManager = $entityManager;
    }

    #[Route('/pdf', name: 'app_pdf')]
    public function pdfPage(Security $security): Response
    {
        $user = $security->getUser();
        $subscriptionId = ($user && $user->getSubscription()) ? $user->getSubscription()->getId() : 1;

        return $this->render('generate_pdf/index.html.twig', [
            'subscriptionId' => $subscriptionId,
        ]);
    }

    #[Route('/pdf/from-url', name: 'url-to-pdf')]
    public function generatePdfFromUrl(Request $request, Security $security): Response
    {
        $user = $security->getUser();

        $formURL = $this->createFormBuilder()
            ->add('url', UrlType::class, ['required' => true, 'label' => 'Entrez votre URL :'])
            ->add('submit', SubmitType::class, ['label' => 'Convertir en PDF'])
            ->getForm();

        $formURL->handleRequest($request);

        if ($formURL->isSubmitted() && $formURL->isValid()) {
            $url = htmlspecialchars($formURL->getData()['url'], ENT_QUOTES, 'UTF-8');

            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return new Response('URL invalide.', Response::HTTP_BAD_REQUEST);
            }

            $pdf = $this->pdfService->convertUrlToPdf($url);
            $pdfName = 'URL - ' . $url;

            $this->saveConversion($user, $pdfName);

            return new Response($pdf, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="url-conversion.pdf"',
            ]);
        }

        return $this->render('generate_pdf/url.html.twig', ['form' => $formURL->createView()]);
    }

    #[Route('/pdf/from-text', name: 'text-to-pdf')]
    public function generatePdfFromText(Request $request, Security $security): Response
    {
        $user = $security->getUser();

        if ($this->isGranted('ROLE_USER') && !$this->isGranted('ROLE_EXPERT') && !$this->isGranted('ROLE_PREMIUM')) {
            throw $this->createAccessDeniedException('Accès refusé.');
        }

        $formText = $this->createFormBuilder()
            ->add('title', TextType::class, ['required' => true, 'label' => 'Votre titre :'])
            ->add('content', TextareaType::class, ['required' => true, 'label' => 'Votre texte :'])
            ->add('submit', SubmitType::class, ['label' => 'Convertir en PDF'])
            ->getForm();

        $formText->handleRequest($request);

        if ($formText->isSubmitted() && $formText->isValid()) {
            $data = $formText->getData();
            $title = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
            $content = strip_tags(htmlspecialchars($data['content'], ENT_QUOTES, 'UTF-8'), '<p><b><i><u><strong><em>');

            $html = "<html>
                        <head>
                            <title>{$title}</title>
                            <style>
                                body {
                                    font-family: Arial, sans-serif;
                                    line-height: 130%;
                                }
                                h1 {
                                    font-size: 20px;
                                    font-weight: bold;
                                }
                                p {
                                    font-size: 14px;
                                }
                            </style>
                        </head>
                        <body>
                            <h1>{$title}</h1>
                            <p>{$content}</p>
                        </body>
                    </html>";

            $pdf = $this->pdfService->generatePdfFromHtml($html);
            $pdfName = 'Texte - ' . $title;

            $this->saveConversion($user, $pdfName);

            return new Response($pdf, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="text-conversion.pdf"',
            ]);
        }

        return $this->render('generate_pdf/text.html.twig', ['form' => $formText->createView()]);
    }

    #[Route('/pdf/from-file', name: 'file-to-pdf')]
    #[IsGranted('ROLE_PREMIUM')]
    public function generatePdfFromFile(Request $request, Security $security): Response
    {
        $user = $security->getUser();

        $form = $this->createFormBuilder()
            ->add('file', FileType::class, ['label' => 'Choisissez un fichier :','mapped' => false, 'required' => true])
            ->add('submit', SubmitType::class, ['label' => 'Convertir en PDF'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('file')->getData();

            if (!$file) {
                return new Response('Aucun fichier reçu.', Response::HTTP_BAD_REQUEST);
            }

            $pdfContent = $this->pdfService->convertFileToPdf($file);
            $pdfName = 'Fichier - ' . $file->getClientOriginalName();

            $this->saveConversion($user, $pdfName);

            return new Response($pdfContent, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="file-conversion.pdf"',
            ]);
        }

        return $this->render('generate_pdf/file.html.twig', ['form' => $form->createView()]);
    }

    private function saveConversion($user, string $pdfName): void
    {
        if (!$user) {
            return;
        }

        $file = new File();
        $file->setUser($user);
        $file->setPdfName($pdfName);
        $file->setCreatedAt((new DateTimeImmutable())->modify('+1 hour')); // Ajout de +1h pour le fuseau

        $this->entityManager->persist($file);
        $this->entityManager->flush();
    }
}
