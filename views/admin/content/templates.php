<div class="page-head"><div><p class="crumbs">System</p><h1>Templates</h1></div></div>
<h2>Page templates</h2>
<div class="table-wrap">
  <table>
    <?php foreach ($pages as $p): ?>
      <tr>
        <td><?= Html::e($p['name']) ?></td>
        <td>
          <form method="post" action="/admin/templates/delete/"><?= Csrf::field() ?>
            <input type="hidden" name="kind" value="page"><input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
            <button class="btn-danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<h2 style="margin-top:18px">Section templates</h2>
<div class="table-wrap">
  <table>
    <?php foreach ($sections as $s): ?>
      <tr>
        <td><?= Html::e($s['name']) ?> · <?= Html::e($s['type']) ?></td>
        <td>
          <form method="post" action="/admin/templates/delete/"><?= Csrf::field() ?>
            <input type="hidden" name="kind" value="section"><input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
            <button class="btn-danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
