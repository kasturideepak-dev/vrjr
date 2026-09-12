<?php
$seo = ['seo_title' => 'Page not found | VR Junior College', 'robots' => 'noindex,follow'];
require ROOT . '/views/public/_start.php';
?>
<section class="page-hero">
  <img class="page-hero__photo" src="<?= $asset ?>img/campus/bowrampet.jpg" alt="">
  <div class="page-hero__shade"></div>
  <div class="container page-hero__inner">
    <div class="page-hero__copy">
    <p class="hero-kicker">404</p>
    <h1>Page not found</h1>
    <p class="hero-lead">That address isn’t on this site. Head home or talk to admissions.</p>
    <p><a class="apply-chip apply-chip--hero" href="/">Back to home <span class="apply-chip__mark">↗</span></a></p>
    </div>
  </div>
</section>
<?php require ROOT . '/views/public/_end.php'; ?>
