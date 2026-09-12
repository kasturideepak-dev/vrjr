<?php
$u = $user ?? Auth::user();
$path = Request::path();
$on = static function (string $p) use ($path): string {
    return str_starts_with($path, $p) ? ' is-on' : '';
};
$initials = strtoupper(substr((string) ($u['name'] ?? 'A'), 0, 1));
$types = $postTypesNav ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="<?= Html::e(Csrf::token()) ?>">
  <title><?= Html::e($title ?? 'Admin') ?> · VR CMS</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= Html::e(admin_asset('admin.css')) ?>">
  <link rel="icon" href="/assets/img/brand/favicon.png">
</head>
<body class="admin">
  <div class="admin-shell">
    <aside class="admin-side">
      <div class="brand">
        <img src="/assets/img/brand/logo-icon.png" alt="">
        <div><strong>VR CMS</strong><span>Junior College</span></div>
      </div>
      <nav class="admin-nav">
        <a class="<?= $path === '/admin/' ? 'is-on' : '' ?>" href="/admin/">Dashboard</a>
        <div class="nav-label">Content</div>
        <a class="<?= $on('/admin/pages/') ?>" href="/admin/pages/">Pages</a>
        <?php foreach ($types as $t): ?>
          <a class="<?= $on('/admin/content/' . $t['slug'] . '/') ?>" href="/admin/content/<?= Html::e($t['slug']) ?>/"><?= Html::e($t['name']) ?></a>
        <?php endforeach; ?>
        <a class="<?= $on('/admin/blog/') ?>" href="/admin/blog/">Blog</a>
        <a class="<?= $on('/admin/faqs/') ?>" href="/admin/faqs/">FAQs</a>
        <a class="<?= $on('/admin/testimonials/') ?>" href="/admin/testimonials/">Testimonials</a>
        <a class="<?= $on('/admin/media/') ?>" href="/admin/media/">Media</a>
        <div class="nav-label">Engage</div>
        <a class="<?= $on('/admin/forms/') ?>" href="/admin/forms/">Forms</a>
        <a class="<?= $on('/admin/leads/') ?>" href="/admin/leads/">Leads</a>
        <a class="<?= $on('/admin/menus/') ?>" href="/admin/menus/">Menus</a>
        <div class="nav-label">System</div>
        <a class="<?= $on('/admin/post-types/') ?>" href="/admin/post-types/">Post types</a>
        <a class="<?= $on('/admin/seo/') ?>" href="/admin/seo/">SEO</a>
        <a class="<?= $on('/admin/redirects/') ?>" href="/admin/redirects/">Redirects</a>
        <a class="<?= $on('/admin/backup/') ?>" href="/admin/backup/">Backups</a>
        <a class="<?= $on('/admin/templates/') ?>" href="/admin/templates/">Templates</a>
        <?php if (Auth::isSuper()): ?>
          <a class="<?= $on('/admin/snippets/') ?>" href="/admin/snippets/">Code snippets</a>
        <?php endif; ?>
        <a class="<?= $on('/admin/users/') ?>" href="/admin/users/">Users</a>
        <a class="<?= $on('/admin/settings/') ?>" href="/admin/settings/">Settings</a>
        <a class="<?= $on('/admin/audit/') ?>" href="/admin/audit/">Activity</a>
      </nav>
    </aside>
    <div class="admin-main">
      <header class="admin-top">
        <form action="/admin/search/" method="get">
          <input type="search" name="q" placeholder="Search pages, entries, posts, media" value="<?= Html::e($_GET['q'] ?? '') ?>">
        </form>
        <div class="admin-user">
          <span class="ava"><?= Html::e($initials) ?></span>
          <span><?= Html::e($u['name'] ?? '') ?> · <?= Html::e($u['role_name'] ?? '') ?></span>
          <a class="btn-ghost" href="/" target="_blank" rel="noopener">View site</a>
          <form action="/admin/logout/" method="post"><?= Csrf::field() ?><button class="btn-ghost" type="submit">Sign out</button></form>
        </div>
      </header>
      <div class="admin-body">
        <?php if (!empty($flash)): ?>
          <div class="flash flash-<?= Html::e($flash['type']) ?>"><?= Html::e($flash['message']) ?></div>
        <?php endif; ?>
        <?= $content ?? '' ?>
      </div>
    </div>
  </div>
  <div class="toast" id="toast"></div>
  <div class="modal" id="media-picker">
    <div class="box glass">
      <div class="page-head">
        <h3>Media library</h3>
        <button class="btn-ghost" type="button" data-media-close>Close</button>
      </div>
      <input type="search" data-media-search placeholder="Search by file name or alt text">
      <p class="hint">Dimensions and file size are shown so you can avoid oversized images.</p>
      <div class="media-grid"></div>
    </div>
  </div>
  <script src="<?= Html::e(admin_asset('admin.js')) ?>"></script>
</body>
</html>
