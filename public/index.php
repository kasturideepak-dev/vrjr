<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$r = new Router();
$auth = ['auth' => true];

$r->get('/admin/login/', fn () => AdminAuth::loginForm());
$r->post('/admin/login/', fn () => AdminAuth::login());
$r->get('/admin/login/2fa/', fn () => AdminAuth::twoFactorForm());
$r->post('/admin/login/2fa/', fn () => AdminAuth::twoFactor());
$r->post('/admin/logout/', fn () => AdminAuth::logout(), $auth);
$r->get('/admin/forgot/', fn () => AdminAuth::forgotForm());
$r->post('/admin/forgot/', fn () => AdminAuth::forgot());
$r->get('/admin/reset/(?P<token>[a-f0-9]{64})/', fn ($t) => AdminAuth::resetForm($t));
$r->post('/admin/reset/(?P<token>[a-f0-9]{64})/', fn ($t) => AdminAuth::reset($t));

$r->get('/admin/', fn () => Admin::dashboard(), $auth);
$r->get('/admin/search/', fn () => Admin::search(), $auth);

$r->get('/admin/pages/', fn () => AdminPages::index(), $auth + ['perm' => 'pages.view']);
$r->get('/admin/pages/new/', fn () => AdminPages::createForm(), $auth + ['perm' => 'pages.create']);
$r->post('/admin/pages/new/', fn () => AdminPages::create(), $auth + ['perm' => 'pages.create']);
$r->get('/admin/pages/(?P<id>\d+)/', fn ($id) => AdminPages::edit($id), $auth + ['perm' => 'pages.edit']);
$r->post('/admin/pages/(?P<id>\d+)/', fn ($id) => AdminPages::save($id), $auth + ['perm' => 'pages.edit']);
$r->post('/admin/pages/(?P<id>\d+)/section/', fn ($id) => AdminPages::addSection($id), $auth + ['perm' => 'pages.edit']);
$r->post('/admin/pages/(?P<id>\d+)/section-delete/', fn ($id) => AdminPages::deleteSection($id), $auth + ['perm' => 'pages.edit']);
$r->post('/admin/pages/(?P<id>\d+)/section-duplicate/', fn ($id) => AdminPages::duplicateSection($id), $auth + ['perm' => 'pages.edit']);
$r->post('/admin/pages/(?P<id>\d+)/reorder/', fn ($id) => AdminPages::reorder($id), $auth + ['perm' => 'pages.edit']);
$r->post('/admin/pages/(?P<id>\d+)/section-visible/', fn ($id) => AdminPages::toggleVisible($id), $auth + ['perm' => 'pages.edit']);
$r->get('/admin/pages/(?P<id>\d+)/revision/(?P<rid>\d+)/preview/', fn ($id, $rid) => AdminPages::revisionPreview($id, $rid), $auth + ['perm' => 'pages.view']);
$r->post('/admin/pages/(?P<id>\d+)/section-template/', fn ($id) => AdminPages::saveSectionTemplate($id), $auth + ['perm' => 'templates.create']);
$r->post('/admin/pages/(?P<id>\d+)/page-template/', fn ($id) => AdminPages::savePageTemplate($id), $auth + ['perm' => 'templates.create']);
$r->post('/admin/pages/(?P<id>\d+)/publish/', fn ($id) => AdminPages::publish($id), $auth + ['perm' => 'pages.publish']);
$r->post('/admin/pages/(?P<id>\d+)/unpublish/', fn ($id) => AdminPages::unpublish($id), $auth + ['perm' => 'pages.publish']);
$r->post('/admin/pages/(?P<id>\d+)/preview/', fn ($id) => AdminPages::preview($id), $auth + ['perm' => 'pages.view']);
$r->post('/admin/pages/(?P<id>\d+)/restore-rev/', fn ($id) => AdminPages::restoreRev($id), $auth + ['perm' => 'pages.edit']);
$r->post('/admin/pages/trash/', fn () => AdminPages::trash(), $auth + ['perm' => 'pages.delete']);
$r->post('/admin/pages/restore/', fn () => AdminPages::restore(), $auth + ['perm' => 'pages.edit']);
$r->post('/admin/pages/delete/', fn () => AdminPages::destroy(), $auth + ['perm' => 'pages.delete']);

$r->get('/admin/post-types/', fn () => AdminPostTypes::index(), $auth + ['perm' => 'post_types.view']);
$r->get('/admin/post-types/new/', fn () => AdminPostTypes::form(), $auth + ['perm' => 'post_types.create']);
$r->get('/admin/post-types/(?P<id>\d+)/confirm-delete/', fn ($id) => AdminPostTypes::confirmDelete($id), $auth + ['perm' => 'post_types.delete']);
$r->get('/admin/post-types/(?P<id>\d+)/', fn ($id) => AdminPostTypes::form($id), $auth + ['perm' => 'post_types.edit']);
$r->post('/admin/post-types/', fn () => AdminPostTypes::save(), $auth);
$r->post('/admin/post-types/delete/', fn () => AdminPostTypes::delete(), $auth + ['perm' => 'post_types.delete']);
$r->post('/admin/post-types/archive/', fn () => AdminPostTypes::archive(), $auth + ['perm' => 'post_types.edit']);
$r->post('/admin/post-types/restore/', fn () => AdminPostTypes::restore(), $auth + ['perm' => 'post_types.edit']);

$r->get('/admin/content/(?P<type>[a-z0-9-]+)/', fn ($t) => AdminEntries::index($t), $auth + ['perm' => 'entries.view']);
$r->get('/admin/content/(?P<type>[a-z0-9-]+)/new/', fn ($t) => AdminEntries::form($t), $auth + ['perm' => 'entries.create']);
$r->get('/admin/content/(?P<type>[a-z0-9-]+)/(?P<id>\d+)/', fn ($t, $id) => AdminEntries::form($t, $id), $auth + ['perm' => 'entries.edit']);
$r->post('/admin/content/(?P<type>[a-z0-9-]+)/', fn ($t) => AdminEntries::save($t), $auth);
$r->post('/admin/content/(?P<type>[a-z0-9-]+)/(?P<id>\d+)/section/', fn ($t, $id) => AdminEntries::addSection($t, $id), $auth + ['perm' => 'entries.edit']);
$r->post('/admin/content/(?P<type>[a-z0-9-]+)/(?P<id>\d+)/section-template/', fn ($t, $id) => AdminEntries::saveSectionTemplate($t, $id), $auth + ['perm' => 'templates.create']);
$r->post('/admin/content/(?P<type>[a-z0-9-]+)/(?P<id>\d+)/section-delete/', fn ($t, $id) => AdminEntries::deleteSection($t, $id), $auth + ['perm' => 'entries.edit']);
$r->post('/admin/content/(?P<type>[a-z0-9-]+)/(?P<id>\d+)/publish/', fn ($t, $id) => AdminEntries::publish($t, $id), $auth + ['perm' => 'entries.publish']);
$r->post('/admin/content/(?P<type>[a-z0-9-]+)/(?P<id>\d+)/preview/', fn ($t, $id) => AdminEntries::preview($t, $id), $auth + ['perm' => 'entries.view']);
$r->post('/admin/content/(?P<type>[a-z0-9-]+)/trash/', fn ($t) => AdminEntries::trash($t), $auth + ['perm' => 'entries.delete']);
$r->post('/admin/content/(?P<type>[a-z0-9-]+)/restore/', fn ($t) => AdminEntries::restore($t), $auth + ['perm' => 'entries.edit']);
$r->post('/admin/content/(?P<type>[a-z0-9-]+)/delete/', fn ($t) => AdminEntries::destroy($t), $auth + ['perm' => 'entries.delete']);

$r->get('/admin/blog/', fn () => AdminBlog::index(), $auth + ['perm' => 'blog.view']);
$r->get('/admin/blog/new/', fn () => AdminBlog::form(), $auth + ['perm' => 'blog.create']);
$r->get('/admin/blog/(?P<id>\d+)/', fn ($id) => AdminBlog::form($id), $auth + ['perm' => 'blog.edit']);
$r->post('/admin/blog/', fn () => AdminBlog::save(), $auth);
$r->post('/admin/blog/trash/', fn () => AdminBlog::trash(), $auth + ['perm' => 'blog.delete']);
$r->post('/admin/blog/restore/', fn () => AdminBlog::restore(), $auth + ['perm' => 'blog.edit']);
$r->post('/admin/blog/delete/', fn () => AdminBlog::destroy(), $auth + ['perm' => 'blog.delete']);
$r->post('/admin/blog/category/', fn () => AdminBlog::saveCategory(), $auth + ['perm' => 'blog.edit']);

$r->get('/admin/users/', fn () => Admin::users(), $auth + ['perm' => 'users.view']);
$r->get('/admin/users/new/', fn () => Admin::userForm(), $auth + ['perm' => 'users.create']);
$r->get('/admin/users/(?P<id>\d+)/', fn ($id) => Admin::userForm($id), $auth + ['perm' => 'users.edit']);
$r->post('/admin/users/', fn () => Admin::userSave(), $auth);

$r->get('/admin/settings/', fn () => Admin::settings(), $auth + ['perm' => 'settings.view']);
$r->post('/admin/settings/', fn () => Admin::settingsSave(), $auth + ['perm' => 'settings.edit']);
$r->post('/admin/settings/smtp-test/', fn () => Admin::smtpTest(), $auth + ['perm' => 'settings.edit']);

$r->get('/admin/media/', fn () => AdminMedia::index(), $auth + ['perm' => 'media.view']);
$r->post('/admin/media/', fn () => AdminMedia::upload(), $auth + ['perm' => 'media.upload']);
$r->post('/admin/media/update/', fn () => AdminMedia::update(), $auth + ['perm' => 'media.upload']);
$r->post('/admin/media/delete/', fn () => AdminMedia::delete(), $auth + ['perm' => 'media.delete']);

$r->get('/admin/menus/', fn () => AdminMenus::index(), $auth + ['perm' => 'menus.view']);
$r->post('/admin/menus/', fn () => AdminMenus::save(), $auth + ['perm' => 'menus.edit']);
$r->post('/admin/menus/item-delete/', fn () => AdminMenus::itemDelete(), $auth + ['perm' => 'menus.edit']);

$r->get('/admin/redirects/', fn () => AdminRedirects::index(), $auth + ['perm' => 'redirects.view']);
$r->post('/admin/redirects/', fn () => AdminRedirects::save(), $auth + ['perm' => 'redirects.edit']);
$r->post('/admin/redirects/delete/', fn () => AdminRedirects::delete(), $auth + ['perm' => 'redirects.edit']);

$r->get('/admin/forms/', fn () => AdminForms::index(), $auth + ['perm' => 'forms.view']);
$r->get('/admin/forms/new/', fn () => AdminForms::edit(), $auth + ['perm' => 'forms.edit']);
$r->get('/admin/forms/(?P<id>\d+)/', fn ($id) => AdminForms::edit($id), $auth + ['perm' => 'forms.edit']);
$r->post('/admin/forms/', fn () => AdminForms::save(), $auth + ['perm' => 'forms.edit']);
$r->post('/admin/forms/recipient/', fn () => AdminForms::recipientAdd(), $auth + ['perm' => 'forms.edit']);
$r->post('/admin/forms/recipient-delete/', fn () => AdminForms::recipientDelete(), $auth + ['perm' => 'forms.edit']);

$r->get('/admin/leads/', fn () => AdminForms::leads(), $auth + ['perm' => 'leads.view']);
$r->get('/admin/leads/export/', fn () => AdminForms::leadsExport(), $auth + ['perm' => 'leads.export']);
$r->get('/admin/leads/(?P<id>\d+)/', fn ($id) => AdminForms::leadView($id), $auth + ['perm' => 'leads.view']);
$r->post('/admin/leads/', fn () => AdminForms::leadSave(), $auth + ['perm' => 'leads.edit']);

$r->get('/admin/seo/', fn () => AdminSeo::index(), $auth + ['perm' => 'seo.view']);
$r->post('/admin/seo/', fn () => AdminSeo::save(), $auth + ['perm' => 'seo.edit']);

$r->get('/admin/backup/', fn () => AdminBackup::index(), $auth + ['perm' => 'backups.view']);
$r->post('/admin/backup/', fn () => AdminBackup::create(), $auth + ['perm' => 'backups.create']);
$r->get('/admin/backup/download/', fn () => AdminBackup::download(), $auth + ['perm' => 'backups.view']);
$r->post('/admin/backup/restore/', fn () => AdminBackup::restore(), $auth + ['perm' => 'backups.restore']);

$r->get('/admin/audit/', fn () => Admin::audit(), $auth);
$r->get('/admin/faqs/', fn () => AdminContent::faqs(), $auth + ['perm' => 'faqs.view']);
$r->post('/admin/faqs/', fn () => AdminContent::faqSave(), $auth + ['perm' => 'faqs.edit']);
$r->post('/admin/faqs/delete/', fn () => AdminContent::faqDelete(), $auth + ['perm' => 'faqs.edit']);
$r->get('/admin/testimonials/', fn () => AdminContent::testimonials(), $auth + ['perm' => 'testimonials.view']);
$r->post('/admin/testimonials/', fn () => AdminContent::testimonialSave(), $auth + ['perm' => 'testimonials.edit']);
$r->post('/admin/testimonials/delete/', fn () => AdminContent::testimonialDelete(), $auth + ['perm' => 'testimonials.edit']);
$super = $auth + ['super' => true];
$r->get('/admin/snippets/', fn () => AdminSnippets::index(), $super);
$r->get('/admin/snippets/new/', fn () => AdminSnippets::form(), $super);
$r->get('/admin/snippets/(?P<id>\d+)/', fn ($id) => AdminSnippets::form($id), $super);
$r->get('/admin/snippets/(?P<id>\d+)/preview/', fn ($id) => AdminSnippets::preview($id), $super);
$r->post('/admin/snippets/', fn () => AdminSnippets::save(), $super);
$r->post('/admin/snippets/(?P<id>\d+)/', fn ($id) => AdminSnippets::save($id), $super);
$r->post('/admin/snippets/(?P<id>\d+)/toggle/', fn ($id) => AdminSnippets::toggle($id), $super);
$r->post('/admin/snippets/duplicate/', fn () => AdminSnippets::duplicate(), $super);
$r->post('/admin/snippets/delete/', fn () => AdminSnippets::delete(), $super);

$r->get('/admin/templates/', fn () => AdminTemplates::index(), $auth + ['perm' => 'templates.view']);
$r->get('/admin/templates/pages/new/', fn () => AdminTemplates::pageNew(), $auth + ['perm' => 'templates.create']);
$r->get('/admin/templates/pages/(?P<id>\d+)/', fn ($id) => AdminTemplates::pageEdit($id), $auth + ['perm' => 'templates.edit']);
$r->post('/admin/templates/pages/(?P<id>\d+)/', fn ($id) => AdminTemplates::pageSave($id), $auth + ['perm' => 'templates.edit']);
$r->post('/admin/templates/pages/duplicate/', fn () => AdminTemplates::pageDuplicate(), $auth + ['perm' => 'templates.create']);
$r->post('/admin/templates/pages/delete/', fn () => AdminTemplates::pageDelete(), $auth + ['perm' => 'templates.delete']);
$r->get('/admin/templates/sections/new/', fn () => AdminTemplates::sectionNew(), $auth + ['perm' => 'templates.create']);
$r->get('/admin/templates/sections/(?P<id>\d+)/', fn ($id) => AdminTemplates::sectionEdit($id), $auth + ['perm' => 'templates.edit']);
$r->post('/admin/templates/sections/', fn () => AdminTemplates::sectionSave(), $auth);
$r->post('/admin/templates/sections/duplicate/', fn () => AdminTemplates::sectionDuplicate(), $auth + ['perm' => 'templates.create']);
$r->post('/admin/templates/sections/delete/', fn () => AdminTemplates::sectionDelete(), $auth + ['perm' => 'templates.delete']);
$r->get('/admin/api/slug-check/', fn () => AdminContent::slugCheck(), $auth);

$r->post('/enquire/', fn () => AdminForms::submit(), ['no_csrf' => false]);
$r->get('/thank-you/', fn () => PublicSite::thankYou());

$cronKey = preg_quote((string) app_config('cron_key'), '#');
$r->get('/cron/' . $cronKey . '/', function () {
    header('Content-Type: application/json');
    echo Html::json(Cron::run());
});

$r->get('/(?P<path>.*)', function ($path) {
    $path = $path === '' ? '/' : '/' . trim($path, '/') . '/';
    if ($path === '//') {
        $path = '/';
    }
    PublicSite::handle($path === '/' ? '/' : $path);
});

$r->dispatch();
