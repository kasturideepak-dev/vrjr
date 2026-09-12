<?php $items = Html::lines($c['items'] ?? ''); ?>
<section class="camp-proof">
  <div class="container camp-proof__grid">
    <?php foreach ($items as $i => $item): ?>
      <article>
        <span><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
        <h3><?= Html::e($item[0] ?? '') ?></h3>
        <?php if (!empty($item[1])): ?><p><?= Html::e($item[1]) ?></p><?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>
</section>
