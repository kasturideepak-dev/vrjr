<?php
$steps = $c['steps'] ?? [];
?>
<section class="section journey" id="journey">
  <div class="container">
    <div class="section-head is-center">
      <div>
        <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
        <h2><?= Html::e($c['heading'] ?? '') ?></h2>
        <?php if (!empty($c['lede'])): ?><p class="lede"><?= Html::e($c['lede']) ?></p><?php endif; ?>
      </div>
    </div>

    <?php if ($steps): ?>
    <div class="journey-track" data-journey>
      <?php foreach ($steps as $i => $s):
        $color = $s['color'] ?? '#2f80ed';
        $items = $s['items'] ?? [];
      ?>
        <div class="jstep" style="--jc: <?= Html::e($color) ?>; --fd: <?= number_format($i * 0.35, 2) ?>s">
          <?php $illus = $s['img'] ?? ('/uploads/2026/09/journey/step-' . ($i + 1) . '.webp'); ?>
          <div class="jstep__illus"><img src="<?= Html::e($illus) ?>" alt="" loading="lazy"></div>
          <div class="jstep__top">
            <span class="jstep__num"><?= Html::e($s['num'] ?? sprintf('%02d', $i + 1)) ?></span>
          </div>
          <div class="jstep__bar"><span><?= Html::e($s['title'] ?? '') ?></span></div>
          <?php if ($items): ?>
            <ul class="jstep__list">
              <?php foreach ($items as $it): ?><li><?= Html::e($it) ?></li><?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
