<?php $rows = Database::all('SELECT * FROM testimonials WHERE is_visible=1 ORDER BY sort_order, id'); ?>
<section class="section section--tight reviews-sec" id="testimonials">
  <div class="container reviews">
    <div class="reviews__rail">
      <p class="reviews__label"><?= Html::e($c['label'] ?? 'Testimonials') ?></p>
      <a class="apply-chip" href="https://wa.me/<?= Html::e($s['whatsapp'] ?? '15559412484') ?>" rel="noopener">Let’s chat <span class="apply-chip__mark">↗</span></a>
      <div class="reviews__nav">
        <button type="button" class="reviews__btn" data-rev-prev aria-label="Previous"></button>
        <button type="button" class="reviews__btn" data-rev-next aria-label="Next"></button>
      </div>
    </div>
    <div class="reviews__viewport" data-reviews>
      <div class="reviews__track">
        <?php foreach ($rows as $r): ?>
          <blockquote class="rev-card">
            <header class="rev-card__who"><span class="rev-card__ava"><?= Html::e($r['initials'] ?: substr($r['name'], 0, 2)) ?></span><div><strong><?= Html::e($r['name']) ?></strong><span><?= Html::e($r['role']) ?></span></div></header>
            <p><?= Html::e($r['quote']) ?></p>
          </blockquote>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>
