<div class="page-head"><div><h1>Courses</h1></div><a class="btn" href="/admin/courses/new/">Add course</a></div>
<?php if (!$rows): ?><div class="empty">No courses yet. <a href="/admin/courses/new/">Add course</a></div><?php endif; ?>
<div class="table-wrap"><table><thead><tr><th>Title</th><th>Slug</th><th>Status</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><a href="/admin/courses/<?= (int) $r['id'] ?>/"><?= Html::e($r['title']) ?></a></td><td><?= Html::e($r['slug']) ?></td><td><?= Html::e($r['status']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
