<?php
$steps = Html::lines($c['steps'] ?? '');
$bg = $c['bg'] ?? 'paper';
$secClass = 'section process-sec';
if ($bg === 'navy') {
    $secClass .= ' process-sec--navy';
} elseif ($bg !== 'white') {
    $secClass .= ' bg-paper';
}
?>
<section class="<?= $secClass ?>">
  <?php if ($bg === 'navy'): ?><div class="process-sec__bg" aria-hidden="true" data-parallax></div><?php endif; ?>
  <div class="container">
    <div class="section-head is-center">
      <div>
        <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
        <h2><?= Html::e($c['heading'] ?? '') ?></h2>
        <?php if (!empty($c['lede'])): ?><p class="lede"><?= Html::e($c['lede']) ?></p><?php endif; ?>
      </div>
    </div>
    <ol class="process-row">
      <?php foreach ($steps as $i => $step): ?>
        <li>
          <span><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <strong><?= Html::e($step[0] ?? '') ?></strong>
          <?php if (!empty($step[1])): ?><p><?= Html::e($step[1]) ?></p><?php endif; ?>
        </li>
      <?php endforeach; ?>
    </ol>
    <?php if (!empty($c['note'])): ?><p class="process-note"><?= Html::e($c['note']) ?></p><?php endif; ?>
  </div>
</section>
