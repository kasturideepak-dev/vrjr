<?php
$fields = $fields ?? [];
$content = $content ?? [];
$prefix = $prefix ?? 'f_';
foreach ($fields as $f):
    $name = $prefix . $f['k'];
    $raw = $content[$f['k']] ?? '';
    $val = is_array($raw) ? implode("\n", $raw) : (string) $raw;
    $label = $f['l'];
    if ($f['t'] === 'image') {
        require ROOT . '/views/admin/partials/image-field.php';
        continue;
    }
    if ($f['t'] === 'images') {
        require ROOT . '/views/admin/partials/images-field.php';
        continue;
    }
?>
  <label class="lab"><?= Html::e($f['l']) ?>
    <?php if ($f['t'] === 'textarea' || $f['t'] === 'html'): ?>
      <textarea name="<?= Html::e($name) ?>"<?= $f['t'] === 'html' ? ' data-wysiwyg' : '' ?>><?= Html::e($val) ?></textarea>
    <?php else: ?>
      <input name="<?= Html::e($name) ?>" value="<?= Html::e($val) ?>">
    <?php endif; ?>
  </label>
<?php endforeach; ?>
