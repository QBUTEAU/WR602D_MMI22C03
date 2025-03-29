<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class GotenbergService
{
    private string $gotenbergUrl;
    private HttpClientInterface $client;
    private string $publicPath;

    public function __construct(HttpClientInterface $client, string $gotenbergUrl, KernelInterface $kernel)
    {
        $this->client = $client;
        $this->gotenbergUrl = $gotenbergUrl;

        // Récupération du chemin public dynamique
        $this->publicPath = $kernel->getProjectDir() . '/public';
    }

    public function convertUrlToPdf(string $url): string
    {
        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/url', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
            'body' => [
                'url' => $url,
            ],
        ]);

        return $response->getContent();
    }

    public function generatePdfFromHtml(string $htmlContent): string
    {
        $htmlFilePath = $this->publicPath . '/index.html';

        file_put_contents($htmlFilePath, $htmlContent);

        $response = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/html', [
            'headers' => [
                'Content-Type' => 'multipart/form-data',
            ],
            'body' => [
                'files' => ['index.html' => fopen($htmlFilePath, 'r')],
            ],
        ]);

        return $response->getContent();
    }

    public function convertFileToPdf(UploadedFile $file): string
    {
        $uploadDir = $this->publicPath . '/uploads';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filePath = $uploadDir . '/' . $file->getClientOriginalName();
        $file->move($uploadDir, $file->getClientOriginalName());

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
