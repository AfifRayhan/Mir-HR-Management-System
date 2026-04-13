# Superpowers for Antigravity

You have superpowers.

This profile adapts Superpowers workflows for Antigravity with strict single-flow execution.

## Core Rules

1. Prefer local skills in `.agent/skills/<skill-name>/SKILL.md`.
2. Execute one core task at a time with `task_boundary`.
3. Use `browser_subagent` only for browser automation tasks.
4. Track checklist progress in `task.md` (table-only live tracker in App Data).
5. Keep changes scoped to the requested task and verify before completion claims.
6. **Environment**: Always use `php.exe` instead of `php` for all artisan and composer commands (e.g., `php.exe artisan migrate`).
7. **Tech Stack**: Laravel 12, Bootstrap 5, Vanilla JavaScript, Vite.
8. **Coding Standards**: PSR-12, Eloquent models, Hybrid Service layer pattern.
    - **Note**: Use `app/Services/` for complex domain math and multi-model processing. Standard CRUD and simple model syncing may reside in Controllers (as seen in `EmployeeController`).

## Tool Translation Contract

When source skills reference legacy tool names, use these Antigravity equivalents:

- Legacy assistant/platform names -> `Antigravity`
- `Task` tool -> `browser_subagent` for browser tasks, otherwise sequential `task_boundary`
- `Skill` tool -> `view_file ~/.gemini/skills/<skill-name>/SKILL.md` (or project-local `.agent/skills/<skill-name>/SKILL.md`)
- `TodoWrite` -> update `<project-root>/docs/plans/task.md` task list
- File operations -> `view_file`, `write_to_file`, `replace_file_content`, `multi_replace_file_content`
- Directory listing -> `list_dir`
- Code structure -> `view_file_outline`, `view_code_item`
- Search -> `grep_search`, `find_by_name`
- Shell -> `run_command`
- Web fetch -> `read_url_content`
- Web search -> `search_web`
- Image generation -> `generate_image`
- User communication during tasks -> `notify_user`
- MCP tools -> `mcp_*` tool family

## Skill Loading

- First preference: project skills at `.agent/skills`.
- Second preference: user skills at `~/.gemini/skills`.
- If both exist, project-local skills win for this profile.
- Optional parity assets may exist at `.agent/workflows/*` and `.agent/agents/*` as entrypoint shims/reference profiles.
- These assets do not change the strict single-flow execution requirements in this file.

## Project Structure

- **Models**: `app/Models/` (Eloquent relations defined here).
- **Services**: `app/Services/` (Business logic, injected into controllers).
- **Controllers**: `app/Http/Controllers/` (Organized by modules: Api, Auth, Personnel, Settings, TeamLead).
- **Views**: `resources/views/` (Blade components and templates).
- **Migrations**: `database/migrations/` (Schema definitions).
- **Routes**: `routes/web.php` & `routes/api.php`.

## Single-Flow Execution Model

- Do not dispatch multiple coding agents in parallel.
- Decompose large work into ordered, explicit steps.
- Keep exactly one active task at a time in `<project-root>/docs/plans/task.md`.
- If browser work is required, isolate it in a dedicated browser step.

## Verification Discipline

Before saying a task is done:

1. Run the relevant verification command(s).
2. Confirm exit status and key output.
3. Update `<project-root>/docs/plans/task.md`.
4. Report evidence, then claim completion.
