<?php
$visionItems = Html::lines($c['vision_items'] ?? '');
$missionItems = Html::lines($c['mission_items'] ?? '');
?>
<section class="section vision-band">
  <div class="container">
    <div class="section-head is-center">
      <div>
        <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
        <h2><?= Html::e($c['heading'] ?? 'Our vision and mission') ?></h2>
        <?php if (!empty($c['lede'])): ?><p class="lede"><?= Html::e($c['lede']) ?></p><?php endif; ?>
      </div>
    </div>
    <div class="vision-grid">
      <article class="vision-card vision-card--navy">
        <span><?= Html::e($c['vision_kicker'] ?? 'Vision') ?></span>
        <h3><?= Html::e($c['vision_title'] ?? 'Our Vision') ?></h3>
        <?php if (!empty($c['vision_text'])): ?><p><?= Html::e($c['vision_text']) ?></p><?php endif; ?>
        <?php if ($visionItems): ?>
          <ul>
            <?php foreach ($visionItems as $item): ?>
              <li><?= Html::e($item[0] ?? '') ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </article>
      <article class="vision-card">
        <span><?= Html::e($c['mission_kicker'] ?? 'Mission') ?></span>
        <h3><?= Html::e($c['mission_title'] ?? 'Our Mission') ?></h3>
        <?php if (!empty($c['mission_text'])): ?><p><?= Html::e($c['mission_text']) ?></p><?php endif; ?>
        <?php if ($missionItems): ?>
          <ul>
            <?php foreach ($missionItems as $item): ?>
              <li><?= Html::e($item[0] ?? '') ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </article>
    </div>
  </div>
</section>

