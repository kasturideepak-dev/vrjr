<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verification · VR CMS</title>
  <link rel="stylesheet" href="<?= Html::e(admin_asset('admin.css')) ?>">
</head>
<body class="admin">
  <div class="login-wrap">
    <div class="login-card">
      <h1>Check your email</h1>
      <p>Enter the 6-digit code we just sent. It expires in 10 minutes.</p>
      <?php if (!empty($flash)): ?><div class="flash flash-<?= Html::e($flash['type']) ?>"><?= Html::e($flash['message']) ?></div><?php endif; ?>
      <form class="form" method="post">
        <?= Csrf::field() ?>
        <label class="lab">Code <input name="code" inputmode="numeric" maxlength="6" required autofocus></label>
        <button class="btn" type="submit">Verify</button>
      </form>
    </div>
  </div>
</body>
</html>
