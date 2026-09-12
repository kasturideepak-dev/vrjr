<?php
declare(strict_types=1);

/**
 * Google Sheets append via a service-account JSON key.
 * Failures are logged; callers must still keep the local DB copy.
 */
final class Sheets
{
    public static function keyPath(): string
    {
        return ROOT . '/storage/secrets/google-service-account.json';
    }

    public static function configured(): bool
    {
        return is_file(self::keyPath());
    }

    public static function append(string $spreadsheetId, string $tab, array $values): bool
    {
        if ($spreadsheetId === '' || !self::configured()) {
            return false;
        }
        try {
            $token = self::accessToken();
            $range = rawurlencode($tab . '!A:Z');
            $url = 'https://sheets.googleapis.com/v4/spreadsheets/' . rawurlencode($spreadsheetId) . '/values/' . $range . ':append?valueInputOption=USER_ENTERED';
            $payload = Html::json(['values' => [$values]]);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $token,
                    'Content-Type: application/json',
                ],
                CURLOPT_POSTFIELDS => $payload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
            ]);
            $res = curl_exec($ch);
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code >= 200 && $code < 300) {
                return true;
            }
            error_log('Sheets append HTTP ' . $code . ' ' . (string) $res);
            return false;
        } catch (Throwable $e) {
            error_log('Sheets append: ' . $e->getMessage());
            return false;
        }
    }

    private static function accessToken(): string
    {
        $json = json_decode((string) file_get_contents(self::keyPath()), true);
        if (!$json || empty($json['client_email']) || empty($json['private_key'])) {
            throw new RuntimeException('Invalid service account JSON');
        }
        $now = time();
        $header = self::b64(Html::json(['alg' => 'RS256', 'typ' => 'JWT']));
        $claim = self::b64(Html::json([
            'iss' => $json['client_email'],
            'scope' => 'https://www.googleapis.com/auth/spreadsheets',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $now + 3600,
        ]));
        $unsigned = $header . '.' . $claim;
        $key = openssl_pkey_get_private($json['private_key']);
        if (!$key) {
            throw new RuntimeException('Invalid private key');
        }
        openssl_sign($unsigned, $sig, $key, OPENSSL_ALGO_SHA256);
        $jwt = $unsigned . '.' . self::b64($sig);
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        $res = curl_exec($ch);
        curl_close($ch);
        $data = json_decode((string) $res, true);
        if (empty($data['access_token'])) {
            throw new RuntimeException('Google token error: ' . (string) $res);
        }
        return $data['access_token'];
    }

    private static function b64(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
