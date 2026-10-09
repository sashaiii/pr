<?php
// public/cron.php
//
// Запуск: php /path/to/public/cron.php sberbank
// Или через GET (для отладки с ключом): cron.php?bank=sberbank&key=SECRET

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\Logger;
use App\Core\ParserException;
use App\Core\ParserFactory;
use App\Repositories\RateRepository;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// --- Определяем банк ---
$bankCode = $argv[1] ?? ($_GET['bank'] ?? null);

if (!$bankCode) {
    http_response_code(400);
    exit("Укажите код банка: cron.php sberbank\n");
}

// Защита от вызова через браузер без ключа
if (PHP_SAPI !== 'cli') {
    $key = $_GET['key'] ?? '';
    if ($key !== ($_ENV['CRON_SECRET'] ?? '')) {
        http_response_code(403);
        exit("Forbidden\n");
    }
}

// Не даём скрипту работать дольше 25 секунд (лимит Beget 30-60с)
set_time_limit(25);

$start = microtime(true);
$repo  = new RateRepository();

try {
    $bank = $repo->findBankByCode($bankCode);
    if (!$bank) {
        throw new ParserException("Банк '{$bankCode}' не найден в БД или отключён");
    }

    $factory = new ParserFactory();
    $parser  = $factory->create($bankCode);

    $rates = $parser->parse();
    $saved = $repo->saveRates((int) $bank['id'], $rates, date('Y-m-d H:i:s'));

    $duration = (int) ((microtime(true) - $start) * 1000);

    $repo->logParse((int) $bank['id'], 'success', "Saved {$saved} rates", $duration);
    Logger::write('info', "[{$bankCode}] OK: {$saved} rates in {$duration}ms");

    echo "OK: {$saved} rates\n";
} catch (\Throwable $e) {
    $duration = (int) ((microtime(true) - $start) * 1000);

    $bankId = $bank['id'] ?? 0;
    if ($bankId) {
        $repo->logParse((int) $bankId, 'error', $e->getMessage(), $duration);
    }
    Logger::write('error', "[{$bankCode}] " . $e->getMessage());

    http_response_code(500);
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    // Важно для Beget: закрываем соединение
    Database::close();
}
