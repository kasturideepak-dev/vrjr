<?php
/**
 * CLI / wget entry for scheduled tasks (publish, backups, cleanup).
 * Preferred public URL: /cron/{CRON_KEY}/
 */
declare(strict_types=1);

if (php_sapi_name() !== 'cli') {
    require __DIR__ . '/public/index.php';
    return;
}

require __DIR__ . '/app/bootstrap.php';
$result = Cron::run();
echo implode("\n", $result['messages']) . "\n";
exit($result['ok'] ? 0 : 1);
