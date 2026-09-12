<?php
$type = $type ?? [];
$entries = $entries ?? [];
$refs = $refs ?? ['header_blocking' => false, 'locations' => []];
$pages = $pages ?? [];
$isSystem = !empty($isSystem);
$count = count($entries);
$name = (string) ($type['name'] ?? '');
$slug = (string) ($type['slug'] ?? '');
$blocked = !empty($refs['header_blocking']);
$headerLocs = array_values(array_filter($refs['locations'] ?? [], static fn ($l) => !empty($l['header'])));
$otherLocs = array_values(array_filter($refs['locations'] ?? [], static fn ($l) => empty($l['header'])));
?>
<div class="page-head">
  <div>
    <p class="crumbs"><a href="/admin/post-types/">Post types</a></p>
    <h1>Delete “<?= Html::e($name) ?>”</h1>
  </div>
  <a class="btn-ghost" href="/admin/post-types/">Cancel</a>
</div>

<?php if ($isSystem): ?>
  <div class="flash flash-error">This is a system post type (Courses, Campuses, and Faculty cannot be deleted). Core templates depend on it.</div>
<?php else: ?>

  <?php if ($count === 0): ?>
    <p>This post type has no entries. Deleting it cannot be undone.</p>
  <?php else: ?>
    <div class="snip-warn" role="alert">
      <strong>This post type has <?= (int) $count ?> <?= $count === 1 ? 'entry' : 'entries' ?>.</strong>
      Deleting it will also delete
      <?php if ($count === 1): ?>
        that entry and its live page at <code>/<?= Html::e($slug) ?>/<?= Html::e($entries[0]['slug']) ?>/</code>.
      <?php else: ?>
        those entries and their live pages at <code>/<?= Html::e($slug) ?>/{slug}/</code>.
      <?php endif; ?>
      This cannot be undone.
    </div>
    <ul class="pt-entry-list">
      <?php foreach ($entries as $e): ?>
        <li><?= Html::e($e['title']) ?> · <code>/<?= Html::e($slug) ?>/<?= Html::e($e['slug']) ?>/</code></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <?php if ($headerLocs): ?>
    <div class="flash flash-error">
      <strong>Linked from the header menu.</strong>
      Remove <?= $count === 1 ? 'it' : 'these items' ?> from the primary navigation before a permanent delete, or archive instead.
      <ul>
        <?php foreach ($headerLocs as $l): ?>
          <li><?= Html::e($l['detail']) ?></li>
        <?php endforeach; ?>
      </ul>
      <p style="margin:8px 0 0"><a href="/admin/menus/" class="btn-ghost">Open menus</a></p>
    </div>
  <?php endif; ?>

  <?php if ($otherLocs): ?>
    <div class="snip-warn">
      <strong>Also linked from:</strong>
      <ul>
        <?php foreach ($otherLocs as $l): ?>
          <li><?= Html::e($l['detail']) ?></li>
        <?php endforeach; ?>
      </ul>
      Permanent delete will remove matching menu items and rewrite remaining URLs to the redirect target below.
    </div>
  <?php endif; ?>

  <div class="pt-actions">
    <?php if (Auth::can('post_types.edit')): ?>
      <form class="card pt-card" method="post" action="/admin/post-types/archive/">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int) $type['id'] ?>">
        <p class="k">Recommended</p>
        <h2>Archive instead</h2>
        <p>Hides this post type and its entries from the live site without deleting data. You can restore it later from the post types list.</p>
        <button class="btn" type="submit">Archive “<?= Html::e($name) ?>”</button>
      </form>
    <?php endif; ?>

    <?php if (!$blocked): ?>
      <form class="card pt-card pt-card--danger" method="post" action="/admin/post-types/delete/" autocomplete="off">
        <?= Csrf::field() ?>
        <input type="hidden" name="id" value="<?= (int) $type['id'] ?>">
        <p class="k">Destructive</p>
        <h2>Permanently delete</h2>
        <?php if ($count === 0): ?>
          <p>Delete post type “<?= Html::e($name) ?>”? This cannot be undone.</p>
        <?php else: ?>
          <p>Type <strong><?= Html::e($name) ?></strong> to confirm. Old URLs will 301 to the target you pick.</p>
          <label class="lab">Type the post type name
            <input name="confirm_name" required autocomplete="off" placeholder="<?= Html::e($name) ?>">
          </label>
        <?php endif; ?>
        <label class="lab">Redirect deleted URLs to
          <select name="redirect_to">
            <option value="/">Homepage (/)</option>
            <?php foreach ($pages as $p):
              if (($p['slug'] ?? '') === '/') {
                  continue;
              }
              $path = '/' . trim((string) $p['slug'], '/') . '/';
            ?>
              <option value="<?= Html::e($path) ?>"><?= Html::e($p['title']) ?> (<?= Html::e($path) ?>)</option>
            <?php endforeach; ?>
          </select>
        </label>
        <button class="btn-danger" type="submit">Permanently delete</button>
      </form>
    <?php endif; ?>
  </div>
<?php endif; ?>
