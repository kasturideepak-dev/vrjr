<div class="page-head"><div><h1>Faculty</h1></div><a class="btn" href="/admin/faculty/new/">Add faculty</a></div>
<div class="table-wrap"><table><thead><tr><th>Name</th><th>Designation</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><a href="/admin/faculty/<?= (int) $r['id'] ?>/"><?= Html::e($r['name']) ?></a></td><td><?= Html::e($r['designation']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
