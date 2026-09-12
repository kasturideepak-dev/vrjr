<div class="page-head"><div><p class="crumbs">System</p><h1>Redirects</h1><p>301s for indexed WordPress URLs and slug changes.</p></div></div>
<form method="get" class="toolbar" style="margin-bottom:14px">
  <input type="search" name="q" value="<?= Html::e($q ?? '') ?>" placeholder="Search from / to">
  <button class="btn-ghost">Search</button>
</form>
<form class="form" method="post">
  <?= Csrf::field() ?>
  <div class="row2">
    <label class="lab">From (no domain) <input name="from_path" placeholder="junior-college-for-mpc" required></label>
    <label class="lab">To <input name="to_path" placeholder="/courses/mpc-with-iit-jee/" required></label>
  </div>
  <div class="row2">
    <label class="lab">Status <select name="status_code"><option value="301">301</option><option value="302">302</option></select></label>
    <label class="lab">Note <input name="note"></label>
  </div>
  <button class="btn" type="submit">Save redirect</button>
</form>
<div class="table-wrap" style="margin-top:16px">
  <table>
    <thead><tr><th>From</th><th>To</th><th>Hits</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><code>/<?= Html::e($r['from_path']) ?>/</code></td>
        <td><?= Html::e($r['to_path']) ?></td>
        <td><?= (int) $r['hits'] ?></td>
        <td>
          <form method="post" action="/admin/redirects/delete/"><?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <button class="btn-danger" type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
