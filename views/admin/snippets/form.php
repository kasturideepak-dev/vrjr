<?php
$row = $row ?? null;
$selected = $selected ?? [];
$catalog = $catalog ?? ['pages' => [], 'cpt' => []];
$isNew = !$row;
$activatedOnce = $row && (int) $row['activated_once'] === 1;
$active = $row && (int) $row['is_active'] === 1;
$type = $row['type'] ?? 'html';
$action = $row ? '/admin/snippets/' . (int) $row['id'] . '/' : '/admin/snippets/';
?>
<div class="page-head">
  <div>
    <p class="crumbs"><a href="/admin/snippets/">Code snippets</a></p>
    <h1><?= $row ? 'Edit snippet' : 'New snippet' ?></h1>
  </div>
  <div class="toolbar">
    <?php if ($row): ?>
      <a class="btn-ghost" href="/admin/snippets/<?= (int) $row['id'] ?>/preview/" target="_blank" rel="noopener">Preview on site</a>
    <?php endif; ?>
    <a class="btn-ghost" href="/admin/snippets/">Back to list</a>
  </div>
</div>

<div class="snip-warn" role="alert">
  <strong>This code runs as-is on the public website.</strong>
  Invalid or malicious HTML, CSS, or JavaScript can break pages or expose visitors to risk.
  Snippets are stored raw (not sanitised like regular content). Only Super Admins can use this module.
  New snippets stay <em>Inactive</em> until you activate them.
</div>

<?php if (!empty($row['last_error'])): ?>
  <div class="flash flash-error">
    Last render error (<?= Html::e($row['last_error_at'] ?? '') ?>): <?= Html::e($row['last_error']) ?>
    The site stayed up; this snippet was skipped.
  </div>
<?php endif; ?>

<form class="form wide" method="post" action="<?= Html::e($action) ?>" id="snippet-form">
  <?= Csrf::field() ?>
  <?php if ($row): ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><?php endif; ?>

  <label class="lab">Name (internal only)
    <input name="name" required maxlength="160" value="<?= Html::e($row['name'] ?? '') ?>" placeholder="Google Tag Manager">
  </label>

  <div class="row3">
    <label class="lab">Type
      <select name="type" id="snip-type">
        <option value="html"<?= Html::selected($type, 'html') ?>>HTML</option>
        <option value="css"<?= Html::selected($type, 'css') ?>>CSS</option>
        <option value="javascript"<?= Html::selected($type, 'javascript') ?>>JavaScript</option>
      </select>
    </label>
    <label class="lab">Placement
      <select name="placement">
        <option value="header"<?= Html::selected($row['placement'] ?? 'header', 'header') ?>>Header (before &lt;/head&gt;)</option>
        <option value="body_start"<?= Html::selected($row['placement'] ?? '', 'body_start') ?>>Body start (after &lt;body&gt;)</option>
        <option value="footer"<?= Html::selected($row['placement'] ?? '', 'footer') ?>>Footer (before &lt;/body&gt;)</option>
      </select>
    </label>
    <label class="lab">Priority (lower loads first)
      <input type="number" name="priority" value="<?= (int) ($row['priority'] ?? 10) ?>">
    </label>
  </div>

  <label class="lab">Code
    <textarea name="code" id="snip-code" rows="16" spellcheck="false"><?= Html::e($row['code'] ?? '') ?></textarea>
  </label>
  <p class="hint">CSS is wrapped in <code>&lt;style&gt;</code> and JavaScript in <code>&lt;script&gt;</code> unless those tags are already present. HTML is printed raw. PHP is never executed.</p>

  <label class="lab">Scope</label>
  <div class="row2">
    <label class="snip-choice">
      <input type="radio" name="scope" value="global"<?= ($row['scope'] ?? 'global') !== 'specific' ? ' checked' : '' ?> data-scope="global">
      <span><strong>Global</strong><br><small>Every public page</small></span>
    </label>
    <label class="snip-choice">
      <input type="radio" name="scope" value="specific"<?= ($row['scope'] ?? '') === 'specific' ? ' checked' : '' ?> data-scope="specific">
      <span><strong>Specific pages</strong><br><small>Only the Pages and CPT entries you pick</small></span>
    </label>
  </div>

  <div id="snip-targets" class="snip-targets"<?= ($row['scope'] ?? 'global') === 'specific' ? '' : ' hidden' ?>>
    <p class="hint">Select one or more pages or custom post type entries.</p>
    <div class="snip-target-grid">
      <fieldset>
        <legend>Pages</legend>
        <?php foreach ($catalog['pages'] as $p):
          $key = 'page:' . (int) $p['id'];
        ?>
          <label><input type="checkbox" name="targets[]" value="<?= $key ?>"<?= in_array($key, $selected, true) ? ' checked' : '' ?>> <?= Html::e($p['title']) ?> <code><?= Html::e($p['slug'] === '/' ? '/' : '/' . $p['slug'] . '/') ?></code></label>
        <?php endforeach; ?>
        <?php if (empty($catalog['pages'])): ?><p class="hint">No pages.</p><?php endif; ?>
      </fieldset>
      <?php foreach ($catalog['cpt'] as $typeName => $entries): ?>
        <fieldset>
          <legend><?= Html::e($typeName) ?></legend>
          <?php foreach ($entries as $e):
            $key = 'cpt:' . (int) $e['id'];
          ?>
            <label><input type="checkbox" name="targets[]" value="<?= $key ?>"<?= in_array($key, $selected, true) ? ' checked' : '' ?>> <?= Html::e($e['title']) ?></label>
          <?php endforeach; ?>
        </fieldset>
      <?php endforeach; ?>
    </div>
  </div>

  <label class="lab">Notes
    <textarea name="notes" rows="3" placeholder="Why this snippet exists, who asked for it, ticket IDs…"><?= Html::e($row['notes'] ?? '') ?></textarea>
  </label>

  <label class="snip-choice">
    <input type="checkbox" name="is_active" value="1" id="snip-active"<?= $active ? ' checked' : '' ?>>
    <span><strong>Active on the live site</strong><br><small>Leave unchecked to save as inactive (the default for new snippets).</small></span>
  </label>

  <?php if (!$activatedOnce): ?>
    <div class="snip-confirm" id="snip-confirm">
      <p>This snippet has never run on the live site. To activate it the first time, type <strong>activate</strong> below or tick the box.</p>
      <label class="lab">Type “activate” to confirm first go-live
        <input name="confirm_activate" autocomplete="off" placeholder="activate">
      </label>
      <label><input type="checkbox" name="confirm_live" value="1"> I understand this code will run for every matching visitor and can break or compromise the site.</label>
    </div>
  <?php endif; ?>

  <div class="toolbar">
    <button class="btn" type="submit">Save snippet</button>
    <a class="btn-ghost" href="/admin/snippets/">Cancel</a>
  </div>
</form>

<link rel="stylesheet" href="<?= Html::e(admin_asset('vendor/codemirror/codemirror.css')) ?>">
<link rel="stylesheet" href="<?= Html::e(admin_asset('vendor/codemirror/material-darker.css')) ?>">
<script src="<?= Html::e(admin_asset('vendor/codemirror/codemirror.js')) ?>"></script>
<script src="<?= Html::e(admin_asset('vendor/codemirror/xml.js')) ?>"></script>
<script src="<?= Html::e(admin_asset('vendor/codemirror/javascript.js')) ?>"></script>
<script src="<?= Html::e(admin_asset('vendor/codemirror/css.js')) ?>"></script>
<script src="<?= Html::e(admin_asset('vendor/codemirror/htmlmixed.js')) ?>"></script>
<script>
(function () {
  var ta = document.getElementById("snip-code");
  var typeSel = document.getElementById("snip-type");
  if (!ta || !window.CodeMirror) return;
  function modeFor(t) {
    if (t === "css") return "css";
    if (t === "javascript") return "javascript";
    return "htmlmixed";
  }
  var cm = CodeMirror.fromTextArea(ta, {
    lineNumbers: true,
    theme: "material-darker",
    indentUnit: 2,
    tabSize: 2,
    lineWrapping: true,
    mode: modeFor(typeSel ? typeSel.value : "html")
  });
  if (typeSel) {
    typeSel.addEventListener("change", function () {
      cm.setOption("mode", modeFor(typeSel.value));
    });
  }
  var form = document.getElementById("snippet-form");
  if (form) {
    form.addEventListener("submit", function () { cm.save(); });
  }
  document.querySelectorAll("[name=scope]").forEach(function (r) {
    r.addEventListener("change", function () {
      var box = document.getElementById("snip-targets");
      if (box) box.hidden = r.value !== "specific" || !r.checked;
    });
  });
})();
</script>
