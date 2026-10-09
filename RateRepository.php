<?php
// src/Repositories/RateRepository.php

namespace App\Repositories;

use App\Core\Database;
use PDO;

class RateRepository
{
    /**
     * Сохранить курсы одного банка.
     * @param array<int, array{currency:string, buy:?float, sell:?float}> $rates
     */
    public function saveRates(int $bankId, array $rates, string $parsedAt): int
    {
        $pdo = Database::getConnection();

        $bankIdStmt = $pdo->prepare('SELECT id FROM currencies WHERE code = ?');
        $insert = $pdo->prepare(
            'INSERT INTO rates (bank_id, currency_id, buy, sell, parsed_at)
             VALUES (?, ?, ?, ?, ?)'
        );

        $saved = 0;
        $pdo->beginTransaction();
        try {
            foreach ($rates as $r) {
                $bankIdStmt->execute([$r['currency']]);
                $currencyId = $bankIdStmt->fetchColumn();

                if ($currencyId === false) {
                    continue; // неизвестная валюта — пропускаем
                }

                $insert->execute([
                    $bankId,
                    (int) $currencyId,
                    $r['buy'],
                    $r['sell'],
                    $parsedAt,
                ]);
                $saved++;
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        return $saved;
    }

    public function findBankByCode(string $code): ?array
    {
        $stmt = Database::getConnection()->prepare(
            'SELECT id, code, name FROM banks WHERE code = ? AND is_active = 1'
        );
        $stmt->execute([$code]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** Логирование результата парсинга */
    public function logParse(int $bankId, string $status, ?string $message, int $durationMs): void
    {
        $stmt = Database::getConnection()->prepare(
            'INSERT INTO parse_logs (bank_id, status, message, duration_ms)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$bankId, $status, $message, $durationMs]);
    }

    /** Последние курсы для фронтенда */
    public function getLatestRates(): array
    {
        $sql = "
            SELECT b.name AS bank, c.code AS currency, r.buy, r.sell, r.parsed_at
            FROM rates r
            JOIN banks b      ON b.id = r.bank_id
            JOIN currencies c ON c.id = r.currency_id
            JOIN (
                SELECT bank_id, currency_id, MAX(parsed_at) AS max_time
                FROM rates
                GROUP BY bank_id, currency_id
            ) latest
              ON latest.bank_id = r.bank_id
             AND latest.currency_id = r.currency_id
             AND latest.max_time = r.parsed_at
            ORDER BY b.name, c.code
        ";
        return Database::getConnection()->query($sql)->fetchAll();
    }
}
