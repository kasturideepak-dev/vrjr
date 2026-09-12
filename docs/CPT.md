# Custom Post Types — staff guide

Nothing about courses, events, or “AI Program” is hardcoded. Every type is created in the dashboard. **Courses** is the working example, built the same way you will build the next one.

## Create a type (example: AI Program)

1. Sign in as Super Admin.
2. Go to **Settings → Post types → Add new post type**.
3. Name: `AI Program`. The URL prefix auto-fills as `ai-program` (edit if you want). Archive URL will be `/ai-program/`.
4. Singular name: `AI Program`.
5. Keep **Public** and **Archive** on if it should have listing + detail pages.
6. Template: **Fields + page builder** (recommended). Fields feed the listing cards; the builder uses the approved frontend sections for a full landing page.
7. Add fields, for example:
   - Duration — text
   - Eligibility — textarea (required)
   - Highlights — repeater (`Highlight|text` in Options)
   - Cover image — image (or use Featured image)
8. Save.

The CMS now has:

- Sidebar item **AI Program**
- List + add/edit screens generated from those fields
- Archive at `/ai-program/`
- Entry URLs at `/ai-program/{entry-slug}/`

## Add an entry

1. **Content → AI Program → Add AI Program**.
2. Title: `AI for Intermediate`. Slug suggests `ai-for-intermediate` (edit before publish; it must be unique in this type).
3. Fill fields, excerpt, featured image.
4. Optionally add landing sections (Hero, Feature grid, FAQ, Enquire…).
5. Set status to **Published** (or **Scheduled** + a datetime).
6. The page is live at `/ai-program/ai-for-intermediate/` with no code change.

## Changing a prefix after entries exist

If you rename `ai-program` → `ai-programmes`, the CMS writes 301s from every old `/ai-program/{slug}/` to the new prefix. Google and old menu links keep working.

## Courses (the seeded example)

| Entry | New URL | Old WordPress URL (301) |
|---|---|---|
| MPC with IIT-JEE | `/courses/mpc-with-iit-jee/` | `/junior-college-for-mpc/` |
| BiPC with NEET | `/courses/bipc-with-neet/` | `/inter-college-for-bipc-in-hyderabad/` |
| NEET Long Term | `/courses/neet-long-term/` | `/neet-long-term/` |

Campuses and Faculty are also post types, but **not public** — they feed the homepage grids and the `/campuses/` and `/team-details/` pages. You can turn a type public later if you want individual campus landing pages.

## What staff never need a developer for

- New programmes, events, notices, “AI Program”, scholarships, etc.
- New fields on an existing type
- New landing-page sections on an entry
- Menus that point at an entry by ID (the URL updates if the slug changes)
