<?php
declare(strict_types=1);

/**
 * Idempotent: create snippet tables and migrate GTM/GA/Meta Pixel settings.
 * Usage: php database/migrate_snippets.php
 */
define('ROOT', dirname(__DIR__));
require ROOT . '/app/bootstrap.php';
Snippets::boot();
$rows = Database::all('SELECT id, name, type, placement, scope, is_active, origin_key FROM code_snippets ORDER BY priority, id');
echo "Snippets: " . count($rows) . "\n";
foreach ($rows as $r) {
    echo sprintf(
        "  #%d %s [%s / %s / %s] %s %s\n",
        (int) $r['id'],
        $r['name'],
        $r['type'],
        $r['placement'],
        $r['scope'],
        ((int) $r['is_active'] === 1) ? 'active' : 'inactive',
        $r['origin_key'] ? '(' . $r['origin_key'] . ')' : ''
    );
}
echo "Done.\n";
