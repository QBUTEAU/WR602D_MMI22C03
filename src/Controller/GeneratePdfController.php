<?php

namespace App\Controller;

use App\Service\GotenbergService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

class GeneratePdfController extends AbstractController
{
    private $pdfService;

    public function __construct(GotenbergService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    #[Route('/pdf', name: 'app_pdf')]
    public function index(): Response
    {
        return $this->render('generate_pdf/index.html.twig', [
            'controller_name' => 'GeneratePdfController',
        ]);
    }

    #[Route('/pdf/from-url', name: 'url-to-pdf')]
    public function generatePdfFromUrl(Request $request): Response
    {
        // Créer le formulaire avec un champ URL et un bouton submit
        $formURL = $this->createFormBuilder()
            ->add('url', UrlType::class, [
                'required' => true,
                'label' => 'Entrez votre URL :'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Générer le PDF'
            ])
            ->getForm();

        // Gérer la soumission du formulaire
        $formURL->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($formURL->isSubmitted() && $formURL->isValid()) {
            // Récupérer l'URL saisie et la sécuriser (nettoyage)
            $url = htmlspecialchars($formURL->getData()['url'], ENT_QUOTES, 'UTF-8');

            // Vérification de l'URL (valide et sécurisée)
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return new Response('URL invalide.', Response::HTTP_BAD_REQUEST);
            }

            // Faire appel au service pour générer le PDF
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

    #[Route('/pdf/from-text', name: 'text-to-pdf')]
    public function generatePdfFromText(Request $request): Response
    {
        // Créer le formulaire avec un champ titre (h1) et un champ texte (textarea)
        $formText = $this->createFormBuilder()
            ->add('title', TextType::class, [
                'required' => true,
                'label' => 'Votre titre :'
            ])
            ->add('content', TextareaType::class, [
                'required' => true,
                'label' => 'Votre texte :'
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Générer le PDF'
            ])
            ->getForm();

        // Gérer la soumission du formulaire
        $formText->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($formText->isSubmitted() && $formText->isValid()) {
            // Récupérer les données du formulaire
            $data = $formText->getData();
            $title = htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8');
            $content = htmlspecialchars($data['content'], ENT_QUOTES, 'UTF-8');

            // Optionnellement, retirer les balises HTML indésirables
            $content = strip_tags($content, '<p><b><i><u><strong><em>'); // Liste des balises permises

            // Générer le HTML à convertir en PDF
            $html = "
                    <!DOCTYPE html>
                    <html lang='fr'>
                        <head>
                            <meta charset='UTF-8'>
                            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                            <title>{$title}</title>
                            <style>
                                body {
                                    font-family: Arial, sans-serif;
                                    background-color:rgb(173, 173, 173);
                                    margin: 0;
                                    padding: 20px;
                                }
                                h1 {
                                    color: #333;
                                    text-align: center;
                                }
                                p {
                                    color: #666;
                                    font-size: 16px;
                                    line-height: 1.5;
                                    margin: 20px 0;
                                }
                                .container {
                                    max-width: 800px;
                                    margin: 0 auto;
                                    background-color: #fff;
                                    padding: 20px;
                                    border-radius: 8px;
                                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                                }
                            </style>
                        </head>
                        <body>
                            <div class='container'>
                                <h1>{$title}</h1>
                                <p>{$content}</p>
                            </div>
                        </body>
                    </html>
                    ";

            // Appel au service pour générer le PDF
            $pdf = $this->pdfService->generatePdfFromHtml($html);

            // Retourner le PDF en réponse HTTP
            return new Response($pdf, Response::HTTP_OK, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="generated.pdf"',
            ]);
        }

        // Afficher le formulaire
        return $this->render('generate_pdf/html.html.twig', [
            'form' => $formText->createView(),
        ]);
    }
}
