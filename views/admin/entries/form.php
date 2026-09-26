<?php
$row = $row ?? null;
$values = $values ?? [];
$mode = $type['template_mode'] ?? 'both';
?>
<div class="page-head">
  <div>
    <p class="crumbs"><?= Html::e($type['name']) ?></p>
    <h1><?= $row ? Html::e($row['title']) : 'New ' . Html::e($type['singular_name']) ?></h1>
    <?php if ($row && (int) $type['public']): ?>
      <p>Live URL: <a href="<?= Html::e(Cpt::permalink($row, $type)) ?>" target="_blank"><?= Html::e(Cpt::permalink($row, $type)) ?></a></p>
    <?php endif; ?>
  </div>
  <div class="toolbar">
    <?php if ($row): ?>
      <?php if ((int) $type['public'] && ($row['status'] ?? '') === 'published'): ?>
        <a class="btn-ghost" href="<?= Html::e(Cpt::permalink($row, $type)) ?>" target="_blank" rel="noopener">View page</a>
      <?php endif; ?>
      <form method="post" action="/admin/content/<?= Html::e($type['slug']) ?>/<?= (int) $row['id'] ?>/preview/"><?= Csrf::field() ?><button class="btn-ghost" type="submit">Preview</button></form>
      <form method="post" action="/admin/content/<?= Html::e($type['slug']) ?>/<?= (int) $row['id'] ?>/publish/"><?= Csrf::field() ?><button class="btn" type="submit">Publish</button></form>
    <?php endif; ?>
  </div>
</div>
<form class="form wide" method="post" action="/admin/content/<?= Html::e($type['slug']) ?>/" data-ajax>
  <?= Csrf::field() ?>
  <?php if ($row): ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><?php endif; ?>
  <?php if (!$row && !empty($pageTemplates) && in_array($mode, ['builder', 'both'], true)):
    $defaultTpl = (int) ($defaultTemplateId ?? 0);
  ?>
    <h3>Start from a template?</h3>
    <p style="color:var(--muted);margin:0 0 12px"><?= ($type['slug'] ?? '') === 'ai' ? 'AI Landing Page (banner, content, video testimonials, campuses) is selected by default.' : 'Landing Page Template is selected by default for new courses and programmes.' ?></p>
    <div class="tpl-grid">
      <label class="tpl-pick">
        <input type="radio" name="template_id" value="" <?= $defaultTpl ? '' : 'checked' ?>>
        <strong>Blank</strong>
        <span>Fields only — add landing sections later.</span>
      </label>
      <?php foreach ($pageTemplates as $t):
        $n = Templates::sectionCount($t['sections_json'] ?? '[]');
      ?>
        <label class="tpl-pick">
          <input type="radio" name="template_id" value="<?= (int) $t['id'] ?>" <?= $defaultTpl === (int) $t['id'] ? 'checked' : '' ?>>
          <strong><?= Html::e($t['name']) ?></strong>
          <span><?= Html::e($t['description'] ?: $t['page_type']) ?></span>
          <small><?= $n ?> section<?= $n === 1 ? '' : 's' ?></small>
        </label>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <div class="row2">
    <label class="lab">Title <input name="title" required value="<?= Html::e($row['title'] ?? '') ?>" data-slug-source="[name=slug]"></label>
    <label class="lab">Slug <input name="slug" value="<?= Html::e($row['slug'] ?? '') ?>" data-slug-check="entry" data-id="<?= (int) ($row['id'] ?? 0) ?>" data-type-id="<?= (int) $type['id'] ?>"></label>
  </div>
  <div class="row3">
    <label class="lab">Status
      <select name="status">
        <?php foreach (['draft','published','scheduled','unpublished'] as $st): ?>
          <option value="<?= $st ?>"<?= Html::selected($row['status'] ?? 'draft', $st) ?>><?= $st ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="lab">Schedule <input type="datetime-local" name="scheduled_at" value="<?= Html::e(!empty($row['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($row['scheduled_at'])) : '') ?>"></label>
    <label class="lab">Sort <input type="number" name="sort_order" value="<?= Html::e((string) ($row['sort_order'] ?? '0')) ?>"></label>
  </div>
  <?php if ((int) $type['supports_excerpt']): ?>
    <label class="lab">Excerpt <textarea name="excerpt"><?= Html::e($row['excerpt'] ?? '') ?></textarea></label>
  <?php endif; ?>
  <?php if ((int) $type['supports_featured_image']): ?>
    <label class="lab">Featured image
      <input name="featured_image" value="<?= Html::e($row['featured_image'] ?? '') ?>">
      <button class="btn-ghost" type="button" data-media-open="[name=featured_image]">Pick</button>
    </label>
  <?php endif; ?>
  <?php if ((int) $type['supports_editor']): ?>
    <label class="lab">Body <textarea name="body_html" rows="8" data-wysiwyg><?= Html::e($row['body_html'] ?? '') ?></textarea></label>
  <?php endif; ?>

  <?php if ($mode !== 'builder'): ?>
    <h3>Custom fields</h3>
    <?php foreach ($fieldDefs as $f):
      $val = $values[$f['name']] ?? '';
      $opts = json_decode($f['options_json'] ?: '{}', true) ?: [];
    ?>
      <label class="lab"><?= Html::e($f['label']) ?><?= (int) $f['is_required'] ? ' *' : '' ?>
        <?php if ($f['help_text']): ?><small style="color:var(--muted);font-weight:500"><?= Html::e($f['help_text']) ?></small><?php endif; ?>
        <?php if ($f['type'] === 'textarea'): ?>
          <textarea name="f_<?= Html::e($f['name']) ?>"><?= Html::e(is_array($val) ? implode("\n", $val) : (string) $val) ?></textarea>
        <?php elseif ($f['type'] === 'richtext'): ?>
          <textarea name="f_<?= Html::e($f['name']) ?>" rows="8" data-wysiwyg><?= Html::e((string) $val) ?></textarea>
        <?php elseif ($f['type'] === 'image'): ?>
          <input name="f_<?= Html::e($f['name']) ?>" value="<?= Html::e((string) $val) ?>">
          <button class="btn-ghost" type="button" data-media-open="[name='f_<?= Html::e($f['name']) ?>']">Pick</button>
        <?php elseif ($f['type'] === 'gallery'): ?>
          <textarea name="f_<?= Html::e($f['name']) ?>" placeholder="One image path per line"><?= Html::e(is_array($val) ? implode("\n", $val) : (string) $val) ?></textarea>
        <?php elseif ($f['type'] === 'select'): ?>
          <select name="f_<?= Html::e($f['name']) ?>">
            <option value="">Select</option>
            <?php foreach ($opts['choices'] ?? [] as $ch): ?>
              <option value="<?= Html::e($ch) ?>"<?= Html::selected((string) $val, $ch) ?>><?= Html::e($ch) ?></option>
            <?php endforeach; ?>
          </select>
        <?php elseif ($f['type'] === 'checkbox'): ?>
          <input type="checkbox" name="f_<?= Html::e($f['name']) ?>" value="1" <?= Html::checked((string) $val === '1') ?>>
        <?php elseif ($f['type'] === 'repeater'): ?>
          <textarea name="f_<?= Html::e($f['name']) ?>_raw" hidden></textarea>
          <div data-repeater>
            <?php
            $rowsR = is_array($val) ? $val : [];
            if (!$rowsR) {
                $rowsR = [[]];
            }
            $subs = $opts['subfields'] ?? [['name' => 'text', 'label' => 'Text', 'type' => 'text']];
            foreach ($rowsR as $ri => $rr):
            ?>
              <div class="row2" style="margin-bottom:8px">
                <?php foreach ($subs as $sf): ?>
                  <label class="lab"><?= Html::e($sf['label']) ?>
                    <input name="f_<?= Html::e($f['name']) ?>[<?= (int) $ri ?>][<?= Html::e($sf['name']) ?>]" value="<?= Html::e((string) ($rr[$sf['name']] ?? '')) ?>">
                  </label>
                <?php endforeach; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php elseif ($f['type'] === 'number'): ?>
          <input type="number" name="f_<?= Html::e($f['name']) ?>" value="<?= Html::e((string) $val) ?>">
        <?php elseif ($f['type'] === 'date'): ?>
          <input type="date" name="f_<?= Html::e($f['name']) ?>" value="<?= Html::e((string) $val) ?>">
        <?php else: ?>
          <input name="f_<?= Html::e($f['name']) ?>" value="<?= Html::e((string) $val) ?>">
        <?php endif; ?>
      </label>
    <?php endforeach; ?>
  <?php endif; ?>

  <?php if ($row && in_array($mode, ['builder', 'both'], true)): ?>
    <h3 id="builder">Landing page sections</h3>
    <p style="color:var(--muted)">These render through the approved frontend templates.</p>
    <div data-sortable>
    <?php foreach ($sections as $sec):
      $cid = (int) $sec['id'];
      $content = json_decode($sec['content_json'] ?: '{}', true) ?: [];
      $fields = $registry[$sec['type']]['fields'] ?? [];
    ?>
      <article class="sec">
        <h3><?= Html::e($registry[$sec['type']]['label'] ?? $sec['type']) ?>
          <label style="font-weight:500;font-size:12px"><input type="checkbox" name="visible[<?= $cid ?>]" <?= $sec['is_visible'] ? 'checked' : '' ?>> Visible</label>
        </h3>
        <input type="hidden" name="section_id[]" value="<?= $cid ?>">
        <?php foreach ($fields as $f):
          $name = 's' . $cid . '_' . $f['k'];
          $val = (string) ($content[$f['k']] ?? '');
        ?>
          <label class="lab"><?= Html::e($f['l']) ?>
            <?php if ($f['t'] === 'textarea' || $f['t'] === 'html'): ?>
              <textarea name="<?= Html::e($name) ?>"<?= $f['t'] === 'html' ? ' data-wysiwyg' : '' ?>><?= Html::e($val) ?></textarea>
            <?php else: ?>
              <input name="<?= Html::e($name) ?>" value="<?= Html::e($val) ?>">
            <?php endif; ?>
          </label>
        <?php endforeach; ?>
        <div class="toolbar" style="margin-top:8px">
          <input form="tpl-<?= $cid ?>" name="name" placeholder="Template name" value="<?= Html::e($registry[$sec['type']]['label'] ?? $sec['type']) ?>">
          <button class="btn-ghost" form="tpl-<?= $cid ?>" type="submit">Save this section as a template</button>
        </div>
      </article>
    <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php $seo = $seo ?? []; require ROOT . '/views/admin/partials/seo.php'; ?>
  <div class="toolbar"><button class="btn" type="submit">Save</button></div>
</form>

<?php if ($row && in_array($mode, ['builder', 'both'], true)): ?>
  <?php foreach ($sections as $sec): $cid = (int) $sec['id']; ?>
    <form id="tpl-<?= $cid ?>" method="post" action="/admin/content/<?= Html::e($type['slug']) ?>/<?= (int) $row['id'] ?>/section-template/" data-ajax="off"><?= Csrf::field() ?><input type="hidden" name="section_id" value="<?= $cid ?>"></form>
  <?php endforeach; ?>
  <form method="post" action="/admin/content/<?= Html::e($type['slug']) ?>/<?= (int) $row['id'] ?>/section/" class="form" style="margin-top:16px">
    <?= Csrf::field() ?>
    <label class="lab">Add section
      <select name="type">
        <?php foreach ($registry as $k => $meta): ?>
          <option value="<?= Html::e($k) ?>"><?= Html::e($meta['label']) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button class="btn-ghost" type="submit">Add section</button>
  </form>
  <?php if (!empty($sectionTemplates)): ?>
    <h3 style="margin-top:18px">Saved section templates</h3>
    <div class="toolbar">
      <?php foreach ($sectionTemplates as $st): ?>
        <form method="post" action="/admin/content/<?= Html::e($type['slug']) ?>/<?= (int) $row['id'] ?>/section/" style="display:inline">
          <?= Csrf::field() ?>
          <input type="hidden" name="type" value="<?= Html::e($st['type']) ?>">
          <input type="hidden" name="section_template_id" value="<?= (int) $st['id'] ?>">
          <button class="btn-ghost" type="submit">Insert: <?= Html::e($st['name']) ?></button>
        </form>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <form method="post" action="/admin/content/<?= Html::e($type['slug']) ?>/trash/" data-confirm="Move to trash?" style="margin-top:20px">
    <?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
    <button class="btn-danger" type="submit">Move to trash</button>
  </form>
<?php endif; ?>
