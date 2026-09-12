<div class="page-head"><div><p class="crumbs">Search</p><h1>Results for “<?= Html::e($q) ?>”</h1></div></div>
<?php foreach (['pages' => 'Pages', 'entries' => 'Entries', 'posts' => 'Blog', 'media' => 'Media'] as $key => $label): ?>
  <h2 style="margin:18px 0 8px"><?= Html::e($label) ?></h2>
  <div class="table-wrap">
    <table>
      <tbody>
      <?php if (empty($$key)): ?><tr><td class="empty">None</td></tr><?php endif; ?>
      <?php foreach ($$key as $row): ?>
        <tr>
          <td>
            <?php if ($key === 'pages'): ?><a href="/admin/pages/<?= (int) $row['id'] ?>/"><?= Html::e($row['title']) ?></a>
            <?php elseif ($key === 'entries'): ?><a href="/admin/content/<?= Html::e($row['type_slug']) ?>/<?= (int) $row['id'] ?>/"><?= Html::e($row['title']) ?></a>
            <?php elseif ($key === 'posts'): ?><a href="/admin/blog/<?= (int) $row['id'] ?>/"><?= Html::e($row['title']) ?></a>
            <?php else: ?><?= Html::e($row['original_name']) ?><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endforeach; ?>
