<?php
declare(strict_types=1);

final class Audit
{
    public static function log(string $action, ?string $type = null, ?int $id = null, array $meta = []): void
    {
        Database::insert('audit_log', [
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => $type,
            'entity_id' => $id,
            'meta_json' => $meta ? Html::json($meta) : null,
            'ip' => Request::ip(),
        ]);
    }
}
