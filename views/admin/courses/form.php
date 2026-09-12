<div class="page-head"><h1><?= Html::e($title) ?></h1></div>
<form class="form" method="post" action="/admin/courses/">
  <?= Csrf::field() ?>
  <input type="hidden" name="id" value="<?= (int) ($row['id'] ?? 0) ?>">
  <label class="lab">Title <input name="title" value="<?= Html::e($row['title'] ?? '') ?>" required></label>
  <label class="lab">Slug <input name="slug" value="<?= Html::e($row['slug'] ?? '') ?>"></label>
  <label class="lab">Tagline <input name="tagline" value="<?= Html::e($row['tagline'] ?? '') ?>"></label>
  <div class="row2">
    <label class="lab">Stream <input name="stream" value="<?= Html::e($row['stream'] ?? '') ?>"></label>
    <label class="lab">Duration <input name="duration" value="<?= Html::e($row['duration'] ?? '') ?>"></label>
  </div>
  <label class="lab">Summary <textarea name="summary"><?= Html::e($row['summary'] ?? '') ?></textarea></label>
  <label class="lab">Body HTML <textarea name="body_html"><?= Html::e($row['body_html'] ?? '') ?></textarea></label>
  <label class="lab">Image path <input name="image_path" value="<?= Html::e($row['image_path'] ?? '') ?>"></label>
  <label class="lab">Order <input name="sort_order" value="<?= Html::e((string) ($row['sort_order'] ?? '0')) ?>"></label>
  <label class="lab">Status <select name="status"><option <?= ($row['status'] ?? '') === 'published' ? 'selected' : '' ?>>published</option><option <?= ($row['status'] ?? '') === 'draft' ? 'selected' : '' ?>>draft</option></select></label>
  <button class="btn" type="submit">Save</button>
</form>
