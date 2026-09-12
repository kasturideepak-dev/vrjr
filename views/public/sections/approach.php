<?php $steps = Html::lines($c['steps'] ?? ''); ?>
<section class="path-band">
  <div class="container">
    <p class="path-band__lead"><?= Html::e($c['lead'] ?? '') ?></p>
    <div class="path-pills" data-path-pills role="radiogroup">
      <?php foreach ($steps as $i => $st): ?>
        <button type="button" class="path-pill <?= $i === 0 ? 'is-on' : '' ?>" role="radio" aria-checked="<?= $i === 0 ? 'true' : 'false' ?>">
          <span class="path-pill__num"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <strong><?= Html::e($st[0] ?? '') ?></strong>
          <em><?= Html::e($st[1] ?? '') ?></em>
        </button>
      <?php endforeach; ?>
    </div>
  </div>
</section>
