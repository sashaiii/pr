<?php
// src/Core/ParserFactory.php

namespace App\Core;

class ParserFactory
{
    /** @var array<string, class-string<ParserInterface>> */
    private array $map;

    public function __construct()
    {
        // Реестр: код банка → класс парсера.
        // Сюда добавляем записи по мере готовности PR'ов.
        $this->map = [
            'sberbank'    => \App\Parsers\SberbankParser::class,
            'tinkoff'     => \App\Parsers\TinkoffParser::class,
            'vtb'         => \App\Parsers\VtbParser::class,
            'alfa'        => \App\Parsers\AlfaParser::class,
            'gazprombank' => \App\Parsers\GazprombankParser::class,
            // ... остальные 10
        ];
    }

    /**
     * @throws ParserException
     */
    public function create(string $bankCode): ParserInterface
    {
        if (!isset($this->map[$bankCode])) {
            throw new ParserException("Парсер для банка '{$bankCode}' не найден");
        }

        $class = $this->map[$bankCode];
        return new $class();
    }

    /** @return string[] */
    public function getBankCodes(): array
    {
        return array_keys($this->map);
    }
}
