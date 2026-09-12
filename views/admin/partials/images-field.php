<?php
$name = $name ?? '';
$val = (string) ($val ?? '');
$label = $label ?? 'Images';
$fid = 'imgs-' . preg_replace('/[^a-z0-9_-]+/i', '-', $name);
$paths = [];
foreach (preg_split("/\r\n|\n|\r/", $val) ?: [] as $line) {
    $line = trim($line);
    if ($line !== '') {
        $paths[] = $line;
    }
}
$canUpload = Auth::can('media.upload');
?>
<div class="img-multi" data-img-multi>
  <span class="img-field__lab"><?= Html::e($label) ?></span>
  <textarea id="<?= Html::e($fid) ?>" name="<?= Html::e($name) ?>" hidden><?= Html::e($val) ?></textarea>
  <div class="img-multi__list" data-img-multi-list>
    <?php foreach ($paths as $p): ?>
      <figure class="img-multi__item" draggable="true" data-path="<?= Html::e($p) ?>">
        <img src="<?= Html::e($p) ?>" alt="">
        <button class="img-multi__x" type="button" data-img-multi-remove title="Remove">×</button>
      </figure>
    <?php endforeach; ?>
  </div>
  <p class="img-multi__empty hint" <?= $paths ? 'hidden' : '' ?>>No images yet. Upload from your computer or pick from the library.</p>
  <div class="toolbar">
    <?php if ($canUpload): ?>
      <label class="btn-ghost btn-file">Upload
        <input type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple data-img-upload data-img-upload-multi>
      </label>
    <?php endif; ?>
    <button class="btn-ghost" type="button" data-media-open="#<?= Html::e($fid) ?>" data-media-mode="append">Library</button>
  </div>
  <p class="hint">JPEG, PNG, WebP or GIF, max 8 MB each. Drag thumbs to reorder. Up to 40 images.</p>
</div>
