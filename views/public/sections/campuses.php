<?php $rows = Cpt::published('campus'); ?>
<section class="section" id="campuses">
  <div class="container">
    <div class="section-head is-center">
      <div>
        <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
        <h2><?= Html::e(($c['heading'] ?? '') !== '' ? $c['heading'] : 'Our campuses') ?></h2>
      </div>
    </div>
    <div class="campus-grid">
      <?php foreach ($rows as $r):
        $addr = (string) Cpt::field($r, 'address');
        $embed = trim((string) Cpt::field($r, 'map_embed'));
        $maps = $embed !== '' && str_starts_with($embed, 'http')
            ? $embed
            : 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode('VR Junior College, ' . ($addr !== '' ? $addr : $r['title']) . ', Hyderabad');
      ?>
        <a class="campus-card" href="<?= Html::e($maps) ?>" rel="noopener" target="_blank">
          <img src="<?= Html::e($r['featured_image'] ?: (string) Cpt::field($r, 'image')) ?>" alt="<?= Html::e($r['title']) ?>" loading="lazy">
          <div class="campus-card__body">
            <h3><?= Html::e($r['title']) ?></h3>
            <?php if ($addr !== ''): ?><p><?= Html::e($addr) ?></p><?php endif; ?>
            <span class="campus-card__maps">View on Maps ↗</span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if (!empty($c['note'])): ?><p class="campus-note"><?= Html::e($c['note']) ?></p><?php endif; ?>
  </div>
</section>

