<?php
$points = Html::lines($c['points'] ?? '');
$left = ($c['image_left'] ?? '') === '1';
?>
<section class="section">
  <div class="container split-2">
    <?php if ($left): ?>
      <div class="split-photo split-photo--wide"><img src="<?= Html::e($c['image'] ?? '') ?>" alt="" loading="lazy"></div>
    <?php endif; ?>
    <div>
      <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
      <h2><?= Html::e($c['heading'] ?? '') ?></h2>
      <?php if (!empty($c['lede'])): ?><p class="lede"><?= Html::e($c['lede']) ?></p><?php endif; ?>
      <?php if (!empty($c['html'])): ?><div class="prose"><?= Html::allowedHtml($c['html']) ?></div><?php endif; ?>
      <?php if ($points): ?>
        <ul class="about-points">
          <?php foreach ($points as $p): ?>
            <li><?php if (!empty($p[1])): ?><strong><?= Html::e($p[0] ?? '') ?></strong><span><?= Html::e($p[1] ?? '') ?></span><?php else: ?><span><?= Html::e($p[0] ?? '') ?></span><?php endif; ?></li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
      <?php if (!empty($c['cta_label'])):
        $ctaUrl = $c['cta_url'] ?? '#';
        $ext = str_starts_with($ctaUrl, 'http');
      ?>
        <a class="apply-chip apply-chip--accent" href="<?= Html::e($ctaUrl) ?>"<?= $ext ? ' rel="noopener" target="_blank"' : '' ?>><?= Html::e($c['cta_label']) ?> <span class="apply-chip__mark">↗</span></a>
      <?php endif; ?>
    </div>
    <?php if (!$left): ?>
      <div class="split-photo split-photo--wide"><img src="<?= Html::e($c['image'] ?? '') ?>" alt="" loading="lazy"></div>
    <?php endif; ?>
  </div>
</section>
