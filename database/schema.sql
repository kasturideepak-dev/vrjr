-- VR Junior College CMS — MySQL 8+ / MariaDB 10.5+
-- Production schema. Character set utf8mb4 throughout.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS roles (
  id TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(40) NOT NULL,
  name VARCHAR(80) NOT NULL,
  description VARCHAR(255) NULL,
  is_system TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roles_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(80) NOT NULL,
  name VARCHAR(120) NOT NULL,
  group_name VARCHAR(40) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_permissions_slug (slug),
  KEY idx_permissions_group (group_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id TINYINT UNSIGNED NOT NULL,
  permission_id SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  role_id TINYINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  two_factor_enabled TINYINT(1) NOT NULL DEFAULT 0,
  last_login_at DATETIME NULL,
  last_login_ip VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  KEY idx_users_status (status),
  CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS login_attempts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  email VARCHAR(190) NOT NULL,
  ip VARCHAR(45) NOT NULL,
  success TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_login_email_time (email, created_at),
  KEY idx_login_ip_time (ip, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS password_resets (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_pr_hash (token_hash),
  KEY idx_pr_user (user_id),
  CONSTRAINT fk_pr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS two_factor_codes (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  code_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_2fa_user (user_id),
  CONSTRAINT fk_2fa_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS remember_tokens (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  selector CHAR(24) NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_remember_selector (selector),
  KEY idx_remember_user (user_id),
  CONSTRAINT fk_remember_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  disk_path VARCHAR(255) NOT NULL,
  public_path VARCHAR(255) NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  mime VARCHAR(100) NOT NULL,
  extension VARCHAR(12) NOT NULL,
  size_bytes INT UNSIGNED NOT NULL DEFAULT 0,
  width SMALLINT UNSIGNED NULL,
  height SMALLINT UNSIGNED NULL,
  alt_text VARCHAR(255) NULL,
  title VARCHAR(190) NULL,
  caption VARCHAR(255) NULL,
  folder VARCHAR(80) NOT NULL DEFAULT 'general',
  uploaded_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_media_folder (folder),
  KEY idx_media_created (created_at),
  KEY idx_media_name (original_name),
  CONSTRAINT fk_media_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS media_usage (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  media_id INT UNSIGNED NOT NULL,
  owner_type VARCHAR(40) NOT NULL,
  owner_id INT UNSIGNED NOT NULL,
  field_name VARCHAR(80) NULL,
  PRIMARY KEY (id),
  KEY idx_mu_media (media_id),
  KEY idx_mu_owner (owner_type, owner_id),
  CONSTRAINT fk_mu_media FOREIGN KEY (media_id) REFERENCES media(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS page_templates (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(80) NOT NULL,
  name VARCHAR(120) NOT NULL,
  description VARCHAR(255) NULL,
  page_type VARCHAR(40) NOT NULL DEFAULT 'standard',
  thumbnail VARCHAR(255) NULL,
  sections_json JSON NULL,
  default_seo_json JSON NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_page_templates_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  type VARCHAR(40) NOT NULL DEFAULT 'standard',
  template_id SMALLINT UNSIGNED NULL,
  title VARCHAR(190) NOT NULL,
  slug VARCHAR(190) NOT NULL,
  status ENUM('draft','published','scheduled','unpublished') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  scheduled_at DATETIME NULL,
  deleted_at DATETIME NULL,
  created_by INT UNSIGNED NULL,
  updated_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_pages_slug (slug),
  KEY idx_pages_status (status),
  KEY idx_pages_type (type),
  KEY idx_pages_published (published_at),
  KEY idx_pages_deleted (deleted_at),
  KEY idx_pages_scheduled (scheduled_at, status),
  CONSTRAINT fk_pages_template FOREIGN KEY (template_id) REFERENCES page_templates(id) ON DELETE SET NULL,
  CONSTRAINT fk_pages_created FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_pages_updated FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS content_sections (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  owner_type VARCHAR(20) NOT NULL DEFAULT 'page',
  owner_id INT UNSIGNED NOT NULL,
  type VARCHAR(60) NOT NULL,
  heading VARCHAR(190) NULL,
  content_json JSON NOT NULL,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  section_template_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_sections_owner (owner_type, owner_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS section_templates (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  type VARCHAR(60) NOT NULL,
  content_json JSON NOT NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_st_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS content_revisions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  owner_type VARCHAR(20) NOT NULL DEFAULT 'page',
  owner_id INT UNSIGNED NOT NULL,
  is_live TINYINT(1) NOT NULL DEFAULT 0,
  snapshot_json JSON NOT NULL,
  note VARCHAR(255) NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_rev_owner_live (owner_type, owner_id, is_live),
  KEY idx_rev_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS seo_metadata (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  entity_type VARCHAR(40) NOT NULL,
  entity_id INT UNSIGNED NOT NULL,
  seo_title VARCHAR(190) NULL,
  meta_description VARCHAR(320) NULL,
  canonical_url VARCHAR(255) NULL,
  robots VARCHAR(40) NOT NULL DEFAULT 'index,follow',
  og_title VARCHAR(190) NULL,
  og_description VARCHAR(320) NULL,
  og_image VARCHAR(255) NULL,
  twitter_title VARCHAR(190) NULL,
  twitter_description VARCHAR(320) NULL,
  twitter_image VARCHAR(255) NULL,
  schema_json JSON NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_seo_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generic Custom Post Type engine (Courses, Events, AI Program, Notices, Faculty, Campuses, ...)
CREATE TABLE IF NOT EXISTS post_types (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  singular_name VARCHAR(120) NOT NULL,
  slug VARCHAR(80) NOT NULL,
  description VARCHAR(255) NULL,
  has_archive TINYINT(1) NOT NULL DEFAULT 1,
  public TINYINT(1) NOT NULL DEFAULT 1,
  hierarchical TINYINT(1) NOT NULL DEFAULT 0,
  menu_icon VARCHAR(40) NULL,
  archive_title VARCHAR(190) NULL,
  archive_intro TEXT NULL,
  template_mode ENUM('fields','builder','both') NOT NULL DEFAULT 'both',
  supports_featured_image TINYINT(1) NOT NULL DEFAULT 1,
  supports_excerpt TINYINT(1) NOT NULL DEFAULT 1,
  supports_editor TINYINT(1) NOT NULL DEFAULT 1,
  supports_seo TINYINT(1) NOT NULL DEFAULT 1,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  status ENUM('active','inactive','archived') NOT NULL DEFAULT 'active',
  is_system TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_post_types_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS post_type_fields (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_type_id INT UNSIGNED NOT NULL,
  name VARCHAR(60) NOT NULL,
  label VARCHAR(120) NOT NULL,
  type VARCHAR(30) NOT NULL,
  options_json JSON NULL,
  help_text VARCHAR(255) NULL,
  placeholder VARCHAR(190) NULL,
  is_required TINYINT(1) NOT NULL DEFAULT 0,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ptf_name (post_type_id, name),
  KEY idx_ptf_type (post_type_id, sort_order),
  CONSTRAINT fk_ptf_type FOREIGN KEY (post_type_id) REFERENCES post_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cpt_entries (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  post_type_id INT UNSIGNED NOT NULL,
  title VARCHAR(190) NOT NULL,
  slug VARCHAR(190) NOT NULL,
  status ENUM('draft','published','scheduled','unpublished') NOT NULL DEFAULT 'draft',
  excerpt TEXT NULL,
  featured_image VARCHAR(255) NULL,
  body_html MEDIUMTEXT NULL,
  fields_json JSON NULL,
  published_at DATETIME NULL,
  scheduled_at DATETIME NULL,
  deleted_at DATETIME NULL,
  author_id INT UNSIGNED NULL,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_cpt_type_slug (post_type_id, slug),
  KEY idx_cpt_status (status),
  KEY idx_cpt_deleted (deleted_at),
  KEY idx_cpt_scheduled (scheduled_at, status),
  KEY idx_cpt_sort (post_type_id, sort_order),
  CONSTRAINT fk_cpt_type FOREIGN KEY (post_type_id) REFERENCES post_types(id) ON DELETE CASCADE,
  CONSTRAINT fk_cpt_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_categories (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(80) NOT NULL,
  description VARCHAR(255) NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_bcat_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_tags (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(80) NOT NULL,
  slug VARCHAR(80) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_btag_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_posts (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(190) NOT NULL,
  slug VARCHAR(190) NOT NULL,
  excerpt TEXT NULL,
  body_html MEDIUMTEXT NULL,
  featured_image VARCHAR(255) NULL,
  author_id INT UNSIGNED NULL,
  status ENUM('draft','published','scheduled','unpublished') NOT NULL DEFAULT 'draft',
  published_at DATETIME NULL,
  scheduled_at DATETIME NULL,
  deleted_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_posts_slug (slug),
  KEY idx_posts_status_pub (status, published_at),
  KEY idx_posts_deleted (deleted_at),
  CONSTRAINT fk_posts_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_post_categories (
  post_id INT UNSIGNED NOT NULL,
  category_id SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (post_id, category_id),
  CONSTRAINT fk_bpc_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
  CONSTRAINT fk_bpc_cat FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS blog_post_tags (
  post_id INT UNSIGNED NOT NULL,
  tag_id SMALLINT UNSIGNED NOT NULL,
  PRIMARY KEY (post_id, tag_id),
  CONSTRAINT fk_bpt_post FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
  CONSTRAINT fk_bpt_tag FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS testimonials (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  role VARCHAR(120) NULL,
  quote TEXT NOT NULL,
  initials VARCHAR(4) NULL,
  video_url VARCHAR(255) NULL,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS faqs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  question VARCHAR(255) NOT NULL,
  answer TEXT NOT NULL,
  entity_type VARCHAR(40) NOT NULL DEFAULT 'global',
  entity_id INT UNSIGNED NULL,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_faqs_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS forms (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(80) NOT NULL,
  success_message VARCHAR(255) NULL,
  notify_email TEXT NULL,
  honeypot_field VARCHAR(40) NOT NULL DEFAULT 'website',
  recaptcha_enabled TINYINT(1) NOT NULL DEFAULT 0,
  sheets_sync TINYINT(1) NOT NULL DEFAULT 0,
  sheet_id VARCHAR(80) NULL,
  sheet_tab VARCHAR(80) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_forms_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS form_recipients (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  form_id SMALLINT UNSIGNED NOT NULL,
  email VARCHAR(190) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_form_recipient (form_id, email),
  CONSTRAINT fk_fr_form FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS form_fields (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  form_id SMALLINT UNSIGNED NOT NULL,
  name VARCHAR(60) NOT NULL,
  label VARCHAR(120) NOT NULL,
  type VARCHAR(30) NOT NULL,
  options_json JSON NULL,
  placeholder VARCHAR(190) NULL,
  width ENUM('full','half') NOT NULL DEFAULT 'full',
  is_required TINYINT(1) NOT NULL DEFAULT 0,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_ff_form (form_id, sort_order),
  CONSTRAINT fk_ff_form FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS form_submissions (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  form_id SMALLINT UNSIGNED NOT NULL,
  payload_json JSON NOT NULL,
  status ENUM('new','contacted','closed') NOT NULL DEFAULT 'new',
  notes TEXT NULL,
  ip VARCHAR(45) NULL,
  sheets_synced TINYINT(1) NOT NULL DEFAULT 0,
  sheets_error VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_fs_form_status (form_id, status),
  KEY idx_fs_created (created_at),
  CONSTRAINT fk_fs_form FOREIGN KEY (form_id) REFERENCES forms(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menus (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(40) NOT NULL,
  name VARCHAR(80) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_menus_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS menu_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  menu_id SMALLINT UNSIGNED NOT NULL,
  parent_id INT UNSIGNED NULL,
  label VARCHAR(120) NOT NULL,
  url VARCHAR(255) NOT NULL DEFAULT '',
  link_type VARCHAR(40) NOT NULL DEFAULT 'custom',
  object_type VARCHAR(40) NULL,
  object_id INT UNSIGNED NULL,
  sort_order SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_mi_menu (menu_id, parent_id, sort_order),
  KEY idx_mi_object (link_type, object_type, object_id),
  CONSTRAINT fk_mi_menu FOREIGN KEY (menu_id) REFERENCES menus(id) ON DELETE CASCADE,
  CONSTRAINT fk_mi_parent FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS redirects (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  from_path VARCHAR(255) NOT NULL,
  to_path VARCHAR(255) NOT NULL,
  status_code SMALLINT UNSIGNED NOT NULL DEFAULT 301,
  hits INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  note VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_redirects_from (from_path)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  setting_key VARCHAR(80) NOT NULL,
  setting_value MEDIUMTEXT NULL,
  PRIMARY KEY (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_log (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NULL,
  action VARCHAR(80) NOT NULL,
  entity_type VARCHAR(40) NULL,
  entity_id INT UNSIGNED NULL,
  meta_json JSON NULL,
  ip VARCHAR(45) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_audit_created (created_at),
  KEY idx_audit_entity (entity_type, entity_id),
  KEY idx_audit_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS code_snippets (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(160) NOT NULL,
  type ENUM('html','css','javascript') NOT NULL DEFAULT 'html',
  code MEDIUMTEXT NOT NULL,
  placement ENUM('header','body_start','footer') NOT NULL DEFAULT 'header',
  scope ENUM('global','specific') NOT NULL DEFAULT 'global',
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  activated_once TINYINT(1) NOT NULL DEFAULT 0,
  priority INT NOT NULL DEFAULT 10,
  notes TEXT NULL,
  origin_key VARCHAR(80) NULL,
  last_error TEXT NULL,
  last_error_at DATETIME NULL,
  created_by INT UNSIGNED NULL,
  updated_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_snippet_origin (origin_key),
  KEY idx_snippets_active (is_active, placement, priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS snippet_targets (
  snippet_id INT UNSIGNED NOT NULL,
  target_type ENUM('page','cpt') NOT NULL,
  target_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (snippet_id, target_type, target_id),
  KEY idx_st_target (target_type, target_id),
  CONSTRAINT fk_st_snippet FOREIGN KEY (snippet_id) REFERENCES code_snippets(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS preview_tokens (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  token CHAR(64) NOT NULL,
  owner_type VARCHAR(20) NOT NULL DEFAULT 'page',
  owner_id INT UNSIGNED NOT NULL,
  snippet_id INT UNSIGNED NULL,
  expires_at DATETIME NOT NULL,
  created_by INT UNSIGNED NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_preview_token (token),
  KEY idx_preview_exp (expires_at),
  KEY idx_preview_snippet (snippet_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cache_keys (
  cache_key VARCHAR(190) NOT NULL,
  cache_value MEDIUMTEXT NOT NULL,
  expires_at DATETIME NULL,
  PRIMARY KEY (cache_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS not_found_log (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  path VARCHAR(255) NOT NULL,
  referrer VARCHAR(255) NULL,
  ip VARCHAR(45) NULL,
  hits INT UNSIGNED NOT NULL DEFAULT 1,
  last_hit_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_nf_path (path),
  KEY idx_nf_last (last_hit_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS backups (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  filename VARCHAR(190) NOT NULL,
  disk_path VARCHAR(255) NOT NULL,
  size_bytes BIGINT UNSIGNED NOT NULL DEFAULT 0,
  kind ENUM('manual','scheduled','pre-restore') NOT NULL DEFAULT 'manual',
  notes VARCHAR(255) NULL,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_backups_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS cron_runs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  task VARCHAR(80) NOT NULL,
  started_at DATETIME NOT NULL,
  finished_at DATETIME NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'ok',
  message VARCHAR(255) NULL,
  PRIMARY KEY (id),
  KEY idx_cron_task (task, started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
