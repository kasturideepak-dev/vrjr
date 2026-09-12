<?php
$registry = $registry ?? SectionRegistry::all();
$sections = $sections ?? [];
$focusSec = (int) ($focusSec ?? 0);
if ($focusSec < 1 && $sections) {
    $focusSec = (int) $sections[0]['id'];
}
$ids = array_map(static fn ($s) => (int) $s['id'], $sections);
if ($focusSec && !in_array($focusSec, $ids, true) && $sections) {
    $focusSec = (int) $sections[0]['id'];
}
?>
<div class="pe-head">
  <div>
    <p class="crumbs">Pages / <?= Html::e($page['title']) ?></p>
    <h1><?= Html::e($page['title']) ?></h1>
    <p>Public URL: <a href="<?= Html::e($publicPath ?? '/') ?>" target="_blank"><?= Html::e($publicPath ?? '/') ?></a></p>
  </div>
  <div class="toolbar">
    <?php if (($page['status'] ?? '') === 'published'): ?>
      <a class="btn-ghost" href="<?= Html::e($publicPath ?? '/') ?>" target="_blank" rel="noopener">View page</a>
      <form method="post" action="/admin/pages/<?= (int) $page['id'] ?>/unpublish/" data-confirm="Unpublish? Direct visits will 404."><?= Csrf::field() ?><button class="btn-ghost" type="submit">Unpublish</button></form>
    <?php endif; ?>
    <?php if (Auth::can('pages.publish')): ?>
      <button class="btn" type="submit" form="page-editor" name="intent" value="publish">Publish</button>
    <?php endif; ?>
  </div>
</div>
<?php if (!empty($menuLinks)): ?>
  <div class="flash flash-error">Linked from menu: <?php foreach ($menuLinks as $m) echo Html::e($m['menu_name'] . ' → ' . $m['label']) . ' '; ?></div>
<?php endif; ?>

<form id="page-editor" class="pe" method="post" data-page-editor data-ajax action="/admin/pages/<?= (int) $page['id'] ?>/" data-reorder="/admin/pages/<?= (int) $page['id'] ?>/reorder/" data-visible="/admin/pages/<?= (int) $page['id'] ?>/section-visible/">
  <?= Csrf::field() ?>
  <input type="hidden" name="focus_sec" id="focus-sec" value="<?= $focusSec ?>">
  <input type="hidden" name="after" id="save-after" value="">

  <aside class="pe-col pe-col--list">
    <div class="pe-col__head">Sections <span class="pe-count"><?= count($sections) ?></span></div>
    <div class="pe-col__body" data-sec-list>
      <?php if (!$sections): ?>
        <p class="empty" style="padding:20px">No sections yet. Add one below.</p>
      <?php endif; ?>
      <?php foreach ($sections as $i => $sec):
        $cid = (int) $sec['id'];
        $on = $cid === $focusSec;
        $vis = (int) $sec['is_visible'] === 1;
      ?>
        <div class="pe-item<?= $on ? ' is-on' : '' ?><?= $vis ? '' : ' is-hidden' ?>" data-sec-id="<?= $cid ?>" draggable="true">
          <span class="pe-handle" title="Drag to reorder" aria-hidden="true">⋮⋮</span>
          <button class="pe-item__main" type="button" data-select-sec="<?= $cid ?>">
            <span class="pe-num"><?= $i + 1 ?></span>
            <span class="pe-ico"><?= Html::e(SectionRegistry::icon($sec['type'])) ?></span>
            <span class="pe-name"><?= Html::e($registry[$sec['type']]['label'] ?? $sec['type']) ?></span>
            <span class="pe-dirty" hidden title="Unsaved changes">●</span>
          </button>
          <button class="pe-eye" type="button" data-vis-toggle="<?= $cid ?>" title="<?= $vis ? 'Hide section' : 'Show section' ?>" aria-pressed="<?= $vis ? 'true' : 'false' ?>">
            <?= $vis ? '◉' : '○' ?>
          </button>
          <input type="hidden" name="section_id[]" value="<?= $cid ?>">
          <input type="hidden" name="visible[<?= $cid ?>]" value="<?= $vis ? '1' : '0' ?>" data-vis-field="<?= $cid ?>">
        </div>
      <?php endforeach; ?>
    </div>
    <div class="pe-add">
      <details>
        <summary class="btn">+ Add section</summary>
        <div class="pe-add__box">
          <label class="lab">Block type
            <select name="add_type_ui" form="add-sec-form">
              <?php foreach ($registry as $k => $meta): ?>
                <option value="<?= Html::e($k) ?>"><?= Html::e($meta['label']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <button class="btn" form="add-sec-form" type="submit">Add block</button>
          <?php if (!empty($sectionTemplates)): ?>
            <p class="hint" style="margin:10px 0 6px">Or insert a saved section</p>
            <?php foreach ($sectionTemplates as $st): ?>
              <button class="btn-ghost" form="add-tpl-<?= (int) $st['id'] ?>" type="submit" style="width:100%;justify-content:flex-start;margin-bottom:4px"><?= Html::e($st['name']) ?></button>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </details>
    </div>
  </aside>

  <section class="pe-col pe-col--main">
    <div class="pe-col__body pe-editor">
      <?php if (!$sections): ?>
        <div class="empty">Add a Hero, FAQ, form, or any other block from the list on the left.</div>
      <?php endif; ?>
      <?php foreach ($sections as $sec):
        $cid = (int) $sec['id'];
        $content = json_decode($sec['content_json'] ?: '{}', true) ?: [];
        $groups = SectionRegistry::groupedFields($sec['type']);
      ?>
        <article class="pe-panel<?= $cid === $focusSec ? ' is-on' : '' ?>" data-panel="<?= $cid ?>" <?= $cid === $focusSec ? '' : 'hidden' ?>>
          <header class="pe-panel__head">
            <span class="pe-ico"><?= Html::e(SectionRegistry::icon($sec['type'])) ?></span>
            <div>
              <h2><?= Html::e($registry[$sec['type']]['label'] ?? $sec['type']) ?></h2>
              <p class="hint">Editing this block only. Other sections stay on the page, unchanged until you switch to them.</p>
            </div>
          </header>
          <?php foreach ($groups as $g => $fields): ?>
            <div class="pe-group">
              <h3><?= Html::e(SectionRegistry::groupLabel($g)) ?></h3>
              <?php foreach ($fields as $f):
                $fname = 's' . $cid . '_' . $f['k'];
                $val = is_array($content[$f['k']] ?? null) ? implode("\n", $content[$f['k']]) : (string) ($content[$f['k']] ?? '');
              ?>
                <?php if ($f['t'] === 'image'):
                  $name = $fname;
                  $label = $f['l'];
                  require ROOT . '/views/admin/partials/image-field.php';
                elseif ($f['t'] === 'images'):
                  $name = $fname;
                  $label = $f['l'];
                  require ROOT . '/views/admin/partials/images-field.php';
                else: ?>
                  <label class="lab"><?= Html::e($f['l']) ?>
                    <?php if ($f['t'] === 'textarea' || $f['t'] === 'html'): ?>
                      <textarea name="<?= Html::e($fname) ?>"><?= Html::e($val) ?></textarea>
                    <?php else: ?>
                      <input name="<?= Html::e($fname) ?>" value="<?= Html::e($val) ?>">
                    <?php endif; ?>
                  </label>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
          <div class="toolbar" style="margin-top:8px">
            <button class="btn-ghost" form="dup-<?= $cid ?>" type="submit">Duplicate block</button>
            <button class="btn-danger" form="del-<?= $cid ?>" type="submit">Delete block</button>
          </div>
          <div class="toolbar" style="margin-top:8px">
            <input form="tpl-<?= $cid ?>" name="name" placeholder="Template name" value="<?= Html::e($registry[$sec['type']]['label'] ?? $sec['type']) ?>">
            <button class="btn-ghost" form="tpl-<?= $cid ?>" type="submit">Save as section template</button>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="pe-sticky-bar">
      <button class="btn" type="submit" name="intent" value="save" data-save><?= ($page['status'] ?? '') === 'published' ? 'Save &amp; update live' : 'Save draft' ?></button>
      <button class="btn-ghost" type="submit" name="intent" value="save" data-save-preview>Save &amp; preview</button>
      <span class="hint" data-dirty-hint hidden>Unsaved changes</span>
    </div>
  </section>

  <aside class="pe-col pe-col--meta">
    <div class="pe-col__body">
      <details class="pe-acc" open>
        <summary>Page settings</summary>
        <div class="pe-acc__body">
          <label class="lab">Title <input name="title" value="<?= Html::e($page['title']) ?>" required></label>
          <label class="lab">Slug <input name="slug" value="<?= Html::e($page['slug']) ?>" data-slug-check="page" data-id="<?= (int) $page['id'] ?>"></label>
          <label class="lab">Status
            <select name="status">
              <?php foreach (['draft','published','scheduled','unpublished'] as $st): ?>
                <option value="<?= $st ?>"<?= Html::selected($page['status'], $st) ?>><?= $st ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="lab">Schedule at <input type="datetime-local" name="scheduled_at" value="<?= Html::e($page['scheduled_at'] ? date('Y-m-d\TH:i', strtotime($page['scheduled_at'])) : '') ?>"></label>
        </div>
      </details>
      <details class="pe-acc">
        <summary>SEO</summary>
        <div class="pe-acc__body">
          <?php $seo = $seo ?? []; ?>
          <label class="lab">SEO title <input name="seo_title" value="<?= Html::e($seo['seo_title'] ?? '') ?>"></label>
          <label class="lab">Robots
            <select name="robots">
              <?php foreach (['index,follow','noindex,follow','index,nofollow','noindex,nofollow'] as $r): ?>
                <option value="<?= $r ?>"<?= Html::selected($seo['robots'] ?? 'index,follow', $r) ?>><?= $r ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="lab">Meta description <textarea name="meta_description"><?= Html::e($seo['meta_description'] ?? '') ?></textarea></label>
          <label class="lab">Canonical URL <input name="canonical_url" value="<?= Html::e($seo['canonical_url'] ?? '') ?>"></label>
          <label class="lab">OG title <input name="og_title" value="<?= Html::e($seo['og_title'] ?? '') ?>"></label>
          <?php $name = 'og_image'; $val = (string) ($seo['og_image'] ?? ''); $label = 'OG image'; require ROOT . '/views/admin/partials/image-field.php'; ?>
          <label class="lab">OG description <textarea name="og_description"><?= Html::e($seo['og_description'] ?? '') ?></textarea></label>
        </div>
      </details>
      <details class="pe-acc" id="revisions-acc">
        <summary>Revisions <span class="pe-count"><?= count($revisions ?? []) ?></span></summary>
        <div class="pe-acc__body">
          <p class="hint">Preview a past version before restoring. Restore overwrites the current draft — it does not publish.</p>
          <?php if (empty($revisions)): ?>
            <p class="hint">No revisions yet. Save the page to create one.</p>
          <?php endif; ?>
          <?php foreach ($revisions ?? [] as $rev): ?>
            <article class="pe-rev" data-rev="<?= (int) $rev['id'] ?>">
              <button class="pe-rev__open" type="button" data-rev-open="<?= (int) $rev['id'] ?>">
                <strong><?= Html::e($rev['change'] ?? 'Saved') ?></strong>
                <span><?= Html::e($rev['user_name'] ?: 'System') ?> · <?= Html::e($rev['created_at']) ?></span>
                <span><?= (int) $rev['section_count'] ?> sections<?= !empty($rev['is_live']) ? ' · live' : '' ?></span>
              </button>
              <div class="pe-rev__detail" hidden>
                <?php if (!empty($rev['type_labels'])): ?>
                  <p class="hint"><?= Html::e(implode(' · ', array_slice($rev['type_labels'], 0, 8))) ?></p>
                <?php endif; ?>
                <div class="toolbar">
                  <a class="btn-ghost" href="/admin/pages/<?= (int) $page['id'] ?>/revision/<?= (int) $rev['id'] ?>/preview/" target="_blank" rel="noopener">Preview this version</a>
                </div>
                <form method="post" action="/admin/pages/<?= (int) $page['id'] ?>/restore-rev/" data-confirm="Overwrite the current draft with this revision? Preview it first if you are unsure.">
                  <?= Csrf::field() ?>
                  <input type="hidden" name="revision_id" value="<?= (int) $rev['id'] ?>">
                  <button class="btn-danger" type="submit">Restore this version</button>
                </form>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </details>
      <?php if ($page['slug'] !== '/'): ?>
        <form method="post" action="/admin/pages/trash/" data-confirm="Move this page to trash?" style="margin-top:16px">
          <?= Csrf::field() ?>
          <input type="hidden" name="id" value="<?= (int) $page['id'] ?>">
          <?php if (!empty($menuLinks)): ?><input type="hidden" name="confirm_links" value="1"><?php endif; ?>
          <button class="btn-danger" type="submit">Move to trash</button>
        </form>
      <?php endif; ?>
    </div>
  </aside>
</form>

<form id="add-sec-form" method="post" action="/admin/pages/<?= (int) $page['id'] ?>/section/">
  <?= Csrf::field() ?>
  <input type="hidden" name="type" id="add-sec-type" value="hero">
</form>
<?php foreach ($sectionTemplates ?? [] as $st): ?>
  <form id="add-tpl-<?= (int) $st['id'] ?>" method="post" action="/admin/pages/<?= (int) $page['id'] ?>/section/">
    <?= Csrf::field() ?>
    <input type="hidden" name="type" value="<?= Html::e($st['type']) ?>">
    <input type="hidden" name="section_template_id" value="<?= (int) $st['id'] ?>">
  </form>
<?php endforeach; ?>
<?php foreach ($sections as $sec): $cid = (int) $sec['id']; ?>
  <form id="dup-<?= $cid ?>" method="post" action="/admin/pages/<?= (int) $page['id'] ?>/section-duplicate/"><?= Csrf::field() ?><input type="hidden" name="section_id" value="<?= $cid ?>"></form>
  <form id="del-<?= $cid ?>" method="post" action="/admin/pages/<?= (int) $page['id'] ?>/section-delete/" data-confirm="Delete this section from the page?"><?= Csrf::field() ?><input type="hidden" name="section_id" value="<?= $cid ?>"></form>
  <form id="tpl-<?= $cid ?>" method="post" action="/admin/pages/<?= (int) $page['id'] ?>/section-template/" data-ajax="off"><?= Csrf::field() ?><input type="hidden" name="section_id" value="<?= $cid ?>"></form>
<?php endforeach; ?>
<script>
(function () {
  var form = document.getElementById("add-sec-form");
  var sel = document.querySelector("[name=add_type_ui]");
  if (form && sel) {
    form.addEventListener("submit", function () {
      document.getElementById("add-sec-type").value = sel.value;
    });
  }
})();
</script>
