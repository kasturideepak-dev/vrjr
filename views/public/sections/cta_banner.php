<section class="cta-banner">
  <img class="cta-banner__photo" src="<?= Html::e($c['image'] ?? $asset . 'img/banner/hero-ceremony.jpg') ?>" alt="">
  <div class="cta-banner__shade"></div>
  <div class="container">
    <div class="cta-banner__copy">
      <?php if (!empty($c['kicker'])): ?><p class="cta-banner__kicker"><?= Html::e($c['kicker']) ?></p><?php endif; ?>
      <h2><?= Html::e($c['heading'] ?? '') ?></h2>
      <p><?= Html::e($c['text'] ?? '') ?></p>
      <div class="cta-banner__actions">
        <?php if (!empty($c['cta_label'])):
          $ctaUrl = $c['cta_url'] ?? '#';
          $ext = str_starts_with($ctaUrl, 'http');
        ?>
          <a class="apply-chip apply-chip--accent" href="<?= Html::e($ctaUrl) ?>"<?= $ext ? ' rel="noopener" target="_blank"' : '' ?>><?= Html::e($c['cta_label']) ?> <span class="apply-chip__mark">↗</span></a>
        <?php endif; ?>
        <?php if (!empty($c['cta2_label'])):
          $cta2 = $c['cta2_url'] ?? '#';
          $ext2 = str_starts_with($cta2, 'http');
        ?>
          <a class="btn btn--light" href="<?= Html::e($cta2) ?>"<?= $ext2 ? ' rel="noopener" target="_blank"' : '' ?>><?= Html::e($c['cta2_label']) ?></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
