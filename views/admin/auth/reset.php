<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>New password · VR CMS</title>
  <link rel="stylesheet" href="<?= Html::e(admin_asset('admin.css')) ?>">
</head>
<body class="admin">
  <div class="login-wrap">
    <div class="login-card">
      <h1>Choose a new password</h1>
      <p>Minimum 10 characters.</p>
      <?php if (!empty($flash)): ?><div class="flash flash-<?= Html::e($flash['type']) ?>"><?= Html::e($flash['message']) ?></div><?php endif; ?>
      <form class="form" method="post">
        <?= Csrf::field() ?>
        <label class="lab">Password <input type="password" name="password" required minlength="10"></label>
        <label class="lab">Confirm <input type="password" name="password_confirm" required minlength="10"></label>
        <button class="btn" type="submit">Update password</button>
      </form>
    </div>
  </div>
</body>
</html>
