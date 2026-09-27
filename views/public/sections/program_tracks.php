<?php
$tracks = $c['tracks'] ?? [];
$steps = $c['steps'] ?? [];
$notes = $c['notes'] ?? [];

$icons = [
  'focus' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.6" fill="currentColor" stroke="none"/></svg>',
  'approach' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18h6M10 21h4"/><path d="M12 3a6 6 0 0 0-3.8 10.6c.6.5.8 1 .8 1.9h6c0-.9.2-1.4.8-1.9A6 6 0 0 0 12 3z"/></svg>',
  'goal' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16"/><rect x="5" y="12" width="3" height="6" rx="1"/><rect x="10.5" y="8" width="3" height="10" rx="1"/><rect x="16" y="4" width="3" height="14" rx="1"/></svg>',
];
?>
<section class="section bg-paper" id="tracks">
  <div class="container">
    <div class="section-head is-center">
      <div>
        <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
        <h2><?= Html::e($c['heading'] ?? '') ?></h2>
        <?php if (!empty($c['lede'])): ?><p class="lede"><?= Html::e($c['lede']) ?></p><?php endif; ?>
      </div>
    </div>

    <?php if ($tracks): ?>
    <div class="tracks-band">
      <?php
      $spark = '<svg class="dec-spark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2v5M12 17v5M2 12h5M17 12h5M5 5l3 3M16 16l3 3"/></svg>';
      $decor = [
        'vision' => '<svg class="dec dec-cap" viewBox="0 0 64 48" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linejoin="round"><path d="M4 16 32 6l28 10-28 10z"/><path d="M16 22v12c0 4 7 8 16 8s16-4 16-8V22"/><path d="M60 16v13"/></svg>' . $spark,
        'future' => '<svg class="dec dec-target" viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="30" cy="34" r="24"/><circle cx="30" cy="34" r="15"/><circle cx="30" cy="34" r="6"/><path d="M44 20 58 6M58 6h-9M58 6v9" stroke-linecap="round" stroke-linejoin="round"/></svg><svg class="dec dec-chart" viewBox="0 0 64 44" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 38 24 22l12 9L58 8"/><path d="M58 8h-9M58 8v9"/></svg>' . $spark,
      ];
      ?>
      <?php foreach ($tracks as $i => $t): $mod = $i === 0 ? 'vision' : 'future'; ?>
        <div class="tband tband--<?= $mod ?>">
          <div class="tband__decor" aria-hidden="true"><?= $decor[$mod] ?></div>
          <span class="tband__badge"><?php $nm = (string) ($t['name'] ?? ''); if (preg_match('/^(.*?)(\s+\S+)$/', $nm, $mm)) { echo Html::e(trim($mm[1])) . ' <b>' . Html::e(trim($mm[2])) . '</b>'; } else { echo Html::e($nm); } ?></span>
          <?php if (!empty($t['tagline'])): ?>
            <h3 class="tband__title"><?= Html::e($t['tagline']) ?><?php if (!empty($t['tagline_accent'])): ?> <span><?= Html::e($t['tagline_accent']) ?></span><?php endif; ?></h3>
          <?php endif; ?>
          <?php if (!empty($t['intro'])): ?><p class="tband__desc"><?= Html::e($t['intro']) ?></p><?php endif; ?>
          <ul class="tband__list">
            <?php foreach (['focus'=>'Focus','approach'=>'Approach','goal'=>'Goal'] as $k => $lab): ?>
              <?php if (!empty($t[$k])): ?>
                <li>
                  <span class="tband__ico"><?= $icons[$k] ?></span>
                  <div><strong><?= $lab ?></strong><p><?= Html::e($t[$k]) ?></p></div>
                </li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($steps): ?>
    <div class="tracks-flow">
      <?php if (!empty($c['steps_heading'])): ?><p class="tracks-flow__title"><?= Html::e($c['steps_heading']) ?></p><?php endif; ?>
      <ol class="flow">
        <?php foreach ($steps as $s): ?><li><?= Html::e($s) ?></li><?php endforeach; ?>
      </ol>
    </div>
    <?php endif; ?>

    <?php if ($notes): ?>
    <p class="tracks-notes">
      <?php foreach ($notes as $i => $n): ?><?php if ($i): ?><span aria-hidden="true">•</span><?php endif; ?><strong><?= Html::e($n) ?></strong><?php endforeach; ?>
    </p>
    <?php endif; ?>
  </div>
</section>
