<?php
$hasFigure = !empty($c['figure']);
$story = str_word_count((string) ($c['heading'] ?? '')) > 4;
?>
<section class="page-hero<?= $hasFigure ? ' page-hero--split' : '' ?><?= $story ? ' page-hero--story' : '' ?>">
  <img class="page-hero__photo" src="<?= Html::e($c['image'] ?? $asset . 'img/banner/hero-class.jpg') ?>" alt="" aria-hidden="true">
  <div class="page-hero__shade" aria-hidden="true"></div>
  <div class="container page-hero__inner">
    <div class="page-hero__copy">
      <nav class="crumbs" aria-label="Breadcrumb">
        <?php if (!empty($breadcrumbs)): ?>
          <?php foreach ($breadcrumbs as $i => $b): ?>
            <?php if ($i): ?><span aria-hidden="true">/</span><?php endif; ?>
            <?php if (!empty($b[1]) && $i < count($breadcrumbs) - 1): ?><a href="<?= Html::e($b[1]) ?>"><?= Html::e($b[0]) ?></a>
            <?php else: ?><span><?= Html::e($b[0]) ?></span><?php endif; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <a href="/">Home</a><span aria-hidden="true">/</span><span><?= Html::e($c['crumb'] ?? $c['heading'] ?? '') ?></span>
        <?php endif; ?>
      </nav>
      <?php if (!empty($c['kicker'])): ?><p class="hero-kicker"><?= Html::e($c['kicker']) ?></p><?php endif; ?>
      <h1><?= Html::e($c['heading'] ?? '') ?></h1>
      <?php if (!empty($c['lead'])): ?><p class="hero-lead"><?= Html::e($c['lead']) ?></p><?php endif; ?>
      <?php if (!empty($c['cta_label']) || !empty($c['cta2_label'])): ?>
        <div class="page-hero__actions">
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
      <?php endif; ?>
    </div>
    <?php if ($hasFigure): ?>
      <div class="page-hero__figure">
        <img src="<?= Html::e($c['figure']) ?>" alt="" width="800" height="1000">
      </div>
    <?php endif; ?>
  </div>
</section>
