<?php $items = array_filter(array_map('trim', preg_split("/\r\n|\n/", $c['items'] ?? '') ?: [])); ?>
<div class="hero-ticker" aria-hidden="true">
  <div class="hero-ticker__track">
    <?php foreach (array_merge($items, $items) as $it): ?><span><?= Html::e($it) ?></span><?php endforeach; ?>
  </div>
</div>
