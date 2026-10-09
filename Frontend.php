<?php
// public/index.php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Repositories\RateRepository;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$repo = new RateRepository();
$rates = $repo->getLatestRates();

// JSON API: /?format=json
if (($_GET['format'] ?? '') === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($rates, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    Database::close();
    exit;
}

// HTML-таблица
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Курсы валют — агрегатор</title>
<style>
  body { font-family: system-ui, sans-serif; background:#f4f6fa; padding:30px; }
  h1 { color:#333; }
  table { width:100%; border-collapse:collapse; background:#fff; border-radius:10px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.08); }
  th, td { padding:12px 16px; text-align:left; border-bottom:1px solid #eee; }
  th { background:#667eea; color:#fff; font-weight:600; }
  tr:hover td { background:#f8f9ff; }
  .buy  { color:#28a745; font-weight:600; }
  .sell { color:#dc3545; font-weight:600; }
  .time { color:#888; font-size:13px; }
</style>
</head>
<body>
  <h1>💱 Актуальные курсы валют</h1>
  <p class="time">Данные обновляются автоматически каждые 30 минут.</p>

  <table>
    <thead>
      <tr><th>Банк</th><th>Валюта</th><th>Покупка</th><th>Продажа</th><th>Обновлено</th></tr>
    </thead>
    <tbody>
      <?php foreach ($rates as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['bank']) ?></td>
        <td><?= htmlspecialchars($r['currency']) ?></td>
        <td class="buy"><?= $r['buy']  !== null ? number_format((float)$r['buy'],  2, ',', ' ') : '—' ?></td>
        <td class="sell"><?= $r['sell'] !== null ? number_format((float)$r['sell'], 2, ',', ' ') : '—' ?></td>
        <td class="time"><?= htmlspecialchars($r['parsed_at']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</body>
</html>
<?php Database::close(); ?>
