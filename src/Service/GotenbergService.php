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
}
