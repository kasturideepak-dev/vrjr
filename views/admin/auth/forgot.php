<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Reset password · VR CMS</title>
  <link rel="stylesheet" href="<?= Html::e(admin_asset('admin.css')) ?>">
</head>
<body class="admin">
  <div class="login-wrap">
    <div class="login-card">
      <h1>Reset password</h1>
      <p>We’ll email a one-hour reset link if that address exists.</p>
      <?php if (!empty($flash)): ?><div class="flash flash-<?= Html::e($flash['type']) ?>"><?= Html::e($flash['message']) ?></div><?php endif; ?>
      <form class="form" method="post">
        <?= Csrf::field() ?>
        <label class="lab">Email <input type="email" name="email" required></label>
        <button class="btn" type="submit">Send reset link</button>
        <p><a href="/admin/login/">Back to sign in</a></p>
      </form>
    </div>
  </div>
</body>
</html>
