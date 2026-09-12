<?php
declare(strict_types=1);

final class Mailer
{
    private static string $lastError = '';

    public static function lastError(): string
    {
        return self::$lastError;
    }

    public static function configured(): bool
    {
        return Settings::get('smtp_user') !== '' && Settings::get('smtp_pass') !== '';
    }

    public static function send(array $to, string $subject, string $html, ?string $replyTo = null): bool
    {
        self::$lastError = '';
        $recipients = [];
        foreach ($to as $addr) {
            $addr = strtolower(trim((string) $addr));
            if ($addr !== '' && filter_var($addr, FILTER_VALIDATE_EMAIL)) {
                $recipients[$addr] = $addr;
            }
        }
        $recipients = array_values($recipients);
        if (!$recipients) {
            return self::fail('No valid recipients');
        }

        $host = Settings::get('smtp_host', 'smtp.gmail.com') ?: 'smtp.gmail.com';
        $enc = strtolower(Settings::get('smtp_encryption', 'tls') ?: 'tls');
        $port = (int) Settings::get('smtp_port', $enc === 'ssl' ? '465' : '587');
        if ($port <= 0) {
            $port = $enc === 'ssl' ? 465 : 587;
        }
        $user = trim(Settings::get('smtp_user', ''));
        $pass = preg_replace('/\s+/', '', Settings::get('smtp_pass', '')) ?? '';
        $from = trim(Settings::get('smtp_from_email', '')) ?: $user;
        $fromName = Settings::get('smtp_from_name', Settings::get('brand_name', 'VR Junior College'));

        if ($user === '' || $pass === '') {
            return self::fail('SMTP is not configured. Add credentials in Settings.');
        }
        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            return self::fail('From email is invalid');
        }

        $remote = ($enc === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $fp = @stream_socket_client($remote, $errno, $errstr, 20, STREAM_CLIENT_CONNECT);
        if (!$fp) {
            return self::fail("Could not connect to $host:$port — $errstr ($errno)");
        }
        stream_set_timeout($fp, 20);

        try {
            self::expect($fp, [220]);
            $ehloHost = parse_url(BASE_URL, PHP_URL_HOST) ?: 'vrjuniorcollege.com';
            self::cmd($fp, 'EHLO ' . $ehloHost, [250]);
            if ($enc !== 'ssl' && ($enc === 'tls' || $port === 587)) {
                self::cmd($fp, 'STARTTLS', [220]);
                $crypto = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
                if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                    $crypto |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
                }
                if (!stream_socket_enable_crypto($fp, true, $crypto)) {
                    throw new RuntimeException('STARTTLS failed');
                }
                self::cmd($fp, 'EHLO ' . $ehloHost, [250]);
            }
            self::cmd($fp, 'AUTH LOGIN', [334]);
            self::cmd($fp, base64_encode($user), [334]);
            self::cmd($fp, base64_encode($pass), [235]);
            self::cmd($fp, 'MAIL FROM:<' . $from . '>', [250]);
            foreach ($recipients as $addr) {
                self::cmd($fp, 'RCPT TO:<' . $addr . '>', [250, 251]);
            }
            self::cmd($fp, 'DATA', [354]);

            $headers = [
                'Date: ' . date('r'),
                'From: ' . self::mailbox($fromName, $from),
                'To: ' . implode(', ', $recipients),
                'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=',
                'MIME-Version: 1.0',
                'Content-Type: text/html; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
            ];
            if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                $headers[] = 'Reply-To: ' . $replyTo;
            }
            $body = str_replace(["\r\n", "\r"], "\n", $html);
            $body = preg_replace('/^\./m', '..', $body) ?? $body;
            fwrite($fp, implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n", "\r\n", $body) . "\r\n.\r\n");
            self::expect($fp, [250]);
            self::cmd($fp, 'QUIT', [221, 250]);
            fclose($fp);
            return true;
        } catch (Throwable $e) {
            if (is_resource($fp)) {
                fclose($fp);
            }
            return self::fail($e->getMessage());
        }
    }

    private static function fail(string $message): bool
    {
        self::$lastError = $message;
        error_log('Mailer: ' . $message);
        return false;
    }

    private static function mailbox(string $name, string $email): string
    {
        $name = trim(str_replace(["\r", "\n"], '', $name));
        return '=?UTF-8?B?' . base64_encode($name) . '?= <' . $email . '>';
    }

    private static function cmd($fp, string $line, array $ok): string
    {
        fwrite($fp, $line . "\r\n");
        return self::expect($fp, $ok);
    }

    private static function expect($fp, array $ok): string
    {
        $data = '';
        while (($line = fgets($fp, 8192)) !== false) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $code = (int) substr($data, 0, 3);
        if (!in_array($code, $ok, true)) {
            throw new RuntimeException('SMTP ' . $code . ': ' . trim($data));
        }
        return $data;
    }
}
