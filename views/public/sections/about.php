<?php $points = Html::lines($c['points'] ?? ''); ?>
<section class="section" id="about">
  <div class="container about-asb">
    <div class="about-stack reveal">
      <img class="about-stack__main" src="<?= Html::e($c['image_main'] ?? '') ?>" alt="" width="1221" height="1600" loading="lazy">
      <?php if (!empty($c['image_card'])): ?><img class="about-stack__card" src="<?= Html::e($c['image_card']) ?>" alt="" width="605" height="1077" loading="lazy"><?php endif; ?>
      <?php if (!empty($c['badge_value'])): ?><div class="about-stack__badge"><strong><?= Html::e($c['badge_value']) ?></strong><span><?= Html::e($c['badge_label'] ?? '') ?></span></div><?php endif; ?>
    </div>
    <div class="reveal">
      <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
      <h2><?= Html::e($c['heading'] ?? '') ?></h2>
      <p class="about-quote"><?= Html::e($c['quote'] ?? '') ?></p>
      <ul class="about-points">
        <?php foreach ($points as $p): ?>
          <li><strong><?= Html::e($p[0] ?? '') ?></strong><span><?= Html::e($p[1] ?? '') ?></span></li>
        <?php endforeach; ?>
      </ul>
      <?php if (!empty($c['cta_label'])):
        $ctaUrl = $c['cta_url'] ?? '#';
        $ext = str_starts_with($ctaUrl, 'http');
      ?>
        <a class="apply-chip apply-chip--accent" href="<?= Html::e($ctaUrl) ?>"<?= $ext ? ' rel="noopener" target="_blank"' : '' ?>><?= Html::e($c['cta_label']) ?> <span class="apply-chip__mark">↗</span></a>
      <?php endif; ?>
    </div>
  </div>
</section>
