<?php
$cards = Html::lines($c['cards'] ?? '');
$hasVideo = !empty($c['video_image']);
?>
<section class="section" id="why">
  <div class="container">
    <div class="section-head"><div><?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?><h2><?= Html::e($c['heading'] ?? '') ?></h2></div></div>
    <?php if ($hasVideo): ?><div class="why-asb"><?php endif; ?>
      <div class="why-quad">
        <?php foreach ($cards as $i => $card): ?>
          <article class="why-card"><span class="why-card__n"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span><h3><?= Html::e($card[0] ?? '') ?></h3><p><?= Html::e($card[1] ?? '') ?></p></article>
        <?php endforeach; ?>
      </div>
      <?php if ($hasVideo): ?>
        <a class="why-video" href="<?= Html::e($c['video_url'] ?? '#') ?>" rel="noopener" target="_blank">
          <img src="<?= Html::e($c['video_image']) ?>" alt="" loading="lazy">
          <span class="why-video__play"><svg><use href="#i-play"></use></svg></span>
        </a>
      </div>
      <?php endif; ?>
  </div>
</section>
