<div class="page-head">
  <div><p class="crumbs">System</p><h1>Backups</h1>
    <p>Last cron: <?= Html::e($lastCron['finished_at'] ?? 'never') ?> <?= Html::e($lastCron['message'] ?? '') ?></p>
  </div>
</div>
<form method="post" class="form" style="max-width:480px">
  <?= Csrf::field() ?>
  <label class="lab">Notes <input name="notes" placeholder="Before launch"></label>
  <button class="btn" type="submit">Create backup now</button>
</form>
<div class="table-wrap" style="margin-top:16px">
  <table>
    <thead><tr><th>File</th><th>Kind</th><th>Size</th><th>When</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= Html::e($r['filename']) ?></td>
        <td><?= Html::e($r['kind']) ?></td>
        <td><?= number_format(((int) $r['size_bytes']) / 1048576, 2) ?> MB</td>
        <td><?= Html::e($r['created_at']) ?></td>
        <td>
          <a class="btn-ghost" href="/admin/backup/download/?id=<?= (int) $r['id'] ?>">Download</a>
          <form method="post" action="/admin/backup/restore/" data-confirm="Restore this backup? A safety copy is made first." style="display:inline">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <input name="confirm" placeholder="Type RESTORE" required>
            <button class="btn-danger" type="submit">Restore</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
