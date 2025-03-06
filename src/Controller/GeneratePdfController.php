<?php

namespace App\Controller;

use App\Service\GotenbergService; // Importez votre service ici
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class GeneratePdfController extends AbstractController
{
    private $pdfService;

    public function __construct(GotenbergService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    #[Route('/pdf/from-url', name: 'url-to-pdf')]
    public function generatePdf(Request $request): Response
    {
        // Créer le formulaire avec un champ URL et un bouton submit
        $formURL = $this->createFormBuilder()
            ->add('url', UrlType::class, [
                'required' => true,
                'label' => 'Entrez l\'URL'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Générer le PDF'
            ])
            ->getForm();

        // Gérer la soumission du formulaire
        $formURL->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($formURL->isSubmitted() && $form->isValid()) {
            // Récupérer l'URL saisie à partir des données du formulaire
            $url = $formURL->getData()['url'];

            // Faites appel à votre service pour générer le PDF
            $pdf = $this->pdfService->convertUrlToPdf($url);

            // Retourner le PDF en réponse HTTP
            return new Response($pdf, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="generated.pdf"',
            ]);
        }

        // Afficher le formulaire
        return $this->render('generate_pdf/url.html.twig', [
            'form' => $formURL->createView(),
        ]);
    }
}