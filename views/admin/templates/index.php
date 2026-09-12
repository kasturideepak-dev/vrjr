<div class="page-head">
  <div>
    <p class="crumbs">System</p>
    <h1>Templates</h1>
    <p>Reusable page layouts and single blocks. Selecting a page template when you create a page or course clones these sections into the new item.</p>
  </div>
</div>

<div class="page-head" style="margin-top:8px">
  <h2>Page templates</h2>
  <a class="btn" href="/admin/templates/pages/new/">Add new template</a>
</div>
<div class="tpl-grid">
  <?php if (!$pages): ?><div class="empty">No page templates yet.</div><?php endif; ?>
  <?php foreach ($pages as $p): ?>
    <article class="card tpl-card-admin">
      <span class="k"><?= Html::e($p['page_type']) ?> · <?= (int) $p['section_count'] ?> sections</span>
      <strong><?= Html::e($p['name']) ?></strong>
      <p class="hint"><?= Html::e($p['description'] ?: 'No description') ?></p>
      <div class="toolbar" style="margin-top:12px">
        <a class="btn-ghost" href="/admin/templates/pages/<?= (int) $p['id'] ?>/">Edit</a>
        <form method="post" action="/admin/templates/pages/duplicate/" style="display:inline"><?= Csrf::field() ?>
          <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
          <button class="btn-ghost" type="submit">Duplicate</button>
        </form>
        <form method="post" action="/admin/templates/pages/delete/" data-confirm="Delete this page template?" style="display:inline"><?= Csrf::field() ?>
          <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
          <button class="btn-danger" type="submit">Delete</button>
        </form>
      </div>
    </article>
  <?php endforeach; ?>
</div>

<div class="page-head" style="margin-top:28px">
  <h2>Section templates</h2>
  <a class="btn" href="/admin/templates/sections/new/">Add new template</a>
</div>
<div class="tpl-grid">
  <?php if (!$sections): ?><div class="empty">No section templates yet. Save any block from a page builder, or add one here.</div><?php endif; ?>
  <?php foreach ($sections as $s): ?>
    <article class="card tpl-card-admin">
      <span class="k"><?= Html::e(SectionRegistry::label($s['type'])) ?></span>
      <strong><?= Html::e($s['name']) ?></strong>
      <p class="hint">Reusable <?= Html::e($s['type']) ?> block — insert from the page builder sidebar.</p>
      <div class="toolbar" style="margin-top:12px">
        <a class="btn-ghost" href="/admin/templates/sections/<?= (int) $s['id'] ?>/">Edit</a>
        <form method="post" action="/admin/templates/sections/duplicate/" style="display:inline"><?= Csrf::field() ?>
          <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
          <button class="btn-ghost" type="submit">Duplicate</button>
        </form>
        <form method="post" action="/admin/templates/sections/delete/" data-confirm="Delete this section template?" style="display:inline"><?= Csrf::field() ?>
          <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
          <button class="btn-danger" type="submit">Delete</button>
        </form>
      </div>
    </article>
  <?php endforeach; ?>
</div>
