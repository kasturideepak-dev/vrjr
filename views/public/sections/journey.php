<?php
$steps = $c['steps'] ?? [];

// Flat SVG illustrations per milestone (currentColor = the step colour).
$svgs = [
  // 1 — School
  '<circle cx="60" cy="58" r="52" fill="currentColor" opacity=".1"/><path d="M28 54 60 33 92 54" fill="none" stroke="currentColor" stroke-width="4" stroke-linejoin="round"/><rect x="36" y="54" width="48" height="38" rx="3" fill="#fff" stroke="currentColor" stroke-width="4"/><rect x="54" y="72" width="12" height="20" rx="1" fill="currentColor"/><rect x="42" y="61" width="9" height="9" rx="1.5" fill="currentColor" opacity=".55"/><rect x="69" y="61" width="9" height="9" rx="1.5" fill="currentColor" opacity=".55"/><path d="M60 24v10" stroke="#12305a" stroke-width="3"/><path d="M61 25h10l-3 4 3 4H61z" fill="currentColor"/>',
  // 2 — Signpost
  '<circle cx="60" cy="58" r="52" fill="currentColor" opacity=".1"/><rect x="57" y="34" width="6" height="60" rx="3" fill="#12305a"/><path d="M60 40h30l7 8-7 8H60z" fill="currentColor"/><path d="M60 58H30l-7 8 7 8h30z" fill="currentColor" opacity=".55"/><path d="M60 76h26l7 8-7 8H60z" fill="currentColor" opacity=".8"/>',
  // 3 — Clipboard / checklist
  '<circle cx="60" cy="58" r="52" fill="currentColor" opacity=".1"/><rect x="38" y="34" width="44" height="56" rx="5" fill="#fff" stroke="currentColor" stroke-width="4"/><rect x="50" y="29" width="20" height="11" rx="3" fill="currentColor"/><path d="M45 51l3.5 3.5 6-7" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><rect x="59" y="50" width="16" height="4" rx="2" fill="currentColor" opacity=".5"/><path d="M45 65l3.5 3.5 6-7" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><rect x="59" y="64" width="16" height="4" rx="2" fill="currentColor" opacity=".5"/><path d="M45 79l3.5 3.5 6-7" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/><rect x="59" y="78" width="16" height="4" rx="2" fill="currentColor" opacity=".5"/>',
  // 4 — College
  '<circle cx="60" cy="58" r="52" fill="currentColor" opacity=".1"/><path d="M30 52 60 33 90 52Z" fill="currentColor"/><rect x="33" y="52" width="54" height="6" fill="#12305a"/><rect x="38" y="58" width="6" height="28" fill="currentColor"/><rect x="51" y="58" width="6" height="28" fill="currentColor"/><rect x="63" y="58" width="6" height="28" fill="currentColor"/><rect x="76" y="58" width="6" height="28" fill="currentColor"/><rect x="32" y="86" width="56" height="7" rx="2" fill="#12305a"/><path d="M60 22v11" stroke="#12305a" stroke-width="3"/><path d="M61 23h9l-3 4 3 4h-9z" fill="currentColor"/>',
  // 5 — Lightbulb / idea
  '<circle cx="60" cy="58" r="52" fill="currentColor" opacity=".1"/><path d="M60 32a19 19 0 0 0-12 34c3 2.5 4 4.5 4.5 8h15c.5-3.5 1.5-5.5 4.5-8a19 19 0 0 0-12-34z" fill="#fff" stroke="currentColor" stroke-width="4"/><rect x="52" y="80" width="16" height="5" rx="2.5" fill="currentColor"/><rect x="55" y="88" width="10" height="4" rx="2" fill="currentColor"/><path d="M60 46v12M54 52h12" stroke="currentColor" stroke-width="3" stroke-linecap="round"/><path d="M32 40l6 3M88 40l-6 3M60 20v6" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>',
  // 6 — Briefcase
  '<circle cx="60" cy="58" r="52" fill="currentColor" opacity=".1"/><rect x="32" y="52" width="56" height="36" rx="6" fill="#fff" stroke="currentColor" stroke-width="4"/><path d="M48 52v-5a5 5 0 0 1 5-5h14a5 5 0 0 1 5 5v5" fill="none" stroke="currentColor" stroke-width="4"/><rect x="32" y="64" width="56" height="6" fill="currentColor" opacity=".45"/><rect x="53" y="62" width="14" height="10" rx="2" fill="currentColor"/>',
  // 7 — Graduation cap + scroll
  '<circle cx="60" cy="58" r="52" fill="currentColor" opacity=".1"/><path d="M60 38 26 50 60 62 94 50Z" fill="currentColor"/><path d="M42 56v13c0 5.5 8 9.5 18 9.5s18-4 18-9.5V56" fill="#fff" stroke="currentColor" stroke-width="4"/><path d="M94 50v15" stroke="#12305a" stroke-width="3"/><circle cx="94" cy="68" r="3.5" fill="currentColor"/><rect x="66" y="80" width="26" height="8" rx="4" fill="#fff" stroke="currentColor" stroke-width="3"/>',
];
?>
<section class="section journey" id="journey">
  <div class="container">
    <div class="section-head is-center">
      <div>
        <?php if (!empty($c['kicker'])): ?><span class="pill"><?= Html::e($c['kicker']) ?></span><?php endif; ?>
        <h2><?= Html::e($c['heading'] ?? '') ?></h2>
        <?php if (!empty($c['lede'])): ?><p class="lede"><?= Html::e($c['lede']) ?></p><?php endif; ?>
      </div>
    </div>

    <?php if ($steps): ?>
    <div class="journey-wrap">
      <svg class="journey-path" viewBox="0 0 1400 80" preserveAspectRatio="none" aria-hidden="true">
        <defs>
          <linearGradient id="jgrad" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0" stop-color="#e5405e"/>
            <stop offset="0.17" stop-color="#f59e0b"/>
            <stop offset="0.34" stop-color="#17a673"/>
            <stop offset="0.5" stop-color="#7b3fe4"/>
            <stop offset="0.67" stop-color="#2f80ed"/>
            <stop offset="0.83" stop-color="#f2711c"/>
            <stop offset="1" stop-color="#e5405e"/>
          </linearGradient>
        </defs>
        <path class="jp-base" vector-effect="non-scaling-stroke" d="M0,40 Q100,12 200,40 T400,40 T600,40 T800,40 T1000,40 T1200,40 T1400,40"/>
        <path class="jp-fill" pathLength="1" vector-effect="non-scaling-stroke" d="M0,40 Q100,12 200,40 T400,40 T600,40 T800,40 T1000,40 T1200,40 T1400,40"/>
      </svg>
    <div class="journey-track" data-journey>
      <?php foreach ($steps as $i => $s):
        $color = $s['color'] ?? '#2f80ed';
        $items = $s['items'] ?? [];
      ?>
        <div class="jstep" style="--jc: <?= Html::e($color) ?>; --fd: <?= number_format($i * 0.35, 2) ?>s">
          <div class="jstep__illus"><svg viewBox="0 0 120 120" role="img" aria-hidden="true"><?= $svgs[$i] ?? '' ?></svg></div>
          <div class="jstep__top">
            <span class="jstep__num"><?= Html::e($s['num'] ?? sprintf('%02d', $i + 1)) ?></span>
          </div>
          <div class="jstep__bar"><span><?= Html::e($s['title'] ?? '') ?></span></div>
          <?php if ($items): ?>
            <ul class="jstep__list">
              <?php foreach ($items as $it): ?><li><?= Html::e($it) ?></li><?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    </div>
    <?php endif; ?>
  </div>
</section>
