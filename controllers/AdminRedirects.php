<?php
declare(strict_types=1);

final class AdminRedirects
{
    public static function index(): void
    {
        Auth::requirePerm('redirects.view');
        $q = Request::str('q');
        $sql = 'SELECT * FROM redirects';
        $params = [];
        if ($q !== '') {
            $sql .= ' WHERE from_path LIKE ? OR to_path LIKE ? OR note LIKE ?';
            $params = ['%' . $q . '%', '%' . $q . '%', '%' . $q . '%'];
        }
        $sql .= ' ORDER BY id DESC';
        View::admin('redirects/index', ['title' => 'Redirects', 'rows' => Database::all($sql, $params), 'q' => $q]);
    }

    public static function save(): void
    {
        Auth::requirePerm('redirects.edit');
        $from = Redirects::normalize(Request::str('from_path'));
        $to = Request::str('to_path');
        if ($from === '' || $from === ltrim($to, '/')) {
            View::flash('error', 'Invalid redirect (empty or loop).');
            View::redirect('/admin/redirects/');
        }
        Redirects::save($from, $to, Request::int('status_code') ?: 301, Request::str('note'));
        Audit::log('redirect.saved', 'redirect', 0, ['from' => $from]);
        View::flash('success', 'Redirect saved.');
        View::redirect('/admin/redirects/');
    }

    public static function delete(): void
    {
        Auth::requirePerm('redirects.edit');
        Database::delete('redirects', 'id = ?', [Request::int('id')]);
        View::redirect('/admin/redirects/');
    }
}
