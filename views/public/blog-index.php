<?php
$seo = $seo ?? ['seo_title' => 'Blog | VR Junior College'];
require ROOT . '/views/public/_start.php';
$cat = $cat ?? null;
$pageNum = $pageNum ?? 1;
$totalPages = $totalPages ?? 1;
$canonicalPath = $canonicalPath ?? '/blog/';
?>
<section class="page-hero">
  <img class="page-hero__photo" src="<?= $asset ?>img/gallery/g6.jpg" alt="">
  <div class="page-hero__shade"></div>
  <div class="container page-hero__inner">
    <div class="page-hero__copy">
    <nav class="crumbs"><a href="/">Home</a><span>/</span><?php if ($cat): ?><a href="/blog/">Blog</a><span>/</span><span><?= Html::e($cat['name']) ?></span><?php else: ?><span>Blog</span><?php endif; ?></nav>
    <p class="hero-kicker">Insights</p>
    <h1><?= Html::e($cat['name'] ?? 'Blog') ?></h1>
    <p class="hero-lead"><?= Html::e($cat['description'] ?? 'Education, NEET and IIT-JEE preparation notes from VR Junior College, Hyderabad.') ?></p>
    </div>
  </div>
</section>
<section class="section">
  <div class="container">
    <?php if (!empty($categories)): ?>
      <p class="lede">
        <a href="/blog/">All</a>
        <?php foreach ($categories as $c): ?>
          · <a href="/blog/category/<?= Html::e($c['slug']) ?>/"><?= Html::e($c['name']) ?></a>
        <?php endforeach; ?>
      </p>
    <?php endif; ?>
    <div class="blog-grid">
      <?php if (empty($posts)): ?>
        <p class="lede">No articles yet.</p>
      <?php endif; ?>
      <?php foreach ($posts as $p): ?>
        <a class="blog-card" href="/blog/<?= Html::e($p['slug']) ?>/">
          <div class="blog-card__media"><img src="<?= Html::e($p['featured_image'] ?: $asset . 'img/gallery/g6.jpg') ?>" alt="<?= Html::e($p['title']) ?>" loading="lazy"></div>
          <div class="blog-card__body">
            <?php if (!empty($p['published_at'])): ?><time datetime="<?= Html::e(substr($p['published_at'], 0, 10)) ?>"><?= Html::e(date('j M Y', strtotime($p['published_at']))) ?></time><?php endif; ?>
            <h3><?= Html::e($p['title']) ?></h3>
            <p><?= Html::e($p['excerpt']) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if ($totalPages > 1): ?>
      <nav class="crumbs" aria-label="Pagination" style="margin-top:24px">
        <?php for ($i = 1; $i <= $totalPages; $i++):
          $href = $i === 1 ? $canonicalPath : rtrim($canonicalPath, '/') . '/page/' . $i . '/';
        ?>
          <?php if ($i === $pageNum): ?><span><?= $i ?></span>
          <?php else: ?><a href="<?= Html::e($href) ?>"><?= $i ?></a><?php endif; ?>
        <?php endfor; ?>
      </nav>
    <?php endif; ?>
  </div>
</section>
<?php require ROOT . '/views/public/_end.php'; ?>
