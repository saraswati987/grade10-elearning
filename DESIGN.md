# Design & Architecture

## Modules

The app is two separate modules that share one database and one stylesheet, and
nothing else. They do **not** share a login.

| Module | Path | Roles | Owns |
|---|---|---|---|
| Portal | `/` | `student`, `teacher` | Studying, submitting, grading, subject content |
| Admin  | `/admin` | `admin` | Subjects (curriculum) and user accounts |

Session keys are namespaced so the two are genuinely independent:

- Portal: `$_SESSION['user_id'|'user_name'|'user_email'|'user_role']`
- Admin: `$_SESSION['admin_id'|'admin_name'|'admin_email']`

Signing in as a teacher grants no admin access, and signing out of one module
leaves the other alone. `admin/login.php` only accepts `role = 'admin'`;
`login.php` refuses admin accounts and points them at the admin panel.

### Who does what

- **Student** — browses subjects, reads materials, submits homework, takes timed
  quizzes, sees results.
- **Teacher** — publishes materials, posts and grades assignments, builds
  quizzes and their questions.
- **Admin** — creates/edits/deletes subjects, creates teacher/admin/student
  accounts, resets passwords, deletes accounts, and reviews school-wide totals.
  The admin module deliberately does not duplicate teaching tools.

## File map

```
config/db.php            PDO connection + BASE_URL (folder-name independent)
includes/auth_check.php  sessions, role gates, CSRF helpers  (both modules)
includes/upload.php      the only place user files reach disk (both modules)
includes/header.php      portal chrome        includes/footer.php
admin/includes/init.php  admin boot + gate, emits no output (so pages can redirect)
admin/includes/header.php  admin shell + sidebar + topbar   admin/includes/footer.php
assets/css/style.css     the entire design system
assets/js/main.js        progressive enhancement only
schema.sql               tables + seed data     setup_database.php  one-time installer
```

## Security rules applied

- Every state-changing action is `POST` + a CSRF token (`csrf_field()` /
  `csrf_verify()`). No destructive `GET` links remain.
- All SQL uses prepared statements; `PDO::ATTR_EMULATE_PREPARES` is off.
- `session_regenerate_id(true)` on every sign in.
- Uploads: extension allow-list, size limit, random filename, and an
  `uploads/.htaccess` that disables PHP execution in that folder.
- Database errors are logged, never echoed to the user.
- Result pages are scoped to the owning student; grading locks a submission.
- The last remaining admin account cannot be deleted.

## Visual direction

Calm institutional, not a SaaS dashboard: deep navy chrome on a warm cream
field, hairline borders instead of decorative shadows, real tables for real
data, one 140ms transition on interactive states.

**Typography: Poppins only.** Weight (400/500/600) and size carry the whole
hierarchy — there is no second family. Loaded from Google Fonts with `system-ui`
as fallback.

Palette, spacing (4px scale), and every component class live in
`assets/css/style.css` under `:root`. Nothing is styled inline except one
dynamic value (the result progress bar width).

## Known limitations

- Teachers are not scoped to specific subjects — any teacher can manage any
  subject's content. Fine for a single-grade school; add a `subject_teachers`
  table if that changes.
- Quiz attempts store the score, not the individual answers, so results cannot
  be reviewed question by question. Add an `attempt_answers` table if needed.
- Quiz timing is enforced server-side from a session timestamp with a 60s grace
  window; it is not a proctoring system.
