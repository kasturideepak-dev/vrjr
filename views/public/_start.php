<?php
$s = $settings ?? [];
$asset = $asset ?? '/assets/';
$seo = $seo ?? [];
$title = $seo['seo_title'] ?? ($page['title'] ?? 'VR Junior College');
$desc = $seo['meta_description'] ?? ($s['default_seo_description'] ?? '');
$canon = $seo['canonical_url'] ?? url(class_exists('Request') ? Request::path() : '/');
$og = $seo['og_image'] ?? ($s['og_image'] ?? $asset . 'img/banner/hero-class.jpg');
if ($og && !str_starts_with((string) $og, 'http')) {
    $og = url($og);
}
$byParent = [];
foreach ($headerMenu ?? [] as $item) {
    $pid = $item['parent_id'] ? (int) $item['parent_id'] : 0;
    $byParent[$pid][] = $item;
}
$topItems = $byParent[0] ?? [];
$path = class_exists('Request') ? Request::path() : '/';
$navCurrent = static function (string $url) use ($path): bool {
    $u = parse_url($url, PHP_URL_PATH);
    $u = is_string($u) && $u !== '' ? $u : $url;
    $u = '/' . trim($u, '/');
    $u = $u === '/' ? '/' : $u . '/';
    if ($u === '/') {
        return $path === '/';
    }
    return $path === $u || str_starts_with($path, $u);
};
$phone1 = $s['phone_primary'] ?? '+91 89298 28498';
$phone1tel = preg_replace('/\D+/', '', $phone1);
$phone2 = $s['phone_secondary'] ?? '+91 92569 25643';
$phone2tel = preg_replace('/\D+/', '', $phone2);
$wa = $s['whatsapp'] ?? '15559412484';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= Html::e($title) ?></title>
  <meta name="description" content="<?= Html::e($desc) ?>">
  <link rel="canonical" href="<?= Html::e($canon) ?>">
  <meta name="robots" content="<?= Html::e($seo['robots'] ?? 'index,follow') ?>">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= Html::e($seo['og_title'] ?? $title) ?>">
  <meta property="og:description" content="<?= Html::e($seo['og_description'] ?? $desc) ?>">
  <meta property="og:image" content="<?= Html::e($og) ?>">
  <meta property="og:url" content="<?= Html::e($canon) ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= Html::e($seo['twitter_title'] ?? $seo['og_title'] ?? $title) ?>">
  <meta name="twitter:description" content="<?= Html::e($seo['twitter_description'] ?? $seo['og_description'] ?? $desc) ?>">
  <meta name="twitter:image" content="<?= Html::e($seo['twitter_image'] ?? $og) ?>">
  <meta name="theme-color" content="#103058">
  <link rel="icon" href="<?= Html::e($s['favicon'] ?? $asset . 'img/brand/favicon.png') ?>">
  <?php if (class_exists('Request') && Request::path() === '/'): ?>
  <link rel="preload" as="image" href="<?= $asset ?>img/banner/slides/slide-1.webp">
  <?php endif; ?>
  <link rel="preload" as="font" type="font/woff2" href="<?= $asset ?>fonts/inter-latin.woff2" crossorigin>
  <link rel="preload" as="font" type="font/woff2" href="<?= $asset ?>fonts/plus-jakarta-sans-latin.woff2" crossorigin>
  <link rel="stylesheet" href="<?= $asset ?>css/fonts.css?v=local-1">
  <link rel="stylesheet" href="<?= $asset ?>css/tokens.css">
  <link rel="stylesheet" href="<?= $asset ?>css/base.css">
  <link rel="stylesheet" href="<?= $asset ?>css/components.css?v=mobile-1">
  <link rel="stylesheet" href="<?= $asset ?>css/sections.css?v=mobile-8">
  <link rel="stylesheet" href="<?= $asset ?>css/pages.css?v=mpc-1">
  <?php if (!empty($s['schema_json'])): ?>
    <script type="application/ld+json"><?= $s['schema_json'] ?></script>
  <?php endif; ?>
  <?php Snippets::emit('header', $snippetCtx ?? []); ?>
</head>
<body<?= !empty($bodyClass) ? ' class="' . Html::e($bodyClass) . '"' : '' ?>>
  <?php Snippets::emit('body_start', $snippetCtx ?? []); ?>
  <a class="skip-link" href="#main">Skip to content</a>
  <svg xmlns="http://www.w3.org/2000/svg" style="display:none">
    <symbol id="i-check" viewBox="0 0 15 13"><path d="M6.4 12.36 0 5.67h3.24L6.2 8.9C7.3 7.5 10.06 2.4 13.98.04c.4.2.4.7 0 1C10.9 4.3 7.7 9.2 6.4 12.36z"/></symbol>
    <symbol id="i-phone" viewBox="0 0 24 24"><path d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1.1-.2 1.2.4 2.5.6 3.8.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.6.6 3.8.1.4 0 .8-.3 1.1L6.6 10.8z"/></symbol>
    <symbol id="i-star" viewBox="0 0 24 24"><path d="M12 3.2l2.1 6.4H21l-5.3 3.9 2 6.5L12 16.8 6.3 20l2-6.5L3 9.6h6.9z"/></symbol>
    <symbol id="i-learn" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="10" cy="8" r="2.8"/><path d="M5.2 18.5c.4-2.3 2.3-3.8 4.8-3.8s4.4 1.5 4.8 3.8"/><rect x="14" y="13" width="5.5" height="3.4" rx="0.5"/></symbol>
    <symbol id="i-medal" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="12" cy="14" r="4.2"/><path d="M9.2 10.4 7 4.8h3.4L12 8l1.6-3.2H17L14.8 10.4"/></symbol>
    <symbol id="i-people" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="9" cy="8" r="2.4"/><circle cx="16" cy="9.2" r="2"/><path d="M4.5 18.5c.4-2.4 2.5-4 5-4s4.6 1.6 5 4M14 14.6c1.8-.2 3.6 1 4.2 3.4"/></symbol>
    <symbol id="i-play" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></symbol>
  </svg>
  <div class="site-chrome">
    <div class="announce">
      <div class="container announce__inner">
        <div class="announce__left">
          <span class="announce-pill">Admissions Open 2026–27</span>
          <span class="announce-meta">MPC &amp; BiPC · Hyderabad</span>
        </div>
        <div class="announce__phones">
          <a href="tel:+<?= Html::e($phone1tel) ?>"><svg width="14" height="14" aria-hidden="true"><use href="#i-phone"></use></svg> <?= Html::e($phone1) ?></a>
          <a href="tel:+<?= Html::e($phone2tel) ?>"><svg width="14" height="14" aria-hidden="true"><use href="#i-phone"></use></svg> <?= Html::e($phone2) ?></a>
        </div>
        <div class="announce__right">
          <span class="live-dot">Admissions Open</span>
          <a class="btn-top btn-top--fill" href="/contact-us/#enquire">Apply now</a>
        </div>
      </div>
    </div>
    <header class="site-header">
      <div class="container site-header__inner">
        <a class="logo" href="/" aria-label="VR Junior College home">
          <img src="<?= Html::e($s['logo'] ?? $asset . 'img/brand/logo.png') ?>" alt="<?= Html::e($s['brand_name'] ?? 'VR Junior College') ?>" width="1600" height="299">
        </a>
        <button class="nav-toggle" type="button" aria-controls="site-nav" aria-expanded="false" data-nav-toggle aria-label="Open menu"><span class="nav-toggle__icon"></span></button>
        <nav class="nav" id="site-nav" data-nav aria-label="Primary">
          <ul class="nav__list">
            <?php foreach ($topItems as $item):
              $kids = $byParent[(int) $item['id']] ?? [];
            ?>
              <?php if ($kids): ?>
                <li class="has-sub">
                  <button class="has-caret" type="button" data-sub-toggle aria-expanded="false"><?= Html::e($item['label']) ?> <svg viewBox="0 0 12 8" aria-hidden="true"><path d="M1 1.5l5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.4"/></svg></button>
                  <div class="subnav">
                    <?php foreach ($kids as $k): ?><a href="<?= Html::e($k['url']) ?>"<?= $navCurrent($k['url']) ? ' aria-current="page"' : '' ?>><?= Html::e($k['label']) ?></a><?php endforeach; ?>
                  </div>
                </li>
              <?php else: ?>
                <li><a href="<?= Html::e($item['url']) ?>"<?= $navCurrent($item['url']) ? ' aria-current="page"' : '' ?>><?= Html::e($item['label']) ?></a></li>
              <?php endif; ?>
            <?php endforeach; ?>
          </ul>
          <a class="apply-chip apply-chip--hero nav-apply-desk" href="/contact-us/#enquire">Apply now <span class="apply-chip__mark" aria-hidden="true">↗</span></a>
          <a class="btn nav-apply" href="/contact-us/#enquire">Apply now</a>
        </nav>
        <script>
        (function () {
          function leaf(p) {
            p = String(p || "").split("#")[0].split("?")[0].replace(/\/+$/, "");
            var n = p.split("/").pop() || "";
            n = n.replace(/\.html$/, "");
            return (!n || n === "index") ? "home" : n;
          }
          var here = leaf(location.pathname);
          document.querySelectorAll(".nav .subnav a, .nav__list > li > a").forEach(function (a) {
            var on = leaf(a.getAttribute("href")) === here;
            a.classList.toggle("is-on", on);
            if (on) a.setAttribute("aria-current", "page");
            else a.removeAttribute("aria-current");
          });
        })();
        </script>
      </div>
    </header>
  </div>
  <div class="nav-backdrop" data-nav-backdrop></div>
  <main id="main">
