<div class="page-head"><div><p class="crumbs">Forms</p><h1><?= Html::e($row['name']) ?></h1></div></div>
<form class="form wide" method="post" action="/admin/forms/" data-ajax>
  <?= Csrf::field() ?>
  <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
  <div class="row2">
    <label class="lab">Name <input name="name" value="<?= Html::e($row['name']) ?>"></label>
    <label class="lab">Success message <input name="success_message" value="<?= Html::e($row['success_message'] ?? '') ?>"></label>
  </div>
  <div class="toolbar">
    <label><input type="checkbox" name="is_active" <?= Html::checked((int) $row['is_active'] === 1) ?>> Active</label>
    <label><input type="checkbox" name="recaptcha_enabled" <?= Html::checked((int) $row['recaptcha_enabled'] === 1) ?>> reCAPTCHA</label>
    <label><input type="checkbox" name="sheets_sync" <?= Html::checked((int) $row['sheets_sync'] === 1) ?>> Sync to Google Sheet</label>
  </div>
  <div class="row2">
    <label class="lab">Spreadsheet ID <input name="sheet_id" value="<?= Html::e($row['sheet_id'] ?? '') ?>"></label>
    <label class="lab">Tab name <input name="sheet_tab" value="<?= Html::e($row['sheet_tab'] ?? 'Sheet1') ?>"></label>
  </div>
  <label class="lab">Notify emails (comma separated) <input name="notify_email" value="<?= Html::e($row['notify_email'] ?? '') ?>"></label>
  <p style="color:var(--muted)"><?= !empty($sheetsReady) ? 'Google Sheets key is installed.' : 'Upload a service-account JSON in Settings to enable Sheets sync.' ?></p>
  <h3>Fields</h3>
  <?php foreach ($fields as $f):
    $opts = json_decode($f['options_json'] ?: '{}', true) ?: [];
    $choices = implode("\n", $opts['choices'] ?? []);
  ?>
    <div class="row2" style="margin-bottom:8px">
      <input type="hidden" name="ff_id[]" value="<?= (int) $f['id'] ?>">
      <label class="lab">Label <input name="ff_label[]" value="<?= Html::e($f['label']) ?>"></label>
      <label class="lab">Name <input name="ff_name[]" value="<?= Html::e($f['name']) ?>"></label>
      <label class="lab">Type
        <select name="ff_type[]">
          <?php foreach ($fieldTypes as $k => $l): ?>
            <option value="<?= Html::e($k) ?>"<?= Html::selected($f['type'], $k) ?>><?= Html::e($l) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="lab">Width
        <select name="ff_width[]">
          <option value="full"<?= Html::selected($f['width'], 'full') ?>>Full</option>
          <option value="half"<?= Html::selected($f['width'], 'half') ?>>Half</option>
        </select>
      </label>
      <label class="lab">Placeholder <input name="ff_placeholder[]" value="<?= Html::e($f['placeholder'] ?? '') ?>"></label>
      <label class="lab">Options (one per line) <textarea name="ff_options[]"><?= Html::e($choices) ?></textarea></label>
      <label><input type="checkbox" name="ff_required[<?= (int) $f['id'] ?>]" <?= Html::checked((int) $f['is_required'] === 1) ?>> Required</label>
    </div>
  <?php endforeach; ?>
  <div class="row2">
    <input type="hidden" name="ff_id[]" value="0">
    <label class="lab">New field label <input name="ff_label[]"></label>
    <label class="lab">New field name <input name="ff_name[]"></label>
    <label class="lab">Type
      <select name="ff_type[]"><?php foreach ($fieldTypes as $k => $l): ?><option value="<?= Html::e($k) ?>"><?= Html::e($l) ?></option><?php endforeach; ?></select>
    </label>
    <label class="lab">Width <select name="ff_width[]"><option value="full">Full</option><option value="half">Half</option></select></label>
    <label class="lab">Placeholder <input name="ff_placeholder[]"></label>
    <label class="lab">Options <textarea name="ff_options[]"></textarea></label>
  </div>
  <button class="btn" type="submit">Save form</button>
</form>
<form class="form" method="post" action="/admin/forms/recipient/" style="margin-top:18px">
  <?= Csrf::field() ?>
  <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
  <label class="lab">Add recipient <input name="email" placeholder="admissions@vrjuniorcollege.com"></label>
  <button class="btn-ghost" type="submit">Add</button>
</form>
<ul>
  <?php foreach ($recipients as $rc): ?>
    <li><?= Html::e($rc['email']) ?>
      <form method="post" action="/admin/forms/recipient-delete/" style="display:inline"><?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
        <input type="hidden" name="recipient_id" value="<?= (int) $rc['id'] ?>">
        <button class="btn-ghost" type="submit">Remove</button>
      </form>
    </li>
  <?php endforeach; ?>
</ul>
