<div class="page-head"><div><p class="crumbs">Content</p><h1>FAQs</h1></div></div>
<form class="form" method="post">
  <?= Csrf::field() ?>
  <label class="lab">Question <input name="question" required></label>
  <label class="lab">Answer <textarea name="answer" required></textarea></label>
  <label class="lab">Order <input type="number" name="sort_order" value="0"></label>
  <label><input type="checkbox" name="is_visible" checked> Visible</label>
  <button class="btn" type="submit">Add FAQ</button>
</form>
<div class="table-wrap" style="margin-top:16px">
  <table>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td>
          <form method="post">
            <?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <input name="question" value="<?= Html::e($r['question']) ?>">
            <textarea name="answer"><?= Html::e($r['answer']) ?></textarea>
            <input type="number" name="sort_order" value="<?= (int) $r['sort_order'] ?>">
            <label><input type="checkbox" name="is_visible" <?= Html::checked((int) $r['is_visible'] === 1) ?>> Visible</label>
            <button class="btn-ghost" type="submit">Save</button>
          </form>
        </td>
        <td>
          <form method="post" action="/admin/faqs/delete/"><?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <button class="btn-danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
