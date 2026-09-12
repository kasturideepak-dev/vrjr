<div class="page-head"><h1>Testimonials</h1></div>
<form class="form" method="post">
  <?= Csrf::field() ?>
  <div class="row2">
    <label class="lab">Name <input name="name" required></label>
    <label class="lab">Role <input name="role"></label>
  </div>
  <label class="lab">Initials <input name="initials"></label>
  <label class="lab">Quote <textarea name="quote" required></textarea></label>
  <button class="btn" type="submit">Add</button>
</form>
<div class="table-wrap" style="margin-top:16px"><table><thead><tr><th>Name</th><th>Quote</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr>
  <td><?= Html::e($r['name']) ?></td>
  <td><?= Html::e($r['quote']) ?></td>
  <td><form method="post" action="/admin/testimonials/delete/" data-confirm="Delete?"><?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn-danger">Delete</button></form></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
