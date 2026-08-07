# Design System — Grade 10 E-Learning

Ground truth for the classic-academic visual redesign. Documents what was
actually built (`assets/css/style.css`), not aspirational goals.

## Direction

Operate mode: a task-completion tool for students, teachers, and a school
admin — not a marketing site. Classic academic tone: navy + cream/warm
neutral, serif headings, native form/table conventions. No dark mode, no
glassmorphism, no SaaS-dashboard gloss.

## Palette (Restrained: neutrals + one accent)

| Token | Hex | Use |
|---|---|---|
| `--color-navy-900` | `#14213d` | Headings, nav/footer background |
| `--color-navy-800` | `#1c2d4f` | Secondary button text |
| `--color-navy-700` | `#24406e` | Primary accent (buttons, links, focus) |
| `--color-navy-600` | `#2d4d82` | Accent hover state |
| `--color-navy-100` | `#e4e9f1` | Focus ring tint, badge tint |
| `--color-cream-000` | `#fbf8f2` | Page background |
| `--color-cream-100` | `#f4efe3` | Card/section fill, empty states |
| `--color-cream-200` | `#ece4d2` | Table zebra rows |
| `--color-ink` | `#221f1a` | Body text (contrast ~14:1 on cream) |
| `--color-ink-muted` | `#5c584f` | Secondary text (contrast ~6:1) |
| `--color-border` / `-strong` | `#d9cfb8` / `#b9ac8b` | Hairline borders |
| Success | `#29623b` / bg `#e6f0e6` | Graded, passed states |
| Danger | `#8c2c2c` / bg `#f6e8e6` | Errors, delete actions |
| Warning | `#8a5a1a` / bg `#f7edd9` | Pending, due-soon |
| Info | `#2a4d75` / bg `#e6ecf3` | Neutral notices, material-type badges |

All text/background pairs verified ≥4.5:1 (body) / ≥3:1 (large text).

## Type

- **Headings (h1–h4): Newsreader** (serif, weights 500/600/700) — chosen for
  an academic, printed-record feel without tipping into an AI-slop cliché
  serif (Playfair/Cormorant); Newsreader is built for on-screen text at
  small sizes, which headings here often are (table section titles, card
  titles).
- **Body/UI: Inter** — highly legible sans for forms, tables, nav, buttons.
- Loaded via Google Fonts `<link>` in both header includes (pragmatic
  fallback since there's no build step); falls back to
  `Georgia/system-ui` if offline.
- Scale: `--text-xs` 13px → `--text-3xl` 36px, defined as CSS custom
  properties in `assets/css/style.css`.

## Spacing

4px-based scale: `--space-1` (4px) through `--space-8` (64px). More space
above headings (`h2` gets `--space-7` margin-top) than below, per the
craft-floor rule. Body copy measure capped at `--measure: 70ch`.

## Components

- **Buttons** — `.btn` + modifier (`.btn-primary` navy fill, `.btn-secondary`
  outline, `.btn-danger`, `.btn-success`, `.btn-sm`, `.btn-block`). One
  authored transition (140ms background/border/color) on hover; `:disabled`
  dims to 55% opacity; `:focus-visible` gets a 2px navy outline everywhere
  via the global `:focus-visible` rule.
- **Cards** — `.card`: white surface, 1px hairline border, real
  offset+blur shadow (`--shadow-card`), not a flat color halo.
- **Forms** — `.form-group` / `.form-label` / `.form-control`. Focus state:
  navy border + 3px navy-tinted ring. Error state: `.form-group.has-error`
  turns the control's border/ring danger-colored, paired with `.field-error`
  text under the field (login/register/assignment forms use this instead of
  a raw exception dump).
- **Tables** — `.table-container` > `<table>`: uppercase small-caps-style
  header row on a cream fill, 2px bottom rule, hairline row dividers, zebra
  striping, right-aligned numeric columns via `.num`. Used for every real
  data list (submissions, courses, students, quizzes) instead of a card
  grid pretending to be a table.
- **Badges** — `.badge-pending/-graded/-neutral/-overdue/-pdf/-text/-link`:
  pill shape, tinted background + matching border, no bare color-only chips.
- **Tabs** — `.tabs-header` / `.tab-btn` / `.tab-pane` on `subject_detail.php`;
  bottom-border indicator, JS toggle from the existing `assets/js/main.js`
  (untouched, class/id contract preserved).
- **Empty states** — `.empty-state`: dashed border, cream fill, plain
  sentence ("No assignments yet.", "No submissions yet.") — no icon soup.
- **Nav** — `.navbar` (site) and `.admin-sidebar` (admin): navy field,
  underline/left-border indicator on hover/active, role-aware links
  server-rendered from session state (unchanged logic).

## States implemented

- **Hover**: buttons, nav links, table rows, subject list rows.
- **Focus-visible**: global 2px navy outline; form controls additionally
  get a soft navy ring.
- **Disabled**: buttons dim to 55% opacity, cursor `not-allowed`.
- **Error**: `.alert-danger` banners + inline `.field-error` on login,
  register, and assignment submission forms.
- **Empty**: dashed-border empty-state block on every list/table that can
  be empty (assignments, materials, quizzes, submissions, students,
  courses).
- **Loading**: not applicable — this is a server-rendered PHP app with no
  async requests; page navigation is the only "loading" state and is
  handled by the browser.

## Motion

One authored motion moment, applied consistently: a 140ms ease transition
on background-color/border-color/color for buttons, nav links, table rows,
and subject rows. No scroll-triggered fades, no per-element animation.

## What changed vs. what didn't

- Rewrote: `assets/css/style.css` (full replacement), `includes/header.php`,
  `includes/footer.php`, every root-level page, `admin/includes/header.php`,
  `admin/includes/sidebar.php`, `admin/includes/footer.php` (was empty),
  `admin/login.php`, `admin/index.php`, `admin/course.php`.
- Removed: FontAwesome CDN dependency, Tailwind/Alpine.js admin template
  (`bundle.js`, utility classes) — admin now shares the one stylesheet with
  the student/teacher site. `admin/style.css` (the old 3792-line compiled
  Tailwind file) is no longer referenced by any page; left in place
  un-deleted since removing files wasn't requested.
- Untouched by design: `setup_database.php` (dev-only setup wizard, not in
  the enumerated page list) still uses the old dark theme — flagged, not
  redesigned, since it's a one-time bootstrap utility rather than a product
  surface.
- Untouched entirely: all PHP business logic — queries, session/auth checks
  (`includes/auth_check.php`), file upload validation (extension/size
  checks + random filenames in `manage_materials.php` and
  `submit_assignment.php`), quiz scoring, grading. Only markup/classes and
  CSS changed.

## Verification note

No PHP runtime was available in this environment, so this was not
browser-verified. Verification consisted of: re-reading every changed file
end-to-end for PHP-in-HTML correctness, `grep`-based open/close tag balance
checks on every `<div>`/`<form>` per file (and across the three-file admin
chrome as a set, since it opens/closes across files by design), a
project-wide sweep for leftover `fa-solid`/old class names/inline dark-theme
styles, and running the `impeccable` detect script (`node
~/.claude/skills/impeccable/scripts/detect.mjs --json` from the project
root), which returned no findings.
