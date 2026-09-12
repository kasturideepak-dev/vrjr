<?php
$msg = $message ?? 'Thank you. Our admissions team will be in touch shortly.';
$seo = $seo ?? [
    'seo_title' => 'Thank you | VR Junior College',
    'robots' => 'noindex,follow',
];
require ROOT . '/views/public/_start.php';
?>
<section class="page-hero page-hero--story">
  <img class="page-hero__photo" src="<?= $asset ?>img/campus/bowrampet.jpg" alt="">
  <div class="page-hero__shade"></div>
  <div class="container page-hero__inner">
    <div class="page-hero__copy">
      <p class="hero-kicker">Enquiry received</p>
      <h1>Thank you</h1>
      <p class="hero-lead"><?= Html::e($msg) ?></p>
      <div class="page-hero__actions">
        <a class="apply-chip apply-chip--hero" href="/">Back to home <span class="apply-chip__mark">↗</span></a>
        <a class="apply-chip apply-chip--accent" href="tel:+<?= Html::e($phone1tel) ?>">Call <?= Html::e($phone1) ?></a>
      </div>
    </div>
  </div>
</section>
<section class="section">
  <div class="container thank-next">
    <h2>What happens next</h2>
    <p class="lede">A counsellor will call you on the number you shared. If you need an answer right away, call or WhatsApp admissions.</p>
    <p>
      <a class="apply-chip" href="tel:+<?= Html::e($phone1tel) ?>"><?= Html::e($phone1) ?></a>
      <a class="apply-chip" href="tel:+<?= Html::e($phone2tel) ?>"><?= Html::e($phone2) ?></a>
      <a class="apply-chip" href="/contact-us/">Contact us</a>
    </p>
  </div>
</section>
<?php require ROOT . '/views/public/_end.php'; ?>
