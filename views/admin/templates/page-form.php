<?php
$sections = $sections ?? [];
$registry = $registry ?? SectionRegistry::all();
?>
<div class="page-head">
  <div>
    <p class="crumbs">Templates / Page</p>
    <h1><?= Html::e($row['name']) ?></h1>
    <p>This layout is cloned into a new page or CPT entry when staff pick it at create-time.</p>
  </div>
  <a class="btn-ghost" href="/admin/templates/">Back to templates</a>
</div>

<form id="tpl-builder" class="form wide" method="post" action="/admin/templates/pages/<?= (int) $row['id'] ?>/" data-dirty>
  <?= Csrf::field() ?>
  <button type="submit" name="action" value="save" hidden>Save</button>
  <div class="builder">
    <aside class="builder-lib">
      <h3>Add section</h3>
      <p class="hint" style="margin:0 0 10px">Same blocks as the Pages builder.</p>
      <label class="lab">Type
        <select name="add_type">
          <?php foreach ($registry as $k => $meta): ?>
            <option value="<?= Html::e($k) ?>"><?= Html::e($meta['label']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <button class="btn" type="submit" name="action" value="add_section" style="margin-top:8px">Add</button>
    </aside>

    <div>
      <div class="card" style="margin-bottom:12px">
        <label class="lab">Template name <input name="name" required value="<?= Html::e($row['name']) ?>"></label>
        <label class="lab">Description <input name="description" value="<?= Html::e($row['description'] ?? '') ?>"></label>
        <label class="lab">Use for
          <select name="page_type">
            <?php foreach (['standard' => 'Standard page', 'landing' => 'Landing / CPT entry', 'blog' => 'Blog / notice'] as $k => $l): ?>
              <option value="<?= $k ?>"<?= Html::selected($row['page_type'], $k) ?>><?= $l ?></option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>

      <?php foreach ($sections as $i => $sec):
        $type = $sec['type'] ?? '';
        $content = $sec['content'] ?? [];
      ?>
        <article class="sec" data-idx="<?= $i ?>">
          <h3>
            <span><?= Html::e($registry[$type]['label'] ?? $type) ?></span>
            <span class="toolbar">
              <button class="btn-ghost" type="submit" name="action" value="duplicate_section" data-idx="<?= $i ?>">Duplicate</button>
              <button class="btn-danger" type="submit" name="action" value="delete_section" data-idx="<?= $i ?>">Delete</button>
            </span>
          </h3>
          <input type="hidden" name="section_idx[]" value="<?= $i ?>">
          <input type="hidden" name="section_type[]" value="<?= Html::e($type) ?>">
          <?php
          $fields = $registry[$type]['fields'] ?? [];
          $prefix = 't' . $i . '_';
          require ROOT . '/views/admin/partials/block-fields.php';
          ?>
        </article>
      <?php endforeach; ?>
      <?php if (!$sections): ?><div class="empty">No sections yet. Add one from the left — Hero, FAQ, Form Block, Custom HTML, and the rest of the page-builder blocks.</div><?php endif; ?>

      <input type="hidden" name="action_idx" id="action-idx" value="0">
      <div class="toolbar" style="margin-top:12px">
        <button class="btn" type="submit" name="action" value="save">Save template</button>
        <button class="btn-ghost" type="submit" name="action" value="save_close">Save and close</button>
      </div>
    </div>
  </div>
</form>
<script>
(function () {
  document.querySelectorAll('#tpl-builder [data-idx]').forEach(function (el) {
    el.addEventListener('click', function () {
      var idx = el.getAttribute('data-idx');
      if (idx !== null) document.getElementById('action-idx').value = idx;
    });
  });
})();
</script>
