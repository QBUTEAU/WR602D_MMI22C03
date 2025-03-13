<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GotenbergService
{
    private string $gotenbergUrl;
    private HttpClientInterface $client;
    private string $publicPath;

    public function __construct(HttpClientInterface $client, string $gotenbergUrl)
    {
        $this->client = $client;
        $this->gotenbergUrl = $gotenbergUrl;

        $this->publicPath = '/var/www/html/WR602D_MMI22C03/public';
    }

    /**
     * Convertit une URL en PDF via Gotenberg.
     */
    public function convertUrlToPdf(string $url): string
    {
        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/url', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
            'body' => [
                'url' => $url, // L'URL à convertir en PDF
            ],
        ]);

        return $response->getContent();  // Retourner le contenu du PDF généré
    }

    /**
     * Convertit un contenu HTML en PDF via Gotenberg.
     */
    public function generatePdfFromHtml(string $htmlContent): string
    {
        // Définir le chemin du fichier HTML temporaire dans le dossier public
        $htmlFilePath = $this->publicPath . '/index.html';

        // Sauvegarder le contenu HTML dans un fichier
        file_put_contents($htmlFilePath, $htmlContent);

        // Envoyer le fichier HTML à Gotenberg pour le convertir en PDF
        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/html', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
            'body' => [
                'files' => ['index.html' => fopen($htmlFilePath, 'r')],
            ],
        ]);

        return $response->getContent();  // Retourner le contenu du PDF généré
    }

    public function convertFileToPdf(\Symfony\Component\HttpFoundation\File\UploadedFile $file): string
    {
    // Définir le dossier des uploads
        $uploadDir = $this->publicPath . '/uploads';

    // Vérifier si le dossier existe, sinon le créer
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

    // Déplacer le fichier uploadé vers le dossier public/uploads/
        $filePath = $uploadDir . '/' . $file->getClientOriginalName();
        $file->move($uploadDir, $file->getClientOriginalName());

    // Envoyer le fichier à Gotenberg pour conversion en PDF
        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/libreoffice/convert', [
        'headers' => [
        'Accept' => 'application/pdf',
        ],
        'body' => [
        'files' => fopen($filePath, 'r'),
        ],
        ]);


        return $response->getContent();
    }
}
