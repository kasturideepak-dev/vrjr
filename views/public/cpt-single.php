<?php
if (!empty($sections)) {
    require ROOT . '/views/public/page.php';
    return;
}
require ROOT . '/views/public/_start.php';
$img = $entry['featured_image'] ?: $asset . 'img/banner/hero-class.jpg';
?>
<section class="page-hero">
  <img class="page-hero__photo" src="<?= Html::e($img) ?>" alt="">
  <div class="page-hero__shade"></div>
  <div class="container page-hero__inner">
    <div class="page-hero__copy">
      <nav class="crumbs" aria-label="Breadcrumb">
        <a href="/">Home</a><span>/</span>
        <?php if ((int) $type['has_archive']): ?><a href="<?= Html::e(Cpt::archiveUrl($type)) ?>"><?= Html::e($type['name']) ?></a><span>/</span><?php endif; ?>
        <span><?= Html::e($entry['title']) ?></span>
      </nav>
      <p class="hero-kicker"><?= Html::e($type['singular_name']) ?></p>
      <h1><?= Html::e($entry['title']) ?></h1>
      <?php if (!empty($entry['excerpt'])): ?><p class="hero-lead"><?= Html::e($entry['excerpt']) ?></p><?php endif; ?>
    </div>
  </div>
</section>
<section class="section">
  <div class="container article-wrap">
    <article class="prose">
      <?= Html::allowedHtml($entry['body_html'] ?? '') ?>
      <?php foreach ($entry['_fields'] ?? [] as $k => $v):
        if ($v === '' || $v === [] || $k === 'image') {
            continue;
        }
      ?>
        <p><strong><?= Html::e(ucwords(str_replace('_', ' ', (string) $k))) ?>:</strong>
          <?= Html::e(is_array($v) ? implode(', ', array_map(fn ($x) => is_array($x) ? implode(' ', $x) : (string) $x, $v)) : (string) $v) ?></p>
      <?php endforeach; ?>
    </article>
  </div>
</section>
<?php
$c = ['heading' => 'Enquire', 'lede' => 'Tell us a little about the student and we’ll help you take the next step.'];
require ROOT . '/views/public/sections/enquire.php';
require ROOT . '/views/public/_end.php';
