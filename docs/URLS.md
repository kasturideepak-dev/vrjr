# URLs, slugs, and redirects

All public URLs are lowercase, hyphenated, and use a trailing slash. The front controller is `public/index.php`. There are no `/pages/about.php` or `?id=` URLs.

## Patterns

| Kind | Pattern |
|---|---|
| Home | `/` |
| Page | `/{slug}/` — e.g. `/about/`, `/admissions/`, `/contact-us/` |
| CPT archive | `/{type}/` — e.g. `/courses/` |
| CPT entry | `/{type}/{entry}/` — e.g. `/courses/mpc-with-iit-jee/` |
| Blog index | `/blog/`, `/blog/page/2/` |
| Blog post | `/blog/{slug}/` |
| Blog category | `/blog/category/{slug}/` |
| Sitemap | `/sitemap.xml` |
| robots.txt | `/robots.txt` |

## Edge cases (handled)

- Duplicate slugs inside a type are auto-suffixed (`about-2`) or blocked with a warning.
- A page cannot take `/courses/` if that is a public post-type archive.
- Reserved: `admin`, `login`, `logout`, `preview`, `uploads`, `assets`, `assets-admin`, `api`, `cron`, `install`, `blog`, `sitemap.xml`, `robots.txt`.
- Changing a published slug writes a 301 from the old path.
- Trash is a soft-delete. Drafts and unpublished items 404 if guessed.
- 404 responses are real HTTP 404s and render the editable 404 page.
- `About` and `ABOUT` 301 to the lowercase canonical. Missing trailing slash 301s to the slash version.
- Filtered/paginated blog URLs keep a canonical tag on the clean archive.

## Seeded 301s (WordPress → this CMS)

| From | To |
|---|---|
| `/junior-college-for-mpc/` | `/courses/mpc-with-iit-jee/` |
| `/inter-college-for-bipc-in-hyderabad/` | `/courses/bipc-with-neet/` |
| `/neet-long-term/` | `/courses/neet-long-term/` |
| `/jee-long-term/` | `/courses/mpc-with-iit-jee/` |
| `/mpc-with-sat/` | `/courses/mpc-with-iit-jee/` |
| `/admissions-new/` | `/admissions/` |
| `/category/business/` | `/blog/category/business/` |

These (and any later slug changes) are editable under **Redirects**. Hits are counted there.

Unchanged indexed URLs, recreated as pages:

`/about/`, `/campuses/`, `/gallery/`, `/blog/`, `/contact-us/`, `/team-details/`

## Menus

Header/footer items can link to a **page**, **post type archive**, or **entry by ID**. The href is resolved at render time, so a slug change does not require a menu edit.

## Preview

Drafts get a signed `/…/?preview={64-char-token}` URL, stored hashed in `preview_tokens`, expiry 1 hour. Tokens are not guessable sequential IDs.
