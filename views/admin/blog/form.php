<div class="page-head">
  <div><p class="crumbs">Blog</p><h1><?= $row ? 'Edit post' : 'New post' ?></h1></div>
  <?php if (!empty($row) && ($row['status'] ?? '') === 'published'): ?>
    <div class="toolbar">
      <a class="btn-ghost" href="/blog/<?= Html::e($row['slug']) ?>/" target="_blank" rel="noopener">View post</a>
    </div>
  <?php endif; ?>
</div>
<form class="form wide" method="post" action="/admin/blog/" data-ajax>
  <?= Csrf::field() ?>
  <?php if ($row): ?><input type="hidden" name="id" value="<?= (int) $row['id'] ?>"><?php endif; ?>
  <label class="lab">Title <input name="title" required value="<?= Html::e($row['title'] ?? '') ?>" data-slug-source="[name=slug]"></label>
  <label class="lab">Slug <input name="slug" value="<?= Html::e($row['slug'] ?? '') ?>"></label>
  <div class="row2">
    <label class="lab">Status
      <select name="status">
        <?php foreach (['draft','published','scheduled','unpublished'] as $st): ?>
          <option value="<?= $st ?>"<?= Html::selected($row['status'] ?? 'draft', $st) ?>><?= $st ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="lab">Publish date <input type="datetime-local" name="published_at" value="<?= Html::e(!empty($row['published_at']) ? date('Y-m-d\TH:i', strtotime($row['published_at'])) : '') ?>"></label>
  </div>
  <label class="lab">Excerpt <textarea name="excerpt"><?= Html::e($row['excerpt'] ?? '') ?></textarea></label>
  <label class="lab">Body <textarea name="body_html" rows="12" data-wysiwyg><?= Html::e($row['body_html'] ?? '') ?></textarea></label>
  <label class="lab">Featured image <input name="featured_image" value="<?= Html::e($row['featured_image'] ?? '') ?>">
    <button class="btn-ghost" type="button" data-media-open="[name=featured_image]">Pick</button></label>
  <label class="lab">Categories
    <div>
      <?php foreach ($categories as $c): ?>
        <label style="display:inline-flex;gap:6px;margin-right:12px"><input type="checkbox" name="categories[]" value="<?= (int) $c['id'] ?>" <?= in_array((string) $c['id'], array_map('strval', $selectedCats), true) ? 'checked' : '' ?>> <?= Html::e($c['name']) ?></label>
      <?php endforeach; ?>
    </div>
  </label>
  <label class="lab">Tags (comma separated) <input name="tags" value=""></label>

  <div class="faq-editor">
    <div class="faq-editor__head">
      <strong>FAQs</strong>
      <span class="faq-editor__hint">Shown as an accordion at the end of the post.</span>
    </div>
    <div data-faq-list data-proto='<div class="faq-editor__row"><input name="faq_question[]" placeholder="Question"><textarea name="faq_answer[]" rows="2" placeholder="Answer"></textarea><button type="button" class="btn-ghost" data-faq-remove>Remove</button></div>'>
      <?php foreach (($faqs ?? []) as $f): ?>
        <div class="faq-editor__row">
          <input name="faq_question[]" placeholder="Question" value="<?= Html::e($f['question']) ?>">
          <textarea name="faq_answer[]" rows="2" placeholder="Answer"><?= Html::e($f['answer']) ?></textarea>
          <button type="button" class="btn-ghost" data-faq-remove>Remove</button>
        </div>
      <?php endforeach; ?>
    </div>
    <button type="button" class="btn-ghost" data-add-field="[data-faq-list]">+ Add FAQ</button>
  </div>

  <?php $seo = $seo ?? []; require ROOT . '/views/admin/partials/seo.php'; ?>
  <button class="btn" type="submit">Save</button>
</form>
