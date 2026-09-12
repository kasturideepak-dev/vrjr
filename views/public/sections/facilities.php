<?php $cards = Html::lines($c['cards'] ?? ''); ?>
<section class="section bg-paper" id="facilities">
  <div class="container">
    <div class="section-head is-center"><div><?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?><h2><?= Html::e($c['heading'] ?? '') ?></h2></div></div>
    <div class="life-grid">
      <?php foreach ($cards as $card): ?>
        <article class="life-card">
          <a class="life-card__media" href="<?= Html::e($card[2] ?? '#') ?>" data-lightbox-src="<?= Html::e($card[2] ?? '') ?>" data-alt="">
            <img src="<?= Html::e($card[2] ?? '') ?>" alt="" loading="lazy">
            <span class="life-card__tag"><?= Html::e($card[3] ?? '') ?></span>
          </a>
          <div class="life-card__body"><h3><?= Html::e($card[0] ?? '') ?></h3><p><?= Html::e($card[1] ?? '') ?></p></div>
        </article>
      <?php endforeach; ?>
    </div>
    <div class="life-cta"><a class="apply-chip apply-chip--accent" href="/gallery/">Campus gallery <span class="apply-chip__mark">↗</span></a></div>
  </div>
</section>
