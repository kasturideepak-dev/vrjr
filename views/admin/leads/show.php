<?php $payload = json_decode($row['payload_json'] ?: '{}', true) ?: []; ?>
<div class="page-head"><div><p class="crumbs">Leads</p><h1>Lead #<?= (int) $row['id'] ?></h1></div></div>
<div class="card" style="margin-bottom:16px">
  <p><strong><?= Html::e($row['form_name']) ?></strong> · <?= Html::e($row['created_at']) ?> · IP <?= Html::e($row['ip'] ?? '') ?></p>
  <table>
    <?php foreach ($payload as $k => $v): ?>
      <tr><th><?= Html::e((string) $k) ?></th><td><?= Html::e(is_scalar($v) ? (string) $v : Html::json($v)) ?></td></tr>
    <?php endforeach; ?>
  </table>
</div>
<form class="form" method="post" action="/admin/leads/">
  <?= Csrf::field() ?>
  <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
  <label class="lab">Status
    <select name="status">
      <?php foreach (['new','contacted','closed'] as $st): ?>
        <option value="<?= $st ?>"<?= Html::selected($row['status'], $st) ?>><?= $st ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label class="lab">Notes <textarea name="notes"><?= Html::e($row['notes'] ?? '') ?></textarea></label>
  <button class="btn" type="submit">Update</button>
</form>
