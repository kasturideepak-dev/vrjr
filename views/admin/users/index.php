<div class="page-head">
  <div><p class="crumbs">System</p><h1>Users</h1></div>
  <a class="btn" href="/admin/users/new/">New user</a>
</div>
<div class="table-wrap">
  <table>
    <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= Html::e($r['name']) ?></td>
        <td><?= Html::e($r['email']) ?></td>
        <td><?= Html::e($r['role_name']) ?></td>
        <td><span class="badge <?= $r['status'] === 'active' ? 'badge-ok' : 'badge-off' ?>"><?= Html::e($r['status']) ?></span></td>
        <td><a href="/admin/users/<?= (int) $r['id'] ?>/">Edit</a></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
