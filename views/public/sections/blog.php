<?php $posts = Database::all('SELECT title, slug, excerpt, featured_image, published_at FROM blog_posts WHERE status="published" AND deleted_at IS NULL ORDER BY published_at DESC LIMIT 4'); ?>
<section class="section bg-paper" id="blog">
  <div class="container">
    <div class="section-head">
      <div><?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?><h2><?= Html::e($c['heading'] ?? 'Latest from the blog') ?></h2></div>
      <a class="apply-chip apply-chip--accent" href="/blog/">View all articles <span class="apply-chip__mark">↗</span></a>
    </div>
    <div class="blog-grid blog-grid--home">
      <?php foreach ($posts as $p): ?>
        <a class="blog-card" href="/blog/<?= Html::e($p['slug']) ?>/">
          <div class="blog-card__media"><img src="<?= Html::e($p['featured_image'] ?: $asset . 'img/gallery/g6.jpg') ?>" alt="<?= Html::e($p['title']) ?>" loading="lazy"></div>
          <div class="blog-card__body">
            <?php if ($p['published_at']): ?><time datetime="<?= Html::e(substr($p['published_at'], 0, 10)) ?>"><?= Html::e(date('j M Y', strtotime($p['published_at']))) ?></time><?php endif; ?>
            <h3><?= Html::e($p['title']) ?></h3>
            <p><?= Html::e($p['excerpt']) ?></p>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
