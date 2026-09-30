<?php
$steps = $c['steps'] ?? [];
$icons = [
  'book' => '<path d="M24 13c-4-3-9.5-3-13.5 0v22c4-3 9.5-3 13.5 0m0-22c4-3 9.5-3 13.5 0v22c-4-3-9.5-3-13.5 0m0-22v22" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linejoin="round"/>',
  'pencil' => '<path d="M29 11l8 8M13 35l-2.5 8.5L19 41 37 23l-8-8-16 20z" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linejoin="round" stroke-linecap="round"/><path d="M27 13l8 8" stroke="currentColor" stroke-width="2.6"/>',
  'timer' => '<circle cx="24" cy="27" r="12" fill="none" stroke="currentColor" stroke-width="2.6"/><path d="M24 27v-7" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/><path d="M20 9h8M24 15V9" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>',
  'magnifier' => '<circle cx="22" cy="22" r="11" fill="none" stroke="currentColor" stroke-width="2.6"/><path d="M30 30l9 9" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/><path d="M17 23l3.5 3.5L27 19" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>',
  'people' => '<circle cx="18" cy="17" r="5" fill="none" stroke="currentColor" stroke-width="2.6"/><circle cx="31" cy="18" r="4.5" fill="none" stroke="currentColor" stroke-width="2.6"/><path d="M9 37c0-6 4-9.5 9-9.5s9 3.5 9 9.5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/><path d="M27.5 28.5c1-.6 2.2-1 3.5-1 5 0 8 3.2 8 9" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round"/>',
  'trophy' => '<path d="M16 11h16v7a8 8 0 0 1-16 0z" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linejoin="round"/><path d="M16 13h-4a4 4 0 0 0 4 4M32 13h4a4 4 0 0 1-4 4" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linejoin="round"/><path d="M21 26v6h6v-6M16 38h16M20 38v-2h8v2" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>',
];
?>
<section class="section methodology" id="methodology">
  <div class="container">
    <div class="section-head is-center">
      <div>
        <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
        <h2><?= Html::e($c['heading'] ?? '') ?></h2>
        <?php if (!empty($c['lede'])): ?><p class="lede"><?= Html::e($c['lede']) ?></p><?php endif; ?>
      </div>
    </div>

    <?php if ($steps): ?>
    <div class="roadmap" data-roadmap>
      <svg class="roadmap__svg" preserveAspectRatio="none" aria-hidden="true">
        <path class="rm-road" pathLength="1" fill="none"/>
        <path class="rm-lane" fill="none"/>
      </svg>
      <?php foreach ($steps as $i => $s):
        $side = $i % 2 === 0 ? 'left' : 'right';
        $color = $s['color'] ?? ($i % 2 === 0 ? '#e87028' : '#12305a');
        $ic = $icons[$s['icon'] ?? ''] ?? $icons['book'];
      ?>
        <div class="rstep rstep--<?= $side ?> reveal" style="--rc: <?= Html::e($color) ?>; --d: <?= number_format($i * 0.05, 2) ?>s">
          <div class="rstep__pair">
            <div class="rstep__card">
              <h3><span class="rstep__key"><?= Html::e($s['key'] ?? '') ?>:</span> <?= Html::e($s['title'] ?? '') ?></h3>
              <p><?= Html::e($s['desc'] ?? '') ?></p>
            </div>
            <div class="rstep__node">
              <span class="rstep__ico"><svg viewBox="0 0 48 48" role="img" aria-hidden="true"><?= $ic ?></svg></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>
