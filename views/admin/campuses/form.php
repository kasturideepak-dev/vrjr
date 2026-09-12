<div class="page-head"><h1><?= Html::e($title) ?></h1></div>
<form class="form" method="post" action="/admin/campuses/">
  <?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int) ($row['id'] ?? 0) ?>">
  <label class="lab">Name <input name="name" value="<?= Html::e($row['name'] ?? '') ?>" required></label>
  <label class="lab">Slug <input name="slug" value="<?= Html::e($row['slug'] ?? '') ?>"></label>
  <label class="lab">Address <input name="address" value="<?= Html::e($row['address'] ?? '') ?>"></label>
  <div class="row2">
    <label class="lab">Phone <input name="phone" value="<?= Html::e($row['phone'] ?? '') ?>"></label>
    <label class="lab">Email <input name="email" value="<?= Html::e($row['email'] ?? '') ?>"></label>
  </div>
  <label class="lab">Image path <input name="image_path" value="<?= Html::e($row['image_path'] ?? '') ?>"></label>
  <label class="lab">Map embed <textarea name="map_embed"><?= Html::e($row['map_embed'] ?? '') ?></textarea></label>
  <label class="lab">Order <input name="sort_order" value="<?= Html::e((string) ($row['sort_order'] ?? '0')) ?>"></label>
  <label class="lab">Status <select name="status"><option>published</option><option <?= ($row['status'] ?? '') === 'draft' ? 'selected' : '' ?>>draft</option></select></label>
  <button class="btn" type="submit">Save</button>
</form>
