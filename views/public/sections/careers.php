<?php $cards = Html::lines($c['cards'] ?? ''); ?>
<section class="section<?= ($c['bg'] ?? 'white') === 'paper' ? ' bg-paper' : '' ?>">
  <div class="container">
    <div class="section-head">
      <div>
        <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
        <h2><?= Html::e($c['heading'] ?? '') ?></h2>
        <?php if (!empty($c['lede'])): ?><p class="lede"><?= Html::e($c['lede']) ?></p><?php endif; ?>
      </div>
    </div>
    <div class="career-grid<?= count($cards) >= 7 ? ' career-grid--4' : '' ?>">
      <?php foreach ($cards as $card): ?>
        <article class="career-card">
          <?php if (!empty($card[2])): ?>
            <div class="career-card__media"><img src="<?= Html::e($card[2]) ?>" alt="" width="1222" height="687" loading="lazy"></div>
          <?php endif; ?>
          <div class="career-card__body">
            <h3><?= Html::e($card[0] ?? '') ?></h3>
            <p><?= Html::e($card[1] ?? '') ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
