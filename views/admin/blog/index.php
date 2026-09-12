<div class="page-head">
  <div><p class="crumbs">Content</p><h1><?= !empty($trash) ? 'Blog — Trash' : 'Blog' ?></h1></div>
  <div class="toolbar">
    <a class="btn-ghost" href="/admin/blog/<?= !empty($trash) ? '' : '?trash=1' ?>"><?= !empty($trash) ? 'Back' : 'Trash' ?></a>
    <a class="btn" href="/admin/blog/new/">New post</a>
  </div>
</div>
<form class="form" method="post" action="/admin/blog/category/" style="max-width:520px;margin-bottom:16px">
  <?= Csrf::field() ?>
  <div class="row2">
    <label class="lab">New category <input name="name" required></label>
    <label class="lab">Slug <input name="slug"></label>
  </div>
  <button class="btn-ghost" type="submit">Add category</button>
</form>
<div class="table-wrap">
  <table>
    <thead><tr><th>Title</th><th>URL</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $p):
      $live = '/blog/' . $p['slug'] . '/';
    ?>
      <tr>
        <td><a href="/admin/blog/<?= (int) $p['id'] ?>/"><?= Html::e($p['title']) ?></a></td>
        <td><a href="<?= Html::e($live) ?>" target="_blank" rel="noopener"><code><?= Html::e($live) ?></code></a></td>
        <td><span class="badge <?= $p['status'] === 'published' ? 'badge-ok' : 'badge-warn' ?>"><?= Html::e($p['status']) ?></span></td>
        <td>
          <?php if (!empty($trash)): ?>
            <form method="post" action="/admin/blog/restore/" style="display:inline"><?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><button class="btn-ghost">Restore</button></form>
          <?php else: ?>
            <div class="toolbar">
              <?php if ($p['status'] === 'published'): ?>
                <a class="btn-ghost" href="<?= Html::e($live) ?>" target="_blank" rel="noopener">View</a>
              <?php endif; ?>
              <a class="btn-ghost" href="/admin/blog/<?= (int) $p['id'] ?>/">Edit</a>
              <form method="post" action="/admin/blog/trash/" style="display:inline"><?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int) $p['id'] ?>"><button class="btn-ghost">Trash</button></form>
            </div>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
