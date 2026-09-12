<?php
$cards = Html::lines($c['cards'] ?? '');
$n = count($cards);
$gridMod = ($n === 2 || $n === 4) ? ' feature-grid--2' : '';
$bg = ($c['bg'] ?? 'paper') === 'white' ? '' : ' bg-paper';
?>
<section class="section<?= $bg ?>">
  <div class="container">
    <div class="section-head is-center">
      <div>
        <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
        <h2><?= Html::e($c['heading'] ?? '') ?></h2>
        <?php if (!empty($c['lede'])): ?><p class="lede"><?= Html::e($c['lede']) ?></p><?php endif; ?>
      </div>
    </div>
    <div class="feature-grid<?= $gridMod ?>">
      <?php foreach ($cards as $i => $card):
        if (count($card) >= 3) {
            $kicker = $card[0] ?? '';
            $title = $card[1] ?? '';
            $text = $card[2] ?? '';
        } else {
            $kicker = str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT);
            $title = $card[0] ?? '';
            $text = $card[1] ?? '';
        }
      ?>
        <article class="feature-card"><span><?= Html::e($kicker) ?></span><h3><?= Html::e($title) ?></h3><p><?= Html::e($text) ?></p></article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
