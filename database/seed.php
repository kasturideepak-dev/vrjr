<?php
declare(strict_types=1);

$A = '/assets/img/';

$permMap = require ROOT . '/config/permissions.php';
$permIds = [];
foreach ($permMap as $group => $actions) {
    foreach ($actions as $act) {
        $slug = $group . '.' . $act;
        $permIds[$slug] = Database::insert('permissions', [
            'slug' => $slug,
            'name' => ucfirst($group) . ' ' . $act,
            'group_name' => $group,
        ]);
    }
}

$roleIds = [];
foreach ([
    'super-admin' => 'Super Admin',
    'editor' => 'Editor',
    'viewer' => 'Viewer',
] as $slug => $name) {
    $roleIds[$slug] = Database::insert('roles', ['slug' => $slug, 'name' => $name, 'is_system' => 1]);
}
foreach ($permIds as $pid) {
    Database::insert('role_permissions', ['role_id' => $roleIds['super-admin'], 'permission_id' => $pid]);
}
foreach ($permIds as $slug => $pid) {
    if (str_ends_with($slug, '.view') || in_array($slug, ['pages.create','pages.edit','pages.publish','entries.create','entries.edit','entries.publish','blog.create','blog.edit','blog.publish','media.upload','faqs.edit','testimonials.edit','forms.edit','leads.edit','menus.edit'], true)) {
        Database::insert('role_permissions', ['role_id' => $roleIds['editor'], 'permission_id' => $pid]);
    }
    if (str_ends_with($slug, '.view')) {
        Database::insert('role_permissions', ['role_id' => $roleIds['viewer'], 'permission_id' => $pid]);
    }
}

$adminId = Database::insert('users', [
    'role_id' => $roleIds['super-admin'],
    'name' => 'Site Admin',
    'email' => 'admin@vrjuniorcollege.com',
    'password_hash' => password_hash('ChangeMe_VRJ2026', PASSWORD_DEFAULT),
    'status' => 'active',
]);

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'EducationalOrganization',
    'name' => 'VR Junior College',
    'url' => 'https://vrjuniorcollege.com/',
    'logo' => 'https://vrjuniorcollege.com/assets/img/brand/logo.png',
    'email' => 'info@vrjuniorcollege.com',
    'telephone' => ['+91 89298 28498', '+91 92569 25643'],
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => 'Plot no: 30, Near SBI bank, Mathrusree Nagar, Hafeezpet, Miyapur',
        'addressLocality' => 'Hyderabad',
        'addressRegion' => 'Telangana',
        'addressCountry' => 'IN',
    ],
    'sameAs' => [
        'https://www.facebook.com/profile.php?id=61583046600673',
        'https://www.instagram.com/vr_junior.college/',
        'https://www.linkedin.com/company/vr-jr-college/',
        'https://www.youtube.com/@VR_Junior_College',
    ],
];

$settings = [
    'brand_name' => 'VR Junior College',
    'tagline' => 'Vision Into Reality',
    'logo' => '/assets/img/brand/logo.png',
    'favicon' => '/assets/img/brand/favicon.png',
    'phone_primary' => '+91 89298 28498',
    'phone_secondary' => '+91 92569 25643',
    'email' => 'info@vrjuniorcollege.com',
    'email_support' => 'support@vrjuniorcollege.com',
    'whatsapp' => '15559412484',
    'hours' => 'Mon – Sun, 10 am – 6 pm',
    'head_office' => 'Plot no: 30, Near SBI bank, Mathrusree Nagar, Hafeezpet, Miyapur, Telangana',
    'brochure' => 'https://drive.google.com/file/d/1ObbqqmENMGtRyrQQIbtI8uGv4Qq_6arj/view',
    'facebook' => 'https://www.facebook.com/profile.php?id=61583046600673',
    'instagram' => 'https://www.instagram.com/vr_junior.college/',
    'linkedin' => 'https://www.linkedin.com/company/vr-jr-college/',
    'youtube' => 'https://www.youtube.com/@VR_Junior_College',
    'default_seo_title' => 'VR Junior College | Best Junior College in Hyderabad',
    'default_seo_description' => 'Hyderabad’s leading residential junior college for MPC & BiPC. Intermediate education with expert IIT-JEE and NEET coaching.',
    'og_image' => '/assets/img/banner/hero-class.jpg',
    'gtm_id' => 'GTM-M7W444LT',
    'analytics_id' => '',
    'meta_pixel_id' => '',
    'schema_json' => json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    'robots_txt' => "User-agent: *\nAllow: /\nDisallow: /admin/\nDisallow: /login/\nDisallow: /preview/\nDisallow: /api/\nDisallow: /cron/\nSitemap: " . url('/sitemap.xml') . "\n",
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => '587',
    'smtp_encryption' => 'tls',
    'smtp_from_name' => 'VR Junior College',
    'smtp_from_email' => 'info@vrjuniorcollege.com',
    'backup_auto' => '0',
    'backup_interval' => 'daily',
    'backup_retention' => '14',
    'maintenance_mode' => '0',
    '404_heading' => 'Page not found',
    '404_text' => 'That address isn’t on this site. Head home or talk to admissions.',
];
foreach ($settings as $k => $v) {
    Database::insert('settings', ['setting_key' => $k, 'setting_value' => $v]);
}

$addField = static function (int $typeId, string $name, string $label, string $type, int $req = 0, ?array $opts = null, int $order = 0) {
    Database::insert('post_type_fields', [
        'post_type_id' => $typeId,
        'name' => $name,
        'label' => $label,
        'type' => $type,
        'is_required' => $req,
        'options_json' => $opts ? json_encode($opts) : null,
        'sort_order' => $order,
    ]);
};

$coursesType = Database::insert('post_types', [
    'name' => 'Courses',
    'singular_name' => 'Course',
    'slug' => 'courses',
    'description' => 'MPC, BiPC and long-term programmes',
    'has_archive' => 1,
    'public' => 1,
    'template_mode' => 'both',
    'archive_title' => 'Our programmes',
    'archive_intro' => 'Two-year Intermediate with integrated IIT-JEE or NEET coaching, plus a one-year NEET long-term option.',
    'sort_order' => 1,
    'status' => 'active',
    'is_system' => 1,
]);
$addField($coursesType, 'tagline', 'Tagline', 'text', 0, null, 1);
$addField($coursesType, 'stream', 'Stream', 'text', 0, null, 2);
$addField($coursesType, 'duration', 'Duration', 'text', 0, null, 3);
$addField($coursesType, 'summary', 'Summary', 'textarea', 0, null, 4);
$addField($coursesType, 'highlights', 'Syllabus highlights', 'repeater', 0, ['subfields' => [['name' => 'item', 'label' => 'Highlight', 'type' => 'text']]], 5);

$campusType = Database::insert('post_types', [
    'name' => 'Campuses',
    'singular_name' => 'Campus',
    'slug' => 'campus',
    'description' => 'Campus locations used on the campuses page and homepage grid',
    'has_archive' => 0,
    'public' => 0,
    'template_mode' => 'fields',
    'sort_order' => 2,
    'status' => 'active',
    'is_system' => 1,
]);
$addField($campusType, 'address', 'Address', 'text', 1, null, 1);
$addField($campusType, 'phone', 'Phone', 'text', 0, null, 2);
$addField($campusType, 'email', 'Email', 'email', 0, null, 3);
$addField($campusType, 'map_embed', 'Map embed', 'textarea', 0, null, 4);

$facultyType = Database::insert('post_types', [
    'name' => 'Faculty',
    'singular_name' => 'Faculty member',
    'slug' => 'faculty',
    'description' => 'Teachers and leadership shown in the faculty slider',
    'has_archive' => 0,
    'public' => 0,
    'template_mode' => 'fields',
    'sort_order' => 3,
    'status' => 'active',
    'is_system' => 1,
]);
$addField($facultyType, 'designation', 'Designation', 'text', 1, null, 1);
$addField($facultyType, 'department', 'Department', 'text', 0, null, 2);
$addField($facultyType, 'experience', 'Experience', 'text', 0, null, 3);
$addField($facultyType, 'bio', 'Bio', 'textarea', 0, null, 4);

$aiType = Database::insert('post_types', [
    'name' => 'AI',
    'singular_name' => 'AI programme',
    'slug' => 'ai',
    'description' => 'AI landing pages: banner, content, video testimonials and campuses.',
    'has_archive' => 1,
    'public' => 1,
    'template_mode' => 'both',
    'archive_title' => 'AI programmes',
    'archive_intro' => 'AI-focused programmes at VR Junior College, Hyderabad.',
    'sort_order' => 4,
    'status' => 'active',
    'is_system' => 0,
]);
$addField($aiType, 'duration', 'Duration', 'text', 0, null, 1);
$addField($aiType, 'who_for', 'Who it is for', 'textarea', 0, null, 2);

$addEntry = static function (int $typeId, string $title, string $slug, array $fields, array $meta = []) use ($adminId): int {
    $id = Database::insert('cpt_entries', [
        'post_type_id' => $typeId,
        'title' => $title,
        'slug' => $slug,
        'status' => 'published',
        'excerpt' => $meta['excerpt'] ?? ($fields['summary'] ?? ''),
        'featured_image' => $meta['image'] ?? ($fields['image'] ?? ''),
        'fields_json' => json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        'published_at' => date('Y-m-d H:i:s'),
        'author_id' => $adminId,
        'sort_order' => $meta['sort'] ?? 0,
    ]);
    Database::insert('seo_metadata', [
        'entity_type' => 'cpt',
        'entity_id' => $id,
        'seo_title' => $meta['seo_title'] ?? ($title . ' | VR Junior College'),
        'meta_description' => $meta['meta_description'] ?? ($fields['summary'] ?? ''),
        'canonical_url' => url('courses/' . $slug),
        'robots' => 'index,follow',
    ]);
    return $id;
};

$courseMeta = [
    'mpc-with-iit-jee' => [
        'old' => 'junior-college-for-mpc',
        'title' => 'MPC with IIT-JEE',
        'fields' => ['tagline' => 'IPE with IIT-JEE & EAMCET', 'stream' => 'VRiiT', 'duration' => '2-year Intermediate', 'summary' => 'For future engineers and innovators. Beyond IIT-JEE, pathways from AI and IoT to Aerospace — guiding each student by their strengths, not by pressure.'],
        'image' => $A . 'life/campus-2.jpg',
        'sort' => 1,
    ],
    'bipc-with-neet' => [
        'old' => 'inter-college-for-bipc-in-hyderabad',
        'title' => 'BiPC with NEET',
        'fields' => ['tagline' => 'IPE with focused NEET coaching', 'stream' => 'VR Doctors', 'duration' => '2-year Intermediate', 'summary' => 'VR Doctors Academy — BiPC for NEET aspirants, because NEET opens doors not just to MBBS, but to AYUSH, BDS, and many healthcare careers.'],
        'image' => $A . 'gallery/g8.jpg',
        'sort' => 2,
    ],
    'neet-long-term' => [
        'old' => 'neet-long-term',
        'title' => 'NEET Long Term',
        'fields' => ['tagline' => '8–10 month intensive programme', 'stream' => 'NEET', 'duration' => '1-year Long Term', 'summary' => 'Part of VR Doctors Academy. Structured coaching with personal mentorship, including guidance for repeaters.'],
        'image' => $A . 'life/facility-1.jpg',
        'sort' => 3,
    ],
];

$coursePages = require ROOT . '/database/course_pages.php';
$courseIds = [];
foreach ($courseMeta as $newSlug => $spec) {
    $pageSpec = $coursePages[$spec['old']] ?? ['seo' => [], 'sections' => []];
    $eid = $addEntry($coursesType, $spec['title'], $newSlug, $spec['fields'], [
        'excerpt' => $spec['fields']['summary'],
        'image' => $spec['image'],
        'sort' => $spec['sort'],
        'seo_title' => $pageSpec['seo']['seo_title'] ?? ($spec['title'] . ' | VR Junior College'),
        'meta_description' => $pageSpec['seo']['meta_description'] ?? $spec['fields']['summary'],
    ]);
    $courseIds[$newSlug] = $eid;
    foreach ($pageSpec['sections'] as $i => $sec) {
        Database::insert('content_sections', [
            'owner_type' => 'cpt',
            'owner_id' => $eid,
            'type' => $sec[0],
            'content_json' => json_encode($sec[1], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'sort_order' => $i,
            'is_visible' => 1,
        ]);
    }
    Content::snapshot('cpt', $eid, true, 'Initial publish');
    if ($spec['old'] !== 'courses/' . $newSlug) {
        Database::insert('redirects', [
            'from_path' => $spec['old'],
            'to_path' => '/courses/' . $newSlug . '/',
            'status_code' => 301,
            'is_active' => 1,
            'note' => 'WordPress course URL → CPT',
        ]);
    }
}

$campuses = [
    ['Miyapur-A', 'miyapur-a', 'Mathrusree Nagar, Miyapur', 'campus/miyapur-a.jpg'],
    ['Miyapur-B', 'miyapur-b', 'Madhava Nagar, Miyapur', 'campus/miyapur-b.jpg'],
    ['Hafeezpet', 'hafeezpet', 'Hafeezpet, Chandanagar', 'campus/hafeezpet.jpg'],
    ['Bowrampet', 'bowrampet', 'VR Building, Bowrampet', 'campus/bowrampet.jpg'],
    ['Hayathnagar', 'hayathnagar', 'Pedda Amberpet, Hayathnagar', 'campus/hayathnagar.jpg'],
];
foreach ($campuses as $i => $c) {
    Database::insert('cpt_entries', [
        'post_type_id' => $campusType,
        'title' => $c[0],
        'slug' => $c[1],
        'status' => 'published',
        'featured_image' => $A . $c[3],
        'fields_json' => json_encode(['address' => $c[2]], JSON_UNESCAPED_UNICODE),
        'published_at' => date('Y-m-d H:i:s'),
        'sort_order' => $i + 1,
    ]);
}

$faculty = [
    ['Mr. Ramesh', 'ramesh', 'Founder', '', 'faculty/ramesh.jpg'],
    ['Mr. Ashish', 'ashish', 'Director', '', 'faculty/ashish.jpg'],
    ['Mr. KVR', 'kvr', 'Academic Dean', '37 years', 'faculty/kvr.jpg'],
    ['Mr. G Ashok', 'g-ashok', 'Sr. Physics Faculty', '22 years', 'faculty/g-ashok.jpg'],
    ['Mr. M Srinath', 'm-srinath', 'Sr. Zoology Faculty', '20 years', 'faculty/m-srinath.jpg'],
    ['Mr. K Prabhakar Reddy', 'k-prabhakar', 'Sr. Zoology Faculty', '16 years', 'faculty/k-prabhakar.jpg'],
    ['Mr. P Malyadri', 'p-malyadri', 'Sr. Chemistry Faculty', '18 years', 'faculty/p-malyadri.jpg'],
    ['Mr. B Nagesh', 'b-nagesh', 'Sr. Botany Faculty', '19 years', 'faculty/b-nagesh.jpg'],
];
foreach ($faculty as $i => $f) {
    Database::insert('cpt_entries', [
        'post_type_id' => $facultyType,
        'title' => $f[0],
        'slug' => $f[1],
        'status' => 'published',
        'featured_image' => $A . $f[4],
        'fields_json' => json_encode(['designation' => $f[2], 'experience' => $f[3]], JSON_UNESCAPED_UNICODE),
        'published_at' => date('Y-m-d H:i:s'),
        'sort_order' => $i + 1,
    ]);
}

Database::insert('testimonials', ['name' => 'Sravani Reddy', 'role' => 'MBBS Student', 'quote' => 'VR Junior College helped me crack NEET on my first attempt. The BiPC program and faculty support were exactly what I needed.', 'initials' => 'SR', 'sort_order' => 1]);
Database::insert('testimonials', ['name' => 'Teja Kumar', 'role' => 'NEET Qualifier', 'quote' => 'I choose VR because it’s known as the best inter college for BiPC in Hyderabad, and it lived up to the name.', 'initials' => 'TK', 'sort_order' => 2]);
Database::insert('testimonials', ['name' => 'M.R_ S.R.K', 'role' => 'Google review', 'quote' => 'Very good college, nice infrastructure and good teaching staff.', 'initials' => 'MR', 'sort_order' => 3]);

$faqs = [
    ['Why is VR Junior College considered one of the leading residential junior colleges in Hyderabad?', 'VR College stands out for guiding each student individually rather than pushing everyone toward one exam. With specialized MPC and BiPC streams, personal mentorship, residential campuses, and a proven record of results, VR helps students grow on the path best suited to their strengths.'],
    ['What courses does VR Junior College offer?', 'VR College offers two specialised two-year Intermediate streams with integrated coaching — MPC (VRiiT) for IPE + IIT-JEE, and BiPC (VR Doctors Academy) for IPE + NEET — plus a separate one-year NEET long-term programme for competitive exams only.'],
    ['Is VR Junior College only a residential campus?', 'VR College offers both residential (hostel) and day-scholar options. Separate boys and girls hostels are supervised 24/7 with wardens and CCTV monitoring.'],
    ['Does VR Junior College provide study materials?', 'Yes. Students receive structured learning support, complete NEET & JEE material, worksheets, chapter tests, and question banks.'],
    ['Where are VR Junior College campuses located?', 'Mathrusree Nagar and Madhava Nagar (Miyapur), Hafeezpet (Chandanagar), Ayyappa Society (Madhapur), VR Building (Bowrampet), and Pedda Amberpet, Hayathnagar.'],
    ['How can students apply for admission?', 'Admissions for MPC and BiPC are open. Families can visit any VR campus, call +91 89298 28498, +91 92569 25643, or enquire through this website.'],
];
foreach ($faqs as $i => $f) {
    Database::insert('faqs', ['question' => $f[0], 'answer' => $f[1], 'entity_type' => 'global', 'sort_order' => $i + 1, 'is_visible' => 1]);
}

$formId = Database::insert('forms', [
    'name' => 'Enquiry',
    'slug' => 'enquire',
    'success_message' => 'Thank you. Please call +91 89298 28498 so our admissions team can guide you.',
    'notify_email' => 'info@vrjuniorcollege.com',
    'honeypot_field' => 'website',
    'is_active' => 1,
]);
Database::insert('form_recipients', ['form_id' => $formId, 'email' => 'info@vrjuniorcollege.com']);
$ff = [
    ['name', 'Name', 'text', 1, 'half', ''],
    ['phone', 'Phone', 'tel', 1, 'half', ''],
    ['email', 'Email', 'email', 1, 'full', ''],
    ['program', 'Programme', 'select', 1, 'half', json_encode(['choices' => ['MPC — Integrated', 'BiPC — Integrated', 'NEET Long Term']])],
    ['class', 'Class', 'select', 1, 'half', json_encode(['choices' => ['8', '9', '10', '11', '12', '12+']])],
    ['branch', 'Branch', 'select', 1, 'full', json_encode(['choices' => ['Miyapur - Hyderabad', 'Madhapur - Hyderabad', 'Chandanagar - Hyderabad']])],
    ['message', 'Message', 'textarea', 0, 'full', ''],
];
foreach ($ff as $i => $f) {
    Database::insert('form_fields', [
        'form_id' => $formId,
        'name' => $f[0],
        'label' => $f[1],
        'type' => $f[2],
        'is_required' => $f[3],
        'width' => $f[4],
        'options_json' => $f[5] ?: null,
        'sort_order' => $i + 1,
    ]);
}

$addPage = static function (string $slug, string $title, string $type, array $sections, array $seo = []) use ($adminId): int {
    $id = Database::insert('pages', [
        'type' => $type,
        'title' => $title,
        'slug' => $slug,
        'status' => 'published',
        'published_at' => date('Y-m-d H:i:s'),
        'created_by' => $adminId,
        'updated_by' => $adminId,
    ]);
    foreach ($sections as $i => $sec) {
        Database::insert('content_sections', [
            'owner_type' => 'page',
            'owner_id' => $id,
            'type' => $sec[0],
            'content_json' => json_encode($sec[1], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'sort_order' => $i,
            'is_visible' => 1,
        ]);
    }
    Database::insert('seo_metadata', array_merge([
        'entity_type' => 'page',
        'entity_id' => $id,
        'seo_title' => $title,
        'canonical_url' => url($slug === '/' ? '/' : $slug),
        'robots' => 'index,follow',
    ], $seo));
    Content::snapshot('page', $id, true, 'Initial publish');
    return $id;
};

$pageIds = [];
$pageIds['home'] = $addPage('/', 'Home', 'standard', [
    ['hero', [
        'kicker' => 'Admissions Open 2026–27 · MPC & BiPC',
        'heading' => 'Where Doctors & Engineers Future Begin Their Journey',
        'heading_highlight' => 'Doctors & Engineers',
        'rotate_label' => 'We Promise...',
        'rotate' => "Focused Learning Journey\nExceptional Academic Results\nIntegrated Coaching Programs\nDedicated Expert Mentors\nPersonalized Learning Experience\nAdvanced Performance Tracking\nSeparate Residential Campuses\nConsistent Proven Success\nFuture-Ready Education\nExcellence Through Learning\nSmart Learning Systems\nAchieve Academic Excellence",
        'lead' => 'At VR Junior College, we combine Intermediate education with expert IIT-JEE and NEET coaching to help students achieve their dreams. With experienced faculty, personal mentorship, and a disciplined residential environment, we empower every student to excel academically and beyond.',
        'image' => $A . 'banner/slides/slide-1.webp',
        'slides' => $A . "banner/slides/slide-1.webp\n" . $A . "banner/slides/slide-2.webp\n" . $A . "banner/slides/slide-3.webp\n" . $A . "banner/slides/slide-4.webp\n" . $A . "banner/slides/slide-5.webp",
        'figure' => $A . 'life/campus-1.jpg',
        'cta_label' => 'Apply now',
        'cta_url' => '/contact-us/#enquire',
        'cta2_label' => 'Book a campus visit',
        'cta2_url' => '/contact-us/',
        'video_url' => 'https://www.youtube.com/watch?v=_3sTInMS-f4',
        'stats' => "2000+|Satisfied students\n310+|MBBS Pvt. colleges\n215+|MBBS Govt. colleges\n5|Golden years",
    ]],
    ['marquee', ['items' => "Admissions Open 2026–27\nMPC with IIT-JEE\nBiPC with NEET\nNEET Long Term\nResidential & day scholar"]],
    ['about', [
        'kicker' => 'About VR',
        'heading' => 'About VR Junior College',
        'quote' => 'At VR Junior College — Vision Into Reality — we believe no child is built for one exam. Intermediate is the bridge between school and a bright career, and we make it strong by guiding each student individually, away from blind competition and toward the path best suited to them.',
        'image_main' => $A . 'campus/bowrampet.jpg',
        'image_card' => $A . 'gallery/g8.jpg',
        'badge_value' => '5',
        'badge_label' => 'Golden years',
        'points' => "Personal guidance|Every student is understood individually — not pushed into one exam.\nVR Doctors Academy|BiPC for NEET aspirants — pathways to MBBS, AYUSH, BDS and healthcare careers.\nVRiiT|MPC for future engineers — IIT-JEE is one path, with many universities beyond it.",
        'cta_label' => 'Discover VR',
        'cta_url' => '/about/',
    ]],
    ['programs', [
        'kicker' => 'Our programmes',
        'heading' => 'MPC and BiPC, with integrated coaching',
        'lede' => 'Two-year Intermediate with integrated IIT-JEE or NEET coaching (IPE + competitive exams). A separate one-year NEET long-term programme is for competitive exams only.',
        'split_left_kicker' => '2-year Intermediate',
        'split_left_title' => 'Integrated coaching',
        'split_left_text' => 'IPE + competitive exams — MPC with IIT-JEE, and BiPC with NEET.',
        'split_right_kicker' => '1-year Long Term',
        'split_right_title' => 'Competitive exams only',
        'split_right_text' => 'NEET long-term for Intermediate-completed students and repeaters.',
    ]],
    ['approach', [
        'lead' => 'Six steps. One guided journey — from concept to confidence.',
        'steps' => "Learn|Understand each concept deeply and explore beyond a single exam.\nPractice|Strengthen learning through focused, smart practice — not blind cramming.\nPerform|Build clarity and confidence to perform without fear or pressure.\nAnalyze|Recognize each student’s strengths to guide them to the right path.\nMentorship|Receive personal, one-on-one guidance at every step of the journey.\nAchieve|Grow beyond exams into confident individuals ready for the future.",
    ]],
    ['why', [
        'kicker' => 'Why choose us',
        'heading' => 'Why choose VR Junior College?',
        'cards' => "Personal guidance|Every student is guided to the path that fits their strengths — not pushed into one exam.\nSpecialised streams|MPC (IIT-JEE) and BiPC (NEET) — with pathways far beyond a single entrance test.\nDedicated mentors|Highly qualified faculty who replace pressure with direction.\nEducation for all|Through our ₹1-fee initiative, no deserving student is left behind.",
        'video_image' => $A . 'gallery/g6.jpg',
        'video_url' => 'https://www.youtube.com/watch?v=_3sTInMS-f4',
    ]],
    ['cta_banner', [
        'kicker' => 'Admissions 2026',
        'heading' => 'Shape your future with the best junior college in Hyderabad',
        'text' => 'Admissions open for MPC & BiPC. Begin a guided academic journey built around your child’s strengths — not a single exam.',
        'image' => $A . 'banner/hero-ceremony.jpg',
        'cta_label' => 'Apply now',
        'cta_url' => '/contact-us/#enquire',
        'cta2_label' => 'Book a campus visit',
        'cta2_url' => '/contact-us/',
    ]],
    ['stats', ['items' => "2000+|Satisfied students\n310+|MBBS Pvt. colleges\n215+|MBBS Govt. colleges\n5|Golden years"]],
    ['faculty', ['kicker' => 'Our faculty', 'heading' => 'The minds behind VR students’ success']],
    ['facilities', [
        'kicker' => 'Campus life',
        'heading' => 'Our Facilities & Features',
        'cards' => "Separate hostel|Safe, dedicated hostels with a caring, home-like environment — so students stay focused, comfortable, and secure through their studies.|{$A}gallery/g2.jpg|Hostel\nCampus gallery|Step inside VR life — smart learning, festival celebrations, sports, and campus moments captured across our Hyderabad campuses.|{$A}gallery/g8.jpg|Campus\nCafeteria|Healthy, hygienic meals and snacks for students and faculty — because good learning starts with good food.|{$A}gallery/g5.jpg|Cafeteria",
    ]],
    ['campuses', [
        'kicker' => 'Our campuses',
        'heading' => 'Across Hyderabad',
        'note' => 'Also listed: Ayyappa Society, Madhapur. Head office: Plot no: 30, Near SBI bank, Mathrusree Nagar, Hafeezpet, Miyapur, Telangana.',
    ]],
    ['testimonials', ['label' => 'Testimonials']],
    ['blog', ['kicker' => 'Blog', 'heading' => 'Latest from the blog']],
    ['enquire', [
        'kicker' => 'Admissions 2026–27',
        'heading' => 'Shape your future with VR Junior College',
        'lede' => 'Admissions open for MPC & BiPC. Begin a guided academic journey built around your child’s strengths — not a single exam.',
    ]],
    ['campus_life', [
        'heading' => 'Campus Life',
        'cta_label' => 'View full gallery',
        'cta_url' => '/gallery/',
        'bg' => $A . 'banner/campus-life-bg.jpg',
        'shot1' => $A . 'banner/life-class.jpg',
        'shot2' => $A . 'banner/life-film.jpg',
        'shot3' => $A . 'banner/life-hostel.jpg',
        'video_url' => 'https://www.youtube.com/watch?v=_3sTInMS-f4',
    ]],
    ['faq', ['kicker' => 'FAQs', 'heading' => 'Questions families ask']],
], [
    'seo_title' => 'VR Junior College | Best Junior College in Hyderabad',
    'meta_description' => 'Hyderabad’s leading residential junior college for MPC & BiPC. Intermediate education with expert IIT-JEE and NEET coaching, personal mentorship and campuses across Hyderabad.',
]);

$brochure = 'https://drive.google.com/file/d/1ObbqqmENMGtRyrQQIbtI8uGv4Qq_6arj/view';
$pageIds['about'] = $addPage('about', 'About us', 'standard', [
    ['page_hero', [
        'kicker' => 'Vision Into Reality',
        'heading' => 'About us',
        'lead' => 'We believe no child is built for one exam. Quality education in medical and engineering streams, guided by expert mentorship.',
        'image' => $A . 'banner/campus-life-bg.jpg',
        'figure' => $A . 'life/campus-1.jpg',
        'crumb' => 'About',
    ]],
    ['about', [
        'kicker' => 'About VR',
        'heading' => 'About VR Junior College',
        'quote' => 'We are committed to shaping the future of young minds through quality education in medical and engineering streams. Rather than pushing every student down a single path, we understand each one individually and guide them — through expert mentorship and a strengths-based approach — toward the future that truly fits them.',
        'image_main' => $A . 'campus/bowrampet.jpg',
        'image_card' => $A . 'gallery/g8.jpg',
        'badge_value' => '5',
        'badge_label' => 'Golden years',
        'points' => "Personal guidance|Every student is understood individually — not pushed into one exam.\nVR Doctors Academy|BiPC for NEET aspirants — pathways to MBBS, AYUSH, BDS and healthcare careers.\nVRiiT|MPC for future engineers — IIT-JEE is one path, with many universities beyond it.",
        'cta_label' => 'Download brochure',
        'cta_url' => $brochure,
    ]],
    ['vision_mission', [
        'kicker' => 'Focusing on results',
        'heading' => 'Our vision and mission',
        'vision_title' => 'Our Vision',
        'vision_text' => 'To turn every student’s vision into reality — nurturing future doctors and engineers alike in a supportive, pressure-free environment, and making quality education accessible to every child, including the economically and physically challenged.',
        'mission_title' => 'Our Mission',
        'mission_items' => "Focused coaching for NEET and IIT-JEE aspirants across BiPC and MPC.\nGuide each student by their strengths and interests — smart, directed learning over blind competition.\nPersonal, one-on-one mentorship in a caring, home-like environment.\nShape confident, capable individuals ready to grow beyond exams into future leaders in medicine, engineering, and every path they choose.",
    ]],
    ['features', [
        'kicker' => 'Why choose us',
        'heading' => 'Why choose VR Junior College?',
        'cards' => "01|Holistic learning approach|A blend of deep conceptual understanding, real application, and personal growth — learning with direction, not pressure.\n02|Dedicated streams|Specialized coaching through VR Doctors Academy for medical (NEET) aspirants and VRiiT for engineering (IIT-JEE) aspirants.\n03|Residential campuses|Safe, home-like hostels with healthy meals and supportive campus life across five locations in Hyderabad.\n04|Comprehensive study material|Well-structured resources built for genuine understanding — not rote cramming.\n05|Proven track record|A legacy of results, white-coat ceremonies, and award-winning recognition, year after year.\n06|Expert faculty|Experienced educators and mentors who guide every student one-on-one, committed to their success.",
    ]],
    ['cta_banner', [
        'kicker' => 'Brochure',
        'heading' => 'Want to know more about us?',
        'text' => 'Download the VR Junior College brochure, or visit a campus and talk to our admissions team.',
        'image' => $A . 'banner/hero-ceremony.jpg',
        'cta_label' => 'Download brochure',
        'cta_url' => $brochure,
        'cta2_label' => 'Apply now',
        'cta2_url' => '/contact-us/#enquire',
    ]],
    ['enquire', [
        'kicker' => 'Admissions 2026–27',
        'heading' => 'Shape your future with VR Junior College',
        'lede' => 'Admissions open for MPC & BiPC. Begin a guided academic journey built around your child’s strengths — not a single exam.',
    ]],
], [
    'seo_title' => 'About VR Junior College | Top Ranked Junior College in Hyderabad',
    'meta_description' => 'VR Junior College — Vision Into Reality — is a leading residential junior college in Hyderabad for MPC and BiPC, with personal mentorship and specialised NEET and IIT-JEE coaching.',
]);

$pageIds['campuses'] = $addPage('campuses', 'Our campuses', 'standard', [
    ['page_hero', ['kicker' => 'Hyderabad', 'heading' => 'Five campuses. One VR standard.', 'lead' => 'Residential and day-scholar campuses across Hyderabad — Miyapur, Hafeezpet, Bowrampet, Madhapur and Hayathnagar.', 'image' => $A . 'banner/campuses-hero.jpg', 'crumb' => 'Campuses']],
    ['campuses', ['kicker' => 'Locations', 'heading' => 'Across Hyderabad', 'note' => 'Also listed: Ayyappa Society, Madhapur.']],
    ['enquire', ['heading' => 'Book a campus visit', 'lede' => 'Visit Miyapur, Hafeezpet, Bowrampet or Hayathnagar.']],
]);
$pageIds['gallery'] = $addPage('gallery', 'Gallery', 'standard', [
    ['page_hero', ['kicker' => 'Campus gallery', 'heading' => 'Life at VR, in pictures.', 'lead' => 'Classrooms, hostels, dining and campuses across Hyderabad — click any photo to see it full size.', 'image' => $A . 'gallery/classroom-empty.jpg', 'crumb' => 'Gallery']],
    ['gallery', ['images' => implode("\n", [$A.'gallery/corridor.jpg',$A.'gallery/hostel-study.jpg',$A.'gallery/dining-portrait.jpg',$A.'gallery/hostel-room.jpg',$A.'gallery/dining-hall.jpg',$A.'gallery/dining-mess-a.jpg',$A.'gallery/classroom-back.jpg',$A.'gallery/classroom-empty.jpg',$A.'gallery/classroom.jpg',$A.'gallery/dining-mess-b.jpg',$A.'gallery/building-bowrampet.jpg',$A.'gallery/building-hafeezpet.jpg',$A.'gallery/building-miyapur-a.jpg',$A.'gallery/building-hayathnagar.jpg',$A.'gallery/building-miyapur-b.jpg'])]],
]);
$pageIds['contact'] = $addPage('contact-us', 'Contact us', 'standard', [
    ['page_hero', ['kicker' => 'Admissions 2026–27', 'heading' => 'Contact us', 'lead' => 'Get in touch for admissions, course details, or a campus visit.', 'image' => $A . 'campus/hafeezpet.jpg', 'crumb' => 'Contact']],
    ['enquire', ['heading' => 'Let us help you take the next step', 'lede' => 'We’re here to guide you on your journey to success.']],
]);
$pageIds['team'] = $addPage('team-details', 'Team details', 'standard', [
    ['page_hero', ['kicker' => 'Faculty & leadership', 'heading' => 'Team details', 'lead' => 'Meet the teachers and academic leaders at VR Junior College.', 'image' => $A . 'faculty/kvr.jpg', 'crumb' => 'Team']],
    ['faculty', ['kicker' => 'Our faculty', 'heading' => 'The minds behind VR students’ success']],
    ['enquire', ['heading' => 'Talk to admissions', 'lede' => '']],
]);
$pageIds['admissions'] = $addPage('admissions', 'Admissions 2026–27', 'landing', [
    ['page_hero', ['kicker' => '11th & 12th integrated', 'heading' => 'Admissions 2026–27', 'lead' => 'Residential coaching for NEET and IIT-JEE — expert faculty, daily tests, and a safe campus.', 'image' => $A . 'banner/hero-class.jpg', 'crumb' => 'Admissions']],
    ['programs', ['kicker' => 'Our programmes', 'heading' => 'Focused residential coaching', 'lede' => 'Two-year Intermediate with integrated coaching is different from one-year long-term (competitive exams only).']],
    ['enquire', ['heading' => 'Get a free consultation', 'lede' => 'Admissions open for MPC & BiPC.']],
]);
$pageIds['blog'] = $addPage('blog', 'Blog', 'blog_index', [
    ['page_hero', ['kicker' => 'Insights', 'heading' => 'Blog', 'lead' => 'Education, NEET and IIT-JEE preparation notes from VR Junior College, Hyderabad.', 'image' => $A . 'gallery/g6.jpg', 'crumb' => 'Blog']],
    ['blog', ['kicker' => 'Blog', 'heading' => 'Latest from the blog']],
]);
$addPage('404', 'Page not found', 'standard', [
    ['page_hero', ['kicker' => '404', 'heading' => 'Page not found', 'lead' => 'That address isn’t on this site. Head home or talk to admissions.', 'image' => $A . 'campus/bowrampet.jpg', 'crumb' => '404', 'cta_label' => 'Back to home', 'cta_url' => '/']],
], ['robots' => 'noindex,follow', 'seo_title' => 'Page not found | VR Junior College']);

$headerId = Database::insert('menus', ['slug' => 'header', 'name' => 'Header']);
$footerId = Database::insert('menus', ['slug' => 'footer', 'name' => 'Footer']);
$mi = static function (int $menu, string $label, string $linkType, ?int $objectId, string $url, int $order, ?int $parent = null, ?string $objectType = null) {
    return Database::insert('menu_items', [
        'menu_id' => $menu,
        'parent_id' => $parent,
        'label' => $label,
        'url' => $url,
        'link_type' => $linkType,
        'object_type' => $objectType,
        'object_id' => $objectId,
        'sort_order' => $order,
        'is_active' => 1,
    ]);
};
$mi($headerId, 'Home', 'page', $pageIds['home'], '/', 1, null, 'page');
$mi($headerId, 'About', 'page', $pageIds['about'], '/about/', 2, null, 'page');
$coursesNav = $mi($headerId, 'Courses', 'cpt_archive', $coursesType, '/courses/', 3, null, 'post_type');
$mi($headerId, 'MPC with IIT-JEE', 'cpt_entry', $courseIds['mpc-with-iit-jee'], '/courses/mpc-with-iit-jee/', 1, $coursesNav, 'cpt');
$mi($headerId, 'BiPC with NEET', 'cpt_entry', $courseIds['bipc-with-neet'], '/courses/bipc-with-neet/', 2, $coursesNav, 'cpt');
$mi($headerId, 'NEET Long Term', 'cpt_entry', $courseIds['neet-long-term'], '/courses/neet-long-term/', 3, $coursesNav, 'cpt');
$mi($headerId, 'Campuses', 'page', $pageIds['campuses'], '/campuses/', 4, null, 'page');
$mi($headerId, 'Gallery', 'page', $pageIds['gallery'], '/gallery/', 5, null, 'page');
$mi($headerId, 'Blog', 'page', $pageIds['blog'], '/blog/', 6, null, 'page');
$mi($headerId, 'Contact', 'page', $pageIds['contact'], '/contact-us/', 7, null, 'page');
$mi($footerId, 'About', 'page', $pageIds['about'], '/about/', 1, null, 'page');
$mi($footerId, 'Campuses', 'page', $pageIds['campuses'], '/campuses/', 2, null, 'page');
$mi($footerId, 'Gallery', 'page', $pageIds['gallery'], '/gallery/', 3, null, 'page');
$mi($footerId, 'Blog', 'blog_index', null, '/blog/', 4);
$mi($footerId, 'Admissions', 'page', $pageIds['admissions'], '/admissions/', 5, null, 'page');

$blogThumbs = [
    'gallery/classroom.jpg',
    'gallery/hostel-study.jpg',
    'gallery/g8.jpg',
    'gallery/classroom-back.jpg',
    'gallery/g5.jpg',
    'gallery/hostel-room.jpg',
    'life/campus-1.jpg',
    'life/students-2.jpg',
    'gallery/g2.jpg',
    'gallery/g1.jpg',
    'gallery/g7.jpg',
    'gallery/g4.jpg',
    'gallery/classroom-empty.jpg',
    'gallery/g3.jpg',
    'gallery/building-miyapur-a.jpg',
    'banner/hero-class.jpg',
];
$blogJson = json_decode(file_get_contents(ROOT . '/data/blog-posts.json'), true);
foreach ($blogJson['posts'] ?? [] as $i => $post) {
    $html = '';
    foreach ($post['body'] ?? [] as $block) {
        if (($block['type'] ?? '') === 'h2') {
            $html .= '<h2>' . htmlspecialchars($block['text'] ?? '', ENT_QUOTES, 'UTF-8') . '</h2>';
        } elseif (($block['type'] ?? '') === 'ul') {
            $html .= '<ul>';
            foreach ($block['items'] ?? [] as $li) {
                $html .= '<li>' . htmlspecialchars($li, ENT_QUOTES, 'UTF-8') . '</li>';
            }
            $html .= '</ul>';
        } else {
            $html .= '<p>' . htmlspecialchars($block['text'] ?? '', ENT_QUOTES, 'UTF-8') . '</p>';
        }
    }
    $pid = Database::insert('blog_posts', [
        'title' => $post['title'],
        'slug' => $post['slug'],
        'excerpt' => $post['excerpt'] ?? '',
        'body_html' => $html,
        'featured_image' => $A . ($blogThumbs[$i] ?? $blogThumbs[$i % count($blogThumbs)]),
        'author_id' => $adminId,
        'status' => 'published',
        'published_at' => date('Y-m-d H:i:s', strtotime($post['date'] ?? 'now') ?: time()),
    ]);
    Database::insert('seo_metadata', [
        'entity_type' => 'blog',
        'entity_id' => $pid,
        'seo_title' => $post['title'] . ' | VR Junior College',
        'meta_description' => $post['seoDescription'] ?? ($post['excerpt'] ?? ''),
        'canonical_url' => url('blog/' . $post['slug']),
        'robots' => 'index,follow',
    ]);
}

foreach ([
    ['jee-long-term', '/courses/mpc-with-iit-jee/', 'No JEE long-term programme — map to MPC'],
    ['admissions-new', '/admissions/', 'Legacy admissions slug'],
    ['mpc-with-sat', '/courses/mpc-with-iit-jee/', 'Empty SAT page'],
    ['category/business', '/blog/', 'WordPress category archive — no Business topic on this site'],
] as $rd) {
    Database::insert('redirects', ['from_path' => $rd[0], 'to_path' => $rd[1], 'status_code' => 301, 'is_active' => 1, 'note' => $rd[2]]);
}

Templates::ensureStarters();
Snippets::migrateFromSettings();

Database::insert('seo_metadata', [
    'entity_type' => 'post_type',
    'entity_id' => $coursesType,
    'seo_title' => 'Courses | VR Junior College',
    'meta_description' => 'MPC with IIT-JEE, BiPC with NEET, and NEET long-term programmes at VR Junior College, Hyderabad.',
    'canonical_url' => url('courses'),
    'robots' => 'index,follow',
]);
