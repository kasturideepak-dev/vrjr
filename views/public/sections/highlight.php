<?php $items = Html::lines($c['items'] ?? ''); ?>
<section class="section<?= ($c['bg'] ?? '') === 'paper' ? ' bg-paper' : '' ?>">
  <div class="container">
    <?php if (!empty($c['kicker']) || !empty($c['heading'])): ?>
      <div class="section-head is-center">
        <div>
          <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
          <?php if (!empty($c['heading'])): ?><h2><?= Html::e($c['heading']) ?></h2><?php endif; ?>
        </div>
      </div>
    <?php endif; ?>
    <div class="program-split program-split--compare">
      <?php foreach ($items as $i => $item): ?>
        <div class="program-split__item is-on">
          <?php if (!empty($item[3])): ?>
            <div class="program-split__media"><img src="<?= Html::e($item[3]) ?>" alt="" width="1222" height="687" loading="lazy"></div>
          <?php endif; ?>
          <div class="program-split__body">
            <span><?= Html::e($item[0] ?? '') ?></span>
            <strong><?= Html::e($item[1] ?? '') ?></strong>
            <?php if (!empty($item[2])): ?><p><?= Html::e($item[2]) ?></p><?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
