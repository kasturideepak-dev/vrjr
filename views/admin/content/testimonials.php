<div class="page-head"><div><p class="crumbs">Content</p><h1>Testimonials</h1></div></div>
<form class="form" method="post">
  <?= Csrf::field() ?>
  <div class="row2">
    <label class="lab">Name <input name="name" required></label>
    <label class="lab">Role <input name="role"></label>
  </div>
  <label class="lab">Quote <textarea name="quote" required></textarea></label>
  <label class="lab">Video URL <input name="video_url" placeholder="https://www.youtube.com/watch?v=..."></label>
  <label class="lab">Initials <input name="initials" maxlength="4"></label>
  <button class="btn" type="submit">Add</button>
</form>
<div class="table-wrap" style="margin-top:16px">
  <table>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td>
          <form method="post">
            <?= Csrf::field() ?><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <input name="name" value="<?= Html::e($r['name']) ?>">
            <input name="role" value="<?= Html::e($r['role'] ?? '') ?>">
            <textarea name="quote"><?= Html::e($r['quote']) ?></textarea>
            <input name="video_url" value="<?= Html::e($r['video_url'] ?? '') ?>" placeholder="YouTube URL">
            <input name="initials" value="<?= Html::e($r['initials'] ?? '') ?>">
            <input type="number" name="sort_order" value="<?= (int) $r['sort_order'] ?>">
            <label><input type="checkbox" name="is_visible" <?= Html::checked((int) $r['is_visible'] === 1) ?>> Visible</label>
            <button class="btn-ghost">Save</button>
          </form>
        </td>
        <td>
          <form method="post" action="/admin/testimonials/delete/"><?= Csrf::field() ?>
            <input type="hidden" name="id" value="<?= (int) $r['id'] ?>"><button class="btn-danger">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
