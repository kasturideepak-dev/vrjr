<div class="page-head"><div><p class="crumbs">System</p><h1>SEO</h1></div></div>
<form class="form" method="post">
  <?= Csrf::field() ?>
  <label class="lab">Default title <input name="default_seo_title" value="<?= Html::e($s['default_seo_title'] ?? '') ?>"></label>
  <label class="lab">Default description <textarea name="default_seo_description"><?= Html::e($s['default_seo_description'] ?? '') ?></textarea></label>
  <label class="lab">OG image <input name="og_image" value="<?= Html::e($s['og_image'] ?? '') ?>"></label>
  <?php if (Auth::isSuper()): ?>
    <p class="hint">GTM, Google Analytics, and Meta Pixel now live in <a href="/admin/snippets/">Code snippets</a>.</p>
  <?php endif; ?>
  <label class="lab">Schema JSON-LD <textarea name="schema_json" rows="8"><?= Html::e($s['schema_json'] ?? '') ?></textarea></label>
  <label class="lab">robots.txt <textarea name="robots_txt" rows="8"><?= Html::e($s['robots_txt'] ?? '') ?></textarea></label>
  <button class="btn" type="submit">Save</button>
</form>
<div class="table-wrap" style="margin-top:18px">
  <table>
    <thead><tr><th>Page</th><th>SEO title</th><th>Robots</th></tr></thead>
    <tbody>
    <?php foreach ($pages as $p): ?>
      <tr>
        <td><a href="/admin/pages/<?= (int) $p['id'] ?>/"><?= Html::e($p['title']) ?></a></td>
        <td><?= Html::e($p['seo_title'] ?? '') ?></td>
        <td><?= Html::e($p['robots'] ?? '') ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
