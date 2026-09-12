<?php $seo = $seo ?? []; ?>
<div class="card" style="margin-top:12px">
  <h3>SEO</h3>
  <div class="row2" style="margin-top:10px">
    <label class="lab">SEO title <input name="seo_title" value="<?= Html::e($seo['seo_title'] ?? '') ?>"></label>
    <label class="lab">Robots
      <select name="robots">
        <?php foreach (['index,follow','noindex,follow','index,nofollow','noindex,nofollow'] as $r): ?>
          <option value="<?= $r ?>"<?= Html::selected($seo['robots'] ?? 'index,follow', $r) ?>><?= $r ?></option>
        <?php endforeach; ?>
      </select>
    </label>
  </div>
  <label class="lab">Meta description <textarea name="meta_description"><?= Html::e($seo['meta_description'] ?? '') ?></textarea></label>
  <label class="lab">Canonical URL <input name="canonical_url" value="<?= Html::e($seo['canonical_url'] ?? '') ?>" placeholder="Auto-filled with production URL if empty"></label>
  <div class="row2">
    <label class="lab">OG title <input name="og_title" value="<?= Html::e($seo['og_title'] ?? '') ?>"></label>
    <label class="lab">OG image <input name="og_image" value="<?= Html::e($seo['og_image'] ?? '') ?>" data-media-field> <button class="btn-ghost" type="button" data-media-open="[name=og_image]">Pick</button></label>
  </div>
  <label class="lab">OG description <textarea name="og_description"><?= Html::e($seo['og_description'] ?? '') ?></textarea></label>
</div>
