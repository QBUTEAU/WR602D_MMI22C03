<?php

namespace App\Controller;

use App\Entity\File;
use App\Repository\FileRepository;
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
    private $fileRepository;

    public function __construct(
        GotenbergService $pdfService,
        EntityManagerInterface $entityManager,
        FileRepository $fileRepository
    ) {
        $this->pdfService = $pdfService;
        $this->entityManager = $entityManager;
        $this->fileRepository = $fileRepository;
    }

    #[Route('/pdf', name: 'app_pdf')]
    public function pdfPage(Security $security): Response
    {
        $user = $security->getUser();
        $subscription = $user?->getSubscription();

        // Récupérer maxPdf depuis l'abonnement
        $maxPdf = $subscription ? $subscription->getMaxPdf() : 3;
        $pdfCountToday = $this->fileRepository->countUserFilesToday($user);

        // Mapping des abonnements vers des IDs fixes
        $subscriptionMapping = [
        'Standard' => 1,
        'Expert' => 2,
        'Premium' => 3,
        ];

        // Obtenir l'ID correspondant au nom de l'abonnement
        $subscriptionId = $subscription ? ($subscriptionMapping[$subscription->getName()] ?? 1) : 1;

        return $this->render('generate_pdf/index.html.twig', [
        'subscriptionId' => $subscriptionId,
        'pdfCountToday' => $pdfCountToday,
        'maxPdf' => $maxPdf,
        ]);
    }




    #[Route('/pdf/from-url', name: 'url-to-pdf')]
    public function generatePdfFromUrl(Request $request, Security $security): Response
    {
        $user = $security->getUser();

        if (!$this->canConvertPdf($user)) {
            $this->addFlash('error', 'Vous avez atteint votre limite journalière de conversions.');
            return $this->redirectToRoute('app_pdf');
        }

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
            $this->saveConversion($user, 'URL - ' . $url);

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

        if (!$this->canConvertPdf($user)) {
            $this->addFlash('error', 'Vous avez atteint votre limite journalière de conversions.');
            return $this->redirectToRoute('app_pdf');
        }

                // VIA textarea avec TinyMCE
        $formText = $this->createFormBuilder()
            ->add('htmlContent', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, [
                'label' => 'Entrez votre contenu : <br>',
                'label_html' => true,
                'required' => true,
                'attr' => ['rows' => 10, 'cols' => 50, 'class' => 'tinymce']  // Ajout de la classe TinyMCE
            ])
            ->add('submit', SubmitType::class, ['label' => 'Générer le PDF'])
            ->getForm();

        $formText->handleRequest($request);
        if ($formText->isSubmitted() && $formText->isValid()) {
            $data = $formText->getData();
            $htmlContent = $data['htmlContent']; // On récupère le contenu sans htmlspecialchars

            $pdf = $this->pdfService->generatePdfFromHtml($htmlContent);
            $this->saveConversion($user, 'Texte HTML');

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

        if (!$this->canConvertPdf($user)) {
            $this->addFlash('error', 'Vous avez atteint votre limite journalière de conversions.');
            return $this->redirectToRoute('app_pdf');
        }

        $form = $this->createFormBuilder()
            ->add('file', FileType::class, ['label' => 'Choisissez un fichier :','mapped' => false,'required' => true])
            ->add('submit', SubmitType::class, ['label' => 'Convertir en PDF'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('file')->getData();

            if (!$file) {
                return new Response('Aucun fichier reçu.', Response::HTTP_BAD_REQUEST);
            }

            $pdfContent = $this->pdfService->convertFileToPdf($file);
            $this->saveConversion($user, 'Fichier - ' . $file->getClientOriginalName());

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
        $file->setCreatedAt(new DateTimeImmutable());

        $this->entityManager->persist($file);
        $this->entityManager->flush();
    }

    private function canConvertPdf($user): bool
    {
        if (!$user) {
            return false;
        }

        $subscription = $user->getSubscription();
        $maxPdf = $subscription ? $subscription->getMaxPdf() : 3;
        $pdfCountToday = $this->fileRepository->countUserFilesToday($user);

        return $pdfCountToday < $maxPdf;
    }
}
