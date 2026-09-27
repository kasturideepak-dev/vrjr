<?php
$cats = $c['categories'] ?? [];
?>
<section class="section bg-paper" id="explore-careers">
  <div class="container">
    <div class="section-head is-center">
      <div>
        <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
        <h2><?= Html::e($c['heading'] ?? '') ?></h2>
        <?php if (!empty($c['lede'])): ?><p class="lede"><?= Html::e($c['lede']) ?></p><?php endif; ?>
      </div>
    </div>

    <?php if ($cats): ?>
    <div class="career-explorer" data-career-explorer>
      <aside class="ce-fields" role="tablist" aria-label="Field of study">
        <p class="ce-fields__title">Field of study</p>
        <?php foreach ($cats as $ci => $cat): ?>
          <button type="button" class="ce-field<?= $ci === 0 ? ' is-active' : '' ?>" role="tab" aria-selected="<?= $ci === 0 ? 'true' : 'false' ?>" data-ce-cat="<?= (int) $ci ?>"><?= Html::e($cat['label'] ?? '') ?></button>
        <?php endforeach; ?>
      </aside>

      <div class="ce-main">
        <?php foreach ($cats as $ci => $cat): $careers = $cat['careers'] ?? []; ?>
          <div class="ce-panel<?= $ci === 0 ? ' is-active' : '' ?>" data-ce-panel="<?= (int) $ci ?>">
            <div class="ce-careers">
              <p class="ce-careers__title">Career options</p>
              <div class="ce-careers__tabs" role="tablist" aria-label="Career options in <?= Html::e($cat['label'] ?? '') ?>">
                <?php foreach ($careers as $ki => $car): ?>
                  <button type="button" class="ce-career<?= $ki === 0 ? ' is-active' : '' ?>" role="tab" aria-selected="<?= $ki === 0 ? 'true' : 'false' ?>" data-ce-career="<?= (int) $ci ?>-<?= (int) $ki ?>"><?= Html::e($car['name'] ?? '') ?></button>
                <?php endforeach; ?>
              </div>
            </div>

            <?php foreach ($careers as $ki => $car): ?>
              <div class="ce-detail<?= $ki === 0 ? ' is-active' : '' ?>" data-ce-detail="<?= (int) $ci ?>-<?= (int) $ki ?>">
                <h3 class="ce-detail__title"><?= Html::e($car['name'] ?? '') ?></h3>
                <div class="ce-detail__grid">
                  <?php if (!empty($car['duration'])): ?><div class="ce-stat"><span>Duration</span><strong><?= Html::e($car['duration']) ?></strong></div><?php endif; ?>
                  <?php if (!empty($car['exam'])): ?><div class="ce-stat"><span>Entrance exam</span><strong><?= Html::e($car['exam']) ?></strong></div><?php endif; ?>
                </div>
                <?php if (!empty($car['focus'])): ?><div class="ce-box ce-box--focus"><span>Focus area</span><p><?= Html::e($car['focus']) ?></p></div><?php endif; ?>
                <?php if (!empty($car['paths'])): ?><div class="ce-box ce-box--paths"><span>Career paths</span><p><?= Html::e($car['paths']) ?></p></div><?php endif; ?>
                <?php if (!empty($car['salary'])): ?><div class="ce-box ce-box--salary"><span>Typical salary</span><p><?= Html::e($car['salary']) ?></p></div><?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
