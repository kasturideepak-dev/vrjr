<?php $items = Html::lines($c['items'] ?? ''); ?>
<section class="stat-band">
  <div class="container stat-band__grid">
    <?php foreach ($items as $it): ?>
      <div><strong><?= Html::e($it[0] ?? '') ?></strong><span><?= Html::e($it[1] ?? '') ?></span></div>
    <?php endforeach; ?>
  </div>
</section>
