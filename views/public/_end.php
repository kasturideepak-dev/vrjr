  </main>
  <footer class="site-footer">
    <div class="container footer-grid">
      <div class="footer-brand">
        <a class="logo" href="/"><img src="<?= Html::e($s['logo'] ?? $asset . 'img/brand/logo.png') ?>" alt="<?= Html::e($s['brand_name'] ?? 'VR Junior College') ?>" width="1600" height="299"></a>
        <p>VR Educational Academy — Where dreams take shape. At VR Junior College, we don’t just educate—we empower.</p>
        <p style="margin-top:12px"><?= Html::e($s['head_office'] ?? '') ?></p>
        <p><a href="tel:+<?= Html::e($phone1tel) ?>"><?= Html::e($phone1) ?></a> · <a href="mailto:<?= Html::e($s['email'] ?? '') ?>"><?= Html::e($s['email'] ?? '') ?></a></p>
        <div class="socials">
          <?php if (!empty($s['facebook'])): ?><a href="<?= Html::e($s['facebook']) ?>" rel="noopener" aria-label="Facebook"><svg viewBox="0 0 24 24" width="14" height="14"><path fill="currentColor" d="M14 9h3V6h-3c-2.2 0-4 1.8-4 4v2H8v3h2v7h3v-7h2.6l.4-3H13v-2c0-.6.4-1 1-1z"/></svg></a><?php endif; ?>
          <?php if (!empty($s['instagram'])): ?><a href="<?= Html::e($s['instagram']) ?>" rel="noopener" aria-label="Instagram"><svg viewBox="0 0 24 24" width="14" height="14"><path fill="currentColor" d="M7 3h10a4 4 0 0 1 4 4v10a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V7a4 4 0 0 1 4-4zm5 5.2A3.8 3.8 0 1 0 15.8 12 3.8 3.8 0 0 0 12 8.2zM17.4 6.6a1 1 0 1 0 1 1 1 1 0 0 0-1-1z"/></svg></a><?php endif; ?>
          <?php if (!empty($s['youtube'])): ?><a href="<?= Html::e($s['youtube']) ?>" rel="noopener" aria-label="YouTube"><svg viewBox="0 0 24 24" width="14" height="14"><path fill="currentColor" d="M23 12.2s0-3.2-.4-4.6c-.2-.9-.9-1.6-1.8-1.8C19.2 5.4 12 5.4 12 5.4s-7.2 0-8.8.4c-.9.2-1.6.9-1.8 1.8C1 9 1 12.2 1 12.2s0 3.2.4 4.6c.2.9.9 1.6 1.8 1.8 1.6.4 8.8.4 8.8.4s7.2 0 8.8-.4c.9-.2 1.6-.9 1.8-1.8.4-1.4.4-4.6.4-4.6zM9.8 15.6V8.8l6.2 3.4-6.2 3.4z"/></svg></a><?php endif; ?>
          <?php if (!empty($s['linkedin'])): ?><a href="<?= Html::e($s['linkedin']) ?>" rel="noopener" aria-label="LinkedIn"><svg viewBox="0 0 24 24" width="14" height="14"><path fill="currentColor" d="M6.5 9H4v11h2.5zM6.7 5.2A1.7 1.7 0 1 1 5 3.5a1.7 1.7 0 0 1 1.7 1.7zM20 13.5c0-3.3-1.8-4.8-4.1-4.8a3.5 3.5 0 0 0-3.1 1.6H12.7V9H10v11h2.5v-6.1c0-1.6.3-3.1 2.3-3.1s2 2 2 3.2V20H20z"/></svg></a><?php endif; ?>
        </div>
      </div>
      <div>
        <h2>Our courses</h2>
        <ul>
          <?php
          $courseParent = 0;
          foreach ($headerMenu ?? [] as $item) {
              if (empty($item['parent_id']) && strcasecmp($item['label'], 'Courses') === 0) {
                  $courseParent = (int) $item['id'];
                  break;
              }
          }
          $courseLinks = [];
          if ($courseParent) {
              foreach ($headerMenu as $item) {
                  if ((int) ($item['parent_id'] ?? 0) === $courseParent) {
                      $courseLinks[] = $item;
                  }
              }
          }
          if (!$courseLinks) {
              foreach (Cpt::published('courses') as $co) {
                  $courseLinks[] = ['label' => $co['title'], 'url' => Cpt::permalink($co)];
              }
          }
          foreach ($courseLinks as $co):
          ?>
            <li><a href="<?= Html::e($co['url']) ?>"><?= Html::e($co['label']) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div>
        <h2>Quick links</h2>
        <ul>
          <?php foreach ($footerMenu ?? [] as $f): ?>
            <li><a href="<?= Html::e($f['url']) ?>"><?= Html::e($f['label']) ?></a></li>
          <?php endforeach; ?>
          <?php if (!empty($s['brochure'])): ?>
            <li><a href="<?= Html::e($s['brochure']) ?>" rel="noopener">Download brochure</a></li>
          <?php endif; ?>
        </ul>
      </div>
      <div>
        <h2>Campuses</h2>
        <ul>
          <?php
          $footerCampuses = $footerCampuses ?? Cpt::published('campus');
          foreach ($footerCampuses as $camp):
          ?>
            <li><?= Html::e((string) (Cpt::field($camp, 'address') ?: $camp['title'])) ?></li>
          <?php endforeach; ?>
          <?php if (!empty($s['hours'])): ?>
            <li>Open: <?= Html::e($s['hours']) ?></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
    <div class="container footer-bottom">
      <p>© <?= date('Y') ?> VR Junior College. All rights reserved.</p>
      <p><?= Html::e($s['tagline'] ?? 'Vision Into Reality') ?> · Hyderabad</p>
    </div>
  </footer>
  <div class="float-contact">
    <a class="wa" href="https://wa.me/<?= Html::e($wa) ?>" aria-label="WhatsApp" rel="noopener" target="_blank"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg></a>
    <a class="call" href="tel:+<?= Html::e($phone1tel) ?>" aria-label="Call"><svg viewBox="0 0 24 24"><path fill="currentColor" d="M6.6 10.8c1.4 2.8 3.8 5.1 6.6 6.6l2.2-2.2c.3-.3.7-.4 1.1-.2 1.2.4 2.5.6 3.8.6.6 0 1 .4 1 1V20c0 .6-.4 1-1 1C10.6 21 3 13.4 3 4c0-.6.4-1 1-1h3.5c.6 0 1 .4 1 1 0 1.3.2 2.6.6 3.8.1.4 0 .8-.3 1.1L6.6 10.8z"/></svg></a>
    <a class="mail" href="mailto:<?= Html::e($s['email'] ?? '') ?>" aria-label="Email"><svg viewBox="0 0 24 24"><path fill="currentColor" d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg></a>
  </div>
  <button class="back-top" type="button" data-back-top aria-label="Back to top">
    <svg class="back-top__ring" viewBox="0 0 36 36" aria-hidden="true"><circle class="back-top__track" cx="18" cy="18" r="15.5"></circle><circle class="back-top__bar" cx="18" cy="18" r="15.5" data-back-progress></circle></svg>
    <svg class="back-top__icon" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 5l-7 7h4v7h6v-7h4z"/></svg>
  </button>
  <div class="lightbox" data-lightbox aria-hidden="true" role="dialog" aria-modal="true" aria-label="Image preview">
    <button type="button" class="lightbox__close" data-lightbox-close aria-label="Close">×</button>
    <button type="button" class="lightbox__nav lightbox__nav--prev" data-lightbox-prev aria-label="Previous image">‹</button>
    <figure class="lightbox__stage">
      <img alt="">
      <figcaption data-lightbox-cap></figcaption>
    </figure>
    <button type="button" class="lightbox__nav lightbox__nav--next" data-lightbox-next aria-label="Next image">›</button>
  </div>
  <script src="<?= $asset ?>js/main.js?v=roadmap-1" defer></script>
  <?php Snippets::emit('footer', $snippetCtx ?? []); ?>
</body>
</html>
