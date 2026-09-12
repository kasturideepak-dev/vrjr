<div class="page-head"><div><p class="crumbs">Engage</p><h1>Menus</h1><p>Link to pages and entries by ID so URLs stay correct after slug changes.</p></div></div>
<div class="toolbar" style="margin-bottom:14px">
  <?php foreach ($menus as $m): ?>
    <a class="btn-ghost" href="/admin/menus/?id=<?= (int) $m['id'] ?>"><?= Html::e($m['name']) ?></a>
  <?php endforeach; ?>
</div>
<form class="form wide" method="post">
  <?= Csrf::field() ?>
  <input type="hidden" name="menu_id" value="<?= (int) $menuId ?>">
  <?php foreach ($items as $it): ?>
    <div class="row2" style="margin-bottom:10px">
      <input type="hidden" name="item_id[]" value="<?= (int) $it['id'] ?>">
      <label class="lab">Label <input name="label[]" value="<?= Html::e($it['label']) ?>"></label>
      <label class="lab">Type
        <select name="link_type[]">
          <?php foreach (['custom'=>'Custom URL','page'=>'Page','cpt_archive'=>'Post type archive','cpt_entry'=>'Post type entry','blog_index'=>'Blog index'] as $k=>$l): ?>
            <option value="<?= $k ?>"<?= Html::selected($it['link_type'], $k) ?>><?= $l ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="lab">Object ID <input name="object_id[]" value="<?= Html::e((string) ($it['object_id'] ?? '')) ?>"></label>
      <label class="lab">Object type <input name="object_type[]" value="<?= Html::e($it['object_type'] ?? '') ?>"></label>
      <label class="lab">Fallback URL <input name="url[]" value="<?= Html::e($it['url']) ?>"></label>
      <label class="lab">Parent ID <input name="parent_id[]" value="<?= Html::e((string) ($it['parent_id'] ?? '')) ?>"></label>
      <label class="lab">Order <input type="number" name="sort_order[]" value="<?= (int) $it['sort_order'] ?>"></label>
      <label><input type="checkbox" name="is_active[<?= (int) $it['id'] ?>]" <?= Html::checked((int) $it['is_active'] === 1) ?>> Active</label>
    </div>
  <?php endforeach; ?>
  <h3>Add item</h3>
  <div class="row2">
    <label class="lab">Label <input name="new_label"></label>
    <label class="lab">Type
      <select name="new_link_type">
        <option value="custom">Custom URL</option>
        <option value="page">Page</option>
        <option value="cpt_entry">Entry</option>
        <option value="cpt_archive">Archive</option>
        <option value="blog_index">Blog</option>
      </select>
    </label>
    <label class="lab">URL <input name="new_url" placeholder="/about/"></label>
    <label class="lab">Object ID <input name="new_object_id"></label>
  </div>
  <button class="btn" type="submit">Save menu</button>
</form>
<div class="card" style="margin-top:16px">
  <h3>Page IDs</h3>
  <p><?php foreach ($pages as $p): ?><?= (int) $p['id'] ?> = <?= Html::e($p['title']) ?> · <?php endforeach; ?></p>
  <h3>Entries</h3>
  <p><?php foreach ($entries as $e): ?><?= (int) $e['id'] ?> = <?= Html::e($e['type_name']) ?> / <?= Html::e($e['title']) ?> · <?php endforeach; ?></p>
</div>
