<?php
$name = $name ?? '';
$val = (string) ($val ?? '');
$label = $label ?? 'Image';
$fid = 'img-' . preg_replace('/[^a-z0-9_-]+/i', '-', $name);
$isImg = $val !== '' && !preg_match('/\.pdf$/i', $val);
?>
<div class="img-field" data-img-field>
  <span class="img-field__lab"><?= Html::e($label) ?></span>
  <div class="img-field__row">
    <button class="img-field__thumb" type="button" data-media-open="#<?= Html::e($fid) ?>" title="Pick image">
      <img src="<?= Html::e($isImg ? $val : '') ?>" alt="" <?= $isImg ? '' : 'hidden' ?>>
      <span class="img-field__empty" <?= $isImg ? 'hidden' : '' ?>>No image</span>
    </button>
    <div class="img-field__meta">
      <input id="<?= Html::e($fid) ?>" type="hidden" name="<?= Html::e($name) ?>" value="<?= Html::e($val) ?>">
      <code class="img-field__path" title="<?= Html::e($val) ?>"><?= Html::e($val !== '' ? basename($val) : 'None selected') ?></code>
      <div class="toolbar">
        <button class="btn-ghost" type="button" data-media-open="#<?= Html::e($fid) ?>">Pick image</button>
        <button class="btn-ghost" type="button" data-img-clear <?= $val ? '' : 'hidden' ?>>Remove</button>
      </div>
    </div>
  </div>
</div>
