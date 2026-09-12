<div class="page-head"><div><p class="crumbs">Content</p><h1>Media library</h1></div></div>
<form class="form" method="post" action="/admin/media/" enctype="multipart/form-data" data-drop-upload>
  <?= Csrf::field() ?>
  <label class="lab">Upload (images / PDF, max 8 MB each)
    <input type="file" name="files[]" multiple accept="image/jpeg,image/png,image/webp,image/gif,application/pdf">
  </label>
  <button class="btn" type="submit">Upload</button>
</form>
<form method="get" class="toolbar" style="margin:16px 0">
  <input type="search" name="q" value="<?= Html::e($q ?? '') ?>" placeholder="Search files">
  <button class="btn-ghost">Search</button>
</form>
<div class="media-grid">
  <?php foreach ($rows as $m): ?>
    <figure>
      <?php if (str_starts_with($m['mime'], 'image/')): ?>
        <img src="<?= Html::e($m['public_path']) ?>" alt="<?= Html::e($m['alt_text'] ?? '') ?>">
      <?php else: ?>
        <div class="empty">PDF</div>
      <?php endif; ?>
      <figcaption>
        <?= Html::e($m['original_name']) ?><br>
        <small><?= (int) ($m['usage'] ?? 0) ?> uses</small>
        <form method="post" action="/admin/media/update/" style="margin-top:6px">
          <?= Csrf::field() ?>
          <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
          <input name="alt_text" value="<?= Html::e($m['alt_text'] ?? '') ?>" placeholder="Alt text">
          <button class="btn-ghost" type="submit">Save</button>
        </form>
        <form method="post" action="/admin/media/delete/" data-confirm="Delete this file?">
          <?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
          <button class="btn-danger" type="submit">Delete</button>
        </form>
      </figcaption>
    </figure>
  <?php endforeach; ?>
</div>
