<?php
$items = Html::lines($c['items'] ?? '');
$videos = [];
foreach ($items as $row) {
    $url = (string) ($row[2] ?? '');
    $id = Html::youtubeId($url);
    if ($id === '') {
        continue;
    }
    $videos[] = [
        'name' => (string) ($row[0] ?? ''),
        'role' => (string) ($row[1] ?? ''),
        'id' => $id,
    ];
}
if (!$videos) {
    foreach (Database::all('SELECT name, role, video_url FROM testimonials WHERE is_visible=1 AND video_url IS NOT NULL AND video_url <> "" ORDER BY sort_order, id') as $r) {
        $id = Html::youtubeId((string) ($r['video_url'] ?? ''));
        if ($id === '') {
            continue;
        }
        $videos[] = ['name' => (string) $r['name'], 'role' => (string) ($r['role'] ?? ''), 'id' => $id];
    }
}
if (!$videos) {
    return;
}
?>
<section class="section bg-paper" id="video-testimonials">
  <div class="container">
    <div class="section-head is-center">
      <div>
        <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
        <h2><?= Html::e($c['heading'] ?? 'Video testimonials') ?></h2>
        <?php if (!empty($c['lede'])): ?><p class="lede"><?= Html::e($c['lede']) ?></p><?php endif; ?>
      </div>
    </div>
    <div class="vt-grid">
      <?php foreach ($videos as $v): ?>
        <article class="vt-card">
          <button class="vt-card__media" type="button" data-yt="<?= Html::e($v['id']) ?>" aria-label="Play <?= Html::e($v['name'] !== '' ? $v['name'] . ' testimonial' : 'video testimonial') ?>">
            <img src="https://i.ytimg.com/vi/<?= Html::e($v['id']) ?>/hqdefault.jpg" alt="<?= Html::e($v['name']) ?>" loading="lazy" width="480" height="360">
            <span class="vt-card__play" aria-hidden="true"><svg><use href="#i-play"></use></svg></span>
          </button>
          <div class="vt-card__body">
            <?php if ($v['name'] !== ''): ?><strong><?= Html::e($v['name']) ?></strong><?php endif; ?>
            <?php if ($v['role'] !== ''): ?><span><?= Html::e($v['role']) ?></span><?php endif; ?>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
