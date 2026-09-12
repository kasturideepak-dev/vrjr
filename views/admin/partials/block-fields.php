<?php
$fields = $fields ?? [];
$content = $content ?? [];
$prefix = $prefix ?? 'f_';
foreach ($fields as $f):
    $name = $prefix . $f['k'];
    $raw = $content[$f['k']] ?? '';
    $val = is_array($raw) ? implode("\n", $raw) : (string) $raw;
?>
  <label class="lab"><?= Html::e($f['l']) ?>
    <?php if ($f['t'] === 'textarea' || $f['t'] === 'html'): ?>
      <textarea name="<?= Html::e($name) ?>"><?= Html::e($val) ?></textarea>
    <?php elseif ($f['t'] === 'image'): ?>
      <input name="<?= Html::e($name) ?>" value="<?= Html::e($val) ?>">
      <button class="btn-ghost" type="button" data-media-open="[name='<?= Html::e($name) ?>']">Pick image</button>
    <?php else: ?>
      <input name="<?= Html::e($name) ?>" value="<?= Html::e($val) ?>">
    <?php endif; ?>
  </label>
<?php endforeach; ?>
