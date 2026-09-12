<div class="page-head">
  <div>
    <p class="crumbs">Content / <?= Html::e($type['name']) ?></p>
    <h1><?= Html::e($type['name']) ?></h1>
    <p><?php if ((int) $type['public']): ?>Archive: <a href="<?= Html::e(Cpt::archiveUrl($type)) ?>" target="_blank"><?= Html::e(Cpt::archiveUrl($type)) ?></a><?php else: ?>Internal data (no public archive)<?php endif; ?></p>
  </div>
  <div class="toolbar">
    <a class="btn-ghost" href="/admin/post-types/<?= (int) $type['id'] ?>/">Edit type</a>
    <a class="btn-ghost" href="/admin/content/<?= Html::e($type['slug']) ?>/<?= !empty($trash) ? '' : '?trash=1' ?>"><?= !empty($trash) ? 'Back' : 'Trash' ?></a>
    <a class="btn" href="/admin/content/<?= Html::e($type['slug']) ?>/new/">Add <?= Html::e($type['singular_name']) ?></a>
  </div>
</div>
<div class="table-wrap">
  <table>
    <thead><tr><th>Title</th><th>URL</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="4" class="empty">No entries yet.</td></tr><?php endif; ?>
    <?php foreach ($rows as $r):
      $live = (int) $type['public'] ? Cpt::permalink($r, $type) : '';
    ?>
      <tr>
        <td><a href="/admin/content/<?= Html::e($type['slug']) ?>/<?= (int) $r['id'] ?>/"><?= Html::e($r['title']) ?></a></td>
        <td><?php if ($live): ?><a href="<?= Html::e($live) ?>" target="_blank" rel="noopener"><code><?= Html::e($live) ?></code></a><?php else: ?><code>—</code><?php endif; ?></td>
        <td><span class="badge <?= $r['status'] === 'published' ? 'badge-ok' : 'badge-warn' ?>"><?= Html::e($r['status']) ?></span></td>
        <td>
          <?php if (!empty($trash)): ?>
            <form method="post" action="/admin/content/<?= Html::e($type['slug']) ?>/restore/" style="display:inline"><?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn-ghost" type="submit">Restore</button></form>
          <?php else: ?>
            <div class="toolbar">
              <?php if ($live && $r['status'] === 'published'): ?>
                <a class="btn-ghost" href="<?= Html::e($live) ?>" target="_blank" rel="noopener">View</a>
              <?php endif; ?>
              <a class="btn-ghost" href="/admin/content/<?= Html::e($type['slug']) ?>/<?= (int) $r['id'] ?>/">Edit</a>
            </div>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
