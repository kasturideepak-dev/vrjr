<section class="campus-life" id="gallery">
  <div class="campus-life__stage">
    <img class="campus-life__bg" src="<?= Html::e($c['bg'] ?? $asset . 'img/banner/campus-life-bg.jpg') ?>" alt="">
    <div class="campus-life__shade"></div>
    <div class="campus-life__intro">
      <h2><?= Html::e($c['heading'] ?? 'Campus Life') ?></h2>
      <a class="apply-chip campus-life__cta" href="<?= Html::e($c['cta_url'] ?? '/gallery/') ?>"><?= Html::e($c['cta_label'] ?? 'View full gallery') ?> <span class="apply-chip__mark">↗</span></a>
    </div>
    <div class="campus-life__board">
      <a class="campus-life__shot" href="/gallery/"><img src="<?= Html::e($c['shot1'] ?? '') ?>" alt="Classroom"><span class="campus-life__cap">Classroom</span></a>
      <a class="campus-life__shot" href="<?= Html::e($c['video_url'] ?? '#') ?>" rel="noopener"><img src="<?= Html::e($c['shot2'] ?? '') ?>" alt="Film"><span class="campus-life__play"><svg><use href="#i-play"></use></svg></span><span class="campus-life__cap">Campus film</span></a>
      <a class="campus-life__shot" href="/gallery/"><img src="<?= Html::e($c['shot3'] ?? '') ?>" alt="Hostel"><span class="campus-life__cap">Hostel</span></a>
    </div>
  </div>
</section>
