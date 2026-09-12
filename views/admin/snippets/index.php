<div class="page-head">
  <div>
    <p class="crumbs">System</p>
    <h1>Code snippets</h1>
    <p>Inject HTML, CSS, or JavaScript into the live site. Super Admin only.</p>
  </div>
  <div class="toolbar">
    <a class="btn" href="/admin/snippets/new/">Add snippet</a>
  </div>
</div>

<div class="snip-warn" role="status">
  <strong>Privileged module.</strong>
  Invalid or malicious code here can break pages or expose the site. New snippets stay inactive until you activate them.
</div>

<form method="get" class="toolbar" style="margin-bottom:14px">
  <input type="search" name="q" value="<?= Html::e($q ?? '') ?>" placeholder="Search name or notes">
  <select name="type">
    <option value="">All types</option>
    <?php foreach (['html' => 'HTML', 'css' => 'CSS', 'javascript' => 'JavaScript'] as $k => $lab): ?>
      <option value="<?= $k ?>"<?= Html::selected($type ?? '', $k) ?>><?= $lab ?></option>
    <?php endforeach; ?>
  </select>
  <select name="placement">
    <option value="">All placements</option>
    <option value="header"<?= Html::selected($placement ?? '', 'header') ?>>Header</option>
    <option value="body_start"<?= Html::selected($placement ?? '', 'body_start') ?>>Body start</option>
    <option value="footer"<?= Html::selected($placement ?? '', 'footer') ?>>Footer</option>
  </select>
  <button class="btn-ghost" type="submit">Filter</button>
</form>

<div class="table-wrap">
  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Type</th>
        <th>Placement</th>
        <th>Scope</th>
        <th>Status</th>
        <th>Last edited</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    <?php if (empty($rows)): ?>
      <tr><td colspan="7" class="empty">No snippets yet.</td></tr>
    <?php endif; ?>
    <?php
    $placeLab = ['header' => 'Header', 'body_start' => 'Body start', 'footer' => 'Footer'];
    $typeLab = ['html' => 'HTML', 'css' => 'CSS', 'javascript' => 'JavaScript'];
    foreach ($rows as $r):
      $active = (int) $r['is_active'] === 1;
      $nTargets = (int) ($counts[(int) $r['id']] ?? 0);
      $scope = ($r['scope'] ?? 'global') === 'global'
        ? 'Global'
        : ($nTargets === 1 ? '1 page' : $nTargets . ' pages');
    ?>
      <tr>
        <td>
          <a href="/admin/snippets/<?= (int) $r['id'] ?>/"><strong><?= Html::e($r['name']) ?></strong></a>
          <?php if (!empty($r['last_error'])): ?>
            <div class="snip-err" title="<?= Html::e($r['last_error']) ?>">Render error <?= Html::e($r['last_error_at'] ?? '') ?></div>
          <?php endif; ?>
        </td>
        <td><span class="badge badge-off"><?= Html::e($typeLab[$r['type']] ?? $r['type']) ?></span></td>
        <td><?= Html::e($placeLab[$r['placement']] ?? $r['placement']) ?></td>
        <td><?= Html::e($scope) ?></td>
        <td>
          <form method="post" action="/admin/snippets/<?= (int) $r['id'] ?>/toggle/" style="display:inline">
            <?= Csrf::field() ?>
            <button class="snip-switch<?= $active ? ' is-on' : '' ?>" type="submit" title="<?= $active ? 'Deactivate' : 'Activate' ?>">
              <span><?= $active ? 'Active' : 'Inactive' ?></span>
            </button>
          </form>
        </td>
        <td>
          <?= Html::e($r['updated_at'] ?? '') ?>
          <?php if (!empty($r['editor_name'])): ?>
            <div class="hint" style="margin:4px 0 0;font-size:12px"><?= Html::e($r['editor_name']) ?></div>
          <?php endif; ?>
        </td>
        <td>
          <div class="toolbar">
            <a class="btn-ghost" href="/admin/snippets/<?= (int) $r['id'] ?>/preview/" target="_blank" rel="noopener">Preview</a>
            <a class="btn-ghost" href="/admin/snippets/<?= (int) $r['id'] ?>/">Edit</a>
            <form method="post" action="/admin/snippets/duplicate/" style="display:inline"><?= Csrf::field() ?>
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn-ghost" type="submit">Duplicate</button>
            </form>
            <form method="post" action="/admin/snippets/delete/" data-confirm="Delete this snippet permanently? It will stop running on the live site." style="display:inline"><?= Csrf::field() ?>
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn-danger" type="submit">Delete</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
