<?php
$typeLabels = ['custom'=>'Custom link','page'=>'Page','cpt_archive'=>'Archive','cpt_entry'=>'Entry','blog_index'=>'Blog','blog_post'=>'Blog post'];
// group entries by type for the picker
$entriesByType = [];
foreach ($entries as $e) { $entriesByType[$e['type_name']][] = $e; }
?>
<div class="page-head">
  <div><p class="crumbs">Engage</p><h1>Menus</h1><p>Build your navigation visually — add pages and posts, drag to reorder, and nest items. Links follow slug changes automatically.</p></div>
</div>

<div class="menu-tabs">
  <?php foreach ($menus as $m): ?>
    <a class="menu-tab<?= (int)$m['id'] === (int)$menuId ? ' is-active' : '' ?>" href="/admin/menus/?id=<?= (int) $m['id'] ?>"><?= Html::e($m['name']) ?></a>
  <?php endforeach; ?>
</div>

<form class="menu-builder" method="post" data-menu-builder>
  <?= Csrf::field() ?>
  <input type="hidden" name="menu_id" value="<?= (int) $menuId ?>">
  <div class="menu-builder__grid">

    <!-- ADD PANEL -->
    <aside class="menu-add">
      <h3>Add to menu</h3>

      <details class="menu-add__grp" open>
        <summary>Pages</summary>
        <div class="menu-add__body">
          <select data-add-select="page">
            <option value="">Choose a page…</option>
            <?php foreach ($pages as $p): ?>
              <option value="<?= (int)$p['id'] ?>" data-label="<?= Html::e($p['title']) ?>" data-url="<?= Html::e($p['slug']==='/'?'/':path_url($p['slug'])) ?>"><?= Html::e($p['title']) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="button" class="btn-ghost" data-add-btn="page">Add</button>
        </div>
      </details>

      <?php if (!empty($entriesByType)): ?>
      <details class="menu-add__grp">
        <summary>Courses &amp; entries</summary>
        <div class="menu-add__body">
          <select data-add-select="cpt_entry">
            <option value="">Choose an entry…</option>
            <?php foreach ($entriesByType as $tname=>$rows): ?>
              <optgroup label="<?= Html::e($tname) ?>">
                <?php foreach ($rows as $e): ?>
                  <option value="<?= (int)$e['id'] ?>" data-label="<?= Html::e($e['title']) ?>" data-url="<?= Html::e(path_url($e['type_slug'].'/'.($e['slug'] ?? ''))) ?>"><?= Html::e($e['title']) ?></option>
                <?php endforeach; ?>
              </optgroup>
            <?php endforeach; ?>
          </select>
          <button type="button" class="btn-ghost" data-add-btn="cpt_entry">Add</button>
        </div>
      </details>
      <?php endif; ?>

      <?php if (!empty($types)): ?>
      <details class="menu-add__grp">
        <summary>Archives</summary>
        <div class="menu-add__body">
          <select data-add-select="cpt_archive">
            <option value="">Choose an archive…</option>
            <?php foreach ($types as $t): if (empty($t['has_archive'])) continue; ?>
              <option value="<?= (int)$t['id'] ?>" data-label="<?= Html::e($t['name']) ?>" data-url="<?= Html::e(path_url($t['slug'])) ?>"><?= Html::e($t['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="button" class="btn-ghost" data-add-btn="cpt_archive">Add</button>
        </div>
      </details>
      <?php endif; ?>

      <details class="menu-add__grp">
        <summary>Blog</summary>
        <div class="menu-add__body">
          <button type="button" class="btn-ghost" data-add-blog data-label="Blog" data-url="<?= Html::e(path_url('blog')) ?>">Add blog index</button>
        </div>
      </details>

      <details class="menu-add__grp">
        <summary>Custom link</summary>
        <div class="menu-add__body menu-add__custom">
          <input type="text" data-custom-label placeholder="Label">
          <input type="text" data-custom-url placeholder="https://…  or  /path/">
          <button type="button" class="btn-ghost" data-add-custom>Add link</button>
        </div>
      </details>
    </aside>

    <!-- STRUCTURE -->
    <section class="menu-structure">
      <div class="menu-structure__head">
        <h3>Menu structure</h3>
        <span class="menu-structure__hint">Drag <span aria-hidden="true">⠿</span> to reorder. Use “Nested under” to make a dropdown.</span>
      </div>
      <div class="menu-items" data-menu-list>
        <?php foreach ($items as $it): ?>
          <?php
            $lt = $it['link_type'] ?: 'custom';
            $badge = $typeLabels[$lt] ?? $lt;
          ?>
          <div class="menu-item" draggable="true" data-item data-id="<?= (int)$it['id'] ?>">
            <span class="menu-item__grip" aria-hidden="true">⠿</span>
            <input type="hidden" name="item_id[]" value="<?= (int)$it['id'] ?>">
            <input type="hidden" name="link_type[]" value="<?= Html::e($lt) ?>">
            <input type="hidden" name="object_type[]" value="<?= Html::e($it['object_type'] ?? '') ?>">
            <input type="hidden" name="object_id[]" value="<?= Html::e((string)($it['object_id'] ?? '')) ?>">
            <input type="hidden" name="url[]" value="<?= Html::e($it['url']) ?>">
            <input type="hidden" name="is_active[]" value="<?= (int)$it['is_active'] === 1 ? '1' : '0' ?>" data-active-input>
            <div class="menu-item__main">
              <input class="menu-item__label" name="label[]" value="<?= Html::e($it['label']) ?>" placeholder="Menu label">
              <span class="menu-item__badge"><?= Html::e($badge) ?></span>
            </div>
            <label class="menu-item__parent">Nested under
              <select name="parent_id[]" data-parent-select data-current="<?= Html::e((string)($it['parent_id'] ?? '')) ?>"></select>
            </label>
            <label class="menu-item__active"><input type="checkbox" data-active-toggle <?= (int)$it['is_active'] === 1 ? 'checked' : '' ?>> Active</label>
            <button type="button" class="menu-item__remove" data-remove title="Remove">✕</button>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="menu-empty" data-menu-empty<?= $items ? ' hidden' : '' ?>>No items yet. Add pages or posts from the left.</p>
      <div class="menu-builder__actions">
        <button class="btn" type="submit">Save menu</button>
        <a class="btn-ghost" href="/admin/menus/?id=<?= (int)$menuId ?>">Cancel</a>
      </div>
    </section>
  </div>

  <div data-deleted-bin hidden></div>

  <!-- template for a new item row -->
  <template data-item-template>
    <div class="menu-item" draggable="true" data-item data-id="0">
      <span class="menu-item__grip" aria-hidden="true">⠿</span>
      <input type="hidden" name="item_id[]" value="0">
      <input type="hidden" name="link_type[]" value="">
      <input type="hidden" name="object_type[]" value="">
      <input type="hidden" name="object_id[]" value="">
      <input type="hidden" name="url[]" value="">
      <input type="hidden" name="is_active[]" value="1" data-active-input>
      <div class="menu-item__main">
        <input class="menu-item__label" name="label[]" value="" placeholder="Menu label">
        <span class="menu-item__badge"></span>
      </div>
      <label class="menu-item__parent">Nested under
        <select name="parent_id[]" data-parent-select data-current=""></select>
      </label>
      <label class="menu-item__active"><input type="checkbox" data-active-toggle checked> Active</label>
      <button type="button" class="menu-item__remove" data-remove title="Remove">✕</button>
    </div>
  </template>
</form>
