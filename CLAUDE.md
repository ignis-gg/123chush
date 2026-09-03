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
