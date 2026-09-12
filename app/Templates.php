<?php
declare(strict_types=1);

final class Templates
{
    public static function apply(string $ownerType, int $ownerId, int $templateId): int
    {
        $tpl = Database::one('SELECT * FROM page_templates WHERE id = ?', [$templateId]);
        if (!$tpl) {
            return 0;
        }
        $secs = json_decode($tpl['sections_json'] ?: '[]', true) ?: [];
        $n = 0;
        foreach ($secs as $i => $s) {
            if (empty($s['type'])) {
                continue;
            }
            Database::insert('content_sections', [
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'type' => $s['type'],
                'content_json' => Html::json($s['content'] ?? []),
                'sort_order' => $i,
                'is_visible' => 1,
            ]);
            $n++;
        }
        return $n;
    }

    public static function sectionCount(?string $json): int
    {
        return count(json_decode($json ?: '[]', true) ?: []);
    }

    public static function landingId(): ?int
    {
        return self::idBySlug('landing-page');
    }

    public static function idBySlug(string $slug): ?int
    {
        $row = Database::one('SELECT id FROM page_templates WHERE slug = ?', [$slug]);
        return $row ? (int) $row['id'] : null;
    }

    public static function defaultIdForType(?array $type): ?int
    {
        if ($type && ($type['slug'] ?? '') === 'ai') {
            self::ensureAiLanding();
            return self::idBySlug('ai-landing') ?: self::landingId();
        }
        return self::landingId();
    }

    public static function aiLandingSections(): array
    {
        $A = '/assets/img/';
        $apply = '/contact-us/#enquire';
        $brochure = 'https://drive.google.com/file/d/1ObbqqmENMGtRyrQQIbtI8uGv4Qq_6arj/view';
        return [
            ['type' => 'campaign_hero', 'content' => [
                'kicker' => 'AI at VR',
                'heading' => 'AI programmes at VR Junior College',
                'lead' => 'A focused landing page for VR’s AI offering — banner, programme story, student videos and Hyderabad campuses.',
                'chips' => "Intermediate\nNEET / IIT-JEE\nPersonal guidance",
                'image' => $A . 'careers/ai-ml.jpg',
                'figure' => $A . 'life/campus-1.jpg',
                'crumb' => 'AI',
                'offer_kicker' => 'At a glance',
                'offer_title' => 'What this programme includes',
                'offer_text' => 'Replace with duration, who it is for, and the outcome.',
                'offer_points' => "Personal mentoring\nStructured tests\nResidential option",
                'cta_label' => 'Apply now',
                'cta_url' => $apply,
                'cta2_label' => 'Download brochure',
                'cta2_url' => $brochure,
            ]],
            ['type' => 'split', 'content' => [
                'kicker' => 'The programme',
                'heading' => 'Built around the student, not one exam',
                'lede' => 'Tell families what this AI programme covers, who it is for, and how it sits alongside Intermediate, NEET and IIT-JEE coaching at VR.',
                'points' => "Guided|A study plan around each student’s strengths.\nPractical|Concept clarity with regular tests.\nSupported|Faculty, hostel and campus life in Hyderabad.",
                'image' => $A . 'careers/data-science.jpg',
                'cta_label' => 'Talk to admissions',
                'cta_url' => $apply,
            ]],
            ['type' => 'video_testimonials', 'content' => self::aiVideoItems()],
            ['type' => 'campuses', 'content' => [
                'kicker' => 'Hyderabad',
                'heading' => 'Our campuses',
                'note' => 'Visit a campus or open the location in Google Maps.',
            ]],
            ['type' => 'enquire', 'content' => [
                'kicker' => 'Admissions open',
                'heading' => 'Enquire about this programme',
                'lede' => 'Tell us a little about the student and we’ll help you choose campus and batch.',
            ]],
        ];
    }

    public static function aiVideoItems(): array
    {
        return [
            'kicker' => 'Hear from VR',
            'heading' => 'Video testimonials',
            'lede' => 'Students and parents on faculty, campus life and results.',
            'items' => "Vaishnavi Adiki|2-year Intermediate|https://www.youtube.com/watch?v=1GoOQhlAduU\nVinamra|Student, NEET|https://www.youtube.com/watch?v=8x_NZ_9U7fw\nA proud parent|Why we chose VR|https://www.youtube.com/watch?v=7IiUv26FzAQ\nKarthik|NEET long-term, 162 to 535|https://www.youtube.com/shorts/0o1_otU7zzI",
        ];
    }

    public static function ensureAiLanding(bool $overwrite = false): void
    {
        $exists = Database::one('SELECT id FROM page_templates WHERE slug = ?', ['ai-landing']);
        if ($exists && !$overwrite) {
            return;
        }
        $data = [
            'name' => 'AI Landing Page',
            'description' => 'Banner, programme content, video testimonials and campus list — default for AI entries.',
            'page_type' => 'landing',
            'sections_json' => Html::json(self::aiLandingSections()),
        ];
        if ($exists) {
            Database::update('page_templates', $data, 'id = ?', [(int) $exists['id']]);
        } else {
            $data['slug'] = 'ai-landing';
            Database::insert('page_templates', $data);
        }
    }

    public static function packFromPost(array $existing = []): array
    {
        $idxs = $_POST['section_idx'] ?? [];
        $types = $_POST['section_type'] ?? [];
        $out = [];
        foreach ($idxs as $i => $idx) {
            $idx = (int) $idx;
            $type = (string) ($types[$i] ?? ($existing[$idx]['type'] ?? ''));
            if ($type === '' || !isset(SectionRegistry::all()[$type])) {
                continue;
            }
            $content = $existing[$idx]['content'] ?? SectionRegistry::defaults($type);
            foreach (SectionRegistry::fields($type) as $f) {
                $key = 't' . $idx . '_' . $f['k'];
                if ($f['t'] === 'html') {
                    $content[$f['k']] = Html::allowedHtml((string) ($_POST[$key] ?? ''));
                } else {
                    $content[$f['k']] = trim((string) ($_POST[$key] ?? ''));
                }
            }
            $out[] = ['type' => $type, 'content' => $content];
        }
        return $out;
    }

    public static function saveSectionAsTemplate(array $sec, string $name): int
    {
        $name = trim($name) !== '' ? trim($name) : SectionRegistry::label($sec['type']);
        return Database::insert('section_templates', [
            'name' => $name,
            'type' => $sec['type'],
            'content_json' => $sec['content_json'] ?? Html::json($sec['content'] ?? []),
            'created_by' => Auth::id(),
        ]);
    }

    /** Upsert the starter page + section templates into the live database. */
    public static function ensureStarters(): void
    {
        $A = '/assets/img/';
        $apply = '/contact-us/#enquire';
        $brochure = 'https://drive.google.com/file/d/1ObbqqmENMGtRyrQQIbtI8uGv4Qq_6arj/view';

        $pages = [
            [
                'slug' => 'homepage',
                'name' => 'Homepage Template',
                'description' => 'Approved VR homepage layout: hero, programmes, stats, faculty, testimonials, enquire, FAQ.',
                'page_type' => 'standard',
                'sections' => [
                    ['type' => 'hero', 'content' => [
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
                        'cta_url' => $apply,
                        'cta2_label' => 'Book a campus visit',
                        'cta2_url' => '/contact-us/',
                        'video_url' => 'https://www.youtube.com/watch?v=_3sTInMS-f4',
                        'stats' => "2000+|Satisfied students\n310+|MBBS Pvt. colleges\n215+|MBBS Govt. colleges\n5|Golden years",
                    ]],
                    ['type' => 'marquee', 'content' => ['items' => "Admissions Open 2026–27\nMPC with IIT-JEE\nBiPC with NEET\nNEET Long Term\nResidential & day scholar"]],
                    ['type' => 'about', 'content' => [
                        'kicker' => 'About VR',
                        'heading' => 'About VR Junior College',
                        'quote' => 'At VR Junior College — Vision Into Reality — we believe no child is built for one exam.',
                        'image_main' => $A . 'campus/bowrampet.jpg',
                        'image_card' => $A . 'gallery/g8.jpg',
                        'badge_value' => '5',
                        'badge_label' => 'Golden years',
                        'points' => "Personal guidance|Every student is understood individually.\nVR Doctors Academy|BiPC for NEET aspirants.\nVRiiT|MPC for future engineers.",
                        'cta_label' => 'Discover VR',
                        'cta_url' => '/about/',
                    ]],
                    ['type' => 'programs', 'content' => [
                        'kicker' => 'Our programmes',
                        'heading' => 'MPC and BiPC, with integrated coaching',
                        'lede' => 'Two-year Intermediate with integrated IIT-JEE or NEET coaching, plus a one-year NEET long-term option.',
                        'split_left_kicker' => '2-year Intermediate',
                        'split_left_title' => 'Integrated coaching',
                        'split_left_text' => 'IPE + competitive exams — MPC with IIT-JEE, and BiPC with NEET.',
                        'split_right_kicker' => '1-year Long Term',
                        'split_right_title' => 'Competitive exams only',
                        'split_right_text' => 'NEET long-term for Intermediate-completed students and repeaters.',
                    ]],
                    ['type' => 'stats', 'content' => ['items' => "2000+|Satisfied students\n310+|MBBS Pvt. colleges\n215+|MBBS Govt. colleges\n5|Golden years"]],
                    ['type' => 'faculty', 'content' => ['kicker' => 'Our faculty', 'heading' => 'The minds behind VR students’ success']],
                    ['type' => 'testimonials', 'content' => ['label' => 'Testimonials']],
                    ['type' => 'enquire', 'content' => [
                        'kicker' => 'Admissions 2026–27',
                        'heading' => 'Shape your future with VR Junior College',
                        'lede' => 'Admissions open for MPC & BiPC.',
                    ]],
                    ['type' => 'faq', 'content' => ['kicker' => 'FAQs', 'heading' => 'Questions families ask']],
                ],
            ],
            [
                'slug' => 'inner-standard',
                'name' => 'Inner Page Template',
                'description' => 'Generic inner page (About, Campuses, team): hero, text + image, features, enquire.',
                'page_type' => 'standard',
                'sections' => [
                    ['type' => 'page_hero', 'content' => [
                        'kicker' => 'VR Junior College',
                        'heading' => 'Page title',
                        'lead' => 'Replace this with a short introduction for the page.',
                        'image' => $A . 'banner/campus-life-bg.jpg',
                        'crumb' => 'Page',
                    ]],
                    ['type' => 'split', 'content' => [
                        'kicker' => 'Overview',
                        'heading' => 'Tell the story',
                        'lede' => 'A two-column text + image block for the main message.',
                        'points' => "Point one|Add a supporting line.\nPoint two|Add a supporting line.",
                        'image' => $A . 'life/campus-1.jpg',
                        'cta_label' => 'Apply now',
                        'cta_url' => $apply,
                    ]],
                    ['type' => 'features', 'content' => [
                        'kicker' => 'Highlights',
                        'heading' => 'Why families choose VR',
                        'cards' => "01|Personal guidance|Every student is understood individually.\n02|Specialised streams|MPC with IIT-JEE and BiPC with NEET.\n03|Residential campuses|Safe hostels across Hyderabad.",
                    ]],
                    ['type' => 'enquire', 'content' => [
                        'heading' => 'Talk to admissions',
                        'lede' => 'Visit a campus or leave a message and we’ll call you back.',
                    ]],
                ],
            ],
            [
                'slug' => 'ai-landing',
                'name' => 'AI Landing Page',
                'description' => 'Banner, programme content, video testimonials and campus list — default for AI entries.',
                'page_type' => 'landing',
                'sections' => self::aiLandingSections(),
            ],
            [
                'slug' => 'landing-page',
                'name' => 'Landing Page Template',
                'description' => 'Default for new courses / CPT entries: campaign hero, highlights, stats, testimonials, FAQ, form.',
                'page_type' => 'landing',
                'sections' => [
                    ['type' => 'campaign_hero', 'content' => [
                        'kicker' => 'New programme',
                        'heading' => 'Landing page headline',
                        'lead' => 'A focused landing layout for a course, event or AI Program.',
                        'chips' => "IPE\nCoaching\nPersonal guidance",
                        'image' => $A . 'life/campus-2.jpg',
                        'figure' => $A . 'life/campus-1.jpg',
                        'crumb' => 'Programme',
                        'offer_kicker' => 'At a glance',
                        'offer_title' => 'What this programme includes',
                        'offer_text' => 'Replace with duration, stream and who it’s for.',
                        'offer_points' => "Personal mentoring\nStructured tests\nResidential option",
                        'cta_label' => 'Apply now',
                        'cta_url' => $apply,
                        'cta2_label' => 'Download brochure',
                        'cta2_url' => $brochure,
                    ]],
                    ['type' => 'proof', 'content' => [
                        'items' => "Experienced faculty|Guide students through board and entrance exams.\nPersonal attention|A plan around each student’s strengths.\nClear pathways|One exam is not the only destination.",
                    ]],
                    ['type' => 'stats', 'content' => ['items' => "2000+|Satisfied students\n5|Campuses\n37 yrs|Academic leadership"]],
                    ['type' => 'testimonials', 'content' => ['label' => 'Testimonials']],
                    ['type' => 'faq', 'content' => [
                        'kicker' => 'FAQs',
                        'heading' => 'Questions families ask',
                        'items' => "Who is this programme for?|Replace with eligibility.\nIs hostel available?|Yes — residential and day-scholar options.",
                    ]],
                    ['type' => 'enquire', 'content' => [
                        'kicker' => 'Admissions open',
                        'heading' => 'Enquire about this programme',
                        'lede' => 'Tell us a little about the student and we’ll help you choose campus and batch.',
                    ]],
                ],
            ],
            [
                'slug' => 'contact-page',
                'name' => 'Contact Page Template',
                'description' => 'Hero plus the admissions enquiry form and campus contact details.',
                'page_type' => 'standard',
                'sections' => [
                    ['type' => 'page_hero', 'content' => [
                        'kicker' => 'Admissions 2026–27',
                        'heading' => 'Contact us',
                        'lead' => 'Get in touch for admissions, course details, or a campus visit.',
                        'image' => $A . 'campus/hafeezpet.jpg',
                        'crumb' => 'Contact',
                    ]],
                    ['type' => 'enquire', 'content' => [
                        'heading' => 'Let us help you take the next step',
                        'lede' => 'We’re here to guide you on your journey to success.',
                    ]],
                ],
            ],
            [
                'slug' => 'blog-post',
                'name' => 'Blog / Notice Post Template',
                'description' => 'Inner hero, rich text body, and a related-posts grid.',
                'page_type' => 'standard',
                'sections' => [
                    ['type' => 'page_hero', 'content' => [
                        'kicker' => 'Insights',
                        'heading' => 'Article title',
                        'lead' => 'A short standfirst for the notice or blog post.',
                        'image' => $A . 'gallery/g6.jpg',
                        'crumb' => 'Blog',
                    ]],
                    ['type' => 'rich_text', 'content' => [
                        'html' => '<p>Write the article here. You can add headings, lists and links.</p><h2>Key takeaway</h2><p>Replace this with the body of the notice or post.</p>',
                    ]],
                    ['type' => 'blog', 'content' => ['kicker' => 'More', 'heading' => 'Latest from the blog']],
                ],
            ],
            [
                'slug' => 'gallery-page',
                'name' => 'Gallery Page Template',
                'description' => 'Hero plus the campus photo grid used on the live gallery page.',
                'page_type' => 'standard',
                'sections' => [
                    ['type' => 'page_hero', 'content' => [
                        'kicker' => 'Campus gallery',
                        'heading' => 'Life at VR, in pictures.',
                        'lead' => 'Classrooms, hostels, dining and campuses across Hyderabad.',
                        'image' => $A . 'gallery/classroom-empty.jpg',
                        'crumb' => 'Gallery',
                    ]],
                    ['type' => 'gallery', 'content' => [
                        'images' => implode("\n", [
                            $A . 'gallery/corridor.jpg',
                            $A . 'gallery/hostel-study.jpg',
                            $A . 'gallery/dining-portrait.jpg',
                            $A . 'gallery/hostel-room.jpg',
                            $A . 'gallery/dining-hall.jpg',
                            $A . 'gallery/classroom.jpg',
                            $A . 'gallery/building-bowrampet.jpg',
                            $A . 'gallery/building-hafeezpet.jpg',
                        ]),
                    ]],
                ],
            ],
        ];

        foreach ($pages as $p) {
            $data = [
                'name' => $p['name'],
                'description' => $p['description'],
                'page_type' => $p['page_type'],
                'sections_json' => Html::json($p['sections']),
            ];
            $exists = Database::one('SELECT id FROM page_templates WHERE slug = ?', [$p['slug']]);
            if ($exists) {
                Database::update('page_templates', $data, 'id = ?', [(int) $exists['id']]);
            } else {
                $data['slug'] = $p['slug'];
                Database::insert('page_templates', $data);
            }
        }

        $sections = [
            ['Hero — Admissions Open', 'hero', [
                'kicker' => 'Admissions Open 2026–27 · MPC & BiPC',
                'heading' => "Hyderabad’s leading residential junior college for MPC & BiPC.",
                'lead' => 'Expert NEET and IIT-JEE coaching backed by personal mentorship.',
                'image' => $A . 'banner/hero-class.jpg',
                'figure' => $A . 'life/campus-1.jpg',
                'cta_label' => 'Apply now',
                'cta_url' => $apply,
                'video_url' => 'https://www.youtube.com/watch?v=_3sTInMS-f4',
                'stats' => "2000+|Satisfied students\n5|Golden years",
            ]],
            ['Stats Counter', 'stats', [
                'items' => "2000+|Satisfied students\n310+|MBBS Pvt. colleges\n215+|MBBS Govt. colleges\n5|Golden years",
            ]],
            ['Testimonials Row', 'testimonials', ['label' => 'Testimonials']],
            ['FAQ Accordion', 'faq', [
                'kicker' => 'FAQs',
                'heading' => 'Questions families ask',
                'items' => "What courses does VR offer?|MPC with IIT-JEE, BiPC with NEET, and NEET long-term.\nIs VR residential?|Yes — hostel and day-scholar options.",
            ]],
            ['CTA Banner', 'cta_banner', [
                'kicker' => 'Admissions 2026',
                'heading' => 'Shape your future with the best junior college in Hyderabad',
                'text' => 'Admissions open for MPC & BiPC.',
                'image' => $A . 'banner/hero-ceremony.jpg',
                'cta_label' => 'Apply now',
                'cta_url' => $apply,
                'cta2_label' => 'Book a campus visit',
                'cta2_url' => '/contact-us/',
            ]],
            ['Faculty / Team Grid', 'faculty', [
                'kicker' => 'Our faculty',
                'heading' => 'The minds behind VR students’ success',
            ]],
        ];

        $editor = Database::one('SELECT id FROM roles WHERE slug = ?', ['editor']);
        if ($editor) {
            foreach (['templates.view', 'templates.create', 'templates.edit'] as $slug) {
                $perm = Database::one('SELECT id FROM permissions WHERE slug = ?', [$slug]);
                if (!$perm) {
                    continue;
                }
                $has = Database::one(
                    'SELECT role_id FROM role_permissions WHERE role_id = ? AND permission_id = ?',
                    [(int) $editor['id'], (int) $perm['id']]
                );
                if (!$has) {
                    Database::insert('role_permissions', [
                        'role_id' => (int) $editor['id'],
                        'permission_id' => (int) $perm['id'],
                    ]);
                }
            }
        }

        foreach ($sections as $s) {
            $exists = Database::one('SELECT id FROM section_templates WHERE name = ? AND type = ?', [$s[0], $s[1]]);
            $payload = [
                'name' => $s[0],
                'type' => $s[1],
                'content_json' => Html::json($s[2]),
                'created_by' => Auth::id(),
            ];
            if ($exists) {
                Database::update('section_templates', [
                    'content_json' => $payload['content_json'],
                ], 'id = ?', [(int) $exists['id']]);
            } else {
                Database::insert('section_templates', $payload);
            }
        }
    }
}
