<div class="page-head"><div><p class="crumbs">Users</p><h1><?= $row ? 'Edit user' : 'New user' ?></h1></div></div>
<form class="form" method="post" action="/admin/users/">
  <?= Csrf::field() ?>
  <?php if ($row): ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><?php endif; ?>
  <label class="lab">Name <input name="name" required value="<?= Html::e($row['name'] ?? '') ?>"></label>
  <label class="lab">Email <input type="email" name="email" required value="<?= Html::e($row['email'] ?? '') ?>"></label>
  <label class="lab">Role
    <select name="role_id">
      <?php foreach ($roles as $r): ?>
        <option value="<?= (int) $r['id'] ?>"<?= Html::selected($row['role_id'] ?? '', $r['id']) ?>><?= Html::e($r['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label class="lab">Status
    <select name="status">
      <option value="active"<?= Html::selected($row['status'] ?? 'active', 'active') ?>>active</option>
      <option value="inactive"<?= Html::selected($row['status'] ?? '', 'inactive') ?>>inactive</option>
    </select>
  </label>
  <label class="lab">Password <?= $row ? '(leave blank to keep)' : '' ?> <input type="password" name="password" <?= $row ? '' : 'required' ?> minlength="10"></label>
  <label><input type="checkbox" name="two_factor_enabled" <?= Html::checked((int) ($row['two_factor_enabled'] ?? 0) === 1) ?>> Email OTP on sign-in</label>
  <button class="btn" type="submit">Save</button>
</form>
