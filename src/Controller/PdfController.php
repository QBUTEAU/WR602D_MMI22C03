<?php

namespace App\Controller;

use App\Service\GotenbergService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PdfController extends AbstractController
{
    private GotenbergService $gotenbergService;

    public function __construct(GotenbergService $gotenbergService)
    {
        $this->gotenbergService = $gotenbergService;
    }

    #[Route('/convert-url-to-pdf', name: 'convert_url_to_pdf')]
    public function convertUrlToPdf(Request $request): Response
    {
        $url = $request->query->get('url'); // Récupération de l'URL depuis la requête GET

        if (!$url) {
            return new Response('URL manquante.', Response::HTTP_BAD_REQUEST);
        }

        try {
            // Génération du PDF
            $pdfContent = $this->gotenbergService->convertUrlToPdf($url);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Retourner le PDF en réponse HTTP
        return new Response($pdfContent, Response::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="converted.pdf"',
        ]);
    }

    #[Route("/pdf", name:"pdf_results")]
    public function generatePdf(): Response
    {
        $htmlContent = '<!DOCTYPE html>
          <html lang="en">
            <head>
              <meta charset="utf-8" />
              <title>My first PDF</title>
            </head>
            <body>
              <h1>J\'TE L\'DIT GENTIMEEEEENT !</h1>
            </body>
          </html>';

        $pdfContent = $this->gotenbergService->generatePdfFromHtml($htmlContent);
        // dd($pdfContent);
        return new Response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"',
        ]);
    }
}
