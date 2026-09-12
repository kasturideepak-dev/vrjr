<div class="page-head"><h1><?= Html::e($title) ?></h1></div>
<form class="form" method="post" action="/admin/faculty/">
  <?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int) ($row['id'] ?? 0) ?>">
  <label class="lab">Name <input name="name" value="<?= Html::e($row['name'] ?? '') ?>" required></label>
  <label class="lab">Slug <input name="slug" value="<?= Html::e($row['slug'] ?? '') ?>"></label>
  <label class="lab">Designation <input name="designation" value="<?= Html::e($row['designation'] ?? '') ?>"></label>
  <label class="lab">Department <input name="department" value="<?= Html::e($row['department'] ?? '') ?>"></label>
  <label class="lab">Experience <input name="experience" value="<?= Html::e($row['experience'] ?? '') ?>"></label>
  <label class="lab">Bio <textarea name="bio"><?= Html::e($row['bio'] ?? '') ?></textarea></label>
  <label class="lab">Image path <input name="image_path" value="<?= Html::e($row['image_path'] ?? '') ?>"></label>
  <label class="lab">Order <input name="sort_order" value="<?= Html::e((string) ($row['sort_order'] ?? '0')) ?>"></label>
  <label class="lab"><input type="checkbox" name="is_visible" <?= !isset($row['is_visible']) || $row['is_visible'] ? 'checked' : '' ?>> Visible</label>
  <button class="btn" type="submit">Save</button>
</form>
