<?php
$type = $type ?? 'hero';
$content = $content ?? [];
$registry = $registry ?? SectionRegistry::all();
?>
<div class="page-head">
  <div>
    <p class="crumbs">Templates / Section</p>
    <h1><?= $row ? 'Edit section template' : 'New section template' ?></h1>
  </div>
  <a class="btn-ghost" href="/admin/templates/">Back</a>
</div>
<form class="form" method="post" action="/admin/templates/sections/">
  <?= Csrf::field() ?>
  <?php if ($row): ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><?php endif; ?>
  <label class="lab">Name <input name="name" required value="<?= Html::e($row['name'] ?? '') ?>" placeholder="Hero — Admissions Open"></label>
  <label class="lab">Block type
    <?php if ($row): ?>
      <input type="hidden" name="type" value="<?= Html::e($type) ?>">
      <input value="<?= Html::e($registry[$type]['label'] ?? $type) ?>" disabled>
    <?php else: ?>
      <select name="type" onchange="location.href='/admin/templates/sections/new/?type='+encodeURIComponent(this.value)">
        <?php foreach ($registry as $k => $meta): ?>
          <option value="<?= Html::e($k) ?>"<?= Html::selected($type, $k) ?>><?= Html::e($meta['label']) ?></option>
        <?php endforeach; ?>
      </select>
    <?php endif; ?>
  </label>
  <?php
  $fields = $registry[$type]['fields'] ?? [];
  $prefix = 'f_';
  require ROOT . '/views/admin/partials/block-fields.php';
  ?>
  <button class="btn" type="submit">Save section template</button>
</form>
