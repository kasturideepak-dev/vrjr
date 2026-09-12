<?php
$row = $row ?? null;
$fields = $fields ?? [];
$proto = '<div class="field-row">'
  . '<input name="field_label[]" placeholder="Label">'
  . '<input name="field_name[]" placeholder="machine_name">'
  . '<select name="field_type[]">';
foreach ($fieldTypes as $k => $l) {
    $proto .= '<option value="' . Html::e($k) . '">' . Html::e($l) . '</option>';
}
$proto .= '</select><select name="field_required[]"><option value="0">Optional</option><option value="1">Required</option></select>'
  . '<input name="field_options[]" placeholder="Select options / repeater subfields">'
  . '<input type="hidden" name="field_id[]" value="0"><button class="btn-ghost" type="button" onclick="this.parentNode.remove()">×</button></div>';
?>
<div class="page-head">
  <div>
    <p class="crumbs">Settings / Post types</p>
    <h1><?= $row ? Html::e($row['name']) : 'Add new post type' ?></h1>
    <p>Name it, define fields, save. The list view, entry form, archive and single URLs are created automatically.</p>
  </div>
</div>
<form class="form wide" method="post" action="/admin/post-types/" data-ajax>
  <?= Csrf::field() ?>
  <?php if ($row): ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><?php endif; ?>
  <div class="row2">
    <label class="lab">Name <input name="name" required value="<?= Html::e($row['name'] ?? '') ?>" data-slug-source="[name=slug]" placeholder="AI Program"></label>
    <label class="lab">Singular <input name="singular_name" value="<?= Html::e($row['singular_name'] ?? '') ?>" placeholder="AI Program"></label>
  </div>
  <label class="lab">URL prefix (archive) <input name="slug" value="<?= Html::e($row['slug'] ?? '') ?>" data-slug-check="post_type" data-id="<?= (int) ($row['id'] ?? 0) ?>" placeholder="ai-program">
    <small style="color:var(--muted)">Live archive: <?= Html::e(url($row['slug'] ?? 'ai-program')) ?></small>
  </label>
  <label class="lab">Description <input name="description" value="<?= Html::e($row['description'] ?? '') ?>"></label>
  <div class="row3">
    <label class="lab">Template
      <select name="template_mode">
        <?php foreach (['both' => 'Fields + page builder', 'fields' => 'Fields only', 'builder' => 'Page builder only'] as $k => $l): ?>
          <option value="<?= $k ?>"<?= Html::selected($row['template_mode'] ?? 'both', $k) ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="lab">Archive title <input name="archive_title" value="<?= Html::e($row['archive_title'] ?? '') ?>"></label>
    <label class="lab">Sort <input type="number" name="sort_order" value="<?= Html::e((string) ($row['sort_order'] ?? '0')) ?>"></label>
  </div>
  <label class="lab">Archive intro <textarea name="archive_intro"><?= Html::e($row['archive_intro'] ?? '') ?></textarea></label>
  <div class="toolbar">
    <label><input type="checkbox" name="public" <?= Html::checked(!$row || (int) $row['public']) ?>> Public (has URLs)</label>
    <label><input type="checkbox" name="has_archive" <?= Html::checked(!$row || (int) $row['has_archive']) ?>> Archive at /{slug}/</label>
    <label><input type="checkbox" name="supports_featured_image" <?= Html::checked(!$row || (int) $row['supports_featured_image']) ?>> Featured image</label>
    <label><input type="checkbox" name="supports_excerpt" <?= Html::checked(!$row || (int) $row['supports_excerpt']) ?>> Excerpt</label>
    <label><input type="checkbox" name="supports_editor" <?= Html::checked(!$row || (int) $row['supports_editor']) ?>> Body editor</label>
  </div>
  <h3>Custom fields</h3>
  <p style="color:var(--muted);margin:0 0 8px">For a dropdown, put one choice per line in Options. For a repeater, use <code>Label|text</code> or <code>Label|image</code> per line.</p>
  <div id="field-builder" data-proto="<?= Html::e($proto) ?>">
    <?php foreach ($fields as $f):
      $opts = json_decode($f['options_json'] ?: '{}', true) ?: [];
      $optText = isset($opts['choices']) ? implode("\n", $opts['choices']) : (isset($opts['subfields']) ? implode("\n", array_map(fn ($s) => ($s['label'] ?? '') . '|' . ($s['type'] ?? 'text'), $opts['subfields'])) : '');
    ?>
      <div class="field-row">
        <input name="field_label[]" value="<?= Html::e($f['label']) ?>">
        <input name="field_name[]" value="<?= Html::e($f['name']) ?>">
        <select name="field_type[]">
          <?php foreach ($fieldTypes as $k => $l): ?>
            <option value="<?= Html::e($k) ?>"<?= Html::selected($f['type'], $k) ?>><?= Html::e($l) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="field_required[]">
          <option value="0"<?= Html::selected((string) $f['is_required'], '0') ?>>Optional</option>
          <option value="1"<?= Html::selected((string) $f['is_required'], '1') ?>>Required</option>
        </select>
        <input name="field_options[]" value="<?= Html::e($optText) ?>">
        <input type="hidden" name="field_id[]" value="<?= (int) $f['id'] ?>">
        <button class="btn-ghost" type="button" onclick="this.parentNode.remove()">×</button>
      </div>
    <?php endforeach; ?>
  </div>
  <p><button class="btn-ghost" type="button" data-add-field="#field-builder">Add field</button></p>
  <div class="toolbar">
    <button class="btn" type="submit">Save post type</button>
    <?php if ($row): ?>
      <a class="btn-ghost" href="/admin/content/<?= Html::e($row['slug']) ?>/new/">Add first entry</a>
      <?php if (!Cpt::isSystem($row) && Auth::can('post_types.delete')): ?>
        <a class="btn-danger" href="/admin/post-types/<?= (int) $row['id'] ?>/confirm-delete/">Delete</a>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</form>
