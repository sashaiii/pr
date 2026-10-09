<?php
// src/Core/ParserInterface.php

namespace App\Core;

interface ParserInterface
{
    /**
     * Код банка (латиницей, snake_case). Должен совпадать
     * с полем banks.code в БД.
     */
    public function getBankCode(): string;

    /**
     * Человекочитаемое имя банка.
     */
    public function getBankName(): string;

    /**
     * Вернуть массив курсов валют.
     *
     * Формат:
     * [
     *   ['currency' => 'USD', 'buy' => 78.50, 'sell' => 80.10],
     *   ['currency' => 'EUR', 'buy' => 85.20, 'sell' => 87.40],
     * ]
     *
     * @return array<int, array{currency:string, buy:?float, sell:?float}>
     * @throws \App\Core\ParserException
     */
    public function parse(): array;
}
