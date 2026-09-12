<div class="page-head"><div><h1>Campuses</h1></div><a class="btn" href="/admin/campuses/new/">Add campus</a></div>
<div class="table-wrap"><table><thead><tr><th>Name</th><th>Address</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr><td><a href="/admin/campuses/<?= (int) $r['id'] ?>/"><?= Html::e($r['name']) ?></a></td><td><?= Html::e($r['address']) ?></td></tr><?php endforeach; ?>
</tbody></table></div>
