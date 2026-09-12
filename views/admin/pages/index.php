<div class="page-head">
  <div>
    <p class="crumbs">Content</p>
    <h1><?= !empty($trash) ? 'Pages — Trash' : 'Pages' ?></h1>
  </div>
  <div class="toolbar">
    <a class="btn-ghost" href="/admin/pages/<?= !empty($trash) ? '' : '?trash=1' ?>"><?= !empty($trash) ? 'Back to pages' : 'Trash' ?></a>
    <a class="btn" href="/admin/pages/new/">New page</a>
  </div>
</div>
<form class="toolbar" method="get" style="margin-bottom:14px">
  <input type="search" name="q" value="<?= Html::e($q ?? '') ?>" placeholder="Filter title or slug">
  <select name="status">
    <option value="">All statuses</option>
    <?php foreach (['draft','published','scheduled','unpublished'] as $st): ?>
      <option value="<?= $st ?>"<?= Html::selected($status ?? '', $st) ?>><?= $st ?></option>
    <?php endforeach; ?>
  </select>
  <button class="btn-ghost" type="submit">Filter</button>
</form>
<div class="table-wrap">
  <table>
    <thead><tr><th>Title</th><th>URL</th><th>Status</th><th>Updated</th><th></th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="5" class="empty">No pages.</td></tr><?php endif; ?>
    <?php foreach ($rows as $p):
      $live = $p['slug'] === '/' ? '/' : '/' . trim($p['slug'], '/') . '/';
    ?>
      <tr>
        <td><a href="/admin/pages/<?= (int) $p['id'] ?>/"><?= Html::e($p['title']) ?></a></td>
        <td><a href="<?= Html::e($live) ?>" target="_blank" rel="noopener"><code><?= Html::e($live) ?></code></a></td>
        <td><span class="badge <?= $p['status'] === 'published' ? 'badge-ok' : 'badge-warn' ?>"><?= Html::e($p['status']) ?></span></td>
        <td><?= Html::e($p['updated_at']) ?></td>
        <td>
          <?php if (!empty($trash)): ?>
            <form method="post" action="/admin/pages/restore/" style="display:inline"><?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><button class="btn-ghost" type="submit">Restore</button></form>
            <form method="post" action="/admin/pages/delete/" data-confirm="Permanently delete this page?" style="display:inline"><?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><button class="btn-danger" type="submit">Delete</button></form>
          <?php else: ?>
            <div class="toolbar">
              <?php if ($p['status'] === 'published'): ?>
                <a class="btn-ghost" href="<?= Html::e($live) ?>" target="_blank" rel="noopener">View</a>
              <?php endif; ?>
              <a class="btn-ghost" href="/admin/pages/<?= (int) $p['id'] ?>/">Edit</a>
            </div>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
