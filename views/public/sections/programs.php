<?php
$courses = Cpt::published('courses');
?>
<section class="section bg-paper" id="programmes">
  <div class="container">
    <div class="section-head is-center">
      <div>
        <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
        <h2><?= Html::e($c['heading'] ?? '') ?></h2>
        <p class="lede"><?= Html::e($c['lede'] ?? '') ?></p>
      </div>
    </div>
    <?php if (!empty($c['split_left_title'])): ?>
    <div class="program-split">
      <div class="program-split__item">
        <span><?= Html::e($c['split_left_kicker'] ?? '') ?></span>
        <strong><?= Html::e($c['split_left_title']) ?></strong>
        <p><?= Html::e($c['split_left_text'] ?? '') ?></p>
      </div>
      <div class="program-split__item">
        <span><?= Html::e($c['split_right_kicker'] ?? '') ?></span>
        <strong><?= Html::e($c['split_right_title'] ?? '') ?></strong>
        <p><?= Html::e($c['split_right_text'] ?? '') ?></p>
      </div>
    </div>
    <?php endif; ?>
    <div class="program-grid">
      <?php foreach ($courses as $co): ?>
        <a class="program reveal" href="<?= Html::e(Cpt::permalink($co)) ?>">
          <div class="program__media">
            <img src="<?= Html::e($co['featured_image'] ?: (string) Cpt::field($co, 'image')) ?>" alt="" loading="lazy">
            <span class="program__tag"><?= Html::e((string) (Cpt::field($co, 'stream') ?: 'Programme')) ?></span>
          </div>
          <div class="program__body">
            <h3><?= Html::e($co['title']) ?></h3>
            <p><?= Html::e($co['excerpt'] ?: (string) Cpt::field($co, 'summary')) ?></p>
            <span class="apply-chip apply-chip--program">View programme <span class="apply-chip__mark">↗</span></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
