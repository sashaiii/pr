<?php
// src/Core/HttpClient.php

namespace App\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class HttpClient
{
    private Client $client;
    private int $delayMs;

    public function __construct(int $delayMs = 500)
    {
        $this->delayMs = $delayMs;

        $this->client = new Client([
            'timeout'         => 20,
            'connect_timeout' => 10,
            'verify'          => true,
            'headers'         => [
                'User-Agent'      => $_ENV['HTTP_USER_AGENT']
                    ?? 'Mozilla/5.0 (compatible; CurrencyAggregator/1.0)',
                'Accept'          => 'text/html,application/xhtml+xml,application/json;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'ru-RU,ru;q=0.9,en;q=0.8',
                'Accept-Encoding' => 'gzip, deflate',
            ],
        ]);
    }

    /**
     * @throws ParserException
     */
    public function get(string $url): string
    {
        // Задержка, чтобы не долбить сайт банка
        usleep($this->delayMs * 1000);

        try {
            $response = $this->client->get($url);
            return (string) $response->getBody();
        } catch (GuzzleException $e) {
            throw new ParserException(
                "HTTP error for {$url}: " . $e->getMessage(),
                0,
                $e
            );
        }
    }
}
