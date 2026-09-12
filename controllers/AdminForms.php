<?php
declare(strict_types=1);

final class AdminForms
{
    public static function index(): void
    {
        Auth::requirePerm('forms.view');
        $rows = Database::all(
            'SELECT f.*, (SELECT COUNT(*) FROM form_fields ff WHERE ff.form_id = f.id) AS field_count,
                    (SELECT COUNT(*) FROM form_submissions s WHERE s.form_id = f.id) AS lead_count
             FROM forms f ORDER BY f.id'
        );
        View::admin('forms/index', ['title' => 'Forms', 'rows' => $rows]);
    }

    public static function edit(?string $id = null): void
    {
        Auth::requirePerm('forms.edit');
        if ($id === null) {
            $id = (string) Database::insert('forms', [
                'name' => 'New form',
                'slug' => Slug::uniqueInTable('forms', 'form'),
                'success_message' => 'Thank you. Our team will be in touch.',
                'honeypot_field' => 'website',
                'is_active' => 1,
            ]);
            View::redirect('/admin/forms/' . $id . '/');
        }
        $id = (int) $id;
        $row = Database::one('SELECT * FROM forms WHERE id = ?', [$id]);
        if (!$row) {
            View::admin('errors/404', ['title' => 'Not found']);
            return;
        }
        View::admin('forms/edit', [
            'title' => 'Form: ' . $row['name'],
            'row' => $row,
            'fields' => Database::all('SELECT * FROM form_fields WHERE form_id = ? ORDER BY sort_order, id', [$id]),
            'recipients' => Database::all('SELECT * FROM form_recipients WHERE form_id = ? ORDER BY email', [$id]),
            'smtpReady' => Mailer::configured(),
            'sheetsReady' => Sheets::configured(),
            'fieldTypes' => Fields::formFieldTypes(),
        ]);
    }

    public static function save(): void
    {
        Auth::requirePerm('forms.edit');
        $id = Request::int('id');
        Database::update('forms', [
            'name' => Request::str('name'),
            'success_message' => Request::str('success_message'),
            'is_active' => Request::bool('is_active') ? 1 : 0,
            'recaptcha_enabled' => Request::bool('recaptcha_enabled') ? 1 : 0,
            'sheets_sync' => Request::bool('sheets_sync') ? 1 : 0,
            'sheet_id' => Request::str('sheet_id'),
            'sheet_tab' => Request::str('sheet_tab') ?: 'Sheet1',
            'notify_email' => Request::str('notify_email'),
        ], 'id = ?', [$id]);
        $ids = $_POST['ff_id'] ?? [];
        $names = $_POST['ff_name'] ?? [];
        $labels = $_POST['ff_label'] ?? [];
        $types = $_POST['ff_type'] ?? [];
        $req = $_POST['ff_required'] ?? [];
        $width = $_POST['ff_width'] ?? [];
        $opts = $_POST['ff_options'] ?? [];
        $ph = $_POST['ff_placeholder'] ?? [];
        $keep = [];
        foreach ($labels as $i => $label) {
            $label = trim((string) $label);
            if ($label === '') {
                continue;
            }
            $fname = Slug::make((string) ($names[$i] ?? $label));
            $fname = str_replace('-', '_', $fname);
            $row = [
                'form_id' => $id,
                'name' => $fname,
                'label' => $label,
                'type' => (string) ($types[$i] ?? 'text'),
                'is_required' => isset($req[$i]) ? 1 : 0,
                'width' => (($width[$i] ?? 'full') === 'half') ? 'half' : 'full',
                'placeholder' => trim((string) ($ph[$i] ?? '')),
                'options_json' => Html::json(['choices' => array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", (string) ($opts[$i] ?? '')) ?: [])))]),
                'sort_order' => (int) $i,
            ];
            $fid = (int) ($ids[$i] ?? 0);
            if ($fid) {
                Database::update('form_fields', $row, 'id = ? AND form_id = ?', [$fid, $id]);
                $keep[] = $fid;
            } else {
                $keep[] = Database::insert('form_fields', $row);
            }
        }
        foreach (Database::all('SELECT id FROM form_fields WHERE form_id = ?', [$id]) as $e) {
            if (!in_array((int) $e['id'], $keep, true)) {
                Database::delete('form_fields', 'id = ?', [(int) $e['id']]);
            }
        }
        Audit::log('form.updated', 'form', $id);
        if (Request::wantsJson()) {
            View::json(['ok' => true]);
        }
        View::flash('success', 'Form saved.');
        View::redirect('/admin/forms/' . $id . '/');
    }

    public static function recipientAdd(): void
    {
        Auth::requirePerm('forms.edit');
        $id = Request::int('id');
        $parts = preg_split('/[\s,;]+/', Request::str('email')) ?: [];
        foreach ($parts as $email) {
            $email = strtolower(trim($email));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            if (!Database::one('SELECT id FROM form_recipients WHERE form_id = ? AND email = ?', [$id, $email])) {
                Database::insert('form_recipients', ['form_id' => $id, 'email' => $email]);
            }
        }
        View::redirect('/admin/forms/' . $id . '/');
    }

    public static function recipientDelete(): void
    {
        Auth::requirePerm('forms.edit');
        $id = Request::int('id');
        Database::delete('form_recipients', 'id = ? AND form_id = ?', [Request::int('recipient_id'), $id]);
        View::redirect('/admin/forms/' . $id . '/');
    }

    public static function leads(): void
    {
        Auth::requirePerm('leads.view');
        $status = Request::str('status');
        $q = Request::str('q');
        $sql = 'SELECT s.*, f.name AS form_name FROM form_submissions s JOIN forms f ON f.id = s.form_id WHERE 1=1';
        $params = [];
        if (in_array($status, ['new', 'contacted', 'closed'], true)) {
            $sql .= ' AND s.status = ?';
            $params[] = $status;
        }
        if ($q !== '') {
            $sql .= ' AND s.payload_json LIKE ?';
            $params[] = '%' . $q . '%';
        }
        $sql .= ' ORDER BY s.id DESC LIMIT 300';
        View::admin('leads/index', ['title' => 'Leads', 'rows' => Database::all($sql, $params), 'status' => $status, 'q' => $q]);
    }

    public static function leadView(?string $id = null): void
    {
        Auth::requirePerm('leads.view');
        $id = (int) ($id ?: Request::int('id'));
        $row = Database::one('SELECT s.*, f.name AS form_name FROM form_submissions s JOIN forms f ON f.id = s.form_id WHERE s.id = ?', [$id]);
        if (!$row) {
            View::admin('errors/404', ['title' => 'Not found']);
            return;
        }
        View::admin('leads/show', ['title' => 'Lead #' . $id, 'row' => $row]);
    }

    public static function leadSave(): void
    {
        Auth::requirePerm('leads.edit');
        $id = Request::int('id');
        $st = Request::str('status');
        if (!in_array($st, ['new', 'contacted', 'closed'], true)) {
            $st = 'new';
        }
        Database::update('form_submissions', ['status' => $st, 'notes' => Request::str('notes')], 'id = ?', [$id]);
        View::flash('success', 'Lead updated.');
        View::redirect('/admin/leads/' . $id . '/');
    }

    public static function leadsExport(): void
    {
        Auth::requirePerm('leads.export');
        $rows = Database::all('SELECT s.*, f.name AS form_name FROM form_submissions s JOIN forms f ON f.id = s.form_id ORDER BY s.id');
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="leads-' . date('Ymd') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['id', 'form', 'status', 'created', 'payload']);
        foreach ($rows as $r) {
            fputcsv($out, [$r['id'], $r['form_name'], $r['status'], $r['created_at'], $r['payload_json']]);
        }
        fclose($out);
        exit;
    }

    public static function submit(): void
    {
        $slug = Request::str('form');
        $form = Database::one('SELECT * FROM forms WHERE slug = ? AND is_active = 1', [$slug]);
        if (!$form) {
            View::flash('error', 'Form unavailable.');
            View::redirect('/');
        }
        $hp = $form['honeypot_field'] ?: 'website';
        if (Request::str($hp) !== '') {
            View::redirect('/');
        }
        $ip = Request::ip();
        $recent = Database::one(
            'SELECT COUNT(*) c FROM form_submissions WHERE ip = ? AND created_at > DATE_SUB(NOW(), INTERVAL 2 MINUTE)',
            [$ip]
        );
        if ((int) ($recent['c'] ?? 0) >= 5) {
            View::flash('error', 'Please wait a moment before submitting again.');
            View::redirect($_SERVER['HTTP_REFERER'] ?? '/');
        }
        if ((int) $form['recaptcha_enabled'] === 1 && Settings::get('recaptcha_secret') !== '') {
            $token = Request::str('g-recaptcha-response');
            if (!self::verifyCaptcha($token)) {
                View::flash('error', 'Captcha failed. Please try again.');
                View::redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }
        }
        $fields = Database::all('SELECT * FROM form_fields WHERE form_id = ? ORDER BY sort_order', [(int) $form['id']]);
        $payload = [];
        foreach ($fields as $f) {
            $val = Request::str($f['name']);
            if ((int) $f['is_required'] && $val === '') {
                View::flash('error', 'Please complete all required fields.');
                View::redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }
            if ($f['type'] === 'email' && $val !== '' && !filter_var($val, FILTER_VALIDATE_EMAIL)) {
                View::flash('error', 'Enter a valid email address.');
                View::redirect($_SERVER['HTTP_REFERER'] ?? '/');
            }
            $payload[$f['name']] = $val;
        }
        $sid = Database::insert('form_submissions', [
            'form_id' => (int) $form['id'],
            'payload_json' => Html::json($payload),
            'ip' => $ip,
        ]);
        $synced = 0;
        $err = null;
        if ((int) $form['sheets_sync'] === 1 && $form['sheet_id']) {
            $row = [date('c'), $form['name']];
            foreach ($fields as $f) {
                $row[] = $payload[$f['name']] ?? '';
            }
            if (Sheets::append($form['sheet_id'], $form['sheet_tab'] ?: 'Sheet1', $row)) {
                $synced = 1;
            } else {
                $err = 'Sheets API failed; saved locally.';
            }
            Database::update('form_submissions', ['sheets_synced' => $synced, 'sheets_error' => $err], 'id = ?', [$sid]);
        }
        self::notify($form, $fields, $payload);
        $msg = trim((string) ($form['success_message'] ?? ''));
        $_SESSION['_form_thanks'] = $msg !== '' ? $msg : 'Thank you. Our admissions team will be in touch shortly.';
        View::redirect('/thank-you/');
    }

    private static function verifyCaptcha(string $token): bool
    {
        if ($token === '') {
            return false;
        }
        $secret = Settings::get('recaptcha_secret');
        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['secret' => $secret, 'response' => $token, 'remoteip' => Request::ip()]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
        ]);
        $res = json_decode((string) curl_exec($ch), true);
        curl_close($ch);
        return !empty($res['success']);
    }

    private static function notify(array $form, array $fields, array $payload): void
    {
        $rows = Database::all('SELECT email FROM form_recipients WHERE form_id = ?', [(int) $form['id']]);
        $to = array_column($rows, 'email');
        if (!$to && !empty($form['notify_email'])) {
            $to = preg_split('/[\s,;]+/', (string) $form['notify_email']) ?: [];
        }
        if (!$to) {
            return;
        }
        $labels = [];
        foreach ($fields as $f) {
            $labels[$f['name']] = $f['label'];
        }
        $html = '<p>New enquiry from ' . Html::e($form['name']) . '.</p><table cellpadding="0" cellspacing="0" style="border-collapse:collapse">';
        foreach ($payload as $k => $v) {
            $html .= '<tr><td style="padding:8px;border:1px solid #e6eaee;font-weight:600">' . Html::e($labels[$k] ?? $k) . '</td><td style="padding:8px;border:1px solid #e6eaee">' . Html::e((string) $v) . '</td></tr>';
        }
        $html .= '</table>';
        $reply = $payload['email'] ?? null;
        Mailer::send($to, 'New enquiry — ' . $form['name'], $html, is_string($reply) ? $reply : null);
    }
}
