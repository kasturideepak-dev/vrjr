<?php
$seo = $seo ?? [];
$seo['seo_title'] = $seo['seo_title'] ?? ($post['title'] . ' | VR Junior College');
require ROOT . '/views/public/_start.php';
?>
<section class="page-hero page-hero--blog">
  <img class="page-hero__photo" src="<?= Html::e($post['featured_image'] ?: $asset . 'img/gallery/g6.jpg') ?>" alt="">
  <div class="page-hero__shade"></div>
  <div class="container page-hero__inner">
    <div class="page-hero__copy">
    <nav class="crumbs"><a href="/">Home</a><span>/</span><a href="/blog/">Blog</a><span>/</span><span>Article</span></nav>
    <p class="hero-kicker">Blog</p>
    <h1><?= Html::e($post['title']) ?></h1>
    <p class="hero-lead"><?= Html::e($post['excerpt']) ?></p>
    </div>
  </div>
</section>
<section class="section">
  <div class="container article-wrap">
    <article class="prose">
      <p class="article-meta"><?= $post['published_at'] ? Html::e(date('j M Y', strtotime($post['published_at']))) : '' ?> · <?= Html::e($post['author_name'] ?? 'VR Junior College') ?></p>
      <?= Html::allowedHtml($post['body_html'] ?? '') ?>
    </article>
    <aside class="article-side">
      <h2>More from the blog</h2>
      <ul><?php foreach ($related as $r): ?><li><a href="/blog/<?= Html::e($r['slug']) ?>/"><?= Html::e($r['title']) ?></a></li><?php endforeach; ?></ul>
    </aside>
  </div>
</section>
<?php require ROOT . '/views/public/_end.php'; ?>
