<div class="page-head">
  <div>
    <p class="crumbs">Overview</p>
    <h1>Dashboard</h1>
    <p>Production control room for vrjuniorcollege.com</p>
  </div>
</div>
<div class="quick">
  <a href="/admin/post-types/new/"><strong>Add new post type</strong><span>Launch AI Program, Events, Notices…</span></a>
  <?php if (!empty($coursesType)): ?>
    <a href="/admin/content/courses/new/"><strong>Add new course</strong><span>Goes live at /courses/{slug}/</span></a>
  <?php endif; ?>
  <a href="/admin/leads/"><strong>View leads</strong><span><?= (int) $stats['leads_new'] ?> new enquiries</span></a>
  <a href="<?= !empty($homeId) ? '/admin/pages/' . (int) $homeId . '/' : '/admin/pages/' ?>"><strong>Edit homepage</strong><span>Approved Phase 1 layout</span></a>
</div>
<div class="cards">
  <div class="card"><span class="k">Pages</span><strong><?= (int) $stats['pages'] ?></strong><div class="hint"><?= (int) $stats['published'] ?> published</div></div>
  <div class="card"><span class="k">Post types</span><strong><?= (int) $stats['types'] ?></strong><div class="hint"><?= (int) $stats['entries'] ?> entries</div></div>
  <div class="card"><span class="k">Leads this month</span><strong><?= (int) $stats['leads_month'] ?></strong><div class="hint"><?= (int) $stats['leads_new'] ?> still new</div></div>
  <div class="card"><span class="k">Blog posts</span><strong><?= (int) $stats['posts'] ?></strong><div class="hint"><?= (int) $stats['media'] ?> media files</div></div>
</div>
<div class="row2">
  <div class="table-wrap">
    <table>
      <thead><tr><th>Recent activity</th><th>When</th></tr></thead>
      <tbody>
        <?php foreach ($activity as $a): ?>
          <tr>
            <td><?= Html::e($a['user_name'] ?: 'System') ?> · <?= Html::e($a['action']) ?></td>
            <td><?= Html::e($a['created_at']) ?></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$activity): ?><tr><td colspan="2" class="empty">No activity yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <div>
    <div class="card" style="margin-bottom:14px">
      <span class="k">Last backup</span>
      <?php if (!empty($lastBackup)): ?>
        <strong style="font-size:1.1rem"><?= Html::e($lastBackup['created_at']) ?></strong>
        <div class="hint"><?= Html::e($lastBackup['filename']) ?> · <?= number_format(((int) $lastBackup['size_bytes']) / 1048576, 2) ?> MB</div>
      <?php else: ?>
        <strong style="font-size:1.1rem">None yet</strong>
        <div class="hint">Create one from Backups.</div>
      <?php endif; ?>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Latest leads</th><th></th></tr></thead>
        <tbody>
          <?php if (!$recentLeads): ?><tr><td colspan="2" class="empty">No enquiries yet.</td></tr><?php endif; ?>
          <?php foreach ($recentLeads as $l): ?>
            <tr>
              <td><?= Html::e($l['form_name']) ?><br><small><?= Html::e($l['created_at']) ?></small></td>
              <td><a href="/admin/leads/<?= (int) $l['id'] ?>/">Open</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php if (!empty($notFound)): ?>
  <div class="table-wrap" style="margin-top:16px">
    <table>
      <thead><tr><th>Recent 404s</th><th>Hits</th></tr></thead>
      <tbody>
        <?php foreach ($notFound as $n): ?>
          <tr><td><?= Html::e($n['path']) ?></td><td><?= (int) $n['hits'] ?></td></tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
