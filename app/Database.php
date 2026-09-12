<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo) {
            return self::$pdo;
        }
        $c = require ROOT . '/config/database.php';
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $c['host'],
            $c['port'],
            $c['database'],
            $c['charset']
        );
        self::$pdo = new PDO($dsn, $c['username'], $c['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return self::$pdo;
    }

    public static function connectWithoutDb(): PDO
    {
        $c = require ROOT . '/config/database.php';
        $dsn = sprintf('mysql:host=%s;port=%s;charset=%s', $c['host'], $c['port'], $c['charset']);
        return new PDO($dsn, $c['username'], $c['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    private static function ident(string $name): string
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) {
            throw new InvalidArgumentException('Invalid identifier');
        }
        return '`' . $name . '`';
    }

    public static function query(string $sql, array $params = []): PDOStatement
    {
        $st = self::pdo()->prepare($sql);
        $st->execute(array_values($params));
        return $st;
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    public static function all(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            self::ident($table),
            implode(',', array_map(fn ($c) => self::ident($c), $cols)),
            implode(',', array_fill(0, count($cols), '?'))
        );
        self::query($sql, array_values($data));
        return (int) self::pdo()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $whereParams): int
    {
        if (!$data) {
            return 0;
        }
        $set = implode(',', array_map(fn ($c) => self::ident($c) . '=?', array_keys($data)));
        $st = self::query(
            'UPDATE ' . self::ident($table) . ' SET ' . $set . ' WHERE ' . $where,
            [...array_values($data), ...$whereParams]
        );
        return $st->rowCount();
    }

    public static function delete(string $table, string $where, array $params): int
    {
        return self::query('DELETE FROM ' . self::ident($table) . ' WHERE ' . $where, $params)->rowCount();
    }

    public static function upsertSeo(string $type, int $id, array $data): void
    {
        $exists = self::one('SELECT id FROM seo_metadata WHERE entity_type = ? AND entity_id = ?', [$type, $id]);
        if ($exists) {
            self::update('seo_metadata', $data, 'entity_type = ? AND entity_id = ?', [$type, $id]);
            return;
        }
        $data['entity_type'] = $type;
        $data['entity_id'] = $id;
        self::insert('seo_metadata', $data);
    }
}
