<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GotenbergService
{
    private string $gotenbergUrl;
    private HttpClientInterface $client;
    public function __construct(HttpClientInterface $client, string $gotenbergUrl)
    {
        $this->client = $client;
        $this->gotenbergUrl = $gotenbergUrl;
    }

    public function generatePdfFromHtml(string $htmlContent): string
    {

        file_put_contents('/var/www/html/WR602D_MMI22C03/public/index.html', $htmlContent);


$response = $this->client->request('POST', $this->gotenbergUrl . '/forms/chromium/convert/html', [
    'headers' => [
        'Content-Type' => 'multipart/form-data',
    ],
    'body' => [
        'files' => ['index.html' => fopen('/var/www/html/WR602D_MMI22C03/public/index.html', 'r')],
    ],
]);

        return $response->getContent();
    }
}
