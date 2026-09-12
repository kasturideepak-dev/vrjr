<div class="page-head"><h1>FAQs</h1></div>
<form class="form" method="post">
  <?= Csrf::field() ?>
  <label class="lab">Question <input name="question" required></label>
  <label class="lab">Answer <textarea name="answer" required></textarea></label>
  <label class="lab">Group <input name="entity_type" value="global"></label>
  <button class="btn" type="submit">Add FAQ</button>
</form>
<div class="table-wrap" style="margin-top:16px"><table><thead><tr><th>Question</th><th></th></tr></thead><tbody>
<?php foreach ($rows as $r): ?>
<tr>
  <td><strong><?= Html::e($r['question']) ?></strong><br><?= Html::e($r['answer']) ?></td>
  <td><form method="post" action="/admin/faqs/delete/" data-confirm="Delete FAQ?"><?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn-danger">Delete</button></form></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
