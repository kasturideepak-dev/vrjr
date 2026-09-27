<?php
$tracks = $c['tracks'] ?? [];
$steps = $c['steps'] ?? [];
$notes = $c['notes'] ?? [];
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
    <div class="tracks">
      <?php foreach ($tracks as $i => $t): ?>
        <article class="track track--<?= $i === 0 ? 'a' : 'b' ?>">
          <span class="track__badge"><?= Html::e($t['name'] ?? '') ?></span>
          <?php if (!empty($t['tagline'])): ?><h3 class="track__tagline"><?= Html::e($t['tagline']) ?></h3><?php endif; ?>
          <?php if (!empty($t['intro'])): ?><p class="track__intro"><?= Html::e($t['intro']) ?></p><?php endif; ?>
          <dl class="track__meta">
            <?php foreach (['focus'=>'Focus','approach'=>'Approach','goal'=>'Goal'] as $k => $lab): ?>
              <?php if (!empty($t[$k])): ?><div><dt><?= $lab ?></dt><dd><?= Html::e($t[$k]) ?></dd></div><?php endif; ?>
            <?php endforeach; ?>
          </dl>
        </article>
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
