<?php
$faqs = [];
if (!empty($c['items'])) {
    foreach (preg_split("/\r\n|\n|\r/", (string) $c['items']) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || !str_contains($line, '|')) {
            continue;
        }
        [$q, $a] = array_map('trim', explode('|', $line, 2));
        if ($q !== '' && $a !== '') {
            $faqs[] = ['question' => $q, 'answer' => $a];
        }
    }
}
if (!$faqs) {
    $faqs = Database::all('SELECT * FROM faqs WHERE is_visible=1 AND entity_type="global" ORDER BY sort_order, id');
}
?>
<section class="section" id="faq">
  <div class="container faq-wrap">
    <div class="section-head is-center"><div><?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?><h2><?= Html::e($c['heading'] ?? '') ?></h2></div></div>
    <div class="faq" data-faq>
      <?php foreach ($faqs as $i => $f): ?>
        <div class="faq__item <?= $i === 0 ? 'is-open' : '' ?>">
          <h3><button class="faq__q" type="button" aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"><?= Html::e($f['question']) ?></button></h3>
          <div class="faq__a"><p><?= Html::e($f['answer']) ?></p></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
