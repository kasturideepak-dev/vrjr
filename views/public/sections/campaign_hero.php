<?php
$chips = array_values(array_filter(array_map('trim', preg_split("/\r\n|\n|\r/", (string) ($c['chips'] ?? '')) ?: [])));
$offerPoints = Html::lines($c['offer_points'] ?? '');
$variant = preg_replace('/[^a-z]/', '', strtolower((string) ($c['variant'] ?? '')));
if ($variant === '') {
    $path = class_exists('Request') ? Request::path() : '';
    if (str_contains($path, 'bipc')) {
        $variant = 'bipc';
    } elseif (str_contains($path, 'neet-long') || str_contains($path, 'long-term')) {
        $variant = 'neet';
    } else {
        $variant = 'mpc';
    }
}
$capOnPhoto = $variant !== 'neet';
?>
<section class="camp-hero camp-hero--course camp-hero--<?= Html::e($variant) ?>">
  <img class="camp-hero__bg" src="<?= Html::e($c['image'] ?? $asset . 'img/banner/hero-class.jpg') ?>" alt="" aria-hidden="true">
  <div class="camp-hero__shade" aria-hidden="true"></div>
  <div class="container camp-hero__grid">
    <div class="camp-hero__copy">
      <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span aria-hidden="true">/</span><span><?= Html::e($c['crumb'] ?? $c['heading'] ?? '') ?></span></nav>
      <?php if (!empty($c['kicker'])): ?><p class="hero-kicker"><?= Html::e($c['kicker']) ?></p><?php endif; ?>
      <h1><?= Html::e($c['heading'] ?? '') ?></h1>
      <?php if (!empty($c['lead'])): ?><p class="camp-hero__lead"><?= Html::e($c['lead']) ?></p><?php endif; ?>
      <?php if ($chips): ?>
        <ul class="camp-chips">
          <?php foreach ($chips as $chip): ?>
            <li><?= Html::e($chip) ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <div class="camp-hero__actions">
        <?php if (!empty($c['cta_label'])): ?>
          <a class="apply-chip apply-chip--accent" href="<?= Html::e($c['cta_url'] ?? '#enquire') ?>"><?= Html::e($c['cta_label']) ?> <span class="apply-chip__mark">↗</span></a>
        <?php endif; ?>
        <?php if (!empty($c['cta2_label'])):
          $cta2 = $c['cta2_url'] ?? '#';
          $ext2 = str_starts_with($cta2, 'http');
        ?>
          <a class="btn btn--light" href="<?= Html::e($cta2) ?>"<?= $ext2 ? ' rel="noopener" target="_blank"' : '' ?>><?= Html::e($c['cta2_label']) ?></a>
        <?php endif; ?>
      </div>
      <?php if ($variant === 'neet' && ($offerPoints || !empty($c['offer_title']))): ?>
        <div class="camp-hero__metrics">
          <?php if (!empty($c['offer_title'])): ?>
            <p class="camp-hero__metrics-kicker"><?= Html::e($c['offer_kicker'] ?? '') ?> · <?= Html::e($c['offer_title']) ?></p>
          <?php endif; ?>
          <?php if (!empty($c['offer_text'])): ?><p class="camp-hero__metrics-lead"><?= Html::e($c['offer_text']) ?></p><?php endif; ?>
          <?php if ($offerPoints): ?>
            <ul>
              <?php foreach ($offerPoints as $p): ?>
                <li><?= Html::e($p[0] ?? '') ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
    <aside class="camp-hero__visual">
      <?php if (!empty($c['figure'])): ?>
        <img class="camp-hero__visual-photo" src="<?= Html::e($c['figure']) ?>" alt="" width="1200" height="1500">
      <?php endif; ?>
      <?php if ($capOnPhoto): ?>
        <div class="camp-hero__visual-cap">
          <?php if (!empty($c['offer_kicker'])): ?><span><?= Html::e($c['offer_kicker']) ?></span><?php endif; ?>
          <?php if (!empty($c['offer_title'])): ?><strong><?= Html::e($c['offer_title']) ?></strong><?php endif; ?>
          <?php if (!empty($c['offer_text'])): ?><p><?= Html::e($c['offer_text']) ?></p><?php endif; ?>
          <?php if ($offerPoints): ?>
            <ul>
              <?php foreach ($offerPoints as $p): ?>
                <li><?= Html::e($p[0] ?? '') ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php elseif ($variant === 'neet' && !empty($c['offer_kicker'])): ?>
        <span class="camp-hero__visual-badge"><?= Html::e($c['offer_kicker']) ?></span>
      <?php endif; ?>
    </aside>
  </div>
</section>
