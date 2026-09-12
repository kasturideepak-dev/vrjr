<div class="page-head"><div><p class="crumbs">System</p><h1>Site settings</h1></div></div>
<form class="form" method="post" enctype="multipart/form-data">
  <?= Csrf::field() ?>
  <h3>Identity</h3>
  <?php
  $fields = [
    'brand_name' => 'Brand name', 'tagline' => 'Tagline', 'logo' => 'Logo path', 'favicon' => 'Favicon path',
    'phone_primary' => 'Primary phone', 'phone_secondary' => 'Secondary phone',
    'email' => 'Email (info)', 'email_support' => 'Support email', 'whatsapp' => 'WhatsApp',
    'hours' => 'Hours', 'head_office' => 'Head office', 'brochure' => 'Brochure URL',
    'facebook' => 'Facebook', 'instagram' => 'Instagram', 'linkedin' => 'LinkedIn', 'youtube' => 'YouTube',
    'accent_color' => 'Accent colour',
  ];
  foreach ($fields as $k => $l): ?>
    <label class="lab"><?= Html::e($l) ?> <input name="<?= $k ?>" value="<?= Html::e($s[$k] ?? '') ?>"></label>
  <?php endforeach; ?>
  <h3>SEO</h3>
  <label class="lab">Default SEO title <input name="default_seo_title" value="<?= Html::e($s['default_seo_title'] ?? '') ?>"></label>
  <label class="lab">Default meta description <textarea name="default_seo_description"><?= Html::e($s['default_seo_description'] ?? '') ?></textarea></label>
  <label class="lab">Default OG image <input name="og_image" value="<?= Html::e($s['og_image'] ?? '') ?>"></label>
  <?php if (Auth::isSuper()): ?>
    <p class="hint">Tracking scripts (GTM, Analytics, Meta Pixel) and other injected HTML/CSS/JS live in <a href="/admin/snippets/">Code snippets</a> — one place for all site-wide code.</p>
  <?php endif; ?>
  <label class="lab">EducationalOrganization schema (JSON-LD) <textarea name="schema_json" rows="8"><?= Html::e($s['schema_json'] ?? '') ?></textarea></label>
  <label class="lab">robots.txt <textarea name="robots_txt" rows="8"><?= Html::e($s['robots_txt'] ?? '') ?></textarea></label>
  <h3>404 page</h3>
  <label class="lab">Heading <input name="404_heading" value="<?= Html::e($s['404_heading'] ?? 'Page not found') ?>"></label>
  <label class="lab">Text <textarea name="404_text"><?= Html::e($s['404_text'] ?? '') ?></textarea></label>
  <h3>Maintenance</h3>
  <label><input type="checkbox" name="maintenance_mode" <?= Html::checked(($s['maintenance_mode'] ?? '0') === '1') ?>> Enable maintenance (HTTP 503)</label>
  <label class="lab">Message <input name="maintenance_message" value="<?= Html::e($s['maintenance_message'] ?? '') ?>"></label>
  <h3>Spam / reCAPTCHA</h3>
  <label class="lab">Site key <input name="recaptcha_site" value="<?= Html::e($s['recaptcha_site'] ?? '') ?>"></label>
  <label class="lab">Secret <input name="recaptcha_secret" value="<?= Html::e($s['recaptcha_secret'] ?? '') ?>"></label>
  <h3>Backups</h3>
  <label><input type="checkbox" name="backup_auto" <?= Html::checked(($s['backup_auto'] ?? '0') === '1') ?>> Automatic backups</label>
  <div class="row2">
    <label class="lab">Interval
      <select name="backup_interval">
        <option value="daily"<?= Html::selected($s['backup_interval'] ?? 'daily', 'daily') ?>>Daily</option>
        <option value="weekly"<?= Html::selected($s['backup_interval'] ?? '', 'weekly') ?>>Weekly</option>
      </select>
    </label>
    <label class="lab">Keep last N <input name="backup_retention" value="<?= Html::e($s['backup_retention'] ?? '14') ?>"></label>
  </div>
  <h3>Google Sheets</h3>
  <p style="color:var(--muted)"><?= !empty($sheetsReady) ? 'Service account key is installed.' : 'Upload the JSON key from Google Cloud (Sheets API + share the sheet with the client_email).' ?></p>
  <label class="lab">Service account JSON <input type="file" name="sheets_json" accept="application/json"></label>
  <h3>SMTP</h3>
  <label class="lab">SMTP host <input name="smtp_host" value="<?= Html::e($s['smtp_host'] ?? 'smtp.gmail.com') ?>"></label>
  <div class="row2">
    <label class="lab">Port <input name="smtp_port" value="<?= Html::e($s['smtp_port'] ?? '587') ?>"></label>
    <label class="lab">Encryption
      <select name="smtp_encryption">
        <option value="tls" <?= ($s['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' ?>>TLS</option>
        <option value="ssl" <?= ($s['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
      </select>
    </label>
  </div>
  <label class="lab">SMTP user <input type="email" name="smtp_user" value="<?= Html::e($s['smtp_user'] ?? '') ?>"></label>
  <label class="lab">SMTP password <input type="password" name="smtp_pass" value="" placeholder="<?= !empty($s['smtp_pass']) ? 'Saved — leave blank to keep' : '' ?>" autocomplete="new-password"></label>
  <div class="row2">
    <label class="lab">From email <input type="email" name="smtp_from_email" value="<?= Html::e($s['smtp_from_email'] ?? '') ?>"></label>
    <label class="lab">From name <input name="smtp_from_name" value="<?= Html::e($s['smtp_from_name'] ?? '') ?>"></label>
  </div>
  <button class="btn" type="submit">Save settings</button>
</form>
<form class="form" method="post" action="/admin/settings/smtp-test/" style="margin-top:18px">
  <?= Csrf::field() ?>
  <label class="lab">Send a test email to <input type="email" name="test_email" value="<?= Html::e($s['smtp_user'] ?? '') ?>"></label>
  <button class="btn-ghost" type="submit">Send test email</button>
</form>
