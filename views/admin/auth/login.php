<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sign in · VR CMS</title>
  <link rel="stylesheet" href="<?= Html::e(admin_asset('admin.css')) ?>">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800&family=Inter:wght@400;600&display=swap" rel="stylesheet">
</head>
<body class="admin">
  <div class="login-wrap">
    <div class="login-card">
      <p class="crumbs">VR Junior College</p>
      <h1>Sign in</h1>
      <p>Secure access to the content dashboard.</p>
      <?php if (!empty($flash)): ?><div class="flash flash-<?= Html::e($flash['type']) ?>"><?= Html::e($flash['message']) ?></div><?php endif; ?>
      <form class="form" method="post" action="/admin/login/">
        <?= Csrf::field() ?>
        <label class="lab">Email <input type="email" name="email" required autocomplete="username"></label>
        <label class="lab">Password <input type="password" name="password" required autocomplete="current-password"></label>
        <label style="display:flex;gap:8px;align-items:center;font-size:13px"><input type="checkbox" name="remember" value="1"> Remember me for 30 days</label>
        <button class="btn" type="submit">Continue</button>
        <p><a href="/admin/forgot/">Forgot password?</a></p>
      </form>
    </div>
  </div>
</body>
</html>
