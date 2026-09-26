<?php
$stats = Html::lines($c['stats'] ?? '');
$icons = ['i-people','i-medal','i-learn','i-star'];
$splitLines = static function (string $raw): array {
    $out = [];
    foreach (preg_split("/\r\n|\n|\r/", $raw) ?: [] as $line) {
        $line = trim($line);
        if ($line !== '') {
            $out[] = $line;
        }
    }
    return $out;
};

$hideBg = !empty($c['hide_background']);
$slides = $splitLines((string) ($c['slides'] ?? ''));
if (!$slides && !empty($c['image'])) {
    $slides = [(string) $c['image']];
}
$rotate = $splitLines((string) ($c['rotate'] ?? ''));
$rotateLabel = trim((string) ($c['rotate_label'] ?? ''));
$highlight = trim((string) ($c['heading_highlight'] ?? ''));
$heading = (string) ($c['heading'] ?? '');

$liveSlides = [
    $asset . 'img/banner/slides/slide-1.webp',
    $asset . 'img/banner/slides/slide-2.webp',
    $asset . 'img/banner/slides/slide-3.webp',
    $asset . 'img/banner/slides/slide-4.webp',
    $asset . 'img/banner/slides/slide-5.webp',
];
$liveRotate = [
    'Focused Learning Journey',
    'Exceptional Academic Results',
    'Integrated Coaching Programs',
    'Dedicated Expert Mentors',
    'Personalized Learning Experience',
    'Advanced Performance Tracking',
    'Separate Residential Campuses',
    'Consistent Proven Success',
    'Future-Ready Education',
    'Excellence Through Learning',
    'Smart Learning Systems',
    'Achieve Academic Excellence',
];
$isHome = class_exists('Request') && Request::path() === '/';
if ($isHome) {
    if (!$slides && !$hideBg) {
        $slides = $liveSlides;
    }
    if (!$rotate) {
        $rotate = $liveRotate;
    }
    if ($rotateLabel === '') {
        $rotateLabel = 'We Promise...';
    }
    if ($highlight === '') {
        $highlight = 'Doctors & Engineers';
    }
    if ($heading === '') {
        $heading = 'Where Doctors & Engineers Future Begin Their Journey';
    }
    if (trim((string) ($c['lead'] ?? '')) === '') {
        $c['lead'] = 'At VR Junior College, we combine Intermediate education with expert IIT-JEE and NEET coaching to help students achieve their dreams. With experienced faculty, personal mentorship, and a disciplined residential environment, we empower every student to excel academically and beyond.';
    }
    if (empty($c['cta2_label'])) {
        $c['cta2_label'] = 'Book a campus visit';
        $c['cta2_url'] = '/contact-us/';
    }
}
if (!$slides && !$hideBg) {
    $slides = [$asset . 'img/banner/hero-class.jpg'];
}

$headingHtml = Html::e($heading);
if ($highlight !== '' && $heading !== '') {
    $pos = mb_stripos($heading, $highlight);
    if ($pos !== false) {
        $len = mb_strlen($highlight);
        $headingHtml = Html::e(mb_substr($heading, 0, $pos))
            . '<em>' . Html::e(mb_substr($heading, $pos, $len)) . '</em>'
            . Html::e(mb_substr($heading, $pos + $len));
    }
}
?>
<?php
$slideN = count($slides);
$slideHold = 5.0;
$slideFade = 0.6;
$slideTotal = max($slideN, 1) * $slideHold;
$rotN = count($rotate);
$rotHold = 2.5;
$rotFade = 0.45;
$rotTotal = max($rotN, 1) * $rotHold;
$rotSizer = $rotate[0] ?? '';
foreach ($rotate as $phrase) {
    if (mb_strlen($phrase) > mb_strlen($rotSizer)) {
        $rotSizer = $phrase;
    }
}
$pct = static function (float $secs, float $total): string {
    return rtrim(rtrim(number_format(100 * $secs / max($total, 0.01), 3, '.', ''), '0'), '.');
};
?>
<?php if ($rotN > 1): ?>
<style>
<?php if ($rotN > 1): ?>
.hero-rotate .hero-rotate__item {
  animation: vrHeroRotate <?= $rotTotal ?>s infinite;
  animation-fill-mode: both;
}
.hero-rotate .hero-rotate__item:nth-child(1) {
  animation-name: vrHeroRotateFirst;
}
<?php for ($i = 0; $i < $rotN; $i++): ?>
.hero-rotate .hero-rotate__item:nth-child(<?= $i + 1 ?>) { animation-delay: <?= $i * $rotHold ?>s; }
<?php endfor; ?>
@keyframes vrHeroRotate {
  0% { opacity: 0; transform: translateY(0.55em); }
  <?= $pct($rotFade, $rotTotal) ?>% { opacity: 1; transform: translateY(0); }
  <?= $pct($rotHold - $rotFade, $rotTotal) ?>% { opacity: 1; transform: translateY(0); }
  <?= $pct($rotHold, $rotTotal) ?>% { opacity: 0; transform: translateY(-0.55em); }
  100% { opacity: 0; transform: translateY(-0.55em); }
}
@keyframes vrHeroRotateFirst {
  0%, <?= $pct($rotHold - $rotFade, $rotTotal) ?>% { opacity: 1; transform: translateY(0); }
  <?= $pct($rotHold, $rotTotal) ?>%, <?= $pct($rotTotal - $rotFade, $rotTotal) ?>% { opacity: 0; transform: translateY(-0.55em); }
  100% { opacity: 1; transform: translateY(0); }
}
<?php endif; ?>
</style>
<?php endif; ?>
<section class="hero<?= $rotate ? ' hero--rotate' : '' ?><?= $slideN > 1 ? ' hero--slides' : '' ?><?= $hideBg ? ' hero--plain' : '' ?>" id="top">
  <?php if (!$hideBg && $slideN > 0): ?>
  <div class="hero-slides"<?php if ($slideN > 1): ?> data-hero-slides data-srcs="<?= Html::e(implode('|', $slides)) ?>"<?php endif; ?>>
    <img class="hero__photo is-on" src="<?= Html::e($slides[0]) ?>" alt="" width="1600" height="900" fetchpriority="high">
  </div>
  <?php endif; ?>
  <div class="hero__shade"></div>
  <div class="container hero__inner">
    <div class="hero-stage">
      <div class="hero-copy">
        <?php if (!empty($c['kicker'])): ?><p class="hero-kicker"><?= Html::e($c['kicker']) ?></p><?php endif; ?>
        <h1><?= $headingHtml ?></h1>
        <?php if ($rotateLabel !== ''): ?><p class="hero-promise"><?= Html::e($rotateLabel) ?></p><?php endif; ?>
        <?php if ($rotate): ?>
          <p class="hero-rotate" data-hero-rotate>
            <?php foreach ($rotate as $i => $phrase): ?>
              <span class="hero-rotate__item<?= $i === 0 ? ' is-on' : '' ?>"><?= Html::e($phrase) ?></span>
            <?php endforeach; ?>
            <?php if ($rotSizer !== ''): ?><span class="hero-rotate__sizer" aria-hidden="true"><?= Html::e($rotSizer) ?></span><?php endif; ?>
          </p>
        <?php endif; ?>
        <p class="hero-lead"><?= Html::e($c['lead'] ?? '') ?></p>
        <div class="hero-actions">
          <a class="apply-chip apply-chip--hero" href="<?= Html::e($c['cta_url'] ?? '/contact-us/#enquire') ?>"><?= Html::e($c['cta_label'] ?? 'Apply now') ?> <span class="apply-chip__mark">↗</span></a>
          <?php if (!empty($c['cta2_label'])): ?>
            <a class="btn btn--light" href="<?= Html::e($c['cta2_url'] ?? '/contact-us/') ?>"><?= Html::e($c['cta2_label']) ?></a>
          <?php endif; ?>
          <?php if (!empty($c['video_url'])): ?><a class="hero-play" href="<?= Html::e($c['video_url']) ?>" rel="noopener" aria-label="Watch film"><span></span></a><?php endif; ?>
        </div>
      </div>
      <?php if (!empty($c['figure'])): ?>
        <div class="hero-figure"><img src="<?= Html::e($c['figure']) ?>" alt="" width="800" height="1000" fetchpriority="high"></div>
      <?php endif; ?>
    </div>
    <?php if ($stats): ?>
    <aside class="hero-stats">
      <?php foreach ($stats as $i => $st): ?>
        <div class="hero-stats__row">
          <strong><?= Html::e($st[0] ?? '') ?></strong>
          <span><?= Html::e($st[1] ?? '') ?></span>
          <i class="hero-stats__ico" aria-hidden="true"><svg><use href="#<?= $icons[$i] ?? 'i-star' ?>"></use></svg></i>
        </div>
      <?php endforeach; ?>
    </aside>
    <?php endif; ?>
  </div>
</section>
