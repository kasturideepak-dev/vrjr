<?php $people = Cpt::published('faculty'); ?>
<section class="section" id="faculty">
  <div class="container">
    <div class="section-head">
      <div><?php if (!empty($c['kicker'])): ?><p class="eyebrow"><?= Html::e($c['kicker']) ?></p><?php endif; ?><h2><?= Html::e($c['heading'] ?? '') ?></h2></div>
      <a class="btn btn--ghost" href="/team-details/">See our experts</a>
    </div>
    <div class="faculty-slider" data-faculty-slider>
      <div class="faculty-track">
        <?php foreach ($people as $p): ?>
          <figure class="faculty"><img src="<?= Html::e($p['featured_image'] ?: (string) Cpt::field($p, 'image')) ?>" alt="<?= Html::e($p['title']) ?>" loading="lazy"><figcaption><h3><?= Html::e($p['title']) ?></h3><p><?= Html::e((string) Cpt::field($p, 'designation')) ?><?= Cpt::field($p, 'experience') ? ' · ' . Html::e((string) Cpt::field($p, 'experience')) : '' ?></p></figcaption></figure>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
