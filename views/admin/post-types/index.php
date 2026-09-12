<div class="page-head">
  <div>
    <p class="crumbs">Settings</p>
    <h1>Post types</h1>
    <p>Create a type once. Staff add entries forever — no developer needed.</p>
  </div>
  <a class="btn" href="/admin/post-types/new/">Add new post type</a>
</div>
<div class="table-wrap">
  <table>
    <thead><tr><th>Name</th><th>Archive URL</th><th>Entries</th><th>Mode</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php if (!$rows): ?><tr><td colspan="6" class="empty">None yet. Start with Courses or AI Program.</td></tr><?php endif; ?>
    <?php foreach ($rows as $r):
      $sys = Cpt::isSystem($r);
      $archived = ($r['status'] ?? '') === 'archived';
    ?>
      <tr>
        <td>
          <a href="/admin/post-types/<?= (int) $r['id'] ?>/"><?= Html::e($r['name']) ?></a>
          <?php if ($sys): ?><span class="badge badge-off" title="Ships with the site and cannot be deleted">System</span><?php endif; ?>
        </td>
        <td><code>/<?= Html::e($r['slug']) ?>/</code></td>
        <td><?= (int) $r['entry_count'] ?></td>
        <td><?= Html::e($r['template_mode']) ?></td>
        <td>
          <?php if ($archived): ?>
            <span class="badge badge-warn">Archived</span>
          <?php elseif (($r['status'] ?? '') === 'inactive'): ?>
            <span class="badge badge-off">Inactive</span>
          <?php else: ?>
            <span class="badge badge-ok">Active</span>
          <?php endif; ?>
        </td>
        <td>
          <div class="toolbar">
            <?php if (!$archived): ?>
              <a class="btn-ghost" href="/admin/content/<?= Html::e($r['slug']) ?>/">Entries</a>
              <a class="btn-ghost" href="/admin/content/<?= Html::e($r['slug']) ?>/new/">Add</a>
            <?php elseif (Auth::can('post_types.edit')): ?>
              <form method="post" action="/admin/post-types/restore/" style="display:inline"><?= Csrf::field() ?>
                <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                <button class="btn-ghost" type="submit">Restore</button>
              </form>
            <?php endif; ?>
            <?php if (!$sys && Auth::can('post_types.delete')): ?>
              <a class="btn-danger" href="/admin/post-types/<?= (int) $r['id'] ?>/confirm-delete/">Delete</a>
            <?php endif; ?>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
