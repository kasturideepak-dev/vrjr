<?php
$people = Cpt::published('faculty');

// Derive a department for each person: explicit field first, else parse the designation.
$deriveDept = static function (array $p): string {
    $explicit = trim((string) Cpt::field($p, 'department'));
    if ($explicit !== '') {
        return $explicit;
    }
    $d = strtolower((string) Cpt::field($p, 'designation'));
    foreach (['mathematics'=>'Mathematics','maths'=>'Mathematics','physics'=>'Physics','chemistry'=>'Chemistry','botany'=>'Botany','zoology'=>'Zoology','biology'=>'Biology','english'=>'English','commerce'=>'Commerce'] as $needle=>$name) {
        if (str_contains($d, $needle)) {
            return $name;
        }
    }
    if (str_contains($d, 'founder') || str_contains($d, 'director') || str_contains($d, 'dean') || str_contains($d, 'principal') || str_contains($d, 'chair')) {
        return 'Leadership';
    }
    return 'Faculty';
};

$groups = [];
foreach ($people as $p) {
    $groups[$deriveDept($p)][] = $p;
}

// Preferred display order; anything else follows alphabetically.
$order = ['Leadership','Mathematics','Physics','Chemistry','Botany','Zoology','Biology','English','Commerce','Faculty'];
uksort($groups, static function ($a, $b) use ($order) {
    $ia = array_search($a, $order, true); $ib = array_search($b, $order, true);
    $ia = $ia === false ? 999 : $ia; $ib = $ib === false ? 999 : $ib;
    return $ia === $ib ? strcmp($a, $b) : $ia - $ib;
});
?>
<section class="section" id="faculty">
  <div class="container">
    <div class="section-head">
      <div><?php if (!empty($c['kicker'])): ?><p class="eyebrow"><?= Html::e($c['kicker']) ?></p><?php endif; ?><h2><?= Html::e($c['heading'] ?? '') ?></h2></div>
      <a class="btn btn--ghost" href="/team-details/">See our experts</a>
    </div>

    <div class="faculty-depts">
      <?php foreach ($groups as $dept => $members): ?>
        <div class="faculty-dept">
          <h3 class="faculty-dept__name"><?= $dept === 'Leadership' ? 'Leadership &amp; Mentors' : ($dept === 'Faculty' ? 'Faculty' : 'Department of ' . Html::e($dept)) ?></h3>
          <div class="faculty-dept__grid">
            <?php foreach ($members as $p):
              $img = $p['featured_image'] ?: (string) Cpt::field($p, 'image');
              $exp = (string) Cpt::field($p, 'experience');
              $desig = (string) Cpt::field($p, 'designation');
              // For subject departments, drop the "Sr./Jr. X Faculty" label and show experience instead.
              $role = ($dept === 'Leadership' || $dept === 'Faculty') ? $desig : ($exp !== '' ? $exp . ' experience' : $desig);
            ?>
              <figure class="faculty">
                <?php if ($img): ?><img src="<?= Html::e($img) ?>" alt="<?= Html::e($p['title']) ?>" loading="lazy"><?php else: ?><div class="faculty__ph" aria-hidden="true"><?= Html::e(mb_substr($p['title'], 0, 1)) ?></div><?php endif; ?>
                <figcaption>
                  <h4><?= Html::e($p['title']) ?></h4>
                  <?php if ($role !== ''): ?><p><?= Html::e($role) ?></p><?php endif; ?>
                </figcaption>
              </figure>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
