# Autonomy

Standing authorization for this project — act without asking for confirmation on:
- Editing/writing any theme, plugin, or config file in this repo
- `git add` / `commit` / `push` (any branch)
- Deploying the demo site (`wrangler pages deploy` → koval-legal-demo.pages.dev)
- `ddev` / `wp-cli` commands (via `sg docker -c "ddev ..."`)

Exception — still confirm first, every time, with an isolated confirmation step:
- Any `rm -rf`, `rmtree`, or bulk-delete of files/directories in this project.
  On 2026-09-02 the project directory was accidentally deleted this way; theme
  CSS/JS/PHP was lost and had to be partially rebuilt from the DB backup. This
  guardrail stays even though the rest of the workflow is fully autonomous.

# Frontend verification

This project needs the **Claude in Chrome browser extension** connected and
kept connected, every session — not optional here. On 2026-09-03, curl/grep
checks (checking HTML text and HTTP status codes) missed two real rendering
bugs in a row: a collapsed `.svc-grid` that made whole sections invisible,
and a broken CSS/JS asset path that unstyled the entire live site — both
only visible by actually looking at the rendered page. The user caught the
second one from a phone screenshot; that should be caught here first.

Before reporting any frontend/theme/CSS/deploy change as done: load the
`claude-in-chrome` skill and actually open the page(s) in the browser
(homepage, the specific page(s) changed, and the demo deploy URL once
pushed), at a few viewport widths (mobile ~375px, tablet ~768px, desktop
~1440px via `resize_window`), and look at the screenshots — not just check
HTTP status or grep page text. If the extension isn't connected, say so
explicitly and ask the user to connect it (chrome extension installed,
logged into claude.ai with the same account) rather than silently falling
back to a text-only check and calling the work verified.

Do **not** close the browser tab(s)/window after verifying — there is no
way to relaunch Chrome itself (only to control it once it's already
running), so closing it means the user has to manually reopen Chrome
before the extension can reconnect next time. Leave tabs open when done.
