<div class="page-head"><div><p class="crumbs">Pages</p><h1>New page</h1></div></div>
<form class="form wide" method="post">
  <?= Csrf::field() ?>
  <label class="lab">Title <input name="title" required data-slug-source="[name=slug]"></label>
  <label class="lab">Slug <input name="slug" data-slug-check="page" placeholder="about"></label>
  <label class="lab">Type
    <select name="type">
      <?php foreach (['standard' => 'Standard', 'landing' => 'Landing', 'blog_index' => 'Blog index'] as $k => $l): ?>
        <option value="<?= $k ?>"><?= $l ?></option>
      <?php endforeach; ?>
    </select>
  </label>

  <h3>Start from a template?</h3>
  <p style="color:var(--muted);margin:0 0 12px">Selecting a layout clones its sections into this page. You can edit every block afterwards.</p>
  <div class="tpl-grid">
    <label class="tpl-pick">
      <input type="radio" name="template_id" value="" checked>
      <strong>Blank</strong>
      <span>Start from scratch — add sections yourself.</span>
    </label>
    <?php foreach ($templates as $t):
      $n = Templates::sectionCount($t['sections_json'] ?? '[]');
    ?>
      <label class="tpl-pick">
        <input type="radio" name="template_id" value="<?= (int) $t['id'] ?>">
        <strong><?= Html::e($t['name']) ?></strong>
        <span><?= Html::e($t['description'] ?: ($t['page_type'] . ' layout')) ?></span>
        <small><?= $n ?> section<?= $n === 1 ? '' : 's' ?></small>
      </label>
    <?php endforeach; ?>
  </div>
  <button class="btn" type="submit">Create draft</button>
</form>
