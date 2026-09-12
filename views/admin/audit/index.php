<div class="page-head"><div><p class="crumbs">System</p><h1>Activity log</h1></div></div>
<div class="table-wrap">
  <table>
    <thead><tr><th>When</th><th>Who</th><th>Action</th><th>Entity</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= Html::e($r['created_at']) ?></td>
        <td><?= Html::e($r['user_name'] ?: 'System') ?></td>
        <td><?= Html::e($r['action']) ?></td>
        <td><?= Html::e(($r['entity_type'] ?? '') . ' #' . ($r['entity_id'] ?? '')) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
