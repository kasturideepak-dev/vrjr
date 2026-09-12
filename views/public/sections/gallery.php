<?php $imgs = array_filter(array_map('trim', preg_split("/\r\n|\n/", $c['images'] ?? '') ?: [])); ?>
<section class="section">
  <div class="container">
    <div class="gallery-grid">
      <?php foreach ($imgs as $src): ?>
        <a href="<?= Html::e($src) ?>" data-lightbox-src="<?= Html::e($src) ?>" data-alt=""><img src="<?= Html::e($src) ?>" alt="" loading="lazy"></a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
