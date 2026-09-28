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
    <div class="journey-wrap">
      <svg class="journey-path" viewBox="0 0 1400 80" preserveAspectRatio="none" aria-hidden="true">
        <defs>
          <linearGradient id="jgrad" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0" stop-color="#e5405e"/>
            <stop offset="0.17" stop-color="#f59e0b"/>
            <stop offset="0.34" stop-color="#17a673"/>
            <stop offset="0.5" stop-color="#7b3fe4"/>
            <stop offset="0.67" stop-color="#2f80ed"/>
            <stop offset="0.83" stop-color="#f2711c"/>
            <stop offset="1" stop-color="#e5405e"/>
          </linearGradient>
        </defs>
        <path class="jp-base" vector-effect="non-scaling-stroke" d="M0,40 Q100,12 200,40 T400,40 T600,40 T800,40 T1000,40 T1200,40 T1400,40"/>
        <path class="jp-fill" pathLength="1" vector-effect="non-scaling-stroke" d="M0,40 Q100,12 200,40 T400,40 T600,40 T800,40 T1000,40 T1200,40 T1400,40"/>
      </svg>
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
    </div>
    <?php endif; ?>
  </div>
</section>
