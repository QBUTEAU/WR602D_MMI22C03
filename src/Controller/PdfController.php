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

    #[Route("/generate-pdf", name:"generate_pdf")]
    public function generatePdf(): Response
    {
        $htmlContent = '<!DOCTYPE html>
          <html lang="en">
            <head>
              <meta charset="utf-8" />
              <title>My first PDF</title>
            </head>
            <body>
              <h1>Hello world!</h1>
              <p>Fortnite > JoJo</p>
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
