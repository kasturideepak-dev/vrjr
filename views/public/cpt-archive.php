<?php
require ROOT . '/views/public/_start.php';
?>
<section class="page-hero">
  <img class="page-hero__photo" src="<?= $asset ?>img/banner/hero-class.jpg" alt="">
  <div class="page-hero__shade"></div>
  <div class="container page-hero__inner">
    <div class="page-hero__copy">
      <nav class="crumbs" aria-label="Breadcrumb"><a href="/">Home</a><span>/</span><span><?= Html::e($type['archive_title'] ?: $type['name']) ?></span></nav>
      <p class="hero-kicker"><?= Html::e($type['name']) ?></p>
      <h1><?= Html::e($type['archive_title'] ?: $type['name']) ?></h1>
      <?php if (!empty($type['archive_intro'])): ?><p class="hero-lead"><?= Html::e($type['archive_intro']) ?></p><?php endif; ?>
    </div>
  </div>
</section>
<section class="section bg-paper">
  <div class="container">
    <div class="program-grid">
      <?php foreach ($entries as $co): ?>
        <a class="program reveal" href="<?= Html::e(Cpt::permalink($co, $type)) ?>">
          <div class="program__media">
            <img src="<?= Html::e($co['featured_image'] ?: $asset . 'img/banner/hero-class.jpg') ?>" alt="" loading="lazy">
            <span class="program__tag"><?= Html::e((string) (Cpt::field($co, 'stream') ?: $type['singular_name'])) ?></span>
          </div>
          <div class="program__body">
            <h3><?= Html::e($co['title']) ?></h3>
            <p><?= Html::e($co['excerpt'] ?: (string) Cpt::field($co, 'summary')) ?></p>
            <span class="apply-chip apply-chip--program">View <?= Html::e($type['singular_name']) ?> <span class="apply-chip__mark">↗</span></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require ROOT . '/views/public/_end.php'; ?>
