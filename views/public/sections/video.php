<section class="section" id="video">
  <div class="container">
    <?php if (!empty($c['heading'])): ?><div class="section-head is-center"><div><h2><?= Html::e($c['heading']) ?></h2></div></div><?php endif; ?>
    <a class="why-video" href="<?= Html::e($c['video_url'] ?? '#') ?>" rel="noopener" target="_blank">
      <img src="<?= Html::e($c['image'] ?? $asset . 'img/banner/why-video.jpg') ?>" alt="" loading="lazy">
      <span class="why-video__play"><svg><use href="#i-play"></use></svg></span>
    </a>
  </div>
</section>
