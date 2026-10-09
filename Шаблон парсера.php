<?php
// src/Parsers/SberbankParser.php

namespace App\Parsers;

use App\Core\HttpClient;
use App\Core\ParserException;
use App\Core\ParserInterface;
use Symfony\Component\DomCrawler\Crawler;

class SberbankParser implements ParserInterface
{
    private const URL = 'https://www.sberbank.ru/ru/quotes/currencies';

    private HttpClient $http;

    public function __construct()
    {
        $this->http = new HttpClient(delayMs: 500);
    }

    public function getBankCode(): string { return 'sberbank'; }
    public function getBankName(): string { return 'Сбербанк'; }

    public function parse(): array
    {
        $html = $this->http->get(self::URL);
        $crawler = new Crawler($html);

        $rates = [];

        // ⚠️ Селектор — ПРИМЕР. Каждый разработчик подбирает свой
        // после анализа реального DOM сайта банка (DevTools → F12).
        $crawler->filter('.currency-table tbody tr')->each(function (Crawler $row) use (&$rates) {
            $cells = $row->filter('td');
            if ($cells->count() < 3) return;

            $code = trim($cells->eq(0)->text());
            if (!preg_match('/^[A-Z]{3}$/', $code)) return;

            $buy  = $this->toFloat($cells->eq(1)->text());
            $sell = $this->toFloat($cells->eq(2)->text());

            if ($buy === null && $sell === null) return;

            $rates[] = ['currency' => $code, 'buy' => $buy, 'sell' => $sell];
        });

        if (empty($rates)) {
            throw new ParserException("Сбербанк: не удалось извлечь курсы — изменился DOM?");
        }

        return $rates;
    }

    /** "78,50" → 78.50 */
    private function toFloat(string $text): ?float
    {
        $clean = preg_replace('/[^\d,.\-]/u', '', $text);
        $clean = str_replace(',', '.', $clean);
        return is_numeric($clean) ? (float) $clean : null;
    }
}
