<div class="page-head">
  <div><p class="crumbs">Engage</p><h1>Forms</h1></div>
  <a class="btn" href="/admin/forms/new/">New form</a>
</div>
<div class="table-wrap">
  <table>
    <thead><tr><th>Name</th><th>Slug</th><th>Fields</th><th>Leads</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><a href="/admin/forms/<?= (int) $r['id'] ?>/"><?= Html::e($r['name']) ?></a></td>
        <td><code><?= Html::e($r['slug']) ?></code></td>
        <td><?= (int) $r['field_count'] ?></td>
        <td><?= (int) $r['lead_count'] ?></td>
        <td><span class="badge <?= $r['is_active'] ? 'badge-ok' : 'badge-off' ?>"><?= $r['is_active'] ? 'active' : 'off' ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
